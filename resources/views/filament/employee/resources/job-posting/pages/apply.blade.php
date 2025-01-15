<x-filament-panels::page>
    {{-- Header Section with Breadcrumb and Status --}}
    <div class="flex justify-between items-start">
        <div class="space-y-2">
            <nav class="flex text-gray-400 text-sm" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="{{ route('filament.employee.resources.job-postings.index') }}"
                           class="hover:text-primary-500 transition-colors">
                            Job Postings
                        </a>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mx-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span>Apply for Position</span>
                    </li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold">{{ $record->title }}</h1>
        </div>

        {{-- Application Deadline Badge --}}
        @if($record->closing_date)
            <div class="inline-flex items-center px-3 py-1 rounded-full text-sm
                {{ now()->gt($record->closing_date) ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ now()->gt($record->closing_date) ? 'Closed' : 'Open until ' . $record->closing_date->format('M j, Y') }}
            </div>
        @endif
    </div>

    {{-- Job Overview Card --}}
    <div class="mt-8 rounded-lg border border-gray-800 overflow-hidden">
        {{-- Header Banner --}}
        <div class="bg-gray-800 p-4">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-300">Reference: {{ $record->position_code }}</span>
                @if($record->is_remote)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                        </svg>
                        Remote Available
                    </span>
                @endif
            </div>
        </div>

        {{-- Job Details Content --}}
        <div class="p-6 space-y-8">
            {{-- Key Information Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Original department, location, and salary information... --}}
            </div>

            {{-- Highlights Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-gray-800">
                <div class="space-y-2">
                    <h4 class="text-sm font-medium text-gray-300">Employment Type</h4>
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-gray-400">{{ $record->employment_type }}</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <h4 class="text-sm font-medium text-gray-300">Experience Level</h4>
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        <span class="text-gray-400">{{ $record->experience_level }}</span>
                    </div>
                </div>
            </div>

            {{-- Rest of your existing content (Job Description, Requirements) --}}
        </div>
    </div>

    {{-- Application Form Section --}}
    <div class="mt-8 space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-medium">Application Form</h2>
                <p class="mt-1 text-sm text-gray-400">Please complete all required fields</p>
            </div>
            <div class="text-sm text-gray-400">
                <span class="text-red-400">*</span> Required fields
            </div>
        </div>

        <div class="rounded-lg border border-gray-800">
            <div class="p-6">
                <form wire:submit="submit" class="space-y-8">
                    {{ $this->form }}

                    <div class="flex items-center justify-between pt-6 border-t border-gray-800">
                        <p class="text-sm text-gray-400">
                            By submitting this application, you confirm that all provided information is accurate.
                        </p>
                        <x-filament::button
                            type="submit"
                            size="lg"
                            class="inline-flex items-center"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Submit Application
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Application Tips --}}
    <div class="mt-8 rounded-lg border border-gray-800 bg-gray-900/50 p-4">
        <h4 class="text-sm font-medium text-gray-300">Application Tips</h4>
        <ul class="mt-2 space-y-2 text-sm text-gray-400">
            <li class="flex items-start">
                <svg class="w-4 h-4 mt-0.5 mr-2 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Ensure your CV is up-to-date and tailored to this position
            </li>
            <li class="flex items-start">
                <svg class="w-4 h-4 mt-0.5 mr-2 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Provide detailed examples in your cover letter
            </li>
            <li class="flex items-start">
                <svg class="w-4 h-4 mt-0.5 mr-2 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Double-check all attachments before submission
            </li>
        </ul>
    </div>
</x-filament-panels::page>
