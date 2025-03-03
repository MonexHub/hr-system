<?php

namespace App\Livewire;

use App\Models\AppSettings;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;

class ManageSettings extends Component implements HasForms
{
    use InteractsWithForms;

    // Define public properties for each setting
    public $app_name = '';
    public $business_hours_start = '';
    public $business_hours_end = '';
    public $timezone = '';
    public $logo_light = null;
    public $logo_dark = null;
    public $primary_color = '';
    public $secondary_color = '';
    public $email_notifications = true;
    public $system_notifications = true;
    public $notification_sender_email = '';
    public $notification_sender_name = '';
    public $confirm_reset = false; // Added this property

    public function mount(): void
    {
        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        // Define default values
        $defaults = [
            'app_name' => config('app.name'),
            'business_hours_start' => '09:00',
            'business_hours_end' => '17:00',
            'timezone' => config('app.timezone'),
            'logo_light' => null,
            'logo_dark' => null,
            'primary_color' => '#10b981',
            'secondary_color' => '#6B7280',
            'email_notifications' => true,
            'system_notifications' => true,
            'notification_sender_email' => config('mail.from.address', ''),
            'notification_sender_name' => config('mail.from.name', ''),
            'confirm_reset' => false,
        ];

        // Load settings from database
        $settings = AppSettings::all()->pluck('value', 'key')->toArray();

        // Merge defaults with actual settings and assign to properties
        $merged = array_merge($defaults, $settings);

        foreach ($merged as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        $this->form->fill($merged);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Settings')
                    ->tabs([
                        Tabs\Tab::make('General')
                            ->schema([
                                TextInput::make('app_name')
                                    ->label('Application Name')
                                    ->required(),
                                TimePicker::make('business_hours_start')
                                    ->label('Business Hours Start')
                                    ->seconds(false),
                                TimePicker::make('business_hours_end')
                                    ->label('Business Hours End')
                                    ->seconds(false),
                                Select::make('timezone')
                                    ->label('Default Timezone')
                                    ->options(collect(timezone_identifiers_list())
                                        ->mapWithKeys(fn ($timezone) => [$timezone => $timezone])
                                        ->toArray())
                                    ->searchable(),
                            ]),
                        Tabs\Tab::make('Appearance')
                            ->schema([
                                FileUpload::make('logo_light')
                                    ->label('Logo (Light Mode)')
                                    ->image()
                                    ->directory('app-settings')
                                    ->visibility('public')
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('16:9')
                                    ->imageResizeTargetWidth('200')
                                    ->imageResizeTargetHeight('50'),
                                FileUpload::make('logo_dark')
                                    ->label('Logo (Dark Mode)')
                                    ->image()
                                    ->directory('app-settings')
                                    ->visibility('public')
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('16:9')
                                    ->imageResizeTargetWidth('200')
                                    ->imageResizeTargetHeight('50'),
                                ColorPicker::make('primary_color')
                                    ->label('Primary Color'),
                                ColorPicker::make('secondary_color')
                                    ->label('Secondary Color'),
                            ]),
                        Tabs\Tab::make('Notifications')
                            ->schema([
                                Toggle::make('email_notifications')
                                    ->label('Enable Email Notifications'),
                                Toggle::make('system_notifications')
                                    ->label('Enable System Notifications'),
                                TextInput::make('notification_sender_email')
                                    ->label('Notification Sender Email')
                                    ->email(),
                                TextInput::make('notification_sender_name')
                                    ->label('Notification Sender Name'),
                            ]),
                        Tabs\Tab::make('Danger Zone')
                            ->icon('heroicon-o-exclamation-triangle')
                            ->badgeColor('danger')
                            ->schema([
                                Section::make('Reset Settings')
                                    ->description('This will delete all custom settings and restore defaults.')
                                    ->schema([
                                        Toggle::make('confirm_reset')
                                            ->label('I understand this action cannot be undone')
                                            ->default(false)
                                            ->helperText('Check this to enable the reset button.')
                                            ->live(),
                                    ])
                                    ->extraAttributes(['class' => 'border-red-500 border-2 p-4 rounded'])
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function saveSettings()
    {
        $this->validate();

        $formData = $this->form->getState();

        // Remove the confirmation toggle from data to be saved
        if (isset($formData['confirm_reset'])) {
            unset($formData['confirm_reset']);
        }

        foreach ($formData as $key => $value) {
            // Skip the confirmation field
            if ($key === 'confirm_reset') {
                continue;
            }

            // Determine which group the setting belongs to
            $group = 'general';
            if (in_array($key, ['logo_light', 'logo_dark', 'primary_color', 'secondary_color'])) {
                $group = 'appearance';
            } elseif (in_array($key, ['email_notifications', 'system_notifications', 'notification_sender_email', 'notification_sender_name'])) {
                $group = 'notifications';
            }

            // Special handling for file uploads
            if (($key === 'logo_light' || $key === 'logo_dark') && is_array($value)) {
                // If it's an array but empty, set to null
                if (empty($value)) {
                    $value = null;
                }
            }

            AppSettings::set($key, $value, $group, true);
        }

        // Clear cache
        Cache::flush();

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    public function resetSettings()
    {
        // Confirm the user wants to reset
        if (!$this->confirm_reset) {
            Notification::make()
                ->title('Action not confirmed')
                ->body('You must check the confirmation toggle to reset settings.')
                ->warning()
                ->send();
            return;
        }

        // Delete all settings
        AppSettings::query()->delete();

        // Clear cache
        Cache::flush();

        // Reload the defaults
        $this->loadSettings();

        Notification::make()
            ->title('Settings have been reset to defaults')
            ->success()
            ->send();
    }

    public function render()
    {
        return view('livewire.manage-settings');
    }
}
