<?php

namespace App\Services\Media;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SvgService
{
    public function illustration(string $name, string $color = '#14532d'): string
    {
        $localPath = public_path("assets/images/illustrations/{$name}.svg");

        if (file_exists($localPath)) {
            $svg = file_get_contents($localPath);
            return $this->recolor($svg, $color);
        }

        return $this->fallbackIllustration($name);
    }

    public function downloadIllustration(string $slug, string $name): bool
    {
        $url = "https://cdn.undraw.co/illustrations/{$slug}.svg";
        $localPath = public_path("assets/images/illustrations/{$name}.svg");

        try {
            $response = Http::timeout(10)->get($url);
            if ($response->successful()) {
                $svg = $response->body();
                file_put_contents($localPath, $svg);
                return true;
            }
        } catch (\Exception $e) {
            report($e);
        }

        return false;
    }

    public function badge(array $options = []): string
    {
        $color = $options['color'] ?? '#14532d';
        $gold = $options['gold'] ?? '#D4AF37';
        $size = $options['size'] ?? 120;

        return <<<SVG
<svg width="{$size}" height="{$size}" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="60" cy="60" r="56" stroke="{$color}" stroke-width="2"/>
  <circle cx="60" cy="60" r="50" stroke="{$gold}" stroke-width="1.5" stroke-dasharray="4 3"/>
  <circle cx="60" cy="60" r="42" fill="{$color}" fill-opacity="0.1" stroke="{$color}" stroke-width="1.5"/>
  <path d="M60 25 L68 45 L90 45 L72 57 L78 78 L60 65 L42 78 L48 57 L30 45 L52 45 Z" fill="{$gold}" opacity="0.9"/>
  <circle cx="60" cy="52" r="8" fill="{$color}"/>
  <text x="60" y="88" text-anchor="middle" fill="{$color}" font-size="10" font-weight="bold">GAF</text>
</svg>
SVG;
    }

    public function shield(array $options = []): string
    {
        $color = $options['color'] ?? '#14532d';
        $gold = $options['gold'] ?? '#D4AF37';
        $size = $options['size'] ?? 80;

        return <<<SVG
<svg width="{$size}" height="{$size}" viewBox="0 0 80 96" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M40 4 L4 20 L4 44 C4 68 40 92 40 92 C40 92 76 68 76 44 L76 20 Z" fill="{$color}" fill-opacity="0.1" stroke="{$color}" stroke-width="2"/>
  <path d="M40 10 L10 24 L10 44 C10 64 40 86 40 86 C40 86 70 64 70 44 L70 24 Z" fill="none" stroke="{$gold}" stroke-width="1.5" stroke-dasharray="3 2"/>
  <path d="M40 16 L16 28 L16 44 C16 60 40 80 40 80 C40 80 64 60 64 44 L64 28 Z" fill="{$color}" fill-opacity="0.2" stroke="{$color}" stroke-width="1"/>
  <text x="40" y="50" text-anchor="middle" fill="{$gold}" font-size="18" font-weight="bold">★</text>
  <line x1="25" y1="62" x2="55" y2="62" stroke="{$gold}" stroke-width="1.5"/>
  <text x="40" y="76" text-anchor="middle" fill="{$color}" font-size="8" font-weight="bold">DMRMS</text>
</svg>
SVG;
    }

    public function star(array $options = []): string
    {
        $color = $options['color'] ?? '#D4AF37';
        $size = $options['size'] ?? 40;

        return <<<SVG
<svg width="{$size}" height="{$size}" viewBox="0 0 40 38" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M20 2 L25 14 L38 15 L28 24 L30 38 L20 30 L10 38 L12 24 L2 15 L15 14 Z" fill="{$color}" stroke="{$color}" stroke-width="0.5"/>
</svg>
SVG;
    }

    public function crossedRifles(array $options = []): string
    {
        $color = $options['color'] ?? '#14532d';
        $size = $options['size'] ?? 60;

        return <<<SVG
<svg width="{$size}" height="{$size}" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
  <line x1="8" y1="52" x2="52" y2="8" stroke="{$color}" stroke-width="2.5" stroke-linecap="round"/>
  <line x1="52" y1="52" x2="8" y2="8" stroke="{$color}" stroke-width="2.5" stroke-linecap="round"/>
  <circle cx="30" cy="30" r="8" stroke="{$color}" stroke-width="1.5" fill="none"/>
  <circle cx="30" cy="30" r="3" fill="{$color}"/>
</svg>
SVG;
    }

    public function laurel(array $options = []): string
    {
        $color = $options['color'] ?? '#D4AF37';
        $size = $options['size'] ?? 80;

        return <<<SVG
<svg width="{$size}" height="{$size}" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M40 4 C40 4 20 20 12 40 C20 30 40 20 40 4 Z" fill="{$color}" fill-opacity="0.3"/>
  <path d="M40 4 C40 4 60 20 68 40 C60 30 40 20 40 4 Z" fill="{$color}" fill-opacity="0.3"/>
  <path d="M40 8 C40 8 22 22 15 40" stroke="{$color}" stroke-width="1.5" fill="none"/>
  <path d="M40 8 C40 8 58 22 65 40" stroke="{$color}" stroke-width="1.5" fill="none"/>
  <text x="40" y="48" text-anchor="middle" fill="{$color}" font-size="14" font-weight="bold">★</text>
</svg>
SVG;
    }

    public function mapGhana(array $options = []): string
    {
        $color = $options['color'] ?? '#14532d';
        $size = $options['size'] ?? 120;

        return <<<SVG
<svg width="{$size}" height="{$size}" viewBox="0 0 120 140" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M60 5 L115 35 L115 70 L60 135 L5 70 L5 35 Z" fill="{$color}" fill-opacity="0.08" stroke="{$color}" stroke-width="2"/>
  <path d="M60 15 L105 40 L105 65 L60 120 L15 65 L15 40 Z" fill="{$color}" fill-opacity="0.12" stroke="{$color}" stroke-width="1.5"/>
  <rect x="35" y="42" width="50" height="35" rx="3" fill="{$color}" fill-opacity="0.15" stroke="{$color}" stroke-width="1"/>
  <line x1="60" y1="15" x2="60" y2="120" stroke="{$color}" stroke-width="0.5" stroke-dasharray="3 2"/>
  <polygon points="60,55 55,65 58,72 62,72 65,65" fill="{$color}" opacity="0.4"/>
</svg>
SVG;
    }

    public function camoLitePattern(array $options = []): string
    {
        $color = $options['color'] ?? '#14532d';
        $opacity = $options['opacity'] ?? 0.03;

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">
  <g fill="{$color}" fill-opacity="{$opacity}">
    <circle cx="30" cy="30" r="20"/>
    <circle cx="170" cy="40" r="25"/>
    <circle cx="100" cy="170" r="30"/>
    <circle cx="60" cy="140" r="15"/>
    <circle cx="150" cy="130" r="18"/>
    <circle cx="20" cy="100" r="22"/>
    <circle cx="180" cy="160" r="12"/>
    <circle cx="80" cy="80" r="10"/>
    <circle cx="130" cy="20" r="14"/>
    <ellipse cx="100" cy="100" rx="35" ry="20"/>
    <ellipse cx="40" cy="180" rx="20" ry="12"/>
    <ellipse cx="160" cy="90" rx="25" ry="15"/>
  </g>
</svg>
SVG;
    }

    protected function recolor(string $svg, string $color): string
    {
        $defaultPurple = '#6C63FF';
        $defaultPurpleRgb = 'rgb(108, 99, 255)';

        $svg = str_replace([$defaultPurple, $defaultPurpleRgb], $color, $svg);

        $hexPattern = '/fill="(#[a-fA-F0-9]{6})"/';
        $svg = preg_replace_callback($hexPattern, function ($matches) use ($color) {
            static $replaced = false;
            if (!$replaced && strtolower($matches[1]) !== strtolower($color)) {
                $replaced = true;
                return 'fill="' . $color . '"';
            }
            return $matches[0];
        }, $svg);

        return $svg;
    }

    protected function fallbackIllustration(string $name): string
    {
        $color = '#14532d';

        $icons = [
            'team' => '<path d="M12 4.5a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm-5 9a4 4 0 0 1 8 0v1H7v-1Z"/><path d="M18 6a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-1 6v1h4v-1a3 3 0 0 0-4-2.5M6 6a2 2 0 1 1 0 4 2 2 0 0 1 0-4Zm1 6v1H3v-1a3 3 0 0 1 4-2.5"/>',
            'docs' => '<path d="M7 4a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V4Z"/><path d="M9 8h6M9 12h6M9 16h4"/>',
            'success' => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
            'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4.35-4.35"/>',
            'contact' => '<path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8Z"/><path d="m3 8 9 6 9-6"/>',
            'faq' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>',
            'profile' => '<circle cx="12" cy="8" r="4"/><path d="M4 20v-1a6 6 0 0 1 12 0v1"/>',
            'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
            'checklist' => '<path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="m9 14 2 2 4-4"/>',
            'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
            'notifications' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
            'empty' => '<circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M9 9h.01"/><path d="M15 9h.01"/>',
            'welcome' => '<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Z"/><path d="m9 12 2 2 4-4"/><path d="M12 6v4"/>',
            'steps' => '<circle cx="6" cy="6" r="3"/><circle cx="18" cy="6" r="3"/><circle cx="12" cy="18" r="3"/><line x1="9" y1="8" x2="15" y2="16"/><line x1="15" y1="8" x2="9" y2="16"/>',
            'security' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
            'verified' => '<path d="M12 22c-4 0-8-4-8-8V5l8-3 8 3v9c0 4-4 8-8 8z"/><path d="m9 12 2 2 4-4"/>',
            'schedule' => '<path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Z"/><polyline points="12 6 12 12 16 14"/>',
            'interview' => '<path d="M5 3a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5Z"/><path d="M9 12h6"/><path d="M9 8h6"/><path d="M9 16h4"/>',
            'medical' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
            'complete' => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/><path d="M12 16v-4"/>',
        ];

        $iconPath = $icons[$name] ?? $icons['empty'];

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 24 24" fill="none" stroke="{$color}" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
  {$iconPath}
</svg>
SVG;
    }
}
