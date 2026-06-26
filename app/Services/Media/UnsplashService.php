<?php

namespace App\Services\Media;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UnsplashService
{
    protected string $accessKey;
    protected bool $enabled;

    public function __construct()
    {
        $this->accessKey = config('media.unsplash.access_key');
        $this->enabled = config('media.unsplash.enabled') && !empty($this->accessKey);
    }

    public function randomPhoto(string $query = 'ghana,military,recruitment', string $orientation = 'landscape'): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        $cacheKey = "unsplash_random_{$query}_{$orientation}";

        return Cache::remember($cacheKey, config('media.unsplash.cache_ttl'), function () use ($query, $orientation) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Client-ID {$this->accessKey}",
                ])->get('https://api.unsplash.com/photos/random', [
                    'query' => $query,
                    'orientation' => $orientation,
                    'count' => 1,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $photo = is_array($data) ? $data[0] : $data;

                    $localPath = $this->downloadLocally($photo);

                    return [
                        'id' => $photo['id'] ?? null,
                        'url' => $localPath ?? $photo['urls']['regular'] ?? null,
                        'raw_url' => $photo['urls']['raw'] ?? null,
                        'regular_url' => $photo['urls']['regular'] ?? null,
                        'thumb_url' => $photo['urls']['thumb'] ?? null,
                        'alt' => $photo['alt_description'] ?? 'Ghana Armed Forces recruitment',
                        'attribution' => [
                            'name' => $photo['user']['name'] ?? 'Unsplash',
                            'username' => $photo['user']['username'] ?? '',
                            'link' => $photo['user']['links']['html'] ?? '',
                        ],
                        'color' => $photo['color'] ?? '#14532d',
                    ];
                }

                Log::warning('Unsplash API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('Unsplash API exception', [
                    'message' => $e->getMessage(),
                ]);

                return null;
            }
        });
    }

    public function randomHero(): ?array
    {
        $queries = ['ghana military', 'african soldier', 'ghana armed forces', 'military parade africa', 'ghana landscape', 'west africa army'];
        $query = $queries[array_rand($queries)];

        return $this->randomPhoto($query);
    }

    protected function downloadLocally(array $photo): ?string
    {
        try {
            $url = $photo['urls']['regular'] ?? null;
            if (!$url) {
                return null;
            }

            $imageId = $photo['id'] ?? md5($url);
            $filename = "unsplash_{$imageId}.jpg";
            $localPath = "media/backgrounds/{$filename}";

            if (Storage::disk('public')->exists($localPath)) {
                return Storage::disk('public')->url($localPath);
            }

            $imageContent = Http::timeout(10)->get($url)->body();
            if (empty($imageContent)) {
                return null;
            }

            Storage::disk('public')->put($localPath, $imageContent);

            return Storage::disk('public')->url($localPath);
        } catch (\Exception $e) {
            Log::warning('Unsplash download failed', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function flushCache(): void
    {
        Cache::flush();
    }
}
