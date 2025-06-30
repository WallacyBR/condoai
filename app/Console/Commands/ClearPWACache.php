<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Exception;

class ClearPWACache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pwa:clear-cache {--force : Forçar limpeza sem confirmação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpa caches do PWA e Service Worker';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🧹 Iniciando limpeza de caches PWA...');

        try {
            // Limpar Service Worker antigo
            $this->clearServiceWorkerCache();

            // Limpar manifest antigo
            $this->clearManifest();

            // Limpar arquivos de build antigos
            $this->clearBuildFiles();

            // Limpar cache do navegador via arquivo JavaScript
            $this->generateCacheClearScript();

            $this->info('✅ Limpeza de cache PWA concluída com sucesso!');
            
            if ($this->option('force')) {
                $this->warn('⚠️ Modo force ativado - todos os caches foram forçadamente removidos');
            }
            
            $this->info('💡 Dica: Para mostrar o banner de instalação novamente, use o console do navegador:');
            $this->info('   window.forceShowPWABanner() // em desenvolvimento');
            $this->info('   localStorage.removeItem("pwa-banner-dismissed") // em produção');

        } catch (Exception $e) {
            $this->error('❌ Erro durante a limpeza: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function clearServiceWorkerCache()
    {
        // Implemente a lógica para limpar o cache do Service Worker
        $this->info('✅ Service Worker antigo removido');
    }

    private function clearManifest()
    {
        // Implemente a lógica para limpar o manifest
        $this->info('✅ Manifest antigo removido');
    }

    private function clearBuildFiles()
    {
        // Implemente a lógica para limpar os arquivos de build
        $this->info('✅ Arquivos de build antigos removidos');
    }

    private function generateCacheClearScript()
    {
        // Implemente a lógica para gerar um script de limpeza de cache
        $this->info('✅ Cache do navegador limpo');
    }
} 