<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WeeklyContractStatusReport extends Mailable
{
    use Queueable, SerializesModels;

    public $report;

    public function __construct(array $report)
    {
        $this->report = $report;
    }

    public function build()
    {
        return $this->markdown('emails.reports.contract-status')
            ->subject('Weekly Contract Status Report - ' . now()->format('d/m/Y'))
            ->with([
                'report' => $this->report,
                'totalProbationEnding' => count($this->report['probation_ending_soon']),
                'totalContractsExpiring' => count($this->report['contracts_expiring_soon']),
            ]);
    }
}
