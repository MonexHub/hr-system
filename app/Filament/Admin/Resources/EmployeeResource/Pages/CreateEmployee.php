<?php

namespace App\Filament\Admin\Resources\EmployeeResource\Pages;

use App\Filament\Admin\Resources\EmployeeResource;
use App\Mail\NewEmployeeAccountSetupMail;
use App\Models\User;
use App\Services\BeemService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Filament\Notifications\Notification;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function afterCreate(): void
    {
        // Generate setup token
        $token = Str::random(64);

        // Create user with temporary random password
        $user = User::create([
            'name' => $this->record->full_name,
            'email' => $this->record->email,
            'password' => Hash::make(Str::random(16))
        ]);

        // Link user to employee
        $this->record->update(['user_id' => $user->id]);

        // Assign roles if selected
        if (isset($this->data['roles'])) {
            $user->syncRoles($this->data['roles']);
        }

        // Store token in cache (48 hours expiry)
        Cache::put(
            'account_setup_' . $this->record->id,
            $token,
            now()->addHours(48)
        );

        // Send notifications
        $this->sendNotifications($token);
    }

    protected function sendNotifications(string $token): void
    {
        $setupUrl = route('employee.setup-account', [
            'token' => $token,
            'email' => $this->record->email,
        ]);

        $success = true;
        $errors = [];

        // Send Email
        try {
            Mail::to($this->record->email)->send(
                new NewEmployeeAccountSetupMail($this->record, $token)
            );
        } catch (\Exception $e) {
            $success = false;
            $errors[] = 'Email sending failed';
            \Log::error('Failed to send employee setup email', [
                'employee_id' => $this->record->id,
                'error' => $e->getMessage()
            ]);
        }

        // Send SMS
        if ($this->record->phone_number) {
            try {
                $beemService = new BeemService();
                $smsMessage = "Welcome to " . config('app.name') . "! Click " . $setupUrl . " to set up your account.";

                $result = $beemService->sendSMS($this->record->phone_number, $smsMessage);

                if (!$result['success']) {
                    throw new \Exception($result['error']);
                }
            } catch (\Exception $e) {
                $success = false;
                $errors[] = 'SMS sending failed';
                \Log::error('Failed to send employee setup SMS', [
                    'employee_id' => $this->record->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Show appropriate notification
        if ($success) {
            Notification::make()
                ->title('Employee created successfully')
                ->body('Account setup instructions have been sent via email and SMS.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Employee created')
                ->body('Created successfully but failed to send notifications: ' . implode(', ', $errors))
                ->warning()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
