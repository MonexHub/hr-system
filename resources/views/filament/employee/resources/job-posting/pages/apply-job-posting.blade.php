{{-- similar-jobs.blade.php --}}
<x-filament::section>
    <x-slot name="heading">Similar Positions</x-slot>

    <div class="space-y-4">
        @foreach($this->getSimilarJobs() as $job)
            <div class="p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">
                            <a href="{{ route('filament.employee.resources.job-postings.view', $job) }}" class="hover:text-primary-600">
                                {{ $job->title }}
                            </a>
                        </h3>
                        <p class="text-sm text-gray-500">{{ $job->department->name }}</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-primary-50 px-2 py-1 text-xs font-medium text-primary-700">
                        {{ $job->employment_type }}
                    </span>
                </div>
                <div class="mt-2 text-sm text-gray-500">
                    <p>{{ $job->location }}</p>
                    @if(!$job->hide_salary)
                        <p>{{ $job->salary_range }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</x-filament::section>

{{-- department-info.blade.php --}}
<x-filament::section>
    <x-slot name="heading">Department Information</x-slot>

    @php
        $stats = $this->getDepartmentStats();
    @endphp

    <div class="prose max-w-none">
        <p class="text-gray-500">{{ $stats['description'] }}</p>
    </div>

    <dl class="mt-4 grid grid-cols-2 gap-4">
        <div class="p-3 bg-gray-50 rounded-lg">
            <dt class="text-sm font-medium text-gray-500">Total Employees</dt>
            <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['total_employees'] }}</dd>
        </div>

        <div class="p-3 bg-gray-50 rounded-lg">
            <dt class="text-sm font-medium text-gray-500">Open Positions</dt>
            <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['open_positions'] }}</dd>
        </div>
    </dl>

    @if($stats['department_head'])
        <div class="mt-4">
            <h4 class="text-sm font-medium text-gray-500">Department Head</h4>
            <p class="mt-1 text-gray-900">{{ $stats['department_head'] }}</p>
        </div>
    @endif
</x-filament::section>
