<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Account Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="font-sans antialiased bg-gray-900">
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-gray-800 to-gray-900">
    <div class="w-full sm:max-w-md px-6 py-8 bg-gray-800 shadow-2xl overflow-hidden sm:rounded-2xl transition-all duration-300 border border-gray-700">
        <!-- Logo & Header -->
        <div class="text-center mb-10">
            <div class="mx-auto h-12 w-12 bg-indigo-500 rounded-full flex items-center justify-center mb-4 shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-100 mb-2">
                Welcome to {{ config('app.name') }}
            </h1>
            <p class="text-gray-400 text-sm">
                Let's get your account ready
            </p>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="mb-6 bg-red-900/30 p-4 rounded-lg flex items-start space-x-3 border border-red-800/50">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-300">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Account Setup Form -->
        <form method="POST" action="{{ route('employee.complete-setup') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <!-- Username Field -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                    Choose your username
                </label>
                <div class="relative">
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none text-gray-100 placeholder-gray-400"
                        placeholder="john.doe"
                        value="{{ old('name') }}"
                        required
                        autofocus
                    >
                    <span class="absolute right-3 top-3 text-gray-400">
                        <i class="fas fa-user"></i>
                    </span>
                </div>
                <p class="mt-2 text-xs text-gray-400">
                    This will be your unique identifier
                </p>
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                    Create password
                </label>
                <div class="relative">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none text-gray-100 placeholder-gray-400"
                        placeholder="••••••••"
                        required
                    >
                    <span class="absolute right-3 top-3 text-gray-400 cursor-pointer hover:text-gray-300" onclick="togglePasswordVisibility()">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <!-- Confirm Password Field -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">
                    Confirm password
                </label>
                <div class="relative">
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none text-gray-100 placeholder-gray-400"
                        placeholder="••••••••"
                        required
                    >
                    <span class="absolute right-3 top-3 text-gray-400 cursor-pointer hover:text-gray-300" onclick="toggleConfirmPasswordVisibility()">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <!-- Password Requirements -->
            <div class="bg-gray-700 p-4 rounded-lg border border-gray-600">
                <p class="text-sm font-medium text-gray-300 mb-3">Password requirements:</p>
                <ul class="space-y-2 text-sm" id="password-requirements">
                    <li class="password-requirement flex items-center" data-requirement="length">
                        <span class="requirement-icon w-5 mr-2">
                            <i class="fas fa-times text-red-400 text-xs"></i>
                        </span>
                        <span class="text-gray-300">Minimum 8 characters</span>
                    </li>
                    <li class="password-requirement flex items-center" data-requirement="uppercase">
                        <span class="requirement-icon w-5 mr-2">
                            <i class="fas fa-times text-red-400 text-xs"></i>
                        </span>
                        <span class="text-gray-300">At least one uppercase letter</span>
                    </li>
                    <li class="password-requirement flex items-center" data-requirement="number">
                        <span class="requirement-icon w-5 mr-2">
                            <i class="fas fa-times text-red-400 text-xs"></i>
                        </span>
                        <span class="text-gray-300">At least one number</span>
                    </li>
                    <li class="password-requirement flex items-center" data-requirement="special">
                        <span class="requirement-icon w-5 mr-2">
                            <i class="fas fa-times text-red-400 text-xs"></i>
                        </span>
                        <span class="text-gray-300">At least one special character</span>
                    </li>
                </ul>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-medium py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-[1.01] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-lg hover:shadow-indigo-500/20"
            >
                Complete Setup
            </button>
        </form>

        <!-- Help Text -->
        <div class="mt-8 text-center text-sm text-gray-400">
            Need help? <a href="#" class="text-indigo-400 hover:text-indigo-300 font-medium">Contact support</a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirmation');
        const requirements = {
            length: false,
            uppercase: false,
            number: false,
            special: false
        };

        function updatePasswordStrength(value) {
            requirements.length = value.length >= 8;
            requirements.uppercase = /[A-Z]/.test(value);
            requirements.number = /[0-9]/.test(value);
            requirements.special = /[!@#$%^&*(),.?":{}|<>]/.test(value);

            document.querySelectorAll('.password-requirement').forEach(item => {
                const requirement = item.dataset.requirement;
                const icon = item.querySelector('.requirement-icon i');
                if (requirements[requirement]) {
                    item.querySelector('span.text-gray-300').classList.add('text-green-400');
                    icon.classList.replace('fa-times', 'fa-check');
                    icon.classList.replace('text-red-400', 'text-green-400');
                } else {
                    item.querySelector('span.text-gray-300').classList.remove('text-green-400');
                    icon.classList.replace('fa-check', 'fa-times');
                    icon.classList.replace('text-green-400', 'text-red-400');
                }
            });
        }

        password.addEventListener('input', () => {
            updatePasswordStrength(password.value);
            validatePasswordMatch();
        });

        confirmPassword.addEventListener('input', validatePasswordMatch);

        function validatePasswordMatch() {
            if (password.value && confirmPassword.value) {
                if (confirmPassword.value === password.value) {
                    confirmPassword.classList.add('border-green-500');
                    confirmPassword.classList.remove('border-red-500');
                } else {
                    confirmPassword.classList.remove('border-green-500');
                    confirmPassword.classList.add('border-red-500');
                }
            }
        }

        window.togglePasswordVisibility = function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
        };

        window.toggleConfirmPasswordVisibility = function() {
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
        };
    });
</script>
</body>
</html>
