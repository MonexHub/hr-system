<?php

namespace App\Filament\Admin\Actions;

use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Cache;
// use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\NewEmployeeAccountSetupMail;
use Filament\Notifications\Notification;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\UnstructuredHeader;

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

                // // Send new setup email
                // Mail::to($record->email)->send(
                //     new NewEmployeeAccountSetupMail($record, $token)
                // );

                                // Initialize Mailtrap API client
                                $mailtrap = MailtrapClient::initSendingEmails(
                                    apiKey: env('MAILTRAP_API_KEY')
                                );

                                // Construct Email
                                $email = (new MailtrapEmail())
                                    ->from(new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')))
                                    ->to(new Address($record->email, $record->full_name))
                                    ->subject('Welcome to ' . config('app.name') . ' - Complete Your Account Setup')
                                    ->html(view('emails.employees.account-setup', [
                                        'name' => $record->full_name,
                                        'setupUrl' => route('employee.setup-account', [
                                            'token' => $token,
                                            'email' => $record->email,
                                        ]),
                                        'expiresIn' => '48 hours',
                                    ])->render());

                                // Send Email
                                try {
                                    $response = $mailtrap->send($email);
                                } catch (\Exception $e) {
                                    throw new \Exception('Mailtrap API Error: ' . $e->getMessage());
                                }
            })
            ->visible(fn ($record) =>
                auth()->user()->hasRole(['super_admin', 'hr_manager']) &&
                $record->user &&
                !$record->user->password
            );
    }
}
