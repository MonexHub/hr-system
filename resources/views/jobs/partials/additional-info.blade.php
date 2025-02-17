<!-- Additional Information Section -->
<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-900">Additional Information</h2>

    <!-- Professional Summary -->
    <div class="relative">
        <textarea name="professional_summary" id="professional_summary" rows="4"
                  class="block w-full rounded-lg border-gray-300 focus:border-indigo-600 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  placeholder="Tell us about your professional background and why you're interested in this position">{{ old('professional_summary') }}</textarea>
        <label for="professional_summary" class="block text-sm font-medium text-gray-700 mb-2">
            Professional Summary
        </label>
        @error('professional_summary')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- URLs -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Portfolio URL -->
        <div class="relative">
            <input type="url" name="portfolio_url" id="portfolio_url"
                   class="peer h-12 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-indigo-600"
                   value="{{ old('portfolio_url') }}">
            <label for="portfolio_url" class="absolute left-0 -top-3.5 text-gray-600 text-sm">
                Portfolio URL
            </label>
            @error('portfolio_url')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- LinkedIn URL -->
        <div class="relative">
            <input type="url" name="linkedin_url" id="linkedin_url"
                   class="peer h-12 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-indigo-600"
                   value="{{ old('linkedin_url') }}">
            <label for="linkedin_url" class="absolute left-0 -top-3.5 text-gray-600 text-sm">
                LinkedIn URL
            </label>
            @error('linkedin_url')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
