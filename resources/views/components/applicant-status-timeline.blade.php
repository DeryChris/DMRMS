@props(['currentStage' => 1, 'stages' => [], 'clickableStages' => []])

@php
    $total = count($stages);
    $pct = $total > 0 ? round(($currentStage / $total) * 100) : 0;

    $stageRoutes = [
        'draft' => route('applicant.application'),
        'submitted' => route('applicant.documents'),
    ];
@endphp

<div class="relative">
    <div class="mb-4">
        <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
            <span>Progress</span>
            <span>Stage {{ min($currentStage, $total) }} of {{ $total }}</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-gaf-green h-2 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
        </div>
    </div>

    <div class="hidden md:flex items-center justify-between overflow-x-auto py-2">
        @foreach($stages as $index => $stage)
            @php
                $status = $stage['status'] ?? 'pending';
                $isCompleted = $status === 'completed';
                $isCurrent = $status === 'current';
                $isPending = $status === 'pending';
                $isClickable = in_array($stage['key'] ?? '', $clickableStages);
                $stageUrl = $stageRoutes[$stage['key'] ?? ''] ?? '#';
                $tag = $isClickable ? 'a' : 'div';
                $attrs = $isClickable ? "href=\"{$stageUrl}\" class=\"flex flex-col items-center relative flex-1 min-w-0 cursor-pointer group\"" : "class=\"flex flex-col items-center relative flex-1 min-w-0\"";
            @endphp
            <{{ $tag }} {!! $attrs !!}>
                <div class="flex items-center w-full">
                    <div class="flex-1 h-0.5 {{ $isCompleted ? 'bg-gaf-green' : ($isCurrent ? 'bg-gaf-green' : 'bg-gray-200') }}"></div>
                    <div class="w-8 h-8 rounded-full flex items-center justify-center z-10 flex-shrink-0 transition-all duration-200
                        {{ $isCompleted ? 'bg-gaf-green text-white' : ($isCurrent ? 'bg-gaf-green text-white ring-4 ring-green-200 animate-pulse' : ($isClickable ? 'bg-gray-200 text-gray-500 group-hover:bg-gaf-khaki group-hover:text-white' : 'bg-gray-200 text-gray-400')) }}">
                        @if($isCompleted)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <span class="text-xs font-bold">{{ $index + 1 }}</span>
                        @endif
                    </div>
                    <div class="flex-1 h-0.5 {{ $isCurrent ? 'bg-gray-200' : ($isCompleted ? 'bg-gaf-green' : 'bg-gray-200') }}"></div>
                </div>
                <div class="mt-1 text-center {{ $isClickable ? 'cursor-pointer' : '' }}" style="max-width: 80px;">
                    <p class="text-[10px] leading-tight font-medium truncate transition-colors duration-200
                        {{ $isCurrent ? 'text-gaf-green font-semibold' : ($isClickable ? 'text-gray-500 group-hover:text-gaf-dark-green' : 'text-gray-500') }}">
                        {{ $stage['title'] }}
                        @if($isClickable)
                        <span class="block text-[8px] text-gaf-khaki font-normal">(click to go back)</span>
                        @endif
                    </p>
                </div>
            </{{ $tag }}>
        @endforeach
    </div>

    <div class="md:hidden space-y-3">
        @foreach($stages as $index => $stage)
            @php
                $status = $stage['status'] ?? 'pending';
                $isCompleted = $status === 'completed';
                $isCurrent = $status === 'current';
                $isPending = $status === 'pending';
                $showDetails = $isCurrent || $isCompleted;
                $isClickable = in_array($stage['key'] ?? '', $clickableStages);
                $stageUrl = $stageRoutes[$stage['key'] ?? ''] ?? '#';
                $tag = $isClickable ? 'a' : 'div';
                $attrs = $isClickable ? "href=\"{$stageUrl}\" class=\"flex items-start space-x-3 group\"" : "class=\"flex items-start space-x-3 " . ($showDetails ? '' : 'opacity-50') . "\"";
            @endphp
            <{{ $tag }} {!! $attrs !!}>
                <div class="flex flex-col items-center">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 transition-all duration-200
                        {{ $isCompleted ? 'bg-gaf-green text-white' : ($isCurrent ? 'bg-gaf-green text-white ring-4 ring-green-200 animate-pulse' : ($isClickable ? 'bg-gray-200 text-gray-500 group-hover:bg-gaf-khaki group-hover:text-white' : 'bg-gray-200 text-gray-400')) }}">
                        @if($isCompleted)
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <span class="text-[10px] font-bold">{{ $index + 1 }}</span>
                        @endif
                    </div>
                    @if(!$loop->last)
                        <div class="w-0.5 h-6 {{ $isCompleted ? 'bg-gaf-green' : 'bg-gray-200' }}"></div>
                    @endif
                </div>
                <div class="pt-0.5 min-w-0">
                    <p class="text-sm font-medium transition-colors duration-200
                        {{ $isCurrent ? 'text-gaf-green font-semibold' : ($isClickable ? 'text-gray-700 group-hover:text-gaf-dark-green' : 'text-gray-700') }}">
                        {{ $stage['title'] }}
                        @if($isClickable)
                        <span class="text-xs text-gaf-khaki ml-1">(go back)</span>
                        @endif
                    </p>
                    @if($isCurrent || $isCompleted)
                        @if(isset($stage['date']) && $stage['date'])
                            <p class="text-xs text-gray-400">{{ $stage['date'] }}</p>
                        @endif
                        @if(isset($stage['note']) && $stage['note'])
                            <p class="text-xs text-gray-500 mt-0.5">{{ $stage['note'] }}</p>
                        @endif
                    @endif
                </div>
            </{{ $tag }}>
        @endforeach
    </div>
</div>
