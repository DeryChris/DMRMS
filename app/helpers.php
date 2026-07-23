<?php

if (!function_exists('status_badge')) {
    function status_badge(string $status, string $type = 'application'): string
    {
        $map = match ($type) {
            'document' => config('recruitment.document_statuses'),
            'cycle' => config('recruitment.cycle_statuses'),
            'appointment' => config('recruitment.appointment_statuses'),
            'screening' => config('recruitment.screening_results'),
            default => config('recruitment.statuses'),
        };

        $info = $map[$status] ?? ['label' => ucfirst(str_replace('_', ' ', $status)), 'color' => 'bg-gray-100 text-gray-500'];
        $label = $info['label'];
        $color = $info['color'];

        return "<span class=\"text-xs font-semibold px-2 py-1 rounded-full {$color}\">{$label}</span>";
    }
}

if (!function_exists('status_label')) {
    function status_label(string $status, string $type = 'application'): string
    {
        $map = match ($type) {
            'document' => config('recruitment.document_statuses'),
            'cycle' => config('recruitment.cycle_statuses'),
            'appointment' => config('recruitment.appointment_statuses'),
            'screening' => config('recruitment.screening_results'),
            default => config('recruitment.statuses'),
        };

        return $map[$status]['label'] ?? ucfirst(str_replace('_', ' ', $status));
    }
}

if (!function_exists('status_color')) {
    function status_color(string $status, string $type = 'application'): string
    {
        $map = match ($type) {
            'document' => config('recruitment.document_statuses'),
            'cycle' => config('recruitment.cycle_statuses'),
            'appointment' => config('recruitment.appointment_statuses'),
            'screening' => config('recruitment.screening_results'),
            default => config('recruitment.statuses'),
        };

        return $map[$status]['color'] ?? 'bg-gray-100 text-gray-500';
    }
}

if (!function_exists('svg_encode')) {
    function svg_encode(string $svg): string
    {
        return base64_encode($svg);
    }
}

if (!function_exists('gradient_css')) {
    function gradient_css(string $from = '#14532d', ?string $via = null, string $to = '#0f2f1f', int $angle = 135): string
    {
        return $via
            ? "linear-gradient({$angle}deg, {$from} 0%, {$via} 50%, {$to} 100%)"
            : "linear-gradient({$angle}deg, {$from} 0%, {$to} 100%)";
    }
}

if (!function_exists('unsplash_hero')) {
    function unsplash_hero(): ?array
    {
        try {
            return app(\App\Services\Media\UnsplashService::class)->randomHero();
        } catch (\Exception $e) {
            return null;
        }
    }
}

if (!function_exists('unsplash_photos')) {
    function unsplash_photos(int $count = 3, string $query = 'ghana military', string $orientation = 'landscape'): array
    {
        try {
            return app(\App\Services\Media\UnsplashService::class)->randomPhotos($count, $query, $orientation);
        } catch (\Exception $e) {
            return [];
        }
    }
}

if (!function_exists('unsplash_search_photos')) {
    function unsplash_search_photos(string $query, int $perPage = 5, ?string $orientation = 'landscape'): array
    {
        try {
            return app(\App\Services\Media\UnsplashService::class)->searchPhotos($query, $perPage, $orientation);
        } catch (\Exception $e) {
            return [];
        }
    }
}

if (!function_exists('unsplash_military_portraits')) {
    function unsplash_military_portraits(int $count = 4): array
    {
        try {
            return app(\App\Services\Media\UnsplashService::class)->militaryPortraits($count);
        } catch (\Exception $e) {
            return [];
        }
    }
}
