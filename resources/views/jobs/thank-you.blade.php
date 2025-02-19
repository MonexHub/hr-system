@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-indigo-50 py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden transition-all duration-300 hover:shadow-2xl">
                <div class="p-8 text-center">
                    <!-- Animated Checkmark -->
                    <div class="mb-8">
                        <div class="mx-auto h-20 w-20 bg-gradient-to-br from-green-400 to-emerald-600 rounded-full flex items-center justify-center shadow-lg animate-pulse">
                            <svg class="h-12 w-12 text-white animate-scale-in" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>

                    <h2 class="text-3xl font-extrabold text-gray-900 mb-4">
                        Application Submitted Successfully! 🎉
                    </h2>

                    <p class="text-lg text-gray-600 mb-8">
                        Thank you for your interest in joining our team. We've received your application and will begin reviewing it shortly.
                    </p>

                    <!-- Process Timeline -->
                    <div class="mb-10">
                        <div class="relative">
                            <!-- Timeline Line -->
                            <div class="absolute left-1/2 w-0.5 h-full bg-gray-200 transform -translate-x-1/2"></div>

                            <!-- Timeline Steps -->
                            <div class="space-y-8">
                                <div class="relative flex items-center">
                                    <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white z-10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2m4-7V7a4 4 0 00-8 0v5" />
                                        </svg>
                                    </div>
                                    <div class="ml-4 text-left flex-1">
                                        <h3 class="font-semibold text-gray-900">Application Received</h3>
                                        <p class="text-gray-600 text-sm">We've successfully received your application</p>
                                    </div>
                                </div>

                                <div class="relative flex items-center">
                                    <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white z-10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <div class="ml-4 text-left flex-1">
                                        <h3 class="font-semibold text-gray-900">HR Review</h3>
                                        <p class="text-gray-600 text-sm">Our team will evaluate your application</p>
                                    </div>
                                </div>

                                <div class="relative flex items-center">
                                    <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white z-10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4 text-left flex-1">
                                        <h3 class="font-semibold text-gray-900">Next Steps</h3>
                                        <p class="text-gray-600 text-sm">We'll contact you within 1-2 weeks</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Resources -->
                    <div class="bg-gray-50 rounded-xl p-6 mb-8">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">While You Wait</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <a href="" class="p-3 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                <div class="text-indigo-600 mb-2">
                                    <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-700">Learn About Us</span>
                            </a>

                            <a href="" class="p-3 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                <div class="text-indigo-600 mb-2">
                                    <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-700">Contact Support</span>
                            </a>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="mt-8">
                        <a href="{{ route('jobs.index') }}"
                           class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-xl text-white bg-gradient-to-br from-indigo-600 to-purple-500 hover:from-indigo-700 hover:to-purple-600 transition-all transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                            Explore More Opportunities
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

