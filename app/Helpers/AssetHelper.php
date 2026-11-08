<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Vite;

class AssetHelper
{
    public static function asset(string $path): string
    {
        $hotFile = public_path('hot');
        if (file_exists($hotFile)) {
            $url = trim(file_get_contents($hotFile));
            if (self::canConnect($url)) {
                return Vite::asset($path);
            }
        }
        try {
            return Vite::asset($path);
        } catch (\Exception $e) {
            $filename = basename($path);
            return "/build/assets/{$filename}";
        }
    }

    protected static function canConnect(string $url): bool
    {
        $parts = parse_url($url);
        if (!isset($parts['host'], $parts['port'])) {
            return false;
        }
        $fp = @fsockopen($parts['host'], $parts['port'], $errno, $errstr, 1);
        if ($fp) {
            fclose($fp);
            return true;
        }
        return false;
    }
}
