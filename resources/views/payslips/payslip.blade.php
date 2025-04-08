<!-- resources/views/payslips/payslip.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Payslip - {{ $payroll->employee->full_name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; margin-top: 10px; border-collapse: collapse; }
        th, td { padding: 6px; border: 1px solid #ccc; }
        .header { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h2 class="header">Payslip for {{ $payroll->period->format('F Y') }}</h2>
    <strong>Employee:</strong> {{ $payroll->employee->full_name }}<br>
    <strong>Employee Code:</strong> {{ $payroll->employee->employee_code }}<br>
    <strong>Bank Account:</strong> {{ $payroll->employee->latestFinancial->account_number ?? 'N/A' }}

    <h4>Summary</h4>
    <table>
        <tr><th>Gross Salary</th><td>{{ number_format($payroll->gross_salary, 2) }}</td></tr>
        <tr><th>Total Benefits</th><td>{{ number_format($payroll->total_benefits, 2) }}</td></tr>
        <tr><th>Total Deductions</th><td>{{ number_format($payroll->total_deductions, 2) }}</td></tr>
        <tr><th><strong>Net Pay</strong></th><td><strong>{{ number_format($payroll->net_pay, 2) }}</strong></td></tr>
    </table>

    <h4>Breakdown - Benefits</h4>
    <table>
        <thead><tr><th>Type</th><th>Amount</th></tr></thead>
        <tbody>
            @foreach ($payroll->benefits as $benefit)
                <tr><td>{{ $benefit->type }}</td><td>{{ number_format($benefit->amount, 2) }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h4>Breakdown - Deductions</h4>
    <table>
        <thead><tr><th>Type</th><th>Amount</th></tr></thead>
        <tbody>
            @foreach ($payroll->deductions as $deduction)
                <tr><td>{{ $deduction->type }}</td><td>{{ number_format($deduction->amount, 2) }}</td></tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
