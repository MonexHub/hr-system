<!-- Personal Information Section -->
<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-900">Personal Information</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- First Name -->
        <div class="relative">
            <input type="text" name="first_name" id="first_name" required
                   class="peer h-12 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-indigo-600"
                   placeholder="First Name"
                   value="{{ old('first_name') }}">
            <label for="first_name" class="absolute left-0 -top-3.5 text-gray-600 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">
                First Name <span class="text-red-500">*</span>
            </label>
            @error('first_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Last Name -->
        <div class="relative">
            <input type="text" name="last_name" id="last_name" required
                   class="peer h-12 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-indigo-600"
                   placeholder="Last Name"
                   value="{{ old('last_name') }}">
            <label for="last_name" class="absolute left-0 -top-3.5 text-gray-600 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">
                Last Name <span class="text-red-500">*</span>
            </label>
            @error('last_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div class="relative">
            <input type="email" name="email" id="email" required
                   class="peer h-12 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-indigo-600"
                   placeholder="Email"
                   value="{{ old('email') }}">
            <label for="email" class="absolute left-0 -top-3.5 text-gray-600 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">
                Email <span class="text-red-500">*</span>
            </label>
            @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Phone -->
        <div class="relative">
            <input type="tel" name="phone" id="phone" required
                   class="peer h-12 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-indigo-600"
                   placeholder="Phone"
                   value="{{ old('phone') }}">
            <label for="phone" class="absolute left-0 -top-3.5 text-gray-600 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">
                Phone <span class="text-red-500">*</span>
            </label>
            @error('phone')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nationality -->
        <div class="relative">
            <select name="nationality" id="nationality" required
                    class="peer h-12 w-full border-b-2 border-gray-300 text-gray-900 focus:outline-none focus:border-indigo-600 bg-transparent">
                <option value="">Select Nationality</option>
                @foreach($nationalityOptions as $value => $label)
                    <option value="{{ $value }}" {{ old('nationality') == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <label for="nationality" class="absolute left-0 -top-3.5 text-gray-600 text-sm">
                Nationality <span class="text-red-500">*</span>
            </label>
            @error('nationality')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
