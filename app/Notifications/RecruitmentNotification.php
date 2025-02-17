<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class RecruitmentNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $type;

    public function __construct(string $type, array $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }
    public function toDatabase($notifiable)
    {
        return [
            'type' => $this->type,
            'job_title' => $this->data['job_title'] ?? null,
            'status' => $this->type,
            'message' => $this->getNotificationMessage(),
            'data' => $this->data
        ];
    }

    private function getNotificationMessage(): string
    {
        return match($this->type) {
            'application_received' => 'Your application has been received',
            'shortlisted' => 'Your application has been shortlisted',
            'interview_scheduled' => 'Interview has been scheduled',
            'interview_completed' => 'Interview feedback is available',
            'interview_rescheduled' => 'Interview has been rescheduled',
            'interview_cancelled' => 'Interview has been cancelled',
            'offer_approved' => 'Job offer has been approved',
            'offer_sent' => 'Job offer has been sent',
            'offer_accepted' => 'Job offer acceptance confirmed',
            'offer_rejected' => 'Job offer has been declined',
            'rejected' => 'Application status updated',
            default => 'Status updated to: ' . ucfirst($this->type)
        };
    }
    public function toMail($notifiable)
    {
        $message = new MailMessage;

        switch ($this->type) {
            case 'application_received':
                return $message
                    ->subject('Application Received - ' . $this->data['job_title'])
                    ->greeting('Dear ' . $notifiable->first_name)
                    ->line('Thank you for applying for the position of ' . $this->data['job_title'])
                    ->line('Your application reference number is: ' . $this->data['application_number'])
                    ->line('We will review your application and get back to you soon.')
                    ->action('View Application', url('/candidate/applications/' . $this->data['application_id']));

            case 'shortlisted':
                return $message
                    ->subject('Congratulations! Your Application Has Been Shortlisted')
                    ->greeting('Dear ' . $notifiable->first_name)
                    ->line('We are pleased to inform you that your application for ' . $this->data['job_title'] . ' has been shortlisted.')
                    ->line('Next Steps:')
                    ->line('- We will contact you soon to schedule an interview')
                    ->line('- Please ensure your contact details are up to date')
                    ->line('- Review the job description and prepare for the interview')
                    ->action('View Details', url('/candidate/applications/' . $this->data['application_id']));

            case 'interview_scheduled':
                $url = '/candidate/applications/' . $this->data['application_id'];
                return $message
                    ->subject('Interview Scheduled - ' . $this->data['job_title'])
                    ->greeting('Dear ' . $notifiable->first_name)
                    ->line('Your interview has been scheduled for the position of ' . $this->data['job_title'])
                    ->line('Interview Details:')
                    ->line('Date: ' . ($this->data['interview_date'] ?? 'To be confirmed'))
                    ->line('Time: ' . ($this->data['interview_time'] ?? 'To be confirmed'))
                    ->line('Mode: ' . ($this->data['interview_mode'] ?? 'To be confirmed'))
                    ->when(isset($this->data['interview_mode']) && $this->data['interview_mode'] === 'video', function ($message) {
                        return $message->line('Meeting Link: ' . ($this->data['meeting_link'] ?? 'To be provided'));
                    })
                    ->when(isset($this->data['interview_mode']) && $this->data['interview_mode'] === 'in_person', function ($message) {
                        return $message->line('Location: ' . ($this->data['location'] ?? 'To be provided'));
                    })
                    ->when(isset($this->data['interviewer_name']), function ($message) {
                        return $message->line('Interviewer: ' . $this->data['interviewer_name']);
                    })
                    ->action('View Details', url($url));

            case 'interview_reminder':
                return $message
                    ->subject('Interview Reminder - Tomorrow')
                    ->greeting('Dear ' . $notifiable->first_name)
                    ->line('This is a reminder for your interview tomorrow for ' . $this->data['job_title'])
                    ->line('Interview Details:')
                    ->line('Date: ' . $this->data['interview_date'])
                    ->line('Time: ' . $this->data['interview_time'])
                    ->line('Mode: ' . $this->data['interview_mode'])
                    ->when($this->data['interview_mode'] === 'video', function ($message) {
                        return $message->line('Meeting Link: ' . $this->data['meeting_link']);
                    })
                    ->action('View Details', url('/candidate/interviews/' . $this->data['interview_id']));

            case 'offer_letter':
                return $message
                    ->subject('Job Offer - ' . $this->data['job_title'])
                    ->greeting('Dear ' . $notifiable->first_name)
                    ->line('Congratulations! We are pleased to offer you the position of ' . $this->data['job_title'])
                    ->line('Offer Details:')
                    ->line('Position: ' . $this->data['job_title'])
                    ->line('Department: ' . $this->data['department'])
                    ->line('Start Date: ' . $this->data['start_date'])
                    ->line('Please review the attached offer letter for complete details.')
                    ->line('To accept this offer, please sign and return the offer letter by ' . $this->data['valid_until'])
                    ->attach(storage::url('app/offers/' . $this->data['offer_letter_path']))
                    ->action('View Offer', url('/candidate/offers/' . $this->data['offer_id']));


            case 'rejected':
                return $message
                    ->subject('Application Status Update - ' . $this->data['job_title'])
                    ->greeting('Dear ' . $notifiable->first_name)
                    ->line('Thank you for your interest in joining our team and for taking the time to apply for the ' . $this->data['job_title'] . ' position.')
                    ->line('After careful consideration of all applications, we regret to inform you that we have decided to move forward with other candidates.')
                    ->line('We encourage you to apply for future positions that match your skills and experience.')
                    ->line('We wish you success in your job search and professional endeavors.');

            default:
                return $message
                    ->subject('Application Status Update')
                    ->greeting('Dear ' . $notifiable->first_name)
                    ->line('Your application status has been updated.')
                    ->line('Current status: ' . ucfirst($this->type));
        }
    }
}
