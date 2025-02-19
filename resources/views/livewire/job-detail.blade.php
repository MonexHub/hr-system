<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold mb-4">{{ $jobPosting->title }}</h1>

    <div class="mb-6">
        <p class="text-gray-600">{{ $jobPosting->department->name }} • {{ ucfirst($jobPosting->employment_type) }}</p>
    </div>

    <div class="prose max-w-none mb-8">
        {!! $jobPosting->description !!}
    </div>

    <div class="mt-6">
        <a href="{{ route('jobs.apply', $jobPosting) }}" class="inline-block px-6 py-3 bg-blue-600 text-white rounded hover:bg-blue-700">
            Apply Now
        </a>
    </div>
</div>
