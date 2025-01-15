<!DOCTYPE html>
<html>
<head>
    <title>Employee Profile - {{ $employee->full_name }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f5f5f5; }
        .section { margin-bottom: 20px; }
        .section-title { background: #0f27a1; color: white; padding: 8px; }
    </style>
</head>
<body>
<div class="header">
    <h1>Employee Profile</h1>
    <h2>{{ $employee->full_name }}</h2>
    <p>Employee Code: {{ $employee->employee_code }}</p>
</div>

<div class="section">
    <div class="section-title">Personal Information</div>
    <table>
        <tr>
            <th>Department</th>
            <td>{{ $employee->department->name }}</td>
            <th>Job Title</th>
            <td>{{ $employee->job_title }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $employee->email }}</td>
            <th>Phone</th>
            <td>{{ $employee->phone_number }}</td>
        </tr>
        <tr>
            <th>Start Date</th>
            <td>{{ $employee->appointment_date?->format('d/m/Y') }}</td>
            <th>Status</th>
            <td>{{ ucfirst($employee->employment_status) }}</td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-title">Education</div>
    <table>
        <tr>
            <th>Institution</th>
            <th>Award</th>
            <th>Period</th>
        </tr>
        @foreach($education as $edu)
            <tr>
                <td>{{ $edu->institution_name }}</td>
                <td>{{ $edu->award_received }}</td>
                <td>{{ $edu->start_date->format('M Y') }} -
                    {{ $edu->end_date ? $edu->end_date->format('M Y') : 'Present' }}</td>
            </tr>
        @endforeach
    </table>
</div>

<div class="section">
    <div class="section-title">Work Experience</div>
    <table>
        <tr>
            <th>Position</th>
            <th>Company</th>
            <th>Period</th>
        </tr>
        @foreach($experience as $exp)
            <tr>
                <td>{{ $exp->job_title }}</td>
                <td>{{ $exp->company_name }}</td>
                <td>{{ $exp->start_date->format('M Y') }} -
                    {{ $exp->end_date ? $exp->end_date->format('M Y') : 'Present' }}</td>
            </tr>
        @endforeach
    </table>
</div>
</body>
</html>
