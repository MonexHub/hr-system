<!DOCTYPE html>
<html>
<head>
    <title>Payslip</title>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .payslip-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .period {
            font-size: 14px;
            margin-bottom: 15px;
        }
        .employee-details, .payment-details {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            margin-bottom: 5px;
            border-bottom: 1px solid #999;
            padding-bottom: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            text-align: left;
            padding: 5px;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #f2f2f2;
        }
        .total-row {
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="payslip-title">PAYSLIP</div>
    <div class="company-name">Your Company Name</div>
    <div class="period">Pay Period: {{ $payroll->period->format('F Y') }}</div>
</div>

<div class="employee-details">
    <div class="section-title">Employee Information</div>
    <table>
        <tr>
            <td width="25%"><strong>Employee ID:</strong></td>
            <td width="25%">{{ $employee->employee_code }}</td>
            <td width="25%"><strong>Name:</strong></td>
            <td width="25%">{{ $employee->full_name }}</td>
        </tr>
        <tr>
            <td><strong>Department:</strong></td>
            <td>{{ $employee->department->name ?? 'N/A' }}</td>
            <td><strong>Position:</strong></td>
            <td>{{ $employee->job_title ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Bank:</strong></td>
            <td>{{ $employee->latestFinancial->bank_name ?? 'N/A' }}</td>
            <td><strong>Account:</strong></td>
            <td>{{ $employee->latestFinancial->account_number ?? 'N/A' }}</td>
        </tr>
    </table>
</div>

<div class="payment-details">
    <div class="section-title">Earnings</div>
    <table>
        <tr>
            <th>Description</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Basic Salary</td>
            <td>{{ number_format($payroll->gross_salary, 2) }}</td>
        </tr>

        <!-- Benefits -->
        @if(isset($payrollBenefits) && $payrollBenefits->count() > 0)
            @foreach($payrollBenefits as $benefit)
                <tr>
                    <td>{{ $benefit->name }}</td>
                    <td>{{ number_format($benefit->amount, 2) }}</td>
                </tr>
            @endforeach
        @endif

        <tr class="total-row">
            <td>Total Earnings</td>
            <td>{{ number_format($payroll->gross_salary + $payroll->total_benefits, 2) }}</td>
        </tr>
    </table>

    <div class="section-title">Deductions</div>
    <table>
        <tr>
            <th>Description</th>
            <th>Amount</th>
        </tr>

        <!-- Deductions -->
        @if(isset($payrollDeductions) && $payrollDeductions->count() > 0)
            @foreach($payrollDeductions as $deduction)
                <tr>
                    <td>{{ $deduction->name }}</td>
                    <td>{{ number_format($deduction->amount, 2) }}</td>
                </tr>
            @endforeach
        @endif

        <tr class="total-row">
            <td>Total Deductions</td>
            <td>{{ number_format($payroll->total_deductions, 2) }}</td>
        </tr>
    </table>

    <div class="section-title">Summary</div>
    <table>
        <tr>
            <th>Description</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Gross Earnings</td>
            <td>{{ number_format($payroll->gross_salary + $payroll->total_benefits, 2) }}</td>
        </tr>
        <tr>
            <td>Total Deductions</td>
            <td>{{ number_format($payroll->total_deductions, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>Net Pay</td>
            <td>{{ number_format($payroll->net_salary, 2) }}</td>
        </tr>
    </table>
</div>

<div class="footer">
    <p>This is a computer-generated document and does not require a signature.</p>
    <p>Generated on: {{ now()->format('d/m/Y H:i:s') }}</p>
</div>
</body>
</html>
