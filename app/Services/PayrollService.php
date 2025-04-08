<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Payee;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PayrollService
{
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

        return Payroll::updateOrCreate(
            ['employee_id' => $employee->id, 'period' => $period->startOfMonth(),'employee'=> $employee],
            [
                'gross_salary' => $gross,
                'total_benefits' => $totalBenefits,
                'total_deductions' => $totalDeductions,
                'net_salary' => $gross,
                'net_pay' => $netPay,
                'status' => 'pending',
            ]
        );
    }

    public function generateForAll(Carbon $period): void
    {
        DB::transaction(function () use ($period) {
            $employees = Employee::active()->get();

            foreach ($employees as $employee) {
                $this->generatePayrollForEmployee($employee, $period);
            }
        });
    }

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

    protected function calculateLoanRepayment(Employee $employee, Carbon $period): float
    {
        return $employee->employeeLoans()
            ->whereIn('status', ['in_repayment'])
            ->where('repayment_start_date', '<=', $period->startOfMonth())
            ->sum('monthly_installment');
    }

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

        if (!$financial || !$financial->bank_name || !$financial->account_number) {
            throw new \Exception("Missing bank details for employee {$employee->full_name}");
        }

        // Here you’d integrate with a bank API or simulate a transfer
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

    public function generatePayslipPDF(Payroll $payroll): BinaryFileResponse
    {
        $payroll->load(['employee.latestFinancial', 'benefits', 'deductions']);

        $pdf = Pdf::loadView('payslips.payslip', [
            'payroll' => $payroll
        ])->setPaper('A4', 'portrait');

        $filename = 'Payslip_' . $payroll->employee->employee_code . '_' . $payroll->period->format('F_Y') . '.pdf';

        // Stream it directly
        $tempPath = storage_path('app/temp/' . $filename);
        Storage::put('temp/' . $filename, $pdf->output());

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }
}
