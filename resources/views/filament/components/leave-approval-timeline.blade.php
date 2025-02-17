<div class="space-y-4 py-4">
    <div class="flow-root">
        <ul role="list" class="-mb-8">
            {{-- Submission --}}
            <li>
                <div class="relative pb-8">
                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                    <div class="relative flex space-x-3">
                        <div>
                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </span>
                        </div>
                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                            <div>
                                <p class="text-sm text-gray-500">Request Submitted</p>
                            </div>
                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                <time>{{ $getRecord()->created_at?->format('M d, Y H:i') }}</time>
                            </div>
                        </div>
                    </div>
                </div>
            </li>

            {{-- Department Approval --}}
            @if($getRecord()->department_approved_at || $getRecord()->status === 'department_approved')
                <li>
                    <div class="relative pb-8">
                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                        <div class="relative flex space-x-3">
                            <div>
                            <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </span>
                            </div>
                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                <div>
                                    <p class="text-sm text-gray-500">Department Head Approval</p>
                                    @if($getRecord()->department_remarks)
                                        <p class="mt-1 text-xs text-gray-400">Remarks: {{ $getRecord()->department_remarks }}</p>
                                    @endif
                                </div>
                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                    <time>{{ $getRecord()->department_approved_at?->format('M d, Y H:i') }}</time>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            @endif

            {{-- HR Approval --}}
            @if($getRecord()->hr_approved_at || $getRecord()->status === 'hr_approved')
                <li>
                    <div class="relative pb-8">
                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                        <div class="relative flex space-x-3">
                            <div>
                            <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </span>
                            </div>
                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                <div>
                                    <p class="text-sm text-gray-500">HR Approval</p>
                                    @if($getRecord()->hr_remarks)
                                        <p class="mt-1 text-xs text-gray-400">Remarks: {{ $getRecord()->hr_remarks }}</p>
                                    @endif
                                </div>
                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                    <time>{{ $getRecord()->hr_approved_at?->format('M d, Y H:i') }}</time>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            @endif

            {{-- CEO Approval (if required) --}}
            @if($getRecord()->ceo_approved_at || $getRecord()->status === 'approved')
                <li>
                    <div class="relative pb-8">
                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                        <div class="relative flex space-x-3">
                            <div>
                            <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </span>
                            </div>
                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                <div>
                                    <p class="text-sm text-gray-500">CEO Approval</p>
                                    @if($getRecord()->ceo_remarks)
                                        <p class="mt-1 text-xs text-gray-400">Remarks: {{ $getRecord()->ceo_remarks }}</p>
                                    @endif
                                </div>
                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                    <time>{{ $getRecord()->ceo_approved_at?->format('M d, Y H:i') }}</time>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            @endif

            {{-- Final Status --}}
            <li>
                <div class="relative">
                    <div class="relative flex space-x-3">
                        <div>
                            @if($getRecord()->status === 'approved')
                                <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                            @elseif($getRecord()->status === 'rejected')
                                <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                            @elseif($getRecord()->status === 'cancelled')
                                <span class="h-8 w-8 rounded-full bg-gray-500 flex items-center justify-center ring-8 ring-white">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                            @else
                                <span class="h-8 w-8 rounded-full bg-yellow-500 flex items-center justify-center ring-8 ring-white">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 102 0V6zm0 6a1 1 0 10-2 0 1 1 0 102 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                            <div>
                                <p class="text-sm text-gray-500">
                                    @if($getRecord()->status === 'approved')
                                        Request Approved
                                    @elseif($getRecord()->status === 'rejected')
                                        Request Rejected
                                @if($getRecord()->rejection_reason)
                                    <p class="mt-1 text-xs text-gray-400">Reason: {{ $getRecord()->rejection_reason }}</p>
                                @endif
                                @elseif($getRecord()->status === 'cancelled')
                                    Request Cancelled
                                    @if($getRecord()->cancellation_reason)
                                        <p class="mt-1 text-xs text-gray-400">Reason: {{ $getRecord()->cancellation_reason }}</p>
                                    @endif
                                @else
                                    Pending Approval
                                    @endif
                                    </p>
                            </div>
                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                <time>{{ $getRecord()->updated_at?->format('M d, Y H:i') }}</time>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>
