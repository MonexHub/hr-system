@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search and Filters -->
            <div class="bg-white overflow-hidden shadow rounded-lg mb-6 p-6">
                <form action="{{ route('jobs.index') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                   placeholder="Search job titles...">
                        </div>
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Employment Type</label>
                            <select name="type" id="type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Types</option>
                                <option value="full_time" {{ request('type') === 'full_time' ? 'selected' : '' }}>Full Time</option>
                                <option value="part_time" {{ request('type') === 'part_time' ? 'selected' : '' }}>Part Time</option>
                                <option value="contract" {{ request('type') === 'contract' ? 'selected' : '' }}>Contract</option>
                                <option value="internship" {{ request('type') === 'internship' ? 'selected' : '' }}>Internship</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <!-- Job Listings -->
            <div class="space-y-6">
                @forelse($jobs as $job)
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900">
                                        <a href="{{ route('jobs.show', $job) }}" class="hover:text-indigo-600">
                                            {{ $job->title }}
                                        </a>
                                    </h2>
                                    @if($job->department)
                                        <p class="text-sm text-gray-600 mt-1">{{ $job->department->name }}</p>
                                    @endif
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold text-white bg-indigo-600 rounded-full">
                                {{ Str::title(str_replace('_', ' ', $job->employment_type)) }}
                            </span>
                            </div>

                            <div class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Location</span>
                                    <p class="text-sm text-gray-900">
                                        {{ $job->location }}
                                        @if($job->is_remote)
                                            <span class="text-green-600">(Remote Available)</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Posted</span>
                                    <p class="text-sm text-gray-900">{{ $job->created_at->diffForHumans() }}</p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">Closing Date</span>
                                    <p class="text-sm text-gray-900">
                                        {{ $job->closing_date ? $job->closing_date->format('M d, Y') : 'Open until filled' }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-between items-center">
                                <div class="text-sm text-gray-500">
                                    {{ $job->positions_available - $job->positions_filled }} position(s) available
                                </div>
                                <a href="{{ route('jobs.show', $job) }}"
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-6 text-center text-gray-500">
                            No job postings available at this time.
                        </div>
                    </div>
                @endforelse

                <!-- Pagination -->
                @if($jobs->hasPages())
                    <div class="mt-6">
                        {{ $jobs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
