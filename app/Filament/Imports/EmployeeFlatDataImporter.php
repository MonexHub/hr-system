<?php

namespace App\Filament\Imports;

use App\Models\EmployeeFlatData;
use Filament\Actions\ImportAction;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Illuminate\Support\Carbon;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;

class EmployeeFlatDataImporter extends Importer
{
    protected static ?string $model = EmployeeFlatData::class;

    protected static function handleDateCasting($state): ?Carbon
    {
        if (empty($state) || in_array(strtoupper($state), ['N/A', 'NA', 'NULL', '-', 'NONE'])) {
            return null;
        }

        try {
            $date = Carbon::parse($state);
            if ($date->year < 1900 || $date->year > 2100) {
                return null;
            }
            return $date;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected static function handleBooleanCasting($state): bool
    {
        if (is_string($state)) {
            $state = strtolower(trim($state));
            return in_array($state, ['1', 'true', 'yes', 'y', 'on', 'active']);
        }
        return (bool) $state;
    }

    protected static function handleNumericCasting($state, $decimals = 2): ?float
    {
        if (empty($state) || in_array(strtoupper($state), ['N/A', 'NA', 'NULL', '-', 'NONE'])) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9.-]/', '', $state);

        return is_numeric($cleaned) ? round((float) $cleaned, $decimals) : null;
    }

    public static function getColumns(): array
    {
        return [
            // Department Columns
            ImportColumn::make('department_name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('department_code')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('department_description')
                ->rules(['nullable', 'string']),
            ImportColumn::make('department_parent_code')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('department_manager_code')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('department_organization_unit_code')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('department_is_active')
                ->rules(['boolean'])
                ->castStateUsing(fn ($state) => static::handleBooleanCasting($state)),
            ImportColumn::make('department_phone')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('department_email')
                ->rules(['nullable', 'email', 'max:255']),
            ImportColumn::make('department_location')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('department_annual_budget')
                ->numeric()
                ->rules(['nullable', 'numeric', 'decimal:0,2'])
                ->castStateUsing(fn ($state) => static::handleNumericCasting($state, 2) ?? 0),
            ImportColumn::make('department_current_headcount')
                ->numeric()
                ->rules(['nullable', 'integer'])
                ->castStateUsing(fn ($state) => (int) (static::handleNumericCasting($state, 0) ?? 0)),
            ImportColumn::make('department_max_headcount')
                ->numeric()
                ->rules(['nullable', 'integer'])
                ->castStateUsing(fn ($state) => (int) (static::handleNumericCasting($state, 0) ?? 0)),

            // Job Title Columns
            ImportColumn::make('job_title_name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('job_title_description')
                ->rules(['nullable', 'string']),
            ImportColumn::make('job_title_net_salary_min')
                ->numeric()
                ->rules(['nullable', 'numeric', 'decimal:0,2'])
                ->castStateUsing(fn ($state) => static::handleNumericCasting($state, 2) ?? 0),
            ImportColumn::make('job_title_net_salary_max')
                ->numeric()
                ->rules(['nullable', 'numeric', 'decimal:0,2'])
                ->castStateUsing(fn ($state) => static::handleNumericCasting($state, 2) ?? 0),
            ImportColumn::make('job_title_is_active')
                ->rules(['boolean'])
                ->castStateUsing(fn ($state) => static::handleBooleanCasting($state)),

            // Employee Columns
            ImportColumn::make('user_code')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('employee_code')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('application_status')
                ->rules(['required', 'in:profile_incomplete,active,inactive'])
                ->castStateUsing(fn ($state) => $state ?? 'profile_incomplete'),
            ImportColumn::make('unit_code')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('first_name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('middle_name')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('last_name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('gender')
                ->rules(['required', 'in:male,female,other'])
                ->castStateUsing(function (string $state) {
                    if (empty($state)) return 'other';
                    $state = strtolower(trim($state));
                    if (in_array($state, ['m', 'male'])) return 'male';
                    if (in_array($state, ['f', 'female'])) return 'female';
                    return 'other';
                }),
            ImportColumn::make('birthdate')
                ->rules(['nullable', 'date'])
                ->castStateUsing(fn ($state) => static::handleDateCasting($state)),
            ImportColumn::make('marital_status')
                ->rules(['nullable', 'in:single,married,divorced,widowed'])
                ->castStateUsing(function ($state) {
                    if (empty($state)) return null;
                    $state = strtolower(trim($state));
                    return in_array($state, ['single', 'married', 'divorced', 'widowed']) ? $state : null;
                }),
            ImportColumn::make('profile_photo')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('phone_number')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('permanent_address')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('city')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('state')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('postal_code')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('job_title')
                ->rules(['required', 'string', 'max:255'])
                ->castStateUsing(fn ($state) => $state ?? 'unassigned'),
            ImportColumn::make('branch')
                ->rules(['required', 'string', 'max:255'])
                ->castStateUsing(fn ($state) => $state ?? 'unassigned'),
            ImportColumn::make('employment_status')
                ->rules(['required', 'in:pending,active,terminated,resigned'])
                ->castStateUsing(fn ($state) => $state ?? 'pending'),
            ImportColumn::make('appointment_date')
                ->rules(['nullable', 'date'])
                ->castStateUsing(fn ($state) => static::handleDateCasting($state)),
            ImportColumn::make('contract_type')
                ->rules(['required', 'in:permanent,contract,probation,undefined'])
                ->castStateUsing(fn ($state) => $state ?? 'undefined'),
            ImportColumn::make('terms_of_employment')
                ->rules(['nullable', 'in:full-time,part-time,temporary']),
            ImportColumn::make('contract_end_date')
                ->rules(['nullable', 'date'])
                ->castStateUsing(fn ($state) => static::handleDateCasting($state)),
            ImportColumn::make('salary')
                ->numeric()
                ->rules(['required', 'numeric', 'decimal:0,2'])
                ->castStateUsing(fn ($state) => static::handleNumericCasting($state, 2) ?? 0),
            ImportColumn::make('reporting_to_code')
                ->rules(['nullable', 'string', 'max:255']),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Section::make('Import Options')
                ->schema([
                    Toggle::make('update_existing')
                        ->label('Update existing records')
                        ->helperText('If enabled, existing records will be updated based on the employee code.')
                        ->default(true),
                    Select::make('date_format')
                        ->label('Date Format')
                        ->options([
                            'Y-m-d' => 'YYYY-MM-DD',
                            'd/m/Y' => 'DD/MM/YYYY',
                            'm/d/Y' => 'MM/DD/YYYY',
                        ])
                        ->default('Y-m-d')
                        ->required(),
                    Toggle::make('skip_invalid_dates')
                        ->label('Skip Invalid Dates')
                        ->helperText('If enabled, invalid dates will be treated as null instead of causing errors.')
                        ->default(true),
                    Toggle::make('trim_strings')
                        ->label('Trim Strings')
                        ->helperText('Remove leading and trailing spaces from text fields.')
                        ->default(true),
                ]),
        ];
    }

    public function resolveRecord(): ?EmployeeFlatData
    {
        return $this->options['update_existing'] ?
            EmployeeFlatData::firstOrNew(['employee_code' => $this->data['employee_code']]) :
            new EmployeeFlatData();
    }

    public function beforeImport(): void
    {
        if ($this->options['trim_strings'] ?? true) {
            foreach ($this->data as $key => $value) {
                if (is_string($value)) {
                    $this->data[$key] = trim($value);
                }
            }
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return 'Employee data has been imported successfully.';
    }
}
