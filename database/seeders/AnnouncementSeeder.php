<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin users to set as creators
        $adminUsers = User::role('super_admin')->get();

        if ($adminUsers->isEmpty()) {
            // Fallback to any user if no admin users exist
            $adminUsers = User::take(3)->get();

            if ($adminUsers->isEmpty()) {
                // Create a dummy user if no users exist at all
                $adminUsers = [User::factory()->create(['name' => 'System Admin'])];
            }
        }

        // Get departments for department-specific announcements
        $departments = Department::all();

        // Create company-wide announcements
        $this->createCompanyWideAnnouncements($adminUsers);

        // Create department-specific announcements if departments exist
        if ($departments->isNotEmpty()) {
            $this->createDepartmentalAnnouncements($adminUsers, $departments);
        }
    }

    /**
     * Create company-wide announcements
     */
    private function createCompanyWideAnnouncements($adminUsers): void
    {
        $companyAnnouncements = [
            [
                'title' => 'New Health Insurance Plan',
                'content' => '<p>We are pleased to announce our new health insurance plan with improved coverage options. Starting next month, all employees will have access to enhanced health benefits including:</p>
                            <ul>
                                <li>Lower deductibles</li>
                                <li>Expanded mental health coverage</li>
                                <li>Improved dental and vision plans</li>
                                <li>New wellness program with fitness reimbursements</li>
                            </ul>
                            <p>Please visit the HR department for more details and to update your enrollment information.</p>',
                'is_important' => true,
                'icon' => 'fas fa-heartbeat',
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'title' => 'Company Holiday Schedule 2025',
                'content' => '<p>The holiday schedule for 2025 has been approved by management. The company will observe the following holidays:</p>
                            <ul>
                                <li>New Year\'s Day - January 1</li>
                                <li>Martin Luther King Jr. Day - January 20</li>
                                <li>Presidents\' Day - February 17</li>
                                <li>Memorial Day - May 26</li>
                                <li>Independence Day - July 4</li>
                                <li>Labor Day - September 1</li>
                                <li>Thanksgiving - November 27-28</li>
                                <li>Christmas - December 24-26</li>
                            </ul>
                            <p>Please check your email for the full calendar and plan your vacations accordingly.</p>',
                'is_important' => false,
                'icon' => 'fas fa-calendar-day',
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'title' => 'Office Renovation Updates',
                'content' => '<p>The office renovation project will commence next month. Here\'s what you need to know:</p>
                            <ul>
                                <li>Temporary workspaces have been arranged on the second floor</li>
                                <li>The renovation will be completed in phases to minimize disruption</li>
                                <li>New amenities will include a larger break room and collaborative spaces</li>
                                <li>Construction hours will be limited to evenings and weekends when possible</li>
                            </ul>
                            <p>We appreciate your patience during this transition period as we work to improve our office environment.</p>',
                'is_important' => false,
                'icon' => 'fas fa-hard-hat',
                'created_at' => Carbon::now()->subDays(7),
            ],
            [
                'title' => 'Annual Company Picnic',
                'content' => '<p>We\'re excited to announce our annual company picnic will be held on Saturday, June 14th at Lakeside Park. This year\'s theme is "Summer Celebration"!</p>
                            <p>Activities will include:</p>
                            <ul>
                                <li>Team building games and competitions</li>
                                <li>Barbecue lunch with vegetarian options</li>
                                <li>Live music and entertainment</li>
                                <li>Activities for children and families</li>
                            </ul>
                            <p>All employees and their families are invited to attend. Please RSVP by June 1st through the HR portal.</p>',
                'is_important' => false,
                'icon' => 'fas fa-umbrella-beach',
                'created_at' => Carbon::now()->subDays(14),
            ],
            [
                'title' => 'Cybersecurity Training Reminder',
                'content' => '<p>This is a reminder that all employees must complete the mandatory cybersecurity training by the end of this month.</p>
                            <p>The training covers important topics such as:</p>
                            <ul>
                                <li>Password security best practices</li>
                                <li>Recognizing phishing attempts</li>
                                <li>Data protection policies</li>
                                <li>Secure remote work guidelines</li>
                            </ul>
                            <p>The training takes approximately 45 minutes to complete and can be accessed through the company learning portal. Please contact IT if you have any questions.</p>',
                'is_important' => true,
                'icon' => 'fas fa-shield-alt',
                'created_at' => Carbon::now()->subDays(10),
            ],
        ];

        foreach ($companyAnnouncements as $announcement) {
            Announcement::create([
                'title' => $announcement['title'],
                'content' => $announcement['content'],
                'is_important' => $announcement['is_important'],
                'icon' => $announcement['icon'],
                'department_id' => null, // Company-wide
                'created_by' => $adminUsers->random()->id,
                'created_at' => $announcement['created_at'],
                'updated_at' => $announcement['created_at'],
            ]);
        }
    }

    /**
     * Create department-specific announcements
     */
    private function createDepartmentalAnnouncements($adminUsers, $departments): void
    {
        $departmentalAnnouncementTemplates = [
            [
                'title' => 'Team Meeting Rescheduled',
                'content' => '<p>Please note that our weekly team meeting has been rescheduled to Thursday at 10 AM in Conference Room B.</p>
                            <p>Agenda items include:</p>
                            <ul>
                                <li>Project status updates</li>
                                <li>Q2 goals discussion</li>
                                <li>Team resource planning</li>
                            </ul>
                            <p>Please come prepared with your progress reports and any questions you may have.</p>',
                'is_important' => false,
                'icon' => 'fas fa-users',
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'title' => 'Project Deadline Extension',
                'content' => '<p>The deadline for Project Alpha has been extended by one week to accommodate client feedback. The new deadline is Friday, April 15th.</p>
                            <p>This additional time will allow us to:</p>
                            <ul>
                                <li>Incorporate the client\'s latest requirements</li>
                                <li>Conduct more thorough testing</li>
                                <li>Improve documentation</li>
                            </ul>
                            <p>Please adjust your schedules accordingly and let your team lead know if you have any concerns.</p>',
                'is_important' => true,
                'icon' => 'fas fa-tasks',
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'title' => 'Department Budget Update',
                'content' => '<p>Our department budget for Q2 has been approved. Key highlights:</p>
                            <ul>
                                <li>10% increase for professional development</li>
                                <li>New equipment purchases approved</li>
                                <li>Additional resources for project B</li>
                            </ul>
                            <p>Team leads should schedule budget reviews with their teams by the end of next week.</p>',
                'is_important' => false,
                'icon' => 'fas fa-chart-pie',
                'created_at' => Carbon::now()->subDays(8),
            ],
        ];

        // Create 1-3 announcements for each department
        foreach ($departments as $department) {
            // Randomly select 1-3 announcement templates
            $templateCount = rand(1, 3);
            $selectedTemplates = collect($departmentalAnnouncementTemplates)
                ->random($templateCount)
                ->all();

            foreach ($selectedTemplates as $template) {
                Announcement::create([
                    'title' => $template['title'],
                    'content' => $template['content'],
                    'is_important' => $template['is_important'],
                    'icon' => $template['icon'],
                    'department_id' => $department->id,
                    'created_by' => $adminUsers->random()->id,
                    'created_at' => $template['created_at'],
                    'updated_at' => $template['created_at'],
                ]);
            }
        }
    }
}
