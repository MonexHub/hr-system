@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-b from-[#fdfdfd] to-[#001f3f] py-12">

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Progress Bar -->
            <div class="max-w-3xl mx-auto mb-8">
                <div class="relative">
                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
                        <div id="progress-bar" class="bg-blue-500 rounded transition-all duration-500 ease-out" style="width:0%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600">
                        <span>Personal Info</span>
                        <span>Professional Info</span>
                        <span>Documents</span>
                    </div>
                </div>
            </div>

            <!-- Job Information Card -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-8 mb-8 transform hover:scale-[1.01] transition-transform duration-300">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="space-y-2">
                        <h1 class="text-4xl font-bold text-gray-900">{{ $jobPosting->title }}</h1>
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center text-gray-600">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span>{{ $jobPosting->department->name }}</span>
                            </div>
                            <span class="inline-flex items-center px-4 py-1 rounded-full text-sm font-semibold bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-700/10">
                            {{ $jobPosting->employment_type }}
                        </span>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->has('error') || $errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                {{ $errors->first('error') ?: 'There was an error submitting your application. Please check all fields and try again.' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Session Success Message -->
            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-8 rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                {{ session('success') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Application Form -->

            <form action="{{ route('jobs.store', $jobPosting) }}" method="POST" enctype="multipart/form-data" class="space-y-8" id="applicationForm">
                @csrf
                <!-- Personal Information Section -->
                <div  class="bg-white rounded-xl shadow-md border border-gray-100 p-8 transition-all duration-300" data-section="1">
                    <div class="flex items-center space-x-2 mb-8">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-700 font-semibold text-lg">1</div>
                        <h2 class="text-2xl font-semibold text-gray-900">Personal Information</h2>
                    </div>

                    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                        <div class="space-y-8">
                            <div class="relative group">
                                <label for="first_name" class="block text-base font-medium text-gray-700 mb-2">First Name</label>
                                <input type="text" name="first_name" id="first_name" required maxlength="255"
                                       class="block w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 @error('first_name') border-red-500 @enderror"
                                       value="{{ old('first_name') }}"
                                       placeholder="Enter your first name">
                                <div class="absolute top-0 right-0 text-red-500">
                                    <span class="text-lg">*</span>
                                </div>
                                @error('first_name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="relative">
                                <label for="email" class="block text-base font-medium text-gray-700 mb-2">Email Address</label>
                                <input type="email" name="email" id="email" required maxlength="255"
                                       class="block w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 @error('email') border-red-500 @enderror"
                                       value="{{ old('email') }}"
                                       placeholder="you@example.com">
                                <div class="absolute top-0 right-0 text-red-500">
                                    <span class="text-lg">*</span>
                                </div>
                                @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="relative">
                                <label for="nationality" class="block text-base font-medium text-gray-700 mb-2">Nationality</label>
                                <select name="nationality" id="nationality" required
                                        class="block w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 @error('nationality') border-red-500 @enderror">
                                    <option value="">Select your nationality</option>
                                    @foreach($nationalityOptions as $value => $label)
                                        <option value="{{ $value }}" {{ old('nationality') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute top-0 right-0 text-red-500">
                                    <span class="text-lg">*</span>
                                </div>
                                @error('nationality')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="space-y-8">
                            <div class="relative">
                                <label for="last_name" class="block text-base font-medium text-gray-700 mb-2">Last Name</label>
                                <input type="text" name="last_name" id="last_name" required maxlength="255"
                                       class="block w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 @error('last_name') border-red-500 @enderror"
                                       value="{{ old('last_name') }}"
                                       placeholder="Enter your last name">
                                <div class="absolute top-0 right-0 text-red-500">
                                    <span class="text-lg">*</span>
                                </div>
                                @error('last_name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="relative">
                                <label for="phone" class="block text-base font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" name="phone" id="phone" required maxlength="20"
                                       class="block w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 @error('phone') border-red-500 @enderror"
                                       value="{{ old('phone') }}"
                                       placeholder="(555) 555-5555">
                                <div class="absolute top-0 right-0 text-red-500">
                                    <span class="text-lg">*</span>
                                </div>
                                @error('phone')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Professional Information Section -->
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-8 transition-all duration-300" data-section="2">
                    <div class="flex items-center space-x-2 mb-8">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-700 font-semibold text-lg">2</div>
                        <h2 class="text-2xl font-semibold text-gray-900">Professional Information</h2>
                    </div>

                    <div class="space-y-8">
                        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                            <div class="relative">
                                <label for="current_job_title" class="block text-base font-medium text-gray-700 mb-2">Current Job Title</label>
                                <input type="text" name="current_job_title" id="current_job_title" required maxlength="255"
                                       class="block w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 @error('current_job_title') border-red-500 @enderror"
                                       value="{{ old('current_job_title') }}"
                                       placeholder="Enter your current position">
                                <div class="absolute top-0 right-0 text-red-500">
                                    <span class="text-lg">*</span>
                                </div>
                                @error('current_job_title')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="relative">
                                <label for="years_of_experience" class="block text-base font-medium text-gray-700 mb-2">Years of Experience</label>
                                <select name="years_of_experience" id="years_of_experience" required
                                        class="block w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 @error('years_of_experience') border-red-500 @enderror">
                                    <option value="">Select years of experience</option>
                                    @foreach($experienceOptions as $value => $label)
                                        <option value="{{ $value }}" {{ old('years_of_experience') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute top-0 right-0 text-red-500">
                                    <span class="text-lg">*</span>
                                </div>
                                @error('years_of_experience')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="space-y-8">
                            <div>
                                <label for="professional_summary" class="block text-base font-medium text-gray-700 mb-2">Professional Summary</label>
                                <textarea name="professional_summary" id="professional_summary" rows="4" maxlength="1000"
                                          class="block w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 @error('professional_summary') border-red-500 @enderror"
                                          placeholder="Brief overview of your professional background">{{ old('professional_summary') }}</textarea>
                                @error('professional_summary')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                                <div>
                                    <label for="expected_salary" class="block text-base font-medium text-gray-700 mb-2">Expected Salary</label>
                                    <input type="number" name="expected_salary" id="expected_salary" min="0" step="1000"
                                           class="block w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 @error('expected_salary') border-red-500 @enderror"
                                           value="{{ old('expected_salary') }}"
                                           placeholder="Enter expected salary">
                                    @error('expected_salary')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="notice_period_days" class="block text-base font-medium text-gray-700 mb-2">Notice Period (Days)</label>
                                    <input type="number" name="notice_period_days" id="notice_period_days" min="0" max="180"
                                           class="block w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 @error('notice_period_days') border-red-500 @enderror"
                                           value="{{ old('notice_period_days') }}"
                                           placeholder="Enter notice period in days">
                                    @error('notice_period_days')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents Section -->
                <div class="bg-white rounded-xl shadow-md border border-gray-100 p-8 transition-all duration-300" data-section="3">
                    <div class="flex items-center space-x-2 mb-8">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-700 font-semibold text-lg">3</div>
                        <h2 class="text-2xl font-semibold text-gray-900">Documents & Links</h2>
                    </div>

                    <div class="space-y-8">
                        <div class="relative">
                            <label class="block text-base font-medium text-gray-700 mb-4">Resume</label>
                            <div class="mt-2 flex justify-center px-8 pt-6 pb-8 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-500 transition-colors duration-200 @error('resume') border-red-500 @enderror">
                                <div class="space-y-2 text-center">
                                    <svg class="mx-auto h-14 w-14 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <div class="flex text-base text-gray-600">
                                        <label for="resume" class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2">
                                            <span>Upload a file</span>
                                            <input id="resume" name="resume" type="file" class="sr-only" required accept=".pdf,.doc,.docx">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-sm text-gray-500">PDF, DOC, DOCX up to 5MB</p>
                                </div>
                            </div>
                            <div class="absolute top-0 right-0 text-red-500">
                                <span class="text-lg">*</span>
                            </div>
                            @error('resume')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="relative">
                            <label class="block text-base font-medium text-gray-700 mb-4">Cover Letter</label>
                            <div class="mt-2 flex justify-center px-8 pt-6 pb-8 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-500 transition-colors duration-200 @error('cover_letter') border-red-500 @enderror">
                                <div class="space-y-2 text-center">
                                    <svg class="mx-auto h-14 w-14 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <div class="flex text-base text-gray-600">
                                        <label for="cover_letter" class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2">
                                            <span>Upload a file</span>
                                            <input id="cover_letter" name="cover_letter" type="file" class="sr-only" accept=".pdf,.doc,.docx">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-sm text-gray-500">PDF, DOC, DOCX up to 5MB</p>
                                </div>
                            </div>
                            @error('cover_letter')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Professional Links -->
                        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                            <div>
                                <label for="portfolio_url" class="block text-base font-medium text-gray-700 mb-2">Portfolio URL</label>
                                <input type="url" name="portfolio_url" id="portfolio_url"
                                       class="block w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 @error('portfolio_url') border-red-500 @enderror"
                                       value="{{ old('portfolio_url') }}"
                                       placeholder="https://your-portfolio.com">
                                @error('portfolio_url')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="linkedin_url" class="block text-base font-medium text-gray-700 mb-2">LinkedIn URL</label>
                                <input type="url" name="linkedin_url" id="linkedin_url"
                                       class="block w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 @error('linkedin_url') border-red-500 @enderror"
                                       value="{{ old('linkedin_url') }}"
                                       placeholder="https://linkedin.com/in/your-profile">
                                @error('linkedin_url')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="bg-gray-50 rounded-xl p-8">
                    <div class="flex items-start space-x-3">
                        <div class="flex items-center h-6">
                            <input id="terms" name="terms" type="checkbox" required
                                   class="h-5 w-5 rounded border-2 border-gray-300 text-blue-600 focus:ring-blue-500">
                        </div>
                        <div class="text-base">
                            <label for="terms" class="text-gray-700">
                                I agree to the
                                <a href="#" class="font-medium text-blue-600 hover:text-blue-500">Terms and Conditions</a>
                                and confirm that all information provided is accurate.
                            </label>
                        </div>
                    </div>
                    @error('terms')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <button type="button"
                            class="px-8 py-4 bg-white text-gray-700 rounded-lg border-2 border-gray-300 text-lg font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        Save as Draft
                    </button>
                    <button type="submit"
                            class="px-8 py-4 bg-blue-600 text-white rounded-lg shadow-sm text-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 flex items-center">
                        <span>Submit Application</span>
                        <svg class="ml-2 -mr-1 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </form>


        </div>

    </div>

    <!-- Progress Bar Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('applicationForm');
            const sections = form.querySelectorAll('[data-section]');
            const progressBar = document.getElementById('progress-bar');

            function updateProgress() {
                let filledSections = 0;
                sections.forEach(section => {
                    const inputs = section.querySelectorAll('input[required], select[required], textarea[required]');
                    const isComplete = Array.from(inputs).every(input => input.value.trim() !== '');
                    if (isComplete) filledSections++;
                });

                const progress = (filledSections / sections.length) * 100;
                progressBar.style.width = `${progress}%`;
            }

            form.addEventListener('input', updateProgress);
            form.addEventListener('change', updateProgress);
        });
    </script>


    <script>
        document.getElementById('applicationForm').addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = `
        <span class="inline-flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Submitting...
        </span>
    `;
        });
    </script>
@endsection
