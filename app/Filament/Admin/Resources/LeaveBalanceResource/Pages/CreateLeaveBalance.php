<?php

namespace App\Filament\Admin\Resources\LeaveBalanceResource\Pages;

use App\Filament\Admin\Resources\LeaveBalanceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLeaveBalance extends CreateRecord
{
    protected static string $resource = LeaveBalanceResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Validate that employee_id is present
        if (!isset($data['employee_id'])) {
            throw new \Exception('Employee ID is required for creating a leave balance');
        }

        return static::getModel()::create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure year is set
        $data['year'] = $data['year'] ?? date('Y');

        // Calculate days remaining if not explicitly set
        if (!isset($data['days_remaining'])) {
            $data['days_remaining'] = $data['total_days'] - $data['days_taken'];
        }

        // Convert any file uploads to proper format
        if (isset($data['attachments'])) {
            $data['attachments'] = array_map(
                fn ($attachment) => $attachment->store('leave-attachments'),
                $data['attachments']
            );
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
