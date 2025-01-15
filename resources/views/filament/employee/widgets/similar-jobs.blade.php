<div class="p-4 bg-white rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Similar Job Openings</h3>
    @if($similarJobs->count())
        <div class="space-y-3">
            @foreach($similarJobs as $job)
                <div class="border p-3 rounded">
                    <h4 class="font-medium">{{ $job->title }}</h4>
                    <p class="text-sm text-gray-600">{{ $job->department->name }}</p>
                    <a href="{{ route('filament.employee.resources.job-postings.view', $job) }}"
                       class="text-primary-600 text-sm hover:underline">View Details</a>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500">No similar jobs found.</p>
    @endif
</div>
