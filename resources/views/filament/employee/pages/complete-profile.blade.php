<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Introduction Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="space-y-2">
                <div class="flex items-center space-x-2">
                    <x-heroicon-s-user-circle class="w-6 h-6 text-primary-500"/>
                    <h2 class="text-xl font-bold tracking-tight">Welcome to {{ config('app.name') }}</h2>
                </div>
                <p class="text-gray-500 dark:text-gray-400">
                    Complete your profile to unlock all features. This information helps us personalize your experience
                    and ensure seamless communication.
                </p>
            </div>
        </div>

        {{-- Main Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <x-filament-panels::form wire:submit="submit">
                    {{ $this->form }}
                </x-filament-panels::form>
            </div>
        </div>

        {{-- Tips and Information Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Document Requirements --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="space-y-4">
                    <div class="flex items-center space-x-2">
                        <x-heroicon-s-document-text class="w-5 h-5 text-primary-500"/>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Document Requirements</h3>
                    </div>
                    <ul class="space-y-3 text-sm text-gray-500 dark:text-gray-400">
                        <li class="flex items-center space-x-2">
                            <x-heroicon-s-check-circle class="w-4 h-4 text-green-500"/>
                            <span>Valid government-issued ID</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <x-heroicon-s-check-circle class="w-4 h-4 text-green-500"/>
                            <span>Updated resume/CV</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <x-heroicon-s-check-circle class="w-4 h-4 text-green-500"/>
                            <span>Professional photo</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Profile Tips --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="space-y-4">
                    <div class="flex items-center space-x-2">
                        <x-heroicon-s-light-bulb class="w-5 h-5 text-primary-500"/>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Profile Tips</h3>
                    </div>
                    <ul class="space-y-3 text-sm text-gray-500 dark:text-gray-400">
                        <li class="flex items-center space-x-2">
                            <x-heroicon-s-check-circle class="w-4 h-4 text-green-500"/>
                            <span>Use a professional profile photo</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <x-heroicon-s-check-circle class="w-4 h-4 text-green-500"/>
                            <span>Provide accurate contact details</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <x-heroicon-s-check-circle class="w-4 h-4 text-green-500"/>
                            <span>Keep emergency contacts updated</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Help & Support --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="space-y-4">
                    <div class="flex items-center space-x-2">
                        <x-heroicon-s-user class="w-5 h-5 text-primary-500"/>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Help & Support</h3>
                    </div>
                    <div class="space-y-3 text-sm text-gray-500 dark:text-gray-400">
                        <p>Need assistance? Our support team is here to help:</p>
                        <div class="flex items-center space-x-2">
                            <x-heroicon-s-envelope class="w-4 h-4 text-primary-500"/>
                            <a href="mailto:support@example.com" class="text-primary-600 hover:text-primary-500">
                                support@example.com
                            </a>
                        </div>
                        <div class="flex items-center space-x-2">
                            <x-heroicon-s-phone class="w-4 h-4 text-primary-500"/>
                            <span>+1 (555) 000-0000</span>
                        </div>
                        <div class="pt-2">
                            <p class="text-xs">Available Monday to Friday, 9 AM - 5 PM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Progress Indicator --}}
        <div wire:loading class="fixed bottom-4 right-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-primary-500"></div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Saving your profile...</span>
                </div>
            </div>
        </div>

        {{-- Footer Notes --}}
        <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
            <p class="flex items-center space-x-1">
                <x-heroicon-s-shield-check class="w-4 h-4 text-green-500"/>
                <span>Your information is securely stored and protected</span>
            </p>
            <p class="flex items-center space-x-1">
                <x-heroicon-s-clock class="w-4 h-4 text-primary-500"/>
                <span>This process typically takes 5-10 minutes to complete</span>
            </p>
        </div>
    </div>

    {{-- Success Modal --}}
    <div
        x-data="{ show: false }"
        x-show="show"
        x-on:notify-profile-completed.window="show = true; setTimeout(() => show = false, 3000)"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-90"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-90"
        class="fixed bottom-4 right-4 z-50"
        style="display: none;"
    >
        <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg shadow-lg p-4">
            <div class="flex items-center space-x-3">
                <x-heroicon-s-check-circle class="w-6 h-6 text-green-500 dark:text-green-400"/>
                <span class="text-green-800 dark:text-green-200">Profile successfully updated!</span>
            </div>
        </div>
    </div>
</x-filament-panels::page>
