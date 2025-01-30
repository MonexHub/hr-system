<?php

namespace App\Filament\Admin\Actions;

use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\NewEmployeeAccountSetupMail;
use Filament\Notifications\Notification;

class ResendSetupLinkAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'resend-setup';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->icon('heroicon-o-envelope')
            ->label('Resend Setup Link')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Resend Account Setup Link')
            ->modalDescription('Are you sure you want to resend the account setup link? This will invalidate any previous setup links.')
            ->modalSubmitActionLabel('Yes, resend link')
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title('Setup link sent')
                    ->body('The account setup link has been sent to the employee.')
            )
            ->action(function ($record) {
                // Generate new token
                $token = Str::random(64);

                // Store new token in cache (48 hours expiry)
                Cache::put(
                    'account_setup_' . $record->id,
                    $token,
                    now()->addHours(48)
                );

                // Send new setup email
                Mail::to($record->email)->send(
                    new NewEmployeeAccountSetupMail($record, $token)
                );
            })
            ->visible(fn ($record) =>
                auth()->user()->hasRole(['super_admin', 'hr_manager']) &&
                $record->user &&
                !$record->user->password
            );
    }
}
