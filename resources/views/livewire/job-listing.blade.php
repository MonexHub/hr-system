<div>
    <div class="mb-4 flex flex-wrap gap-4">
        <div class="flex-1">
            <input wire:model.live="search" type="text" placeholder="Search jobs..." class="w-full px-4 py-2 border rounded">
        </div>
        <div>
            <select wire:model.live="department" class="px-4 py-2 border rounded">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select wire:model.live="type" class="px-4 py-2 border rounded">
                <option value="">All Types</option>
                <option value="full-time">Full Time</option>
                <option value="part-time">Part Time</option>
                <option value="contract">Contract</option>
            </select>
        </div>
    </div>

    <div class="space-y-4">
        @foreach($jobs as $job)
            <div class="p-4 border rounded shadow">
                <h3 class="text-xl font-bold">{{ $job->title }}</h3>
                <div class="mt-2 text-gray-600">
                    <p>{{ $job->department->name }} • {{ ucfirst($job->employment_type) }}</p>
                </div>
                <div class="mt-4">
{{--                    <a href="{{ route('jobs.show', $job) }}" class="text-blue-600 hover:underline">View Details</a>--}}
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $jobs->links() }}
    </div>
</div>
