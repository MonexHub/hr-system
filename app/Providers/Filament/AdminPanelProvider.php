<?php

namespace App\Providers\Filament;
use App\Filament\Admin\Widgets\HolidayCalendarWidget;
use App\Filament\Widgets\AttendanceTrendsChart;
use App\Filament\Widgets\BudgetHeadcountChart;
use App\Filament\Widgets\ContractDistributionChart;
use App\Filament\Widgets\ContractExpiryWidget;
use App\Filament\Widgets\CustEmployeeOverview;
use App\Filament\Widgets\DepartmentBudgetWidget;
use App\Filament\Widgets\DepartmentHeadcountChart;
use App\Filament\Widgets\EmployeeAnnouncementsWidget;
use App\Filament\Widgets\EmployeeAttendanceSummaryWidget;
use App\Filament\Widgets\EmployeeDocumentsWidget;
use App\Filament\Widgets\EmployeeGenderDistribution;
use App\Filament\Widgets\EmployeeLeaveBalanceWidget;
use App\Filament\Widgets\EmployeeLeaveRequestsWidget;
use App\Filament\Widgets\EmployeeOverviewWidget;
use App\Filament\Widgets\EmployeeProfileSummaryWidget;
use App\Filament\Widgets\EmploymentDistributionWidget;
use App\Filament\Widgets\LeaveDistributionChart;
use App\Filament\Widgets\LeaveManagementWidget;
use App\Filament\Widgets\OrganizationalHealthWidget;
use App\Filament\Widgets\RecentNotificationsWidget;
use App\Helpers\SettingsHelper;
use App\Models\Employee;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Nuxtifyts\DashStackTheme\DashStackThemePlugin;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;
use Swis\Filament\Backgrounds\ImageProviders\MyImages;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Get settings from database
        $appName = SettingsHelper::getAppName();
        $primaryColor = SettingsHelper::getPrimaryColor();
        $logoLight = SettingsHelper::getLogo('light');
        $logoDark = SettingsHelper::getLogo('dark');

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn() => auth()->user()->name)
                    ->url(fn (): string => EditProfilePage::getUrl())
                    ->icon('heroicon-m-user-circle')

            ])
            ->colors([
                'primary' => Color::Amber,
                'secondary' => Color::Gray,
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger' => Color::Red,
            ])
            ->brandName($appName ?? 'HR Management System')
            ->brandLogo(fn () => $logoLight ?? asset('images/monexLogo.png'))
            ->darkModeBrandLogo(fn () => $logoDark ?? asset('images/monexLogo.png'))
            ->favicon(asset('images/favicon.ico'))
            ->font('Inter')
            ->darkMode(true)
            ->spa()
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            // Resource Discovery
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])

            // Widget Discovery
//            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                // Priority 1: Employee Overview and Key Metrics
                EmployeeProfileSummaryWidget::class,
                EmployeeAttendanceSummaryWidget::class,
                OrganizationalHealthWidget::class,
                EmploymentDistributionWidget::class,

                // Priority 2: Performance and Attendance Management
                LeaveManagementWidget::class,
                AttendanceTrendsChart::class,
                EmployeeLeaveRequestsWidget::class,
                EmployeeLeaveBalanceWidget::class,
                LeaveDistributionChart::class,

                // Priority 3: Workforce Planning and Budget
                DepartmentHeadcountChart::class,
                BudgetHeadcountChart::class,
                DepartmentBudgetWidget::class,
                ContractDistributionChart::class,
                ContractExpiryWidget::class,

                // Priority 4: Communication and Announcements
                EmployeeAnnouncementsWidget::class,
                RecentNotificationsWidget::class,
                EmployeeDocumentsWidget::class,
                HolidayCalendarWidget::class,
            ])
            ->plugins([
//                FilamentApexChartsPlugin::make(),
                FilamentApexChartsPlugin::make(),
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
                FilamentBackgroundsPlugin::make()
                    ->imageProvider(
                        MyImages::make()
                            ->directory('images/backgrounds')
                    ),
                FilamentEditProfilePlugin::make()
                    ->slug('my-profile')
                    ->setTitle('My Profile')
                    ->setNavigationLabel('My Profile')
                    ->setNavigationGroup('Group Profile')
                    ->setIcon('heroicon-o-user')
                    ->setSort(10)
                    ->canAccess(fn () => auth()->user()->id === 1)
                    ->shouldRegisterNavigation(false)
                    ->shouldShowDeleteAccountForm(false)
                    ->shouldShowSanctumTokens()
                    ->shouldShowBrowserSessionsForm()
                    ->shouldShowAvatarForm()

//                DashStackThemePlugin::make()
            ])
            // Middleware
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
