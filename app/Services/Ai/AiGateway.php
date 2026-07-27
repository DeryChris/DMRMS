<?php

namespace App\Services\Ai;

use App\Models\Application;
use App\Services\Ai\Providers\AiProviderInterface;
use App\Services\Ai\Providers\FallbackProvider;
use App\Services\Ai\Providers\GeminiProvider;
use App\Services\Ai\Providers\OpenAiProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiGateway
{
    protected ?AiProviderInterface $provider = null;

    public function chat(string $prompt, array $context = []): array
    {
        $messages = [];

        if (!empty($context)) {
            $messages[] = ['role' => 'system', 'content' => json_encode($context)];
        }

        $messages[] = ['role' => 'user', 'content' => $prompt];

        return $this->getProvider()->chat($messages);
    }

    public function crossVerifyDocuments(array $documents, array $referenceData, string $prompt): array
    {
        // Convert any PDF documents to images first
        $converted = [];
        try {
            foreach ($documents as &$doc) {
                $originalPath = $doc['path'];
                $convertedPath = $this->convertToImage($originalPath);
                $doc['path'] = $convertedPath;
                if ($convertedPath !== $originalPath) {
                    $converted[] = $convertedPath;
                }
            }
            unset($doc);

            return $this->getProvider()->crossVerifyDocuments($documents, $referenceData, $prompt);
        } finally {
            foreach ($converted as $tmp) {
                @unlink($tmp);
            }
        }
    }

    public function analyzeDocument(string $imagePath, string $documentType = '', array $context = []): array
    {
        if (empty($documentType)) {
            $documentType = pathinfo($imagePath, PATHINFO_EXTENSION);
        }

        $converted = $this->convertToImage($imagePath);

        try {
            return $this->getProvider()->analyzeDocument($converted, $documentType, $context);
        } finally {
            if ($converted !== $imagePath) {
                @unlink($converted);
            }
        }
    }

    public function getEmbeddings(string $text): array
    {
        return $this->getProvider()->getEmbeddings($text);
    }

    public function chatWithMessages(array $messages): array
    {
        return $this->getProvider()->chat($messages);
    }

    public function generateRanking(array $candidates): array
    {
        $requirements = config('recruitment', []);

        return $this->getProvider()->generateRanking($candidates, $requirements);
    }

    public function analyzeEligibility(Application $application): array
    {
        $applicant = $application->applicant;
        $cycle = $application->cycle;

        $prompt = "Analyze this recruitment application for eligibility:\n\n"
            . "Applicant: {$applicant?->first_name} {$applicant?->last_name}\n"
            . "Age: {$applicant?->date_of_birth?->age}\n"
            . "Height: {$application->height}m\n"
            . "Education: {$application->education_level} in {$application->degree_field}\n"
            . "Nationality: {$applicant?->nationality}\n"
            . "Criminal record: " . ($application->criminal_record ? 'Yes' : 'No') . "\n"
            . "Cycle: {$cycle?->name}\n"
            . "Cycle requirements: " . json_encode($cycle?->requirements ?? []);

        return $this->chat($prompt, ['type' => 'eligibility_analysis']);
    }

    public function generateInsights(): array
    {
        $prompt = "Generate a brief summary of the current DMRMS recruitment system status. "
            . "Include trends, bottlenecks, and recommendations for improvement.";

        return $this->chat($prompt, ['type' => 'insights']);
    }

    public function generateReport(array $params): array
    {
        $type = $params['type'] ?? 'summary';
        $cycle = $params['cycle'] ?? 'All Cycles';
        $data = $params['data'] ?? [];

        $prompt = "Generate a {$type} recruitment report for cycle: {$cycle}\n\n"
            . "Data:\n" . json_encode($data, JSON_PRETTY_PRINT);

        return $this->chat($prompt, ['type' => 'report_generation', 'format' => $params['format'] ?? 'paragraph']);
    }

    protected function convertToImage(string $path): string
    {
        $mime = @mime_content_type($path);

        if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp'], true)) {
            // Downscale images that are too large for the AI provider
            $maxBytes = 512 * 1024; // 512KB max
            $maxDim = 1200;         // max 1200px on longest side

            if (filesize($path) > $maxBytes) {
                $resized = $this->resizeImage($path, $maxDim, $maxBytes);
                if ($resized !== null) {
                    Log::info('AiGateway: image resized/compressed for AI provider', [
                        'source' => basename($path),
                        'original' => round(filesize($path) / 1024, 1) . 'KB',
                        'resized' => round(filesize($resized) / 1024, 1) . 'KB',
                    ]);
                    return $resized;
                }
            }

            return $path;
        }

        if ($mime !== 'application/pdf') {
            Log::warning("AiGateway: unsupported MIME type {$mime} for {$path} — sending as-is");
            return $path;
        }

        if (class_exists(\Imagick::class, false)) {
            try {
                $tmpPath = sys_get_temp_dir() . '/dmrms_conv_' . Str::random(16) . '.jpg';
                $img = new \Imagick();
                $img->setResolution(150, 150);
                $img->readImage($path);
                $img->setIteratorIndex(0);
                $img->setImageFormat('jpg');
                $img->setImageCompressionQuality(60);
                $img->writeImage($tmpPath);
                $img->clear();
                Log::info('AiGateway: PDF converted to image via Imagick', ['source' => $path]);
                return $tmpPath;
            } catch (\Exception $e) {
                Log::warning('AiGateway: Imagick PDF conversion failed', ['error' => $e->getMessage()]);
            }
        }

        $pdftoppm = shell_exec('where pdftoppm 2>NUL') ?: shell_exec('which pdftoppm 2>/dev/null');
        if ($pdftoppm) {
            try {
                $tmpBase = sys_get_temp_dir() . '/dmrms_conv_' . Str::random(16);
                $cmd = sprintf(
                    '"%s" -jpeg -r 150 -f 1 -l 1 "%s" "%s" 2>&1',
                    trim($pdftoppm),
                    $path,
                    $tmpBase
                );
                exec($cmd, $output, $exitCode);
                $outFile = $tmpBase . '-1.jpg';
                if ($exitCode === 0 && file_exists($outFile)) {
                    Log::info('AiGateway: PDF converted to image via pdftoppm', ['source' => $path]);
                    return $outFile;
                }
                Log::warning('AiGateway: pdftoppm conversion failed', ['exit' => $exitCode]);
            } catch (\Exception $e) {
                Log::warning('AiGateway: pdftoppm exception', ['error' => $e->getMessage()]);
            }
        }

        Log::warning('AiGateway: PDF conversion unavailable — install imagick+ghostscript or poppler-utils. Sending PDF as-is (will fall to needs_review).');

        return $path;
    }

    /**
     * Resize/compress an image so it doesn't exceed size/memory limits.
     * Returns the path to the resized image (a temp file) or null on failure.
     */
    protected function resizeImage(string $path, int $maxDim, int $maxBytes): ?string
    {
        $tmpPath = sys_get_temp_dir() . '/dmrms_resized_' . Str::random(16) . '.jpg';

        // Try Imagick first (best quality)
        if (class_exists(\Imagick::class, false)) {
            try {
                $img = new \Imagick($path);
                $geo = $img->getImageGeometry();
                if ($geo['width'] > $maxDim || $geo['height'] > $maxDim) {
                    $img->resizeImage($maxDim, $maxDim, \Imagick::FILTER_LANCZOS, 1, true);
                }
                $img->setImageFormat('jpeg');
                $img->setImageCompressionQuality(60);
                $img->writeImage($tmpPath);
                $img->clear();
                return $tmpPath;
            } catch (\Exception $e) {
                Log::warning('AiGateway: Imagick resize failed', ['error' => $e->getMessage()]);
                @unlink($tmpPath);
            }
        }

        // Fallback: GD
        if (function_exists('imagecreatefromstring')) {
            try {
                $src = @imagecreatefromstring(file_get_contents($path));
                if (!$src) return null;

                $origW = imagesx($src);
                $origH = imagesy($src);

                // Calculate new dimensions
                $ratio = min($maxDim / $origW, $maxDim / $origH, 1);
                $newW = (int) round($origW * $ratio);
                $newH = (int) round($origH * $ratio);

                $dst = imagecreatetruecolor($newW, $newH);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

                // Start with quality 60, reduce until under maxBytes
                $quality = 60;
                do {
                    ob_start();
                    imagejpeg($dst, null, $quality);
                    $data = ob_get_clean();
                    $quality -= 10;
                } while (strlen($data) > $maxBytes && $quality > 15);

                file_put_contents($tmpPath, $data);
                imagedestroy($src);
                imagedestroy($dst);
                return $tmpPath;
            } catch (\Exception $e) {
                Log::warning('AiGateway: GD resize failed', ['error' => $e->getMessage()]);
                @unlink($tmpPath);
            }
        }

        return null;
    }

    protected function getProvider(): AiProviderInterface
    {
        if ($this->provider !== null) {
            return $this->provider;
        }

        $defaultProvider = config('ai.default_provider', 'openai');

        $this->provider = match ($defaultProvider) {
            'openai'  => app(OpenAiProvider::class),
            'gemini'  => app(GeminiProvider::class),
            'fallback' => app(FallbackProvider::class),
            default   => $this->resolveCustomProvider($defaultProvider),
        };

        if (!$this->provider) {
            return app(FallbackProvider::class);
        }

        if (config('ai.fallback_enabled', true)) {
            try {
                $testResult = $this->provider->chat([['role' => 'user', 'content' => 'ping']]);
                if (!($testResult['success'] ?? false)) {
                    Log::warning("Primary AI provider health check returned failure, falling back");
                    $this->provider = app(FallbackProvider::class);
                }
            } catch (\Exception $e) {
                Log::warning("Primary AI provider health check failed ({$e->getMessage()}), continuing with primary provider");
            }
        }

        return $this->provider;
    }

    protected function resolveCustomProvider(string $provider): AiProviderInterface
    {
        $class = config("ai.providers.{$provider}.class");

        if ($class && class_exists($class)) {
            return app($class);
        }

        throw new \InvalidArgumentException("Unknown AI provider: {$provider}");
    }

    protected function logUsage($promptType, $tokens, $cost): void
    {
        Log::channel('ai')->info('AI Gateway usage', [
            'prompt_type' => $promptType,
            'tokens'      => $tokens,
            'cost'        => $cost,
            'provider'    => config('ai.default_provider'),
            'timestamp'   => Carbon::now()->toDateTimeString(),
        ]);
    }
}
