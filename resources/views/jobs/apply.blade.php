@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>

                    <h2 class="mt-4 text-2xl font-bold text-gray-900">Application Submitted Successfully!</h2>

                    <p class="mt-2 text-gray-600">
                        Thank you for your interest in joining our team. We have received your application and will review it shortly.
                    </p>

                    <div class="mt-6">
                        <p class="text-sm text-gray-500">What happens next?</p>
                        <ul class="mt-2 text-sm text-gray-600 space-y-1">
                            <li>Our HR team will review your application</li>
                            <li>If your profile matches our requirements, we'll contact you for next steps</li>
                            <li>The review process typically takes 1-2 weeks</li>
                        </ul>
                    </div>

                    <div class="mt-8">
                        <a href="{{ route('jobs.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                            View More Opportunities
                        </a>
                    </div>
                    <!-- Track Application -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <p class="text-sm text-gray-500">Keep this reference number for your records:</p>
                        <p class="mt-2 text-lg font-mono font-semibold text-gray-900">{{ session('application_reference') }}</p>
                    </div>

                    <div class="mt-8">
                        <a href="{{ route('jobs.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                            View More Opportunities
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
