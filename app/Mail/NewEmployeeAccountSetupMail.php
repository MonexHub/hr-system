<?php

namespace App\Mail;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewEmployeeAccountSetupMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Employee $employee,
        public string $token
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . config('app.name') . ' - Complete Your Account Setup',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.employees.account-setup',
            with: [
                'name' => $this->employee->full_name,
                'setupUrl' => route('employee.setup-account', [
                    'token' => $this->token,
                    'email' => $this->employee->email,
                ]),
                'expiresIn' => '48 hours',
            ],
        );
    }
}
