@props(['currentStage' => 1, 'stages' => []])

<div class="relative">
    <div class="hidden md:flex items-center justify-between">
        @foreach($stages as $index => $stage)
            @php
                $status = $stage['status'] ?? 'pending';
                $isCompleted = $status === 'completed';
                $isCurrent = $status === 'current';
                $isPending = $status === 'pending';
            @endphp
            <div class="flex flex-col items-center relative flex-1">
                <div class="flex items-center w-full">
                    <div class="flex-1 h-1 {{ $isCompleted ? 'bg-gaf-green' : ($isCurrent ? 'bg-gaf-green' : 'bg-gray-200') }}"></div>
                    <div class="w-10 h-10 rounded-full flex items-center justify-center z-10
                        {{ $isCompleted ? 'bg-gaf-green text-white' : ($isCurrent ? 'bg-gaf-green text-white ring-4 ring-green-200 animate-pulse' : 'bg-gray-200 text-gray-400') }}">
                        @if($isCompleted)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <span class="text-sm font-bold">{{ $index + 1 }}</span>
                        @endif
                    </div>
                    <div class="flex-1 h-1 {{ $isCurrent ? 'bg-gray-200' : ($isCompleted ? 'bg-gaf-green' : 'bg-gray-200') }}"></div>
                </div>
                <div class="mt-2 text-center {{ $isCurrent ? 'text-gaf-green font-semibold' : 'text-gray-500' }}">
                    <p class="text-xs font-medium">{{ $stage['title'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="md:hidden space-y-4">
        @foreach($stages as $index => $stage)
            @php
                $status = $stage['status'] ?? 'pending';
                $isCompleted = $status === 'completed';
                $isCurrent = $status === 'current';
                $isPending = $status === 'pending';
            @endphp
            <div class="flex items-start space-x-3">
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                        {{ $isCompleted ? 'bg-gaf-green text-white' : ($isCurrent ? 'bg-gaf-green text-white ring-4 ring-green-200 animate-pulse' : 'bg-gray-200 text-gray-400') }}">
                        @if($isCompleted)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <span class="text-xs font-bold">{{ $index + 1 }}</span>
                        @endif
                    </div>
                    @if(!$loop->last)
                        <div class="w-0.5 h-8 {{ $isCompleted ? 'bg-gaf-green' : 'bg-gray-200' }}"></div>
                    @endif
                </div>
                <div class="pt-1">
                    <p class="text-sm font-medium {{ $isCurrent ? 'text-gaf-green font-semibold' : 'text-gray-700' }}">{{ $stage['title'] }}</p>
                    @if(isset($stage['date']) && $stage['date'])
                        <p class="text-xs text-gray-400">{{ $stage['date'] }}</p>
                    @endif
                    @if(isset($stage['note']) && $stage['note'])
                        <p class="text-xs text-gray-500 mt-0.5">{{ $stage['note'] }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
