<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Employee;
use App\Models\Department;
use App\Models\JobTitle;
use App\Filament\Imports\EmployeeImporter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class ApiImportEmployeesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function handle(): void
    {
        $importer = new EmployeeImporter();

        $rows = array_map('str_getcsv', file(storage_path("app/{$this->path}")));
        $headers = array_map('trim', array_shift($rows));

        $chunks = array_chunk($rows, 100);

        foreach ($chunks as $chunk) {
            foreach ($chunk as $row) {
                $row = array_combine($headers, $row);
                try {
                    $validated = Validator::make($row, $this->rules())->validate();
                    $data = $importer->mutateBeforeCreate($validated);
                    Employee::create($data);
                } catch (\Throwable $e) {
                    Log::error('Import row failed', [
                        'row' => $row,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    protected function rules(): array
    {
        return collect(EmployeeImporter::getColumns())
            ->mapWithKeys(fn($col) => [$col->getName() => $col->getRules()])
            ->toArray();
    }
}
