@extends('layouts.app')

@section('content')
    <div class="bg-gray-50 min-h-screen print:bg-white">
        <div class="container mx-auto py-8 px-4 max-w-6xl">
            <!-- Header with actions -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 print:hidden">
                <h1 class="text-3xl font-bold text-[#080d2a]">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-[#080d2a] to-[#1a237e]">
                    Employee Profile
                </span>
                </h1>
                <div class="flex flex-wrap gap-3">
                    <button onclick="window.print()" class="px-4 py-2 bg-[#080d2a] text-white rounded-lg hover:bg-opacity-90 flex items-center transition shadow-sm">
                        <i class="fas fa-print mr-2"></i>
                        Print Resume
                    </button>
                    <a href="{{ route('filament.admin.resources.employees.view', $employee) }}" class="px-4 py-2 bg-[#ffd658] text-[#080d2a] font-medium rounded-lg hover:bg-opacity-90 flex items-center transition shadow-sm">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Details
                    </a>
                </div>
            </div>

            <!-- Print Header - Only visible when printing -->
            <div class="hidden print:block print:mb-8">
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-black mb-1">Employee Profile</h1>
                    <h2 class="text-xl font-semibold text-black">{{ $employee->full_name }}</h2>
                    <p class="text-gray-600">{{ $employee->jobTitle->name ?? 'No Position' }} | ID: {{ $employee->employee_code }}</p>
                </div>
            </div>

            <!-- Profile Card -->
            <div class="bg-white shadow-md rounded-xl overflow-hidden mb-8 print:shadow-none print:border-0 border border-gray-100">
                <!-- Profile Header Section -->
                <div class="relative print:static">
                    <!-- Background Banner -->
                    <div class="h-32 bg-gradient-to-r from-[#080d2a] to-[#1a237e] print:hidden"></div>

                    <!-- Profile Info Container -->
                    <div class="px-6 relative print:px-0">
                        <!-- Profile Photo -->
                        <div class="absolute -top-16 flex justify-center w-full print:static print:mt-4 print:mb-6">
                            <div class="relative print:flex print:items-center print:justify-center">
                                @if($employee->profile_photo)
                                    <img src="{{ Storage::url($employee->profile_photo) }}" alt="{{ $employee->full_name }}"
                                         class="w-32 h-32 object-cover rounded-full border-4 border-white shadow-md print:w-24 print:h-24 print:shadow-none print:border-2 print:border-gray-300">
                                @else
                                    <div class="w-32 h-32 rounded-full flex items-center justify-center bg-gradient-to-br from-[#080d2a] to-[#1a237e] text-white text-3xl font-bold border-4 border-white shadow-md print:w-24 print:h-24 print:bg-gray-200 print:text-gray-700 print:shadow-none print:border-2 print:border-gray-300">
                                        {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name, 0, 1) }}
                                    </div>
                                @endif
                                <div class="absolute -bottom-1 -right-1 w-8 h-8 bg-[#ffd658] text-[#080d2a] rounded-full flex items-center justify-center border-2 border-white print:hidden">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Employee Basic Info -->
                        <div class="pt-20 pb-6 print:pt-2 print:pb-4">
                            <div class="text-center mb-4 print:hidden">
                                <h2 class="text-3xl font-bold text-[#080d2a]">{{ $employee->full_name }}</h2>
                                <p class="text-lg text-gray-600 mt-1">{{ $employee->jobTitle->name ?? 'No Position' }}</p>

                                <div class="flex justify-center items-center gap-3 mt-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    {{ $employee->employment_status === 'active' ? 'bg-green-100 text-green-800' :
                                      ($employee->employment_status === 'probation' ? 'bg-[#ffd658] text-[#080d2a]' :
                                      'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($employee->employment_status) }}
                                </span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-[#080d2a] text-white">
                                    ID: {{ $employee->employee_code }}
                                </span>
                                </div>
                            </div>

                            <!-- Employee Tags -->
                            <div class="mt-6 flex flex-wrap justify-center gap-2 print:hidden">
                                <div class="flex items-center px-3 py-2 rounded-lg bg-gray-100 text-[#080d2a]">
                                    <i class="fas fa-calendar-alt mr-2 text-[#080d2a]"></i>
                                    <span>Joined: {{ $employee->appointment_date ? $employee->appointment_date->format('M d, Y') : 'Not Available' }}</span>
                                </div>

                                <div class="flex items-center px-3 py-2 rounded-lg bg-gray-100 text-[#080d2a]">
                                    <i class="fas fa-file-contract mr-2 text-[#080d2a]"></i>
                                    <span>{{ ucfirst($employee->contract_type) }} Contract</span>
                                </div>

                                <div class="flex items-center px-3 py-2 rounded-lg bg-gray-100 text-[#080d2a]">
                                    <i class="fas fa-building mr-2 text-[#080d2a]"></i>
                                    <span>{{ $employee->department->name ?? 'No Department' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="p-6 print:p-0">
                    <!-- Two Column Layout -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 print:block">
                        <!-- Left Column - Work & Personal -->
                        <div class="md:col-span-2 space-y-6 print:space-y-4">
                            <!-- Employment Details -->
                            <div class="border border-gray-200 rounded-lg overflow-hidden shadow-sm transition-all hover:shadow-md print:border print:border-gray-300 print:shadow-none print:hover:shadow-none print:rounded print:page-break-inside-avoid">
                                <div class="bg-gradient-to-r from-[#080d2a] to-[#1a237e] px-4 py-3 border-b border-gray-200 print:bg-none print:bg-gray-200 print:text-black">
                                    <h3 class="font-bold text-lg text-white flex items-center print:text-black">
                                        <i class="fas fa-briefcase mr-2 text-[#ffd658] print:text-gray-700"></i>
                                        Employment Details
                                    </h3>
                                </div>
                                <div class="p-5 bg-white grid grid-cols-1 sm:grid-cols-2 gap-5 print:grid print:grid-cols-2 print:gap-4 print:p-4">
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors print:bg-white print:border print:border-gray-200 print:p-3">
                                        <p class="text-sm text-gray-500 mb-1">Department</p>
                                        <p class="font-medium text-[#080d2a] print:text-black">{{ $employee->department->name ?? 'Not Assigned' }}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors print:bg-white print:border print:border-gray-200 print:p-3">
                                        <p class="text-sm text-gray-500 mb-1">Position</p>
                                        <p class="font-medium text-[#080d2a] print:text-black">{{ $employee->jobTitle->name ?? 'Not Assigned' }}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors print:bg-white print:border print:border-gray-200 print:p-3">
                                        <p class="text-sm text-gray-500 mb-1">Reports To</p>
                                        <p class="font-medium text-[#080d2a] print:text-black">{{ $employee->reportingTo->full_name ?? 'Not Assigned' }}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors print:bg-white print:border print:border-gray-200 print:p-3">
                                        <p class="text-sm text-gray-500 mb-1">Branch</p>
                                        <p class="font-medium text-[#080d2a] print:text-black">{{ $employee->branch }}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors print:bg-white print:border print:border-gray-200 print:p-3">
                                        <p class="text-sm text-gray-500 mb-1">Start Date</p>
                                        <p class="font-medium text-[#080d2a] print:text-black">{{ $employee->appointment_date ? $employee->appointment_date->format('M d, Y') : 'Not Available' }}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors print:bg-white print:border print:border-gray-200 print:p-3">
                                        <p class="text-sm text-gray-500 mb-1">Contract Type</p>
                                        <p class="font-medium text-[#080d2a] print:text-black">{{ ucfirst($employee->contract_type) }}</p>
                                    </div>
                                    @if($employee->contract_type !== 'permanent')
                                        <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors print:bg-white print:border print:border-gray-200 print:p-3">
                                            <p class="text-sm text-gray-500 mb-1">Contract End Date</p>
                                            <p class="font-medium text-[#080d2a] print:text-black">{{ $employee->contract_end_date ? $employee->contract_end_date->format('M d, Y') : 'Not Set' }}</p>
                                        </div>
                                    @endif
                                    @if(auth()->user()->hasRole(['super_admin', 'hr_manager']))
                                        <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors print:bg-white print:border print:border-gray-200 print:p-3">
                                            <p class="text-sm text-gray-500 mb-1">Net Salary</p>
                                            <p class="font-medium text-[#080d2a] print:text-black">TZS {{ number_format($employee->net_salary) }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Personal Information -->
                            <div class="border border-gray-200 rounded-lg overflow-hidden shadow-sm transition-all hover:shadow-md print:border print:border-gray-300 print:shadow-none print:hover:shadow-none print:rounded print:page-break-inside-avoid">
                                <div class="bg-gradient-to-r from-[#080d2a] to-[#1a237e] px-4 py-3 border-b border-gray-200 print:bg-none print:bg-gray-200 print:text-black">
                                    <h3 class="font-bold text-lg text-white flex items-center print:text-black">
                                        <i class="fas fa-user mr-2 text-[#ffd658] print:text-gray-700"></i>
                                        Personal Information
                                    </h3>
                                </div>
                                <div class="p-5 bg-white grid grid-cols-1 sm:grid-cols-3 gap-5 print:grid print:grid-cols-3 print:gap-4 print:p-4">
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors print:bg-white print:border print:border-gray-200 print:p-3">
                                        <p class="text-sm text-gray-500 mb-1">Gender</p>
                                        <p class="font-medium text-[#080d2a] print:text-black">{{ ucfirst($employee->gender) }}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors print:bg-white print:border print:border-gray-200 print:p-3">
                                        <p class="text-sm text-gray-500 mb-1">Birthdate</p>
                                        <p class="font-medium text-[#080d2a] print:text-black">{{ $employee->birthdate ? $employee->birthdate->format('M d, Y') : 'Not Available' }}</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors print:bg-white print:border print:border-gray-200 print:p-3">
                                        <p class="text-sm text-gray-500 mb-1">Marital Status</p>
                                        <p class="font-medium text-[#080d2a] print:text-black">{{ ucfirst($employee->marital_status) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Skills -->
                            @if($employee->skills && $employee->skills->count() > 0)
                                <div class="border border-gray-200 rounded-lg overflow-hidden shadow-sm transition-all hover:shadow-md print:border print:border-gray-300 print:shadow-none print:hover:shadow-none print:rounded print:page-break-inside-avoid">
                                    <div class="bg-gradient-to-r from-[#080d2a] to-[#1a237e] px-4 py-3 border-b border-gray-200 print:bg-none print:bg-gray-200 print:text-black">
                                        <h3 class="font-bold text-lg text-white flex items-center print:text-black">
                                            <i class="fas fa-bolt mr-2 text-[#ffd658] print:text-gray-700"></i>
                                            Skills & Expertise
                                        </h3>
                                    </div>
                                    <div class="p-5 bg-white print:p-4">
                                        <div class="flex flex-wrap gap-2 print:gap-1">
                                            @foreach($employee->skills as $skill)
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium bg-gray-100 text-[#080d2a] border border-gray-200 hover:bg-[#ffd658] hover:text-[#080d2a] transition-colors duration-200 print:bg-white print:border print:border-gray-300 print:text-black">
                                                {{ $skill->name }}
                                                    @if($skill->pivot && $skill->pivot->proficiency_level)
                                                        <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-medium
                                                        {{ $skill->pivot->proficiency_level === 'beginner' ? 'bg-[#ffd658] text-[#080d2a]' :
                                                        ($skill->pivot->proficiency_level === 'intermediate' ? 'bg-[#ffecb3] text-[#080d2a]' :
                                                        'bg-[#080d2a] text-white') }} print:bg-gray-200 print:text-black">
                                                        {{ ucfirst($skill->pivot->proficiency_level) }}
                                                    </span>
                                                    @endif
                                            </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Education -->
                            @if($employee->education && $employee->education->count() > 0)
                                <div class="border border-gray-200 rounded-lg overflow-hidden shadow-sm transition-all hover:shadow-md print:border print:border-gray-300 print:shadow-none print:hover:shadow-none print:rounded print:page-break-inside-avoid">
                                    <div class="bg-gradient-to-r from-[#080d2a] to-[#1a237e] px-4 py-3 border-b border-gray-200 print:bg-none print:bg-gray-200 print:text-black">
                                        <h3 class="font-bold text-lg text-white flex items-center print:text-black">
                                            <i class="fas fa-graduation-cap mr-2 text-[#ffd658] print:text-gray-700"></i>
                                            Education
                                        </h3>
                                    </div>
                                    <div class="divide-y divide-gray-100 bg-white">
                                        @foreach($employee->education as $education)
                                            <div class="p-5 hover:bg-gray-50 transition-colors print:hover:bg-white print:p-4">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <h4 class="font-semibold text-base text-[#080d2a] print:text-black">{{ $education->degree }} in {{ $education->field_of_study }}</h4>
                                                        <p class="text-gray-600 mt-1">{{ $education->institution }}</p>
                                                    </div>
                                                    @if($education->grade)
                                                        <span class="px-2 py-1 bg-[#ffd658] text-[#080d2a] rounded text-sm font-medium print:bg-white print:border print:border-gray-300 print:text-black">
                                                        {{ $education->grade }}
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="mt-2 text-sm text-gray-500">
                                                    {{ $education->start_date ? $education->start_date->format('M Y') : '' }} -
                                                    {{ $education->end_date ? $education->end_date->format('M Y') : 'Present' }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Right Column - Contact & Other -->
                        <div class="space-y-6 print:space-y-4 print:mt-4">
                            <!-- Contact Information -->
                            <div class="border border-gray-200 rounded-lg overflow-hidden shadow-sm transition-all hover:shadow-md print:border print:border-gray-300 print:shadow-none print:hover:shadow-none print:rounded print:page-break-inside-avoid">
                                <div class="bg-gradient-to-r from-[#080d2a] to-[#1a237e] px-4 py-3 border-b border-gray-200 print:bg-none print:bg-gray-200 print:text-black">
                                    <h3 class="font-bold text-lg text-white flex items-center print:text-black">
                                        <i class="fas fa-address-card mr-2 text-[#ffd658] print:text-gray-700"></i>
                                        Contact Information
                                    </h3>
                                </div>
                                <div class="p-5 bg-white space-y-4 print:p-4 print:space-y-3">
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors print:bg-white print:border print:border-gray-200 print:p-3">
                                        <i class="fas fa-envelope mr-3 text-[#080d2a] print:text-gray-700"></i>
                                        <div>
                                            <p class="text-xs text-gray-500">Email</p>
                                            <p class="font-medium text-[#080d2a] print:text-black">{{ $employee->email }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors print:bg-white print:border print:border-gray-200 print:p-3">
                                        <i class="fas fa-phone mr-3 text-[#080d2a] print:text-gray-700"></i>
                                        <div>
                                            <p class="text-xs text-gray-500">Phone</p>
                                            <p class="font-medium text-[#080d2a] print:text-black">{{ $employee->phone_number }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors print:bg-white print:border print:border-gray-200 print:p-3">
                                        <i class="fas fa-map-marker-alt mr-3 text-[#080d2a] print:text-gray-700"></i>
                                        <div>
                                            <p class="text-xs text-gray-500">Address</p>
                                            <p class="font-medium text-[#080d2a] print:text-black">{{ $employee->permanent_address }}</p>
                                            <p class="font-medium text-[#080d2a] print:text-black">{{ $employee->city }}{{ $employee->postal_code ? ', ' . $employee->postal_code : '' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Contacts -->
                            @if($employee->emergencyContacts && $employee->emergencyContacts->count() > 0)
                                <div class="border border-gray-200 rounded-lg overflow-hidden shadow-sm transition-all hover:shadow-md print:border print:border-gray-300 print:shadow-none print:hover:shadow-none print:rounded print:page-break-inside-avoid">
                                    <div class="bg-gradient-to-r from-[#080d2a] to-[#1a237e] px-4 py-3 border-b border-gray-200 print:bg-none print:bg-gray-200 print:text-black">
                                        <h3 class="font-bold text-lg text-white flex items-center print:text-black">
                                            <i class="fas fa-first-aid mr-2 text-[#ffd658] print:text-gray-700"></i>
                                            Emergency Contacts
                                        </h3>
                                    </div>
                                    <div class="divide-y divide-gray-100 bg-white">
                                        @foreach($employee->emergencyContacts as $contact)
                                            <div class="p-4 hover:bg-gray-50 transition-colors print:hover:bg-white print:p-3">
                                                <div class="flex items-center mb-2">
                                                    <div class="w-8 h-8 rounded-full bg-[#ffd658] text-[#080d2a] flex items-center justify-center mr-3 print:bg-white print:border print:border-gray-300 print:text-black">
                                                        <i class="fas fa-user-friends text-sm"></i>
                                                    </div>
                                                    <p class="font-medium text-[#080d2a] print:text-black">{{ $contact->name }}</p>
                                                    <span class="ml-2 px-2 py-0.5 bg-gray-100 text-[#080d2a] rounded-md text-xs print:bg-white print:border print:border-gray-300 print:text-black">{{ $contact->relationship }}</span>
                                                </div>
                                                <div class="ml-11 space-y-1">
                                                    <div class="flex items-center text-sm">
                                                        <i class="fas fa-phone text-gray-400 mr-2 w-4 text-center"></i>
                                                        <span class="text-gray-600">{{ $contact->phone }}</span>
                                                    </div>
                                                    @if($contact->email)
                                                        <div class="flex items-center text-sm">
                                                            <i class="fas fa-envelope text-gray-400 mr-2 w-4 text-center"></i>
                                                            <span class="text-gray-600">{{ $contact->email }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Dependents -->
                            @if($employee->dependents && $employee->dependents->count() > 0)
                                <div class="border border-gray-200 rounded-lg overflow-hidden shadow-sm transition-all hover:shadow-md print:border print:border-gray-300 print:shadow-none print:hover:shadow-none print:rounded print:page-break-inside-avoid">
                                    <div class="bg-gradient-to-r from-[#080d2a] to-[#1a237e] px-4 py-3 border-b border-gray-200 print:bg-none print:bg-gray-200 print:text-black">
                                        <h3 class="font-bold text-lg text-white flex items-center print:text-black">
                                            <i class="fas fa-users mr-2 text-[#ffd658] print:text-gray-700"></i>
                                            Dependents
                                        </h3>
                                    </div>
                                    <div class="divide-y divide-gray-100 bg-white">
                                        @foreach($employee->dependents as $dependent)
                                            <div class="p-4 hover:bg-gray-50 transition-colors print:hover:bg-white print:p-3">
                                                <div class="flex items-center mb-1">
                                                    <p class="font-medium text-[#080d2a] print:text-black">{{ $dependent->name }}</p>
                                                    <span class="ml-2 px-2 py-0.5 bg-[#ffd658] text-[#080d2a] rounded-md text-xs print:bg-white print:border print:border-gray-300 print:text-black">{{ ucfirst($dependent->relationship) }}</span>
                                                </div>
                                                @if($dependent->birthdate)
                                                    <div class="text-sm text-gray-600 flex items-center">
                                                        <i class="fas fa-birthday-cake text-gray-400 mr-2 w-4 text-center"></i>
                                                        Born: {{ $dependent->birthdate->format('M d, Y') }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Documents -->
                            @if($employee->documents && $employee->documents->count() > 0)
                                <div class="border border-gray-200 rounded-lg overflow-hidden shadow-sm transition-all hover:shadow-md print:border print:border-gray-300 print:shadow-none print:hover:shadow-none print:rounded print:page-break-inside-avoid">
                                    <div class="bg-gradient-to-r from-[#080d2a] to-[#1a237e] px-4 py-3 border-b border-gray-200 print:bg-none print:bg-gray-200 print:text-black">
                                        <h3 class="font-bold text-lg text-white flex items-center print:text-black">
                                            <i class="fas fa-file-alt mr-2 text-[#ffd658] print:text-gray-700"></i>
                                            Documents
                                        </h3>
                                    </div>
                                    <div class="p-4 bg-white print:p-3">
                                        <ul class="space-y-3 print:space-y-2">
                                            @foreach($employee->documents as $document)
                                                <li class="group">
                                                    <a href="{{ Storage::url($document->file_path) }}" target="_blank"
                                                       class="flex items-center p-3 bg-gray-50 rounded-lg group-hover:bg-[#ffd658] transition duration-200 print:bg-white print:border print:border-gray-300 print:p-2">
                                                        <div class="w-10 h-10 flex-shrink-0 bg-[#080d2a] text-white rounded flex items-center justify-center mr-3 group-hover:bg-[#080d2a] print:bg-white print:border print:border-gray-300 print:text-black">
                                                            <i class="fas fa-file-alt"></i>
                                                        </div>
                                                        <div class="flex-grow">
                                                            <p class="font-medium text-[#080d2a] group-hover:text-[#080d2a] print:text-black">{{ $document->document_type }}</p>
                                                            <p class="text-xs text-gray-500 group-hover:text-[#080d2a]">{{ $document->description }}</p>
                                                        </div>
                                                        <i class="fas fa-external-link-alt text-gray-400 group-hover:text-[#080d2a] print:hidden"></i>
                                                        <span class="hidden print:inline print:text-gray-600">({{ Storage::url($document->file_path) }})</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Print footer -->
            <div class="hidden print:block print:mt-8 print:text-center print:text-sm print:text-gray-500">
                <p>Printed on: {{ now()->format('F d, Y') }}</p>
                <p>This document is for official use only</p>
            </div>
        </div>
    </div>

    <style>
        /* Base Styles for Print */
        @media print {
            @page {
                size: A4;
                margin: 1.5cm;
            }

            body {
                background-color: white !important;
                color: #000 !important;
                font-size: 11pt !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .container {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            /* Remove shadows and borders for cleaner print */
            .print\:shadow-none {
                box-shadow: none !important;
            }

            /* Make sure all content is in black and white for better printing */
            .bg-gradient-to-r, .bg-gradient-to-br, [class*='from-'], [class*='to-'] {
                background: white !important;
                color: black !important;
                border-bottom: 1px solid #000 !important;
            }

            /* Make sure text is visible */
            [class*='text-white'] {
                color: #000 !important;
            }

            /* Adjust header colors for printing */
            h3 {
                color: #000 !important;
                border-bottom: 1px solid #000 !important;
                padding-bottom: 5px !important;
            }

            /* Change icon colors for better contrast in print */
            [class*='text-[#ffd658]'], i.fas {
                color: #000 !important;
            }

            /* Hide elements that shouldn't print */
            button, a.px-4, .flex.space-x-2, *[onclick], .hover\:shadow-md, .shadow-sm, .print\:hidden {
                display: none !important;
            }

            /* Show elements specifically for print */
            .hidden.print\:block, .print\:inline, .print\:flex {
                display: block !important;
            }

            /* Grid adjustments for better print layout */
            .grid {
                display: block !important;
            }

            .md\:col-span-2, .md\:grid-cols-3 {
                grid-column: span 1 !important;
                width: 100% !important;
            }

            /* Add space between sections */
            .space-y-6 > div, .print\:space-y-4 > div {
                margin-top: 15px !important;
                margin-bottom: 15px !important;
            }

            /* Prevent page breaks in the middle of important content */
            .print\:page-break-inside-avoid {
                page-break-inside: avoid !important;
            }

            /* Make sure icons print properly */
            .fas {
                font-family: 'FontAwesome' !important;
            }

            /* Document link styling */
            a[href^="http"], a[href^="/"]{
                text-decoration: none !important;
                color: black !important;
            }

            /* Force show document URLs in print */
            .print\:inline {
                display: inline !important;
            }
        }
    </style>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection
