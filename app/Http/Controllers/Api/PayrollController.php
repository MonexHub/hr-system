<?php

namespace App\Http\Controllers\Api;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Services\PayrollService;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Payroll;

class PayrollController extends Controller
{
    protected PayrollService $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }


    public function index()
    {
        $payrolls = Payroll::with(['employee', 'deductions', 'benefits'])
            ->orderBy('period', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Payroll records retrieved successfully',
            'data' => $payrolls,
        ]);
    }

    public function generateForAll(Request $request)
    {
        $date = $request->input('period', now()->format('Y-m-d'));

        $this->payrollService->generateForAll(Carbon::parse($date));

        return response()->json([
            'status' => true,
            'message' => 'Payroll generated for all active employees',
        ]);
    }

    public function generateForEmployee(Request $request, Employee $employee)
    {
        $date = $request->input('period', now()->format('Y-m-d'));

        $payroll = $this->payrollService->generatePayrollForEmployee($employee, Carbon::parse($date));

        return response()->json([
            'status' => true,
            'message' => 'Payroll generated for employee',
            'data' => $payroll,
        ]);
    }

    public function listPayrollsForEmployee(Employee $employee)
    {
        $payrolls = $employee->payrolls()
            ->orderBy('period', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Employee payroll records retrieved successfully',
            'data' => $payrolls,
        ]);
    }


    public function getPayrollDetails($payrollId)
    {
        $payroll = Payroll::with(['employee', 'deductions', 'benefits', 'bankTransfer'])
            ->findOrFail($payrollId);

        return response()->json([
            'status' => true,
            'message' => 'Payroll details retrieved successfully',
            'data' => $payroll,
        ]);
    }

    public function processAllPayments()
    {
        $results = $this->payrollService->processAllPendingPayments();

        return response()->json([
            'status' => true,
            'message' => 'All pending payroll payments processed',
            'data' => $results,
        ]);
    }

    public function processSinglePayment($payrollId)
    {
        $payroll = \App\Models\Payroll::findOrFail($payrollId);

        try {
            $success = $this->payrollService->processPayment($payroll);

            return response()->json([
                'status' => $success,
                'message' => $success ? 'Payment successful' : 'Payment failed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Payment failed: ' . $e->getMessage(),
            ], 422);
        }
    }
    public function downloadPayslip($payrollId)
    {
        $payroll = Payroll::findOrFail($payrollId);
        return $this->payrollService->generatePayslipPDF($payroll);
    }
}
