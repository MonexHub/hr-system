<?php

namespace App\Filament\Widgets;

use App\Models\Announcement;
use App\Models\Employee;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class EmployeeAnnouncementsWidget extends Widget
{
    protected static string $view = 'filament.widgets.employee-announcements-widget';
    protected int | string | array $columnSpan = 'full';
    use HasWidgetShield;

    public function getAnnouncementsData(): array
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        $departmentId = $employee?->department_id;

        // Get company-wide announcements
        $companyAnnouncements = Announcement::companyWide()
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($announcement) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'content' => $announcement->content,
                    'created_at' => $announcement->created_at,
                    'is_important' => $announcement->is_important,
                    'icon' => $announcement->icon,
                ];
            })
            ->toArray();

        // Get department-specific announcements if the employee belongs to a department
        $teamAnnouncements = [];
        if ($departmentId) {
            $teamAnnouncements = Announcement::departmental($departmentId)
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($announcement) use ($employee) {
                    return [
                        'id' => $announcement->id,
                        'title' => $announcement->title,
                        'content' => $announcement->content,
                        'created_at' => $announcement->created_at,
                        'department' => $employee->department->name,
                        'icon' => $announcement->icon,
                    ];
                })
                ->toArray();
        }

        // Get team members for the Team sidebar
        $teamMembers = [];
        if ($departmentId) {
            $teamMembers = Employee::where('department_id', $departmentId)
                ->where('employment_status', 'active')
                ->where('id', '!=', $employee->id)
                ->limit(5)
                ->get()
                ->map(function($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->full_name,
                        'job_title' => $member->jobTitle->name ?? 'Team Member',
                        'profile_photo' => $member->profile_photo,
                        'is_online' => (bool) rand(0, 1), // In a real app, this would check actual online status
                    ];
                })
                ->toArray();
        }

        return [
            'company_announcements' => $companyAnnouncements,
            'team_announcements' => $teamAnnouncements,
            'team_members' => $teamMembers,
            'department' => $employee?->department?->name ?? null,
        ];
    }
}
