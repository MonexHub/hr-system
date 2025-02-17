@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100">
        <!-- Hero Section -->
        <div class="relative bg-indigo-900 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-800 to-purple-900 opacity-75"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <div class="text-center">
                    <h1 class="text-4xl font-extrabold text-white sm:text-5xl md:text-6xl animate-fade-in-down">
                        Transform Your Career
                    </h1>
                    <p class="mt-4 max-w-2xl text-xl text-indigo-100 mx-auto animate-fade-in-up delay-100">
                        Join innovators shaping the future. Discover roles that match your ambition and expertise.
                    </p>
                </div>

                <!-- Stats Grid -->
                <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-3 lg:grid-cols-3 animate-slide-up delay-200">
                    <div class="bg-white/10 backdrop-blur-lg rounded-xl p-6 transition-transform hover:scale-105">
                        <dt class="text-sm font-medium text-indigo-300">Open Positions</dt>
                        <dd class="mt-2 text-4xl font-bold text-white">{{ $jobs->total() }}</dd>
                    </div>
                    <div class="bg-white/10 backdrop-blur-lg rounded-xl p-6 transition-transform hover:scale-105">
                        <dt class="text-sm font-medium text-indigo-300">Departments</dt>
                        <dd class="mt-2 text-4xl font-bold text-white">{{ $departments->count() }}</dd>
                    </div>
                    <div class="bg-white/10 backdrop-blur-lg rounded-xl p-6 transition-transform hover:scale-105">
                        <dt class="text-sm font-medium text-indigo-300">Remote Roles</dt>
                        <dd class="mt-2 text-4xl font-bold text-white">{{ $jobs->where('is_remote', true)->count() }}</dd>
                    </div>
                </div>
            </div>

            <!-- Animated Waves Background -->
            <div class="absolute bottom-0 left-0 right-0">
                <svg class="animate-waves" viewBox="0 24 150 28" preserveAspectRatio="none">
                    <defs>
                        <path id="wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z"/>
                    </defs>
                    <g class="wave-bg">
                        <use xlink:href="#wave" x="50" y="0" fill="rgba(255,255,255,0.1)"/>
                        <use xlink:href="#wave" x="50" y="3" fill="rgba(255,255,255,0.2)"/>
                        <use xlink:href="#wave" x="50" y="6" fill="rgba(255,255,255,0.3)"/>
                    </g>
                </svg>
            </div>
        </div>

        <!-- Search Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 z-10 relative">
            <div class="bg-white rounded-2xl shadow-2xl p-8 border border-gray-100/50 backdrop-blur-lg">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Find Your Next Role</h2>
                <form action="{{ route('jobs.index') }}" method="GET" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Search Input -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="search" id="search" value="{{ request('search') }}"
                                   class="w-full pl-10 pr-4 py-3 border-0 ring-1 ring-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-shadow"
                                   placeholder="Job title, keywords...">
                        </div>

                        <!-- Department Select -->
                        <div class="relative">
                            <select name="department" id="department"
                                    class="w-full pl-4 pr-10 py-3 border-0 ring-1 ring-gray-200 rounded-xl appearance-none focus:ring-2 focus:ring-indigo-500 bg-select-arrow bg-no-repeat bg-[length:20px_20px] bg-[right:1rem_center] transition-shadow">
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ request('department') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Employment Type Select -->
                        <div class="relative">
                            <select name="type" id="type"
                                    class="w-full pl-4 pr-10 py-3 border-0 ring-1 ring-gray-200 rounded-xl appearance-none focus:ring-2 focus:ring-indigo-500 bg-select-arrow bg-no-repeat bg-[length:20px_20px] bg-[right:1rem_center] transition-shadow">
                                <option value="">All Employment Types</option>
                                @foreach(['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'internship' => 'Internship'] as $key => $label)
                                    <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('jobs.index') }}"
                           class="px-6 py-2.5 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                            Clear Filters
                        </a>
                        <button type="submit"
                                class="px-6 py-2.5 text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Search Jobs
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Jobs Grid -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-bold text-gray-900">
                    Latest Opportunities
                    @if(request()->anyFilled(['search', 'department', 'type']))
                        <span class="text-gray-500 text-lg">(Filtered Results)</span>
                    @endif
                </h3>
                <span class="text-sm text-gray-500">
                {{ $jobs->firstItem() ?? 0 }} - {{ $jobs->lastItem() ?? 0 }} of {{ $jobs->total() }} results
            </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($jobs as $job)
                    <div class="group bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-100 hover:border-indigo-100">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900">
                                        <a href="{{ route('jobs.show', $job) }}" class="hover:text-indigo-600 transition-colors">
                                            {{ $job->title }}
                                        </a>
                                    </h4>
                                    @if($job->department)
                                        <span class="text-sm text-indigo-600 font-medium">
                                {{ $job->department->name }}
                            </span>
                                    @endif
                                </div>
                                <span class="px-3 py-1 text-sm font-medium rounded-full
                                {{ $job->employment_type === 'full_time'
                                    ? 'bg-green-100 text-green-800'
                                    : ($job->employment_type === 'part_time'
                                        ? 'bg-blue-100 text-blue-800'
                                        : ($job->employment_type === 'contract'
                                            ? 'bg-purple-100 text-purple-800'
                                            : 'bg-yellow-100 text-yellow-800')) }}">
                                {{ Str::title(str_replace('_', ' ', $job->employment_type)) }}
                            </span>
                            </div>

                            <div class="space-y-3 text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>
                                {{ $job->location }}
                                        @if($job->is_remote)
                                            <span class="text-indigo-600 font-medium">(Remote)</span>
                                        @endif
                            </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>Posted {{ $job->created_at->diffForHumans() }}</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Closes {{ $job->closing_date?->format('M j, Y') ?? 'Soon' }}</span>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-between items-center">
                        <span class="text-sm font-medium text-indigo-600">
                            {{ $job->positions_available - $job->positions_filled }} positions left
                        </span>
                                <a href="{{ route('jobs.show', $job) }}"
                                   class="flex items-center gap-1.5 text-indigo-600 hover:text-indigo-700 font-medium">
                                    View Role
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="md:col-span-3">
                        <div class="text-center bg-white rounded-2xl p-12 border-2 border-dashed border-gray-200">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="mt-4 text-xl font-medium text-gray-900">No positions found</h3>
                            <p class="mt-2 text-sm text-gray-500">Try adjusting your filters or set up job alerts</p>
                            <div class="mt-6">
                                <button class="inline-flex items-center px-6 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    Create Job Alert
                                </button>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($jobs->hasPages())
                <div class="mt-6">
                    {{ $jobs->onEachSide(1)->links('vendor.pagination.modern') }}
                </div>
            @endif
        </div>
    </div>

    <style>
        .animate-fade-in-down {
            animation: fadeInDown 0.6s ease-out;
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .animate-slide-up {
            animation: slideUp 0.8s cubic-bezier(0.22, 1, 0.36, 1);
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-waves {
            animation: wave 12s linear infinite;
        }

        @keyframes wave {
            0% { transform: translateX(0); }
            50% { transform: translateX(-25%); }
            100% { transform: translateX(0); }
        }

        .wave-bg {
            fill: rgba(255,255,255,0.15);
        }
    </style>
@endsection
