@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Back Navigation -->
            <div class="mb-8">
                <a href="{{ route('jobs.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-900 font-medium group transition-colors">
                    <svg class="w-5 h-5 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Open Positions
                </a>
            </div>

            <!-- Job Header Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 mb-8">
                <div class="p-8">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between">
                        <div class="flex-1 mb-6 md:mb-0">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $jobPosting->title }}</h1>
                            @if($jobPosting->department)
                                <div class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-100 text-indigo-800 text-sm font-medium">
                                    {{ $jobPosting->department->name }}
                                </div>
                            @endif
                        </div>

                        @if($jobPosting->isOpen())
                            <div class="md:text-right">
                                <a href="{{ route('jobs.apply', $jobPosting) }}" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-xl shadow-sm text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transition-all transform hover:shadow-md">
                                    Apply Now
                                    <svg class="ml-2 -mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                </a>
                                <p class="mt-2 text-sm text-gray-500">
                                    {{ $jobPosting->positions_available - $jobPosting->positions_filled }} positions remaining
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Meta Grid -->
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0 text-indigo-600">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Employment Type</span>
                                <p class="font-medium text-gray-900">
                                    {{ Str::title(str_replace('_', ' ', $jobPosting->employment_type)) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0 text-indigo-600">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Location</span>
                                <p class="font-medium text-gray-900">
                                    {{ $jobPosting->location }}
                                    @if($jobPosting->is_remote)
                                        <span class="text-indigo-600">(Remote)</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if(!$jobPosting->hide_salary)
                            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0 text-indigo-600">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Salary Range</span>
                                    <p class="font-medium text-gray-900">{{ $jobPosting->salary_range }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0 text-indigo-600">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Posted</span>
                                <p class="font-medium text-gray-900">
                                    {{ $jobPosting->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    @if($jobPosting->is_document_based)
                        <!-- Document Card -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Job Description</h2>
                                    <p class="text-gray-600">Download the full job description document</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <svg class="h-12 w-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-6">
                                <a href="{{ Storage::url($jobPosting->document_path) }}" target="_blank" class="inline-flex items-center px-5 py-3 border border-transparent text-base font-medium rounded-xl text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition-colors">
                                    <svg class="h-6 w-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                                    </svg>
                                    Download PDF
                                </a>
                            </div>
                        </div>
                    @else
                        <!-- Job Details Content -->
                        <div class="space-y-8">
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                                <h2 class="text-2xl font-semibold text-gray-900 mb-6">Position Details</h2>
                                <div class="prose prose-indigo max-w-none">
                                    {!! Str::markdown($jobPosting->description) !!}
                                </div>
                            </div>

                            @if($jobPosting->requirements)
                                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Key Requirements</h3>
                                    <ul class="space-y-4">
                                        @foreach($jobPosting->requirements as $requirement)
                                            <li class="flex items-start space-x-3">
                                                <svg class="flex-shrink-0 h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span class="text-gray-700">{{ $requirement }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($jobPosting->responsibilities)
                                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Core Responsibilities</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        @foreach($jobPosting->responsibilities as $responsibility)
                                            <div class="flex items-start space-x-3 bg-gray-50 p-4 rounded-lg">
                                                <svg class="flex-shrink-0 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                                <span class="text-gray-700">{{ $responsibility }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-8">
                    <!-- Application Deadline Card -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="flex-shrink-0 text-indigo-600">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Application Deadline</h3>
                                <p class="text-gray-600">
                                    {{ $jobPosting->closing_date ? $jobPosting->closing_date->format('M j, Y') : 'Open until filled' }}
                                </p>
                            </div>
                        </div>
                        @if($jobPosting->closing_date && $jobPosting->closing_date->isPast())
                            <div class="mt-4 px-4 py-3 bg-red-50 text-red-700 rounded-lg border border-red-200">
                                This position is now closed
                            </div>
                        @elseif($jobPosting->isOpen())
                            <div class="mt-6">
                                <a href="{{ route('jobs.apply', $jobPosting) }}" class="w-full flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-xl shadow-sm text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transition-all">
                                    Apply Now
                                </a>
                            </div>
                        @endif
                    </div>

                    @if($jobPosting->benefits)
                        <!-- Benefits Card -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Employee Benefits</h3>
                            <ul class="space-y-3">
                                @foreach($jobPosting->benefits as $benefit)
                                    <li class="flex items-start space-x-3">
                                        <svg class="flex-shrink-0 h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="text-gray-700">{{ $benefit }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Share Card -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Share This Position</h3>
                        <div class="space-y-3">
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('jobs.show', $jobPosting)) }}"
                               target="_blank"
                               class="w-full flex items-center justify-center space-x-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                <svg class="h-5 w-5 text-[#0077b5]" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                                </svg>
                                <span>Share on LinkedIn</span>
                            </a>

                            <button onclick="copyToClipboard()"
                                    class="w-full flex items-center justify-center space-x-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                                <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <span>Copy Job Link</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                const toast = document.createElement('div');
                toast.className = 'fixed bottom-4 right-4 px-4 py-2 bg-gray-800 text-white rounded-lg shadow-lg text-sm';
                toast.textContent = 'Link copied to clipboard!';
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 2000);
            });
        }
    </script>
@endsection
