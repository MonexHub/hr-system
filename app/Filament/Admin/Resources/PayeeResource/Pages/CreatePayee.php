<?php

namespace App\Filament\Admin\Resources\PayeeResource\Pages;

use App\Filament\Admin\Resources\PayeeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePayee extends CreateRecord
{
    protected static string $resource = PayeeResource::class;


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Format numerical values - remove commas
        $data['min_amount'] = str_replace(',', '', $data['min_amount']);
        $data['fixed_amount'] = str_replace(',', '', $data['fixed_amount']);

        // Ensure the max amount is null when empty (highest bracket)
        if (empty($data['max_amount'])) {
            $data['max_amount'] = null;
        } else {
            $data['max_amount'] = str_replace(',', '', $data['max_amount']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Verify that tax brackets don't overlap
        $this->validateTaxBrackets();
    }


    protected function validateTaxBrackets(): void
    {
        // Check for overlapping brackets
        $allBrackets = \App\Models\Payee::orderBy('min_amount')->get();
        $issues = [];
        $lastMax = 0;

        foreach ($allBrackets as $bracket) {
            // Check for gaps
            if ($bracket->min_amount > $lastMax && $lastMax > 0) {
                $issues[] = "Gap found between TSh " . number_format($lastMax, 0) .
                    " and TSh " . number_format($bracket->min_amount, 0);
            }

            // Check for overlaps
            foreach ($allBrackets as $otherBracket) {
                if ($bracket->id == $otherBracket->id) continue;

                // Skip if current bracket has no max and is the highest bracket
                if ($bracket->max_amount === null) continue;

                // Check for overlap
                if ($bracket->min_amount < $otherBracket->max_amount &&
                    $otherBracket->min_amount < $bracket->max_amount &&
                    $otherBracket->max_amount !== null) {
                    $issues[] = "Overlap between brackets starting at TSh " .
                        number_format($bracket->min_amount, 0) . " and TSh " .
                        number_format($otherBracket->min_amount, 0);
                }
            }

            // Update last max
            if ($bracket->max_amount !== null) {
                $lastMax = max($lastMax, $bracket->max_amount);
            } else {
                $lastMax = PHP_INT_MAX; // Consider it infinity
            }
        }

        // Show warning if issues found
        if (count($issues) > 0) {
            \Filament\Notifications\Notification::make()
                ->title('Tax Bracket Issues Detected')
                ->body("The following issues were found in your tax brackets:\n- " .
                    implode("\n- ", $issues) . "\n\nPlease review your tax brackets.")
                ->warning()
                ->persistent()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('review')
                        ->label('Review Brackets')
                        ->url(route('filament.admin.resources.payees.index')),
                    \Filament\Notifications\Actions\Action::make('dismiss')
                        ->label('Dismiss')
                        ->close(),
                ])
                ->send();
        }
    }
}

