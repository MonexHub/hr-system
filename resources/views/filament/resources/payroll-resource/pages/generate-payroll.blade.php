<x-filament::page>
    <x-filament::section>
        <x-slot name="heading">
            Generate Payroll
        </x-slot>

        <x-slot name="description">
            Generate payroll for all employees or a specific employee for the selected period.
        </x-slot>

        <form wire:submit="generatePayroll">
            {{ $this->form }}

            <div class="mt-6 flex justify-end">
                <x-filament::button
                    type="submit"
                    color="primary"
                >
                    Generate Payroll
                </x-filament::button>
            </div>
        </form>

        <div class="mt-8">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <p><strong>Note:</strong> This will generate or update payroll records for the selected month. Existing payroll records will be updated with the current calculations.</p>
                <ul class="list-disc list-inside mt-2">
                    <li>Gross salary will be based on employee's current salary.</li>
                    <li>Benefits and deductions will be calculated based on active records.</li>
                    <li>PAYE tax will be calculated based on current tax brackets.</li>
                    <li>Active loan repayments will be included in the calculations.</li>
                </ul>
            </div>
        </div>
    </x-filament::section>
</x-filament::page>
