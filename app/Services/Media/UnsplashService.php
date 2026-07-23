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

    public function randomPhotos(int $count = 3, string $query = 'ghana military', string $orientation = 'landscape'): array
    {
        if (!$this->enabled) {
            return [];
        }

        $cacheKey = "unsplash_photos_{$query}_{$orientation}_{$count}";

        return Cache::remember($cacheKey, config('media.unsplash.cache_ttl'), function () use ($count, $query, $orientation) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Client-ID {$this->accessKey}",
                ])->get('https://api.unsplash.com/photos/random', [
                    'query' => $query,
                    'orientation' => $orientation,
                    'count' => min($count, 30),
                ]);

                if ($response->successful()) {
                    $photos = $response->json();
                    $results = [];

                    foreach ($photos as $photo) {
                        $localPath = $this->downloadLocally($photo);
                        $results[] = [
                            'id' => $photo['id'] ?? null,
                            'url' => $localPath ?? $photo['urls']['regular'] ?? null,
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

                    return $results;
                }

                Log::warning('Unsplash API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Unsplash API exception', [
                    'message' => $e->getMessage(),
                ]);

                return [];
            }
        });
    }

    public function searchPhotos(string $query, int $perPage = 5, ?string $orientation = 'landscape'): array
    {
        if (!$this->enabled) {
            return [];
        }

        $cacheKey = "unsplash_search_" . md5("{$query}_{$perPage}_{$orientation}");

        return Cache::remember($cacheKey, config('media.unsplash.cache_ttl'), function () use ($query, $perPage, $orientation) {
            try {
                $params = [
                    'query' => $query,
                    'per_page' => min($perPage, 30),
                ];
                if ($orientation) {
                    $params['orientation'] = $orientation;
                }

                $response = Http::withHeaders([
                    'Authorization' => "Client-ID {$this->accessKey}",
                ])->get('https://api.unsplash.com/search/photos', $params);

                if ($response->successful()) {
                    $data = $response->json();
                    $photos = $data['results'] ?? [];
                    $results = [];

                    foreach ($photos as $photo) {
                        $localPath = $this->downloadLocally($photo);
                        $results[] = [
                            'id' => $photo['id'] ?? null,
                            'url' => $localPath ?? $photo['urls']['regular'] ?? null,
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

                    shuffle($results);
                    return $results;
                }

                Log::warning('Unsplash search API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('Unsplash search API exception', [
                    'message' => $e->getMessage(),
                ]);

                return [];
            }
        });
    }

    public function getPhotoById(string $id): ?array
    {
        $cacheKey = "unsplash_photo_{$id}";

        return Cache::remember($cacheKey, config('media.unsplash.cache_ttl'), function () use ($id) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Client-ID {$this->accessKey}",
                ])->get("https://api.unsplash.com/photos/{$id}");

                if ($response->successful()) {
                    $photo = $response->json();
                    $localPath = $this->downloadLocally($photo);

                    return [
                        'id' => $photo['id'] ?? null,
                        'url' => $localPath ?? $photo['urls']['regular'] ?? null,
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

                return null;
            } catch (\Exception $e) {
                Log::error('Unsplash getPhotoById exception', [
                    'id' => $id,
                    'message' => $e->getMessage(),
                ]);

                return null;
            }
        });
    }

    public function militaryPortraits(int $count = 4): array
    {
        $results = [];
        $seen = [];

        $queries = [
            'ghana military soldier africa',
            'african army soldier uniform',
            'west africa armed forces',
            'ghana armed forces personnel',
        ];

        shuffle($queries);
        foreach ($queries as $query) {
            if (count($results) >= $count) break;
            $photos = $this->searchPhotos($query, 3, 'portrait');
            foreach ($photos as $photo) {
                if (count($results) >= $count) break 2;
                $id = $photo['id'] ?? '';
                if ($id && !isset($seen[$id])) {
                    $seen[$id] = true;
                    $results[] = $photo;
                }
            }
        }

        foreach ($queries as $query) {
            if (count($results) >= $count) break;
            $photos = $this->searchPhotos($query, 3, null);
            foreach ($photos as $photo) {
                if (count($results) >= $count) break 2;
                $id = $photo['id'] ?? '';
                if ($id && !isset($seen[$id])) {
                    $seen[$id] = true;
                    $results[] = $photo;
                }
            }
        }

        $fallbackIds = [
            'ZesUefCgYLc',
            'AUMq5JIjL-c',
            'X_HJpuAumI4',
        ];

        foreach ($fallbackIds as $id) {
            if (count($results) >= $count) break;
            if (isset($seen[$id])) continue;
            $photo = $this->getPhotoById($id);
            if ($photo) {
                $seen[$id] = true;
                $results[] = $photo;
            }
        }

        shuffle($results);
        return $results;
    }

    public function randomHero(): ?array
    {
        $queries = [
            'ghana armed forces parade',
            'african military training',
            'ghana army soldiers uniform',
            'military ceremony africa',
            'african soldier marching',
            'ghana military academy',
            'west africa army drill',
            'armed forces africa',
            'military parade formation',
            'ghana national service',
        ];
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
