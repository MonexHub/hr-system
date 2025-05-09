<?php

namespace App\Filament\Admin\Resources\PayrollResource\Pages;

use App\Filament\Admin\Resources\PayrollResource;
use App\Models\Employee;
use App\Services\PayrollService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GeneratePayroll extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = PayrollResource::class;

    protected static ?string $title = 'Generate Payroll';

    protected static string $view = 'filament.admin.resources.payroll-resource.pages.generate-payroll';

    // This is needed to properly register the route
    protected static string $routePath = '/generate';

    // Define the properties directly on the class
    public $period;
    public $type = 'all';
    public $employee_id = null;

    public function mount(): void
    {
        $this->period = now()->startOfMonth()->format('Y-m-d');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('period')
                    ->label('Payroll Period')
                    ->required()
                    ->default(now()->startOfMonth())
                    ->helperText('Payroll will be generated for this month'),

                Select::make('type')
                    ->label('Generate For')
                    ->options([
                        'all' => 'All Employees',
                        'single' => 'Single Employee',
                    ])
                    ->required()
                    ->reactive()
                    ->default('all'),

                Select::make('employee_id')
                    ->label('Employee')
                    ->options(function () {
                        $employees = Employee::select(['id', 'first_name', 'last_name'])
                            ->get();

                        return $employees->mapWithKeys(function ($employee) {
                            return [$employee->id => $employee->first_name . ' ' . $employee->last_name];
                        })->toArray();
                    })
                    ->searchable()
                    ->required(fn (callable $get) => $get('type') === 'single')
                    ->visible(fn (callable $get) => $get('type') === 'single'),
            ]);
    }

    public function generatePayroll(PayrollService $payrollService)
    {
        // Access properties directly on the class
        $period = Carbon::parse($this->period);

        // Use a database transaction for safety
        DB::beginTransaction();

        try {
            if ($this->type === 'all') {
                $payrollService->generateForAll($period);

                DB::commit();

                Notification::make()
                    ->title('Payroll generated successfully for all employees')
                    ->success()
                    ->send();
            } else {
                $employee = Employee::findOrFail($this->employee_id);

                // Check if generatePayrollForEmployee requires the full_name attribute
                $employee->full_name = $employee->first_name . ' ' . $employee->last_name;

                $payrollService->generatePayrollForEmployee($employee, $period);

                DB::commit();

                Notification::make()
                    ->title("Payroll generated successfully for {$employee->first_name} {$employee->last_name}")
                    ->success()
                    ->send();
            }

            return redirect('/admin/payrolls');
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error generating payroll')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
