<div class="max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Apply for {{ $jobPosting->title }}</h2>

    <form wire:submit="submit" class="space-y-6">
        @if ($errors->has('form'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ $errors->first('form') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block mb-1">First Name</label>
                <input type="text" wire:model="first_name" class="w-full border rounded px-3 py-2">
                @error('first_name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-1">Last Name</label>
                <input type="text" wire:model="last_name" class="w-full border rounded px-3 py-2">
                @error('last_name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-1">Email</label>
                <input type="email" wire:model="email" class="w-full border rounded px-3 py-2">
                @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-1">Phone</label>
                <input type="tel" wire:model="phone" class="w-full border rounded px-3 py-2">
                @error('phone') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-1">Nationality</label>
                <select wire:model="nationality" class="w-full border rounded px-3 py-2">
                    <option value="">Select Nationality</option>
                    @foreach($nationalityOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('nationality') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-1">Current Job Title</label>
                <input type="text" wire:model="current_job_title" class="w-full border rounded px-3 py-2">
                @error('current_job_title') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-1">Years of Experience</label>
                <select wire:model="years_of_experience" class="w-full border rounded px-3 py-2">
                    <option value="">Select Experience</option>
                    @foreach($experienceOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('years_of_experience') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block mb-1">Resume</label>
                <input type="file" wire:model="resume" accept=".pdf,.doc,.docx">
                @error('resume') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-1">Cover Letter (Optional)</label>
                <input type="file" wire:model="cover_letter" accept=".pdf,.doc,.docx">
                @error('cover_letter') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-1">Portfolio URL (Optional)</label>
                <input type="url" wire:model="portfolio_url" class="w-full border rounded px-3 py-2">
                @error('portfolio_url') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-1">LinkedIn URL (Optional)</label>
                <input type="url" wire:model="linkedin_url" class="w-full border rounded px-3 py-2">
                @error('linkedin_url') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-1">Expected Salary (Optional)</label>
                <input type="number" wire:model="expected_salary" class="w-full border rounded px-3 py-2">
                @error('expected_salary') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-1">Notice Period (Days)</label>
                <input type="number" wire:model="notice_period_days" class="w-full border rounded px-3 py-2" min="0" max="180">
                @error('notice_period_days') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block mb-1">Professional Summary (Optional)</label>
                <textarea wire:model="professional_summary" rows="4" class="w-full border rounded px-3 py-2"></textarea>
                @error('professional_summary') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded hover:bg-blue-700">
                Submit Application
            </button>
        </div>
    </form>
</div>
