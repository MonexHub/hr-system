<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Payee;
use Filament\Notifications\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PayrollService
{
    /**
     * Save benefit records for a payroll
     */
    protected function saveBenefits(Payroll $payroll, Employee $employee, float $gross): void
    {
        // Clear any existing benefits for this payroll
        $payroll->payrollBenefits()->delete();

        foreach ($employee->employeeBenefits()->active()->with('benefit')->get() as $empBenefit) {
            $type = $empBenefit->type ?? $empBenefit->benefit->type;
            $value = $empBenefit->value ?? $empBenefit->benefit->value;
            $amount = $type === 'percentage' ? ($gross * $value / 100) : $value;

            $payroll->payrollBenefits()->create([
                'benefit_id' => $empBenefit->benefit_id,
                'name' => $empBenefit->benefit->name,
                'type' => $type,
                'value' => $value,
                'amount' => $amount,
                'employee_id' => $employee->id,
            ]);
        }
    }

    /**
     * Save the calculated deductions to PayrollDeduction records
     */
    protected function saveDeductions(Payroll $payroll, Employee $employee, float $gross, float $paye, float $loanRepayment): void
    {
        // Clear any existing deductions for this payroll
        $payroll->payrollDeductions()->delete();

        // Save PAYE tax deduction
        if ($paye > 0) {
            // Find a system deduction for PAYE or create one if needed
            $payeDeductionId = $this->findOrCreateSystemDeduction('PAYE Tax');

            $payroll->payrollDeductions()->create([
                'deduction_id' => $payeDeductionId, // Use the deduction ID instead of null
                'name' => 'PAYE Tax',
                'type' => 'calculated',
                'value' => 0,
                'amount' => $paye,
                'employee_id' => $employee->id,
            ]);
        }

        // Save loan repayment deduction
        if ($loanRepayment > 0) {
            // Find a system deduction for loan repayment or create one if needed
            $loanDeductionId = $this->findOrCreateSystemDeduction('Loan Repayment');

            $payroll->payrollDeductions()->create([
                'deduction_id' => $loanDeductionId, // Use the deduction ID instead of null
                'name' => 'Loan Repayment',
                'type' => 'fixed',
                'value' => $loanRepayment,
                'amount' => $loanRepayment,
                'employee_id' => $employee->id,
            ]);
        }

        // Save other deductions
        foreach ($employee->employeeDeductions()->active()->with('deduction')->get() as $empDeduction) {
            $type = $empDeduction->type ?? $empDeduction->deduction->type;
            $value = $empDeduction->value ?? $empDeduction->deduction->value;
            $amount = $type === 'percentage' ? ($gross * $value / 100) : $value;

            $payroll->payrollDeductions()->create([
                'deduction_id' => $empDeduction->deduction_id,
                'name' => $empDeduction->deduction->name,
                'type' => $type,
                'value' => $value,
                'amount' => $amount,
                'employee_id' => $employee->id,
            ]);
        }
    }

    /**
     * Find or create a system deduction for standard items
     */
    protected function findOrCreateSystemDeduction(string $name): int
    {
        // We'll assume you have a Deduction model - adjust the namespace if needed
        $deduction = \App\Models\Deduction::firstOrCreate(
            ['name' => $name],
            [
                'type' => 'system',
                'is_active' => true,
                'description' => "System generated {$name} deduction"
            ]
        );

        return $deduction->id;
    }

    /**
     * Generate or update the payroll record for an employee
     */
    public function generatePayrollForEmployee(Employee $employee, Carbon $period): Payroll
    {
        $gross = $employee->salary;

        // Apply benefits
        $totalBenefits = $this->calculateBenefits($employee, $gross);

        // Apply deductions
        $totalDeductions = $this->calculateDeductions($employee, $gross);

        // Calculate PAYE
        $paye = $this->calculatePAYE($gross);
        $totalDeductions += $paye;

        // Loans
        $loanRepayment = $this->calculateLoanRepayment($employee, $period);
        $totalDeductions += $loanRepayment;

        // Final net pay
        $netPay = $gross + $totalBenefits - $totalDeductions;

        // Create or update the payroll record
        $payroll = Payroll::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'period' => $period->startOfMonth()
            ],
            [
                'gross_salary' => $gross,
                'total_benefits' => $totalBenefits,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netPay,
                'net_pay' => $netPay, // Make sure both net_salary and net_pay are set to the same value
                'status' => 'pending',
            ]
        );

        // Save the detailed benefits and deductions
        $this->saveBenefits($payroll, $employee, $gross);
        $this->saveDeductions($payroll, $employee, $gross, $paye, $loanRepayment);

        return $payroll;
    }

    /**
     * Generate payroll records for all active employees
     */
    public function generateForAll(Carbon $period): void
    {
        DB::transaction(function () use ($period) {
            $employees = Employee::active()->get();

            foreach ($employees as $employee) {
                try {
                    $this->generatePayrollForEmployee($employee, $period);
                } catch (\Exception $e) {
                    Log::error("Failed to generate payroll for employee {$employee->id}: " . $e->getMessage());
                    // Continue with next employee instead of aborting the entire process
                }
            }
        });
    }

    /**
     * Calculate total deductions for an employee
     */
    protected function calculateDeductions(Employee $employee, float $gross): float
    {
        $total = 0;

        foreach ($employee->employeeDeductions()->active()->with('deduction')->get() as $empDeduction) {
            $type = $empDeduction->type ?? $empDeduction->deduction->type;
            $value = $empDeduction->value ?? $empDeduction->deduction->value;

            $total += $type === 'percentage' ? ($gross * $value / 100) : $value;
        }

        return $total;
    }

    /**
     * Calculate total benefits for an employee
     */
    protected function calculateBenefits(Employee $employee, float $gross): float
    {
        $total = 0;

        foreach ($employee->employeeBenefits()->active()->with('benefit')->get() as $empBenefit) {
            $type = $empBenefit->type ?? $empBenefit->benefit->type;
            $value = $empBenefit->value ?? $empBenefit->benefit->value;

            $total += $type === 'percentage' ? ($gross * $value / 100) : $value;
        }

        return $total;
    }

    /**
     * Calculate total loan repayment amount for an employee
     */
    protected function calculateLoanRepayment(Employee $employee, Carbon $period): float
    {
        return $employee->employeeLoans()
            ->whereIn('status', ['in_repayment'])
            ->where('repayment_start_date', '<=', $period->startOfMonth())
            ->sum('monthly_installment');
    }

    /**
     * Calculate PAYE tax for a given gross salary
     */
    protected function calculatePAYE(float $gross): float
    {
        $bracket = Payee::where('min_amount', '<=', $gross)
            ->where(function ($query) use ($gross) {
                $query->whereNull('max_amount')
                    ->orWhere('max_amount', '>=', $gross);
            })
            ->orderBy('min_amount', 'desc')
            ->first();

        return $bracket
            ? ($gross - $bracket->min_amount) * ($bracket->rate / 100) + $bracket->fixed_amount
            : 0;
    }

    /**
     * Process a single payroll payment
     */
    public function processPayment(Payroll $payroll): bool
    {
        $employee = $payroll->employee;

        $financial = $employee->latestFinancial;

        if (!$financial) {
            throw new \Exception("No financial information found for employee {$employee->full_name}");
        }

        if (!$financial->bank_name) {
            throw new \Exception("Bank name is missing for employee {$employee->full_name}");
        }

        if (!$financial->account_number) {
            throw new \Exception("Account number is missing for employee {$employee->full_name}");
        }

        // Here you'd integrate with a bank API or simulate a transfer
        $reference = strtoupper('TXN' . uniqid());

        // Example: simulate response
        $response = [
            'bank_name' => $financial->bank_name,
            'account_number' => $financial->account_number,
            'amount' => $payroll->net_pay,
            'reference' => $reference,
            'status' => 'completed', // or 'failed'
        ];

        // Save payment log
        $payroll->paymentLog()->create([
            'reference_number' => $reference,
            'amount' => $payroll->net_pay,
            'status' => $response['status'],
            'response_data' => $response,
            'employee_id' => $employee->id,
        ]);

        // Update payroll status if successful
        if ($response['status'] === 'completed') {
            $payroll->update(['status' => 'paid']);
        } elseif ($response['status'] === 'failed') {
            $payroll->update(['status' => 'pending']);
        }

        return $response['status'] === 'completed';
    }


    /**
     * Process all pending payrolls
     */
    public function processAllPendingPayments(): array
    {
        $pendingPayrolls = Payroll::where('status', 'pending')
            ->whereDoesntHave('paymentLog')
            ->get();

        $results = [];

        foreach ($pendingPayrolls as $payroll) {
            try {
                $success = $this->processPayment($payroll);
                $results[] = [
                    'payroll_id' => $payroll->id,
                    'employee' => $payroll->employee->full_name,
                    'success' => $success,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'payroll_id' => $payroll->id,
                    'employee' => $payroll->employee->full_name,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Generate a PDF payslip for a single payroll record
     */
    public function generatePayslipPDF(Payroll $payroll): BinaryFileResponse
    {
        // Enable error reporting for debugging
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        // Load all necessary relationships
        $payroll->load([
            'employee.latestFinancial',
            'employee.department',
            'payrollBenefits',
            'payrollDeductions'
        ]);

        // Log payroll data for debugging
        Log::info('Payroll Data:', [
            'id' => $payroll->id,
            'employee_id' => $payroll->employee_id,
            'period' => $payroll->period,
            'gross_salary' => $payroll->gross_salary,
            'total_benefits' => $payroll->total_benefits,
            'total_deductions' => $payroll->total_deductions,
            'net_salary' => $payroll->net_salary,
            'net_pay' => $payroll->net_pay
        ]);

        // Check if employee relationship is loaded
        if (!$payroll->employee) {
            Log::error('Employee relationship not loaded for payroll ID: ' . $payroll->id);
            throw new \Exception("Employee data not found for payroll");
        }

        // FIX: Ensure net_salary and net_pay are consistent and never zero
        // If one has value and the other doesn't, use the available value
        if (($payroll->net_salary <= 0 || $payroll->net_salary === null) && $payroll->net_pay > 0) {
            Log::warning('Fixing net_salary with net_pay value for payroll ID: ' . $payroll->id);
            // Update the database record as well as the loaded model
            $payroll->net_salary = $payroll->net_pay;
            $payroll->save(['net_salary']);
        }

        if (($payroll->net_pay <= 0 || $payroll->net_pay === null) && $payroll->net_salary > 0) {
            Log::warning('Fixing net_pay with net_salary value for payroll ID: ' . $payroll->id);
            // Update the database record as well as the loaded model
            $payroll->net_pay = $payroll->net_salary;
            $payroll->save(['net_pay']);
        }

        // Initialize empty collections for benefits and deductions if they're null
        if (!$payroll->payrollBenefits) {
            Log::warning('payrollBenefits relation is null, creating empty collection');
            $payroll->setRelation('payrollBenefits', collect([]));
        }

        if (!$payroll->payrollDeductions) {
            Log::warning('payrollDeductions relation is null, creating empty collection');
            $payroll->setRelation('payrollDeductions', collect([]));
        }

        // Warn about zero gross salary
        if ($payroll->gross_salary <= 0) {
            Log::warning('Gross salary is zero or negative: ' . $payroll->gross_salary);
        }

        // Get department name with fallback
        $department = $payroll->employee->department ? $payroll->employee->department->name : 'N/A';

        // Create view data for the PDF
        $viewData = [
            'payroll' => $payroll,
            'employee' => $payroll->employee,
            'department' => $department,
            'payrollBenefits' => $payroll->payrollBenefits,
            'payrollDeductions' => $payroll->payrollDeductions,
            'period' => $payroll->period,
            'grossSalary' => $payroll->gross_salary,
            'netSalary' => max($payroll->net_salary, $payroll->net_pay), // Use the maximum value to avoid zeros
            'totalBenefits' => $payroll->total_benefits,
            'totalDeductions' => $payroll->total_deductions
        ];

        try {
            // Generate the PDF
            $pdf = Pdf::loadView('payslips.payslip', $viewData)->setPaper('A4', 'portrait');

            // Create filename based on employee code and period
            $filename = 'Payslip_' . $payroll->employee->employee_code . '_' . $payroll->period->format('F_Y') . '.pdf';

            // Create temp directory if it doesn't exist
            $tempPath = storage_path('app/temp');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }

            // Full path to the PDF file
            $pdfPath = $tempPath . '/' . $filename;

            // Save the PDF
            file_put_contents($pdfPath, $pdf->output());

            // Verify the file was created
            if (!file_exists($pdfPath)) {
                throw new \Exception("Failed to create PDF file at: $pdfPath");
            }

            Log::info('PDF generated successfully at: ' . $pdfPath);

            // Return the file for download
            return response()->download($pdfPath, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            // Log any errors that occur
            Log::error('PDF generation failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Generate multiple payslips as a single PDF or ZIP file
     */
    public function generateBulkPayslipsPDF($records): BinaryFileResponse
    {
        // Convert collection to array of IDs if needed
        if ($records instanceof Collection) {
            $payrolls = $records;
        } else {
            $payrolls = Payroll::whereIn('id', collect($records)->pluck('id'))->get();
        }

        // Make sure temp directory exists
        $tempPath = storage_path('app/temp');
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        // If only one payslip, just return that
        if ($payrolls->count() === 1) {
            return $this->generatePayslipPDF($payrolls->first());
        }

        // Generate individual PDFs
        $files = [];
        foreach ($payrolls as $payroll) {
            try {
                // Load the data
                $payroll->load([
                    'employee.latestFinancial',
                    'employee.department',
                    'payrollBenefits',
                    'payrollDeductions'
                ]);

                // Fix net_salary/net_pay consistency issues
                if (($payroll->net_salary <= 0 || $payroll->net_salary === null) && $payroll->net_pay > 0) {
                    $payroll->net_salary = $payroll->net_pay;
                    $payroll->save(['net_salary']);
                }
                if (($payroll->net_pay <= 0 || $payroll->net_pay === null) && $payroll->net_salary > 0) {
                    $payroll->net_pay = $payroll->net_salary;
                    $payroll->save(['net_pay']);
                }

                // Initialize empty collections if needed
                if (!$payroll->payrollBenefits) {
                    $payroll->setRelation('payrollBenefits', collect([]));
                }
                if (!$payroll->payrollDeductions) {
                    $payroll->setRelation('payrollDeductions', collect([]));
                }

                // Department fallback
                $department = $payroll->employee->department ? $payroll->employee->department->name : 'N/A';

                // Create view data
                $viewData = [
                    'payroll' => $payroll,
                    'employee' => $payroll->employee,
                    'department' => $department,
                    'payrollBenefits' => $payroll->payrollBenefits,
                    'payrollDeductions' => $payroll->payrollDeductions,
                    'period' => $payroll->period,
                    'grossSalary' => $payroll->gross_salary,
                    'netSalary' => max($payroll->net_salary, $payroll->net_pay),
                    'totalBenefits' => $payroll->total_benefits,
                    'totalDeductions' => $payroll->total_deductions
                ];

                // Generate the PDF
                $pdf = Pdf::loadView('payslips.payslip', $viewData)->setPaper('A4', 'portrait');
                $filename = 'Payslip_' . $payroll->employee->employee_code . '_' . $payroll->period->format('F_Y') . '.pdf';
                $pdfPath = $tempPath . '/' . $filename;

                file_put_contents($pdfPath, $pdf->output());
                $files[] = $pdfPath;

            } catch (\Exception $e) {
                Log::error('Failed to generate payslip for employee ID: ' . $payroll->employee_id . ': ' . $e->getMessage());
            }
        }

        // If we have multiple files, create a ZIP archive
        if (count($files) > 1) {
            $zipPath = $tempPath . '/Payslips_' . now()->format('Y_m_d_H_i_s') . '.zip';
            $zip = new \ZipArchive();

            if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
                foreach ($files as $file) {
                    $zip->addFile($file, basename($file));
                }
                $zip->close();

                // Delete individual PDF files
                foreach ($files as $file) {
                    @unlink($file);
                }

                return response()->download($zipPath, basename($zipPath), [
                    'Content-Type' => 'application/zip',
                ])->deleteFileAfterSend(true);
            } else {
                throw new \Exception("Failed to create ZIP file");
            }
        } elseif (count($files) === 1) {
            // If we have just one file, return it
            return response()->download($files[0], basename($files[0]), [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        } else {
            throw new \Exception("No payslips could be generated");
        }
    }
    /**
     * Get a summary of the employee's financials
     */
    public function getFinancialSummary(Employee $employee, ?Carbon $period = null): array
    {
        if (!$period) {
            return $this->getLifetimeFinancialSummary($employee);
        }

        $gross = $employee->salary;
        $totalBenefits = $this->calculateBenefits($employee, $gross);
        $paye = $this->calculatePAYE($gross);
        $loanRepayment = $this->calculateLoanRepayment($employee, $period);
        $totalDeductions = $this->calculateDeductions($employee, $gross) + $paye + $loanRepayment;
        $net = $gross + $totalBenefits - $totalDeductions;

        $activeLoans = $employee->employeeLoans()
            ->where('status', 'in_repayment')
            ->where('repayment_start_date', '<=', $period->startOfMonth())
            ->get();

        $pendingLoans = $employee->employeeLoans()
            ->where('status', 'pending')
            ->get();

        $pendingRepayments = $activeLoans->sum('monthly_installment');

        return [
            'gross_salary' => round($gross, 2),
            'total_benefits' => round($totalBenefits, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_salary' => round($net, 2),
            'loan_summary' => [
                'active_loans_count' => $activeLoans->count(),
                'pending_loans_count' => $pendingLoans->count(),
                'pending_repayments_total' => round($pendingRepayments, 2),
                'active_loans' => $activeLoans->map->only(['id', 'amount_approved', 'monthly_installment', 'repayment_start_date', 'status']),
                'pending_loans' => $pendingLoans->map->only(['id', 'amount_requested', 'status']),
            ],
        ];
    }

    /**
     * Get lifetime financial summary for the employee
     */
    public function getLifetimeFinancialSummary(Employee $employee): array
    {
        $gross = $employee->salary;

        $allPayrolls = Payroll::where('employee_id', $employee->id)->get();

        $totalGross = $allPayrolls->sum('gross_salary');
        $totalBenefits = $allPayrolls->sum('total_benefits');
        $totalDeductions = $allPayrolls->sum('total_deductions');
        $net = $allPayrolls->sum('net_pay');

        $allLoans = $employee->employeeLoans()->get();
        $activeLoans = $allLoans->where('status', 'in_repayment');
        $pendingLoans = $allLoans->where('status', 'pending');
        $repaidLoans = $allLoans->where('status', 'paid');

        $pendingRepayments = $activeLoans->sum('monthly_installment');

        return [
            'gross_salary_total' => round($totalGross, 2),
            'total_benefits' => round($totalBenefits, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_salary_total' => round($net, 2),
            'loan_summary' => [
                'total_loans' => $allLoans->count(),
                'active_loans_count' => $activeLoans->count(),
                'pending_loans_count' => $pendingLoans->count(),
                'repaid_loans_count' => $repaidLoans->count(),
                'pending_repayments_total' => round($pendingRepayments, 2),
                'active_loans' => $activeLoans->map->only(['id', 'amount_approved', 'monthly_installment', 'repayment_start_date', 'status']),
                'pending_loans' => $pendingLoans->map->only(['id', 'amount_requested', 'status']),
                'repaid_loans' => $repaidLoans->map->only(['id', 'amount_approved', 'status']),
            ],
        ];
    }

    /**
     * Get financial summary for the company or filtered by department/employee/year/month
     */
    public function getCompanyFinancialSummary(array $filters = []): array
    {
        $query = Payroll::query()->with('employee');

        if (!empty($filters['department_id'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('period', $filters['year']);
        }

        if (!empty($filters['month'])) {
            $query->whereMonth('period', $filters['month']);
        }

        $payrolls = $query->get();

        $totalGross = $payrolls->sum('gross_salary');
        $totalBenefits = $payrolls->sum('total_benefits');
        $totalDeductions = $payrolls->sum('total_deductions');
        $netSalary = $payrolls->sum('net_pay');

        $employeeIds = $payrolls->pluck('employee_id')->unique();

        $allLoans = \App\Models\EmployeeLoan::whereIn('employee_id', $employeeIds)->get();
        $activeLoans = $allLoans->where('status', 'in_repayment');
        $pendingLoans = $allLoans->where('status', 'pending');
        $repaidLoans = $allLoans->where('status', 'paid');
        $pendingRepayments = $activeLoans->sum('monthly_installment');

        return [
            'gross_salary_total' => round($totalGross, 2),
            'total_benefits' => round($totalBenefits, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_salary_total' => round($netSalary, 2),
            'loan_summary' => [
                'total_loans' => $allLoans->count(),
                'active_loans_count' => $activeLoans->count(),
                'pending_loans_count' => $pendingLoans->count(),
                'repaid_loans_count' => $repaidLoans->count(),
                'pending_repayments_total' => round($pendingRepayments, 2),
                'active_loans' => $activeLoans->map->only(['id', 'amount_approved', 'monthly_installment', 'repayment_start_date', 'status']),
                'pending_loans' => $pendingLoans->map->only(['id', 'amount_requested', 'status']),
                'repaid_loans' => $repaidLoans->map->only(['id', 'amount_approved', 'status']),
            ],
        ];
    }
}
