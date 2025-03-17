<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class ContractExpiryWidget extends Widget
{
    protected static string $view = 'filament.widgets.contract-expiry-widget';

    // Set a default polling interval (optional)
    protected static ?string $pollingInterval = '30s';
    protected int | string | array $columnSpan = 'full';

    public function calculateContractStats()
    {
        $currentDate = Carbon::now();
        $thirtyDaysLater = $currentDate->copy()->addDays(30);
        $sixtyDaysLater = $currentDate->copy()->addDays(60);
        $ninetyDaysLater = $currentDate->copy()->addDays(90);

        return [
            'probation_ending_soon' => $this->getProbationEndingSoon(),
            'contracts_expiring_30_days' => $this->getContractsExpiring($currentDate, $thirtyDaysLater),
            'contracts_expiring_60_days' => $this->getContractsExpiring($thirtyDaysLater, $sixtyDaysLater),
            'contracts_expiring_90_days' => $this->getContractsExpiring($sixtyDaysLater, $ninetyDaysLater),
        ];
    }

    protected function getProbationEndingSoon()
    {
        return Employee::probationEnding(30)
            ->where('employment_status', 'active')
            ->count();
    }

    protected function getContractsExpiring($startDate, $endDate)
    {
        return Employee::where('contract_type', 'contract')
            ->where('employment_status', 'active')
            ->whereNotNull('contract_end_date')
            ->whereBetween('contract_end_date', [$startDate, $endDate])
            ->count();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('filament.widgets.contract-expiry-widget', [
            'stats' => $this->calculateContractStats(),
        ]);
    }
}
