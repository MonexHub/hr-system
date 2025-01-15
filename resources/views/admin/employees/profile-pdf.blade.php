<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Profile - {{ $employee->full_name }}</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f5f7; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background-color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        .header { background-color: #080c2a; color: white; padding: 20px; text-align: center; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { display: flex; padding: 20px; }
        .profile-photo {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #b99a57;
            margin-right: 20px;
        }
        .sections { display: flex; width: 100%; }
        .section { flex: 1; padding: 0 10px; }
        .section-title {
            border-bottom: 2px solid #b99a57;
            color: #080c2a;
            padding-bottom: 10px;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .info-label { color: #7f8c8d; flex: 1; }
        .info-value { color: #080c2a; font-weight: bold; flex: 2; text-align: right; }
        .status-active { color: #27ae60; }
        .status-suspended { color: #e74c3c; }
        .status-other { color: #f39c12; }
        .footer {
            background-color: #ecf0f1;
            text-align: center;
            padding: 10px;
            color: #7f8c8d;
            font-size: 12px;
        }
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .employee-id {
            background-color: #b99a57;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            margin-left: 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Employee Profile</h1>
        <div class="employee-id">Employee Code #{{ $employee->employee_code }}</div>
    </div>

    <div class="content">
        <div class="profile-header">
            @if($employee->photo)
                <img src="{{ storage_path('app/public/' . $employee->photo) }}" alt="Profile Photo" class="profile-photo">
            @else
                <img src="{{ public_path('images/default-avatar.png') }}" alt="Default Photo" class="profile-photo">
            @endif

            <div class="section" style="margin-left: 20px;">
                <h2 class="section-title">Personal Details</h2>
                <div class="info-row">
                    <span class="info-label">Full Name:</span>
                    <span class="info-value">{{ $employee->full_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Gender:</span>
                    <span class="info-value">{{ ucfirst($employee->gender) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Birth Date:</span>
                    <span class="info-value">{{ $employee->birthdate->format('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="sections">
        <div class="section">
            <h2 class="section-title">Employment Details</h2>
            <div class="info-row">
                <span class="info-label">Department:</span>
                <span class="info-value">{{ $employee->department->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Job Title:</span>
                <span class="info-value">{{ $employee->job_title }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Branch:</span>
                <span class="info-value">{{ $employee->branch }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Contract Type:</span>
                <span class="info-value">{{ ucfirst($employee->contract_type) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Appointment Date:</span>
                <span class="info-value">{{ $employee->appointment_date->format('d M Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Employment Status:</span>
                <span class="info-value {{
                        $employee->employment_status == 'active' ? 'status-active' :
                        ($employee->employment_status == 'suspended' ? 'status-suspended' : 'status-other')
                    }}">
                        {{ ucfirst($employee->employment_status) }}
                    </span>
            </div>
        </div>

        <div class="section">
            <h2 class="section-title">Compensation & Location</h2>
            <div class="info-row">
                <span class="info-label">Salary:</span>
                <span class="info-value">${{ number_format($employee->salary, 2) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Work Location:</span>
                <span class="info-value">{{ $employee->work_location }}</span>
            </div>
            @if($employee->contract_end_date)
                <div class="info-row">
                    <span class="info-label">Contract End Date:</span>
                    <span class="info-value">{{ $employee->contract_end_date->format('d M Y') }}</span>
                </div>
            @endif
        </div>
    </div>

    <div class="footer">
        Generated on {{ now()->format('d M Y H:i A') }}
    </div>
</div>
</body>
</html>
