<!DOCTYPE html>
<html lang="en">
<head>
    <title>Professional Payslip</title>
    <meta charset="utf-8">
    <style>
        /* Reset and base styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
            background: #f8f9fa;
            padding: 0;
            margin: 0;
        }

        /* A4 size container */
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 5mm;
            margin: 0 auto;
            background: white;
        }

        /* Improved watermark */
        .watermark-wrapper {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 10;
            pointer-events: none;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 150px;
            font-weight: 800;
            font-family: Arial, sans-serif;
            color: rgba(220, 220, 220, 0.25);
            white-space: nowrap;
            letter-spacing: 5px;
            text-transform: uppercase;
        }

        /* Main container */
        .payslip-container {
            position: relative;
            width: 100%;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }

        /* Stylish header */
        .header {
            background: linear-gradient(135deg, #1a365d 0%, #2563eb 100%);
            background-color: #1a365d !important;
            color: white;
            padding: 20px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 30% 40%, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 50%);
            opacity: 0.7;
        }

        .header-content {
            position: relative;
            z-index: 20;
        }

        .company-logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 10px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #1a365d;
            font-size: 22px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .payslip-title {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 2px;
            margin-bottom: 5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .company-name {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .period {
            font-size: 15px;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 8px 25px;
            display: inline-block;
            border-radius: 50px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        /* Content area */
        .content {
            padding: 20px;
            position: relative;
            z-index: 20;
        }

        .employee-details,
        .payment-details {
            margin-bottom: 20px;
        }

        /* Section titles */
        .section-title {
            color: #1a365d;
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #3b82f6;
            position: relative;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 50px;
            height: 2px;
            background-color: #ef4444;
        }

        /* Tables */
        .employee-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 15px;
            border-radius: 8px;
            overflow: hidden;
            background-color: #f8fafc;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .employee-table td {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
        }

        .employee-label {
            font-weight: 600;
            color: #1e40af;
            width: 30%;
        }

        table.data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 15px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        table.data-table th {
            background: linear-gradient(to bottom, #f1f5f9, #e2e8f0);
            color: #1e40af;
            font-weight: 600;
            text-align: left;
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        table.data-table td {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
        }

        .amount-cell {
            text-align: right;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .total-row {
            font-weight: 700;
            background: linear-gradient(to bottom, #eff6ff, #dbeafe);
        }

        .summary-table .total-row {
            background: linear-gradient(to bottom, #ecfdf5, #d1fae5);
        }

        .summary-table .total-row td {
            font-size: 16px;
            color: #047857;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            background-color: #f1f5f9;
            font-size: 11px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }

        .footer p {
            margin: 5px 0;
        }

        /* Print styles */
        @media print {
            body {
                background-color: white;
                padding: 0;
                margin: 0;
            }

            .page {
                width: 210mm;
                height: 297mm;
                padding: 0;
                margin: 0;
                box-shadow: none;
            }

            .payslip-container {
                box-shadow: none;
                margin: 0;
                width: 100%;
                border-radius: 0;
            }

            .watermark-wrapper {
                display: block !important;
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: 100% !important;
                z-index: 10 !important;
            }

            .header {
                background: linear-gradient(135deg, #1a365d 0%, #2563eb 100%) !important;
                background-color: #1a365d !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color-adjust: exact;
            }

            table.data-table th {
                background: linear-gradient(to bottom, #f1f5f9, #e2e8f0) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .total-row {
                background: linear-gradient(to bottom, #eff6ff, #dbeafe) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .summary-table .total-row {
                background: linear-gradient(to bottom, #ecfdf5, #d1fae5) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="payslip-container">
        <!-- Watermark -->
        <div class="watermark-wrapper">
            <div class="watermark">PAYSLIP</div>
        </div>

        <div class="header">
            <div class="header-content">
                <div class="company-logo"><img src="{{ asset('images/monexLogo.png') }}" alt="Monex Logo"></div>
                <div class="payslip-title">PAYSLIP</div>
                <div class="company-name">{{ config('app.name') }}</div>
                <div class="period">Pay Period: {{ $payroll->period->format('F Y') }}</div>
            </div>
        </div>

        <div class="content">
            <div class="employee-details">
                <div class="section-title">Employee Information</div>
                <table class="employee-table">
                    <tr>
                        <td class="employee-label">Employee ID:</td>
                        <td>{{ $employee->employee_code }}</td>
                        <td class="employee-label">Name:</td>
                        <td>{{ $employee->full_name }}</td>
                    </tr>
                    <tr>
                        <td class="employee-label">Department:</td>
                        <td>{{ $employee->department->name ?? 'N/A' }}</td>
                        <td class="employee-label">Position:</td>
                        <td>{{ $employee->job_title ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="employee-label">Bank:</td>
                        <td>{{ $employee->latestFinancial->bank_name ?? 'N/A' }}</td>
                        <td class="employee-label">Account:</td>
                        <td>{{ $employee->latestFinancial->account_number ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>

            <div class="payment-details">
                <div class="section-title">Earnings</div>
                <table class="data-table">
                    <tr>
                        <th>DESCRIPTION</th>
                        <th>AMOUNT</th>
                    </tr>
                    <tr>
                        <td>Basic Salary</td>
                        <td class="amount-cell">{{ number_format($payroll->gross_salary, 2) }}</td>
                    </tr>

                    <!-- Benefits -->
                    @if(isset($payrollBenefits) && $payrollBenefits->count() > 0)
                        @foreach($payrollBenefits as $benefit)
                            <tr>
                                <td>{{ $benefit->name }}</td>
                                <td class="amount-cell">{{ number_format($benefit->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    @endif

                    <tr class="total-row">
                        <td>Total Earnings</td>
                        <td class="amount-cell">{{ number_format($payroll->gross_salary + $payroll->total_benefits, 2) }}</td>
                    </tr>
                </table>

                <div class="section-title">Deductions</div>
                <table class="data-table">
                    <tr>
                        <th>DESCRIPTION</th>
                        <th>AMOUNT</th>
                    </tr>

                    <!-- Deductions -->
                    @if(isset($payrollDeductions) && $payrollDeductions->count() > 0)
                        @foreach($payrollDeductions as $deduction)
                            <tr>
                                <td>{{ $deduction->name }}</td>
                                <td class="amount-cell">{{ number_format($deduction->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    @endif

                    <tr class="total-row">
                        <td>Total Deductions</td>
                        <td class="amount-cell">{{ number_format($payroll->total_deductions, 2) }}</td>
                    </tr>
                </table>

                <div class="section-title">Summary</div>
                <table class="data-table summary-table">
                    <tr>
                        <th>DESCRIPTION</th>
                        <th>AMOUNT</th>
                    </tr>
                    <tr>
                        <td>Gross Earnings</td>
                        <td class="amount-cell">{{ number_format($payroll->gross_salary + $payroll->total_benefits, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Total Deductions</td>
                        <td class="amount-cell">{{ number_format($payroll->total_deductions, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Net Pay</td>
                        <td class="amount-cell">{{ number_format($payroll->net_salary, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="footer">
            <p>This is a computer-generated document and does not require a signature.</p>
            <p>Generated on: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</div>
</body>
</html>
