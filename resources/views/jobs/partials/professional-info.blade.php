<!-- Professional Information Section -->
<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-900">Professional Information</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Current Job Title -->
        <div class="relative">
            <input type="text" name="current_job_title" id="current_job_title" required
                   class="peer h-12 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-indigo-600"
                   value="{{ old('current_job_title') }}">
            <label for="current_job_title" class="absolute left-0 -top-3.5 text-gray-600 text-sm">
                Current Job Title <span class="text-red-500">*</span>
            </label>
            @error('current_job_title')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Years of Experience -->
        <div class="relative">
            <select name="years_of_experience" id="years_of_experience" required
                    class="peer h-12 w-full border-b-2 border-gray-300 text-gray-900 focus:outline-none focus:border-indigo-600 bg-transparent">
                <option value="">Select Experience</option>
                @foreach($experienceOptions as $value => $label)
                    <option value="{{ $value }}" {{ old('years_of_experience') == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <label for="years_of_experience" class="absolute left-0 -top-3.5 text-gray-600 text-sm">
                Years of Experience <span class="text-red-500">*</span>
            </label>
            @error('years_of_experience')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Expected Salary -->
        <div class="relative">
            <input type="number" name="expected_salary" id="expected_salary"
                   class="peer h-12 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-indigo-600"
                   value="{{ old('expected_salary') }}">
            <label for="expected_salary" class="absolute left-0 -top-3.5 text-gray-600 text-sm">
                Expected Salary
            </label>
            @error('expected_salary')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Notice Period -->
        <div class="relative">
            <input type="number" name="notice_period_days" id="notice_period_days"
                   class="peer h-12 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-indigo-600"
                   value="{{ old('notice_period_days') }}"
                   min="0" max="180">
            <label for="notice_period_days" class="absolute left-0 -top-3.5 text-gray-600 text-sm">
                Notice Period (days)
            </label>
            @error('notice_period_days')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
