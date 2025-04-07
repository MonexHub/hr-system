<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use App\Models\EmployeeTraining;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewEmployeeAccountSetupMail;
use App\Services\BeemService;
use App\Jobs\ImportEmployeesJob;
use Symfony\Component\HttpFoundation\StreamedResponse;




class EmployeeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Employee::query()->with(['department', 'jobTitle']);

        return response()->json([
            "message" => "Employee Data retrieved successfully",
            "data" => $query->latest()->get()
        ], 200);
    }


    public function getTeamMembers()
    {
        $user = Auth::user();
        $query = Employee::query()->with(['department', 'jobTitle']);
        $query->where('department_id', $user->employee->department_id);

        return response()->json([
            "message" => "Employee Data",
            "data" => $query->latest()->get()
        ], 200);
    }

    public function getEmployee($id)
    {

        $employee = Employee::find($id)->with(['department', 'jobTitle', 'user'])->first();

        if ($employee) {
            return response([
                'message' => 'Employee Detail',
                'data' => $employee
            ]);
        } else {
            return response(['message' => 'Employee not found']);
        }
    }


    public function editEmployeeDetails($request)
    {

        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'address' => 'required',
            'department_id' => 'required',
            'job_title_id' => 'required',
            'salary' => 'required',
            'joining_date' => 'required',
            'status' => 'required'
        ]);

        $employee = Employee::where('id', Auth::user()->employee->id)->first();

        $employee->name = $validatedData['name'];
        $employee->email = $validatedData['email'];
        $employee->phone = $validatedData['phone'];
        $employee->address = $validatedData['address'];
        $employee->department_id = $validatedData['department_id'];
        $employee->job_title_id = $validatedData['job_title_id'];
        $employee->salary = $validatedData['salary'];
        $employee->joining_date = $validatedData['joining_date'];
        $employee->status = $validatedData['status'];

        $employee->save();

        return response([
            'message' => 'Employee Details Updated',
            'data' => $employee
        ]);
    }


    public function getEmployeeTraining()
    {

        $employee = EmployeeTraining::all();

        if ($employee) {
            return response([
                'message' => 'Employee Training',
                'data' => $employee
            ]);
        } else {
            return response(['message' => 'Employee not found'], 402);
        }
    }

    public function getEmployeeTrainingDetails($id)
    {

        $employee = EmployeeTraining::where('employee_id', $id)->get();

        if ($employee) {
            return response([
                'message' => 'Employee Training Details',
                'data' => $employee
            ]);
        } else {
            return response(['message' => 'Employee not found'], 403);
        }
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:employees,email',
            'phone_number' => 'required|string',
            'birthdate' => 'required|date',
            'gender' => 'required|string|in:male,female,other',
            'marital_status' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'job_title_id' => 'required|exists:job_titles,id',
            'contract_type' => 'required|in:permanent,contract,probation,intern',
            'employment_status' => 'required|in:active,probation,suspended,terminated,resigned',
            'appointment_date' => 'required|date',
            'contract_end_date' => 'nullable|date',
            'branch' => 'required|string',
            'permanent_address' => 'required|string',
            'city' => 'required|string',
            'postal_code' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
            'net_salary' => 'nullable|numeric',
        ]);

        $data['employee_code'] = 'EMP-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $employee = Employee::create($data);

        return response()->json($employee->fresh(), 201);
    }

    public function show($id)
    {
        $employee = Employee::with(['department', 'jobTitle', 'user'])->findOrFail($id);
        return response()->json($employee);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $data = $request->validate([
            'first_name' => 'sometimes|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'sometimes|string',
            'phone_number' => 'sometimes|string',
            'email' => 'sometimes|email|unique:employees,email,' . $id,
            'branch' => 'sometimes|string',
            'net_salary' => 'nullable|numeric',
            'employment_status' => 'nullable|in:active,probation,suspended,terminated,resigned',
            'contract_type' => 'nullable|in:permanent,contract,probation,intern',
            'contract_end_date' => 'nullable|date',
        ]);

        $employee->update($data);

        return response()->json($employee->fresh());
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return response()->json(['message' => 'Employee deleted']);
    }

    public function createUserAccount($id)
    {
        $employee = Employee::findOrFail($id);

        if ($employee->user) {
            return response()->json(['message' => 'User account already exists.'], 400);
        }

        $user = User::create([
            'name' => $employee->full_name,
            'email' => $employee->email,
            'password' => Hash::make(Str::random(16)),
        ]);

        $user->assignRole('employee');

        $employee->update(['user_id' => $user->id]);

        $token = Str::random(64);
        Cache::put("account_setup_{$employee->id}", $token, now()->addHours(48));
        $setupUrl = route('employee.setup-account', ['token' => $token, 'email' => $employee->email]);

        try {
            Mail::to($employee->email)->send(new NewEmployeeAccountSetupMail($employee, $token));
        } catch (\Exception $e) {
            Log::error('Failed to send setup email', ['error' => $e->getMessage()]);
        }

        if ($employee->phone_number) {
            try {
                $beem = new BeemService();
                $beem->sendSMS($employee->phone_number, "Set up your account at: $setupUrl");
            } catch (\Exception $e) {
                Log::error('Failed to send setup SMS', ['error' => $e->getMessage()]);
            }
        }

        return response()->json(['message' => 'User account created and setup instructions sent.']);
    }

    public function resendSetupLink($id)
    {
        $employee = Employee::findOrFail($id);

        if (!$employee->user) {
            return response()->json(['message' => 'User account not found.'], 404);
        }

        $token = Str::random(64);
        Cache::put("account_setup_{$employee->id}", $token, now()->addHours(48));
        $setupUrl = route('employee.setup-account', ['token' => $token, 'email' => $employee->email]);

        try {
            Mail::to($employee->email)->send(new NewEmployeeAccountSetupMail($employee, $token));
        } catch (\Exception $e) {
            Log::error('Failed to send setup email', ['error' => $e->getMessage()]);
        }

        if ($employee->phone_number) {
            try {
                $beem = new BeemService();
                $beem->sendSMS($employee->phone_number, "Reset your account setup at: $setupUrl");
            } catch (\Exception $e) {
                Log::error('Failed to send setup SMS', ['error' => $e->getMessage()]);
            }
        }

        return response()->json(['message' => 'Setup instructions resent.']);
    }


    /**
     * Handle background import of CSV via EmployeeImporter.
     */
    public function import(Request $request)
    {
        if (! $request->user()->can('import_employee')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt']
        ]);

        $path = $request->file('file')->store('imports/employees');

        ImportEmployeesJob::dispatch($path);

        return response()->json(['message' => 'Import started.'], 200);
    }

    /**
     * Download a CSV sample for the employee import.
     */
    public function downloadSample(): StreamedResponse
    {
        if (! auth()->user()->can('import_employee')) {
            abort(403);
        }

        $headers = [
            'employee_code',
            'first_name',
            'last_name',
            'middle_name',
            'gender',
            'birthdate',
            'contract_type',
            'appointment_date',
            'job_title',
            'branch',
            'department',
            'salary',
            'email'
        ];

        $callback = function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, ['EMP-00001', 'Jane', 'Doe', '', 'female', '01/01/1990', 'permanent', '01/01/2020', 'Developer', 'HQ', 'IT', '2000000', 'jane@example.com']);
            fclose($file);
        };

        return response()->streamDownload($callback, 'employee_import_sample.csv');
    }
}
