<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Vite;

/**
 * Auxilia na resolução de caminhos para assets gerados via Vite.
 *
 * Verifica se o servidor de desenvolvimento está ativo a partir do arquivo
 * `public/hot`. Caso não esteja disponível, utiliza o manifest gerado pelo
 * comando `npm run build` para descobrir o caminho correto do arquivo.
 */

class AssetHelper
{
    public static function asset(string $path): string
    {
        $hotFile = public_path('hot');
        if (file_exists($hotFile)) {
            $url = trim(file_get_contents($hotFile));
            if (self::canConnect($url)) {
                return $url . '/' . ltrim($path, '/');
            }
        }

        $fromManifest = self::fromManifest($path);
        if ($fromManifest !== null) {
            return $fromManifest;
        }

        $filename = basename($path);
        return '/build/assets/' . $filename;
    }

    protected static function fromManifest(string $path): ?string
    {
        $manifestPath = public_path('build/manifest.json');
        if (!file_exists($manifestPath)) {
            return null;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (isset($manifest[$path]['file'])) {
            return '/build/' . $manifest[$path]['file'];
        }

        return null;
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
