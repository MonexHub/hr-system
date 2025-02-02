@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Back Button -->
            <div class="mb-6">
                <a href="{{ route('jobs.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Jobs
                </a>
            </div>

            <!-- Job Header -->
            <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $jobPosting->title }}</h1>
                            @if($jobPosting->department)
                                <p class="text-lg text-gray-600 mt-1">{{ $jobPosting->department->name }}</p>
                            @endif
                        </div>
                        @if($jobPosting->isOpen())
                            <a href="{{ route('jobs.apply', $jobPosting) }}"
                               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                Apply Now
                            </a>
                        @endif
                    </div>

                    <!-- Job Meta Information -->
                    <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4 border-t border-gray-200 pt-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Employment Type</span>
                            <p class="mt-1">{{ Str::title(str_replace('_', ' ', $jobPosting->employment_type)) }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Location</span>
                            <p class="mt-1">
                                {{ $jobPosting->location }}
                                @if($jobPosting->is_remote)
                                    <span class="text-green-600">(Remote Available)</span>
                                @endif
                            </p>
                        </div>
                        @if(!$jobPosting->hide_salary)
                            <div>
                                <span class="text-sm font-medium text-gray-500">Salary Range</span>
                                <p class="mt-1">{{ $jobPosting->salary_range }}</p>
                            </div>
                        @endif
                        <div>
                            <span class="text-sm font-medium text-gray-500">Positions Available</span>
                            <p class="mt-1">{{ $jobPosting->positions_available - $jobPosting->positions_filled }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Job Description -->
                    @if($jobPosting->is_document_based)
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold text-gray-900 mb-4">Job Description</h2>
                                <div class="border rounded-lg p-4 bg-gray-50">
                                    <a href="{{ Storage::url($jobPosting->document_path) }}"
                                       class="inline-flex items-center text-indigo-600 hover:text-indigo-900"
                                       target="_blank">
                                        <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                                        </svg>
                                        View Job Description Document
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold text-gray-900 mb-4">Job Description</h2>
                                <div class="prose max-w-none">
                                    {!! Str::markdown($jobPosting->description) !!}
                                </div>
                            </div>
                        </div>

                        @if($jobPosting->requirements)
                            <div class="bg-white overflow-hidden shadow rounded-lg">
                                <div class="p-6">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Requirements</h2>
                                    <ul class="list-disc pl-5 space-y-2">
                                        @foreach($jobPosting->requirements as $requirement)
                                            <li class="text-gray-600">{{ $requirement }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        @if($jobPosting->responsibilities)
                            <div class="bg-white overflow-hidden shadow rounded-lg">
                                <div class="p-6">
                                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Responsibilities</h2>
                                    <ul class="list-disc pl-5 space-y-2">
                                        @foreach($jobPosting->responsibilities as $responsibility)
                                            <li class="text-gray-600">{{ $responsibility }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Application Deadline -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Application Deadline</h2>
                            <p class="text-gray-600">
                                {{ $jobPosting->closing_date ? $jobPosting->closing_date->format('M d, Y') : 'Open until filled' }}
                            </p>
                            @if($jobPosting->closing_date && $jobPosting->closing_date->isPast())
                                <p class="text-red-600 mt-2">This position is now closed</p>
                            @elseif($jobPosting->isOpen())
                                <div class="mt-4">
                                    <a href="{{ route('jobs.apply', $jobPosting) }}"
                                       class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                                        Apply Now
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Benefits -->
                    @if($jobPosting->benefits)
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold text-gray-900 mb-4">Benefits</h2>
                                <ul class="list-disc pl-5 space-y-2">
                                    @foreach($jobPosting->benefits as $benefit)
                                        <li class="text-gray-600">{{ $benefit }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <!-- Share Job -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Share This Job</h2>
                            <div class="space-y-2">
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('jobs.show', $jobPosting)) }}"
                                   target="_blank"
                                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Share on LinkedIn
                                </a>
                                <button onclick="navigator.clipboard.writeText(window.location.href)"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Copy Link
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
