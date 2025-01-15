<?php

namespace App\Filament\Admin\Resources\CandidateResource\Pages;

use App\Filament\Admin\Resources\CandidateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCandidate extends CreateRecord
{
    protected static string $resource = CandidateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeCreate(): void
    {
        // Set default status if not set
        if (!isset($this->data['status'])) {
            $this->data['status'] = 'applied';
        }

        // Format education data
        if (isset($this->data['education']) && is_array($this->data['education'])) {
            foreach ($this->data['education'] as &$education) {
                $education['achievements'] = $education['achievements'] ?? null;
            }
        }

        // Format skills as array if it's a string
        if (isset($this->data['skills']) && is_string($this->data['skills'])) {
            $this->data['skills'] = explode(',', $this->data['skills']);
        }

        // Ensure expected_salary is numeric
        if (isset($this->data['expected_salary'])) {
            $this->data['expected_salary'] = (float) $this->data['expected_salary'];
        }
    }

    protected function afterCreate(): void
    {
        // Add any notifications or additional processing here
        $candidate = $this->record;

        // Optional: Send notification
        // Notification::make()
        //     ->title('Candidate Created')
        //     ->success()
        //     ->send();
    }
}
