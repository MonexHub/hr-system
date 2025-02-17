{{-- resources/views/livewire/job-show.blade.php --}}
<div class="max-w-4xl mx-auto p-6">
    <nav class="mb-6">
        <a href="{{ route('jobs.index') }}" class="text-blue-600 hover:underline">&larr; Back to Jobs</a>
    </nav>

    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-3xl font-bold mb-4">{{ $jobPosting->title }}</h1>

        <div class="flex items-center gap-4 mb-6 text-gray-600">
            <span>{{ $jobPosting->department->name }}</span>
            <span>•</span>
            <span>{{ ucfirst($jobPosting->employment_type) }}</span>
            @if($jobPosting->salary_range)
                <span>•</span>
                <span>{{ $jobPosting->salary_range }}</span>
            @endif
        </div>

        <div class="prose max-w-none mb-8">
            {!! $jobPosting->description !!}
        </div>

        @if($jobPosting->requirements)
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Requirements</h2>
                <div class="prose max-w-none">
                    {!! $jobPosting->requirements !!}
                </div>
            </div>
        @endif

        @if($jobPosting->benefits)
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Benefits</h2>
                <div class="prose max-w-none">
                    {!! $jobPosting->benefits !!}
                </div>
            </div>
        @endif

        <div class="flex justify-between items-center">
            <button
                wire:click="apply"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            >
                Apply Now
            </button>

            <div class="text-gray-600">
                <p>Posted: {{ $jobPosting->created_at->diffForHumans() }}</p>
                @if($jobPosting->application_deadline)
                    <p>Deadline: {{ $jobPosting->application_deadline->format('M d, Y') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
