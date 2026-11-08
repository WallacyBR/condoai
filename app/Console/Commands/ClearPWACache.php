<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

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
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('Tem certeza que deseja limpar todos os caches do PWA?')) {
                $this->info('Operação cancelada.');
                return 0;
            }
        }

        $this->info('🧹 Iniciando limpeza de caches do PWA...');

        $cleared = 0;
        $errors = 0;

        // Limpar cache do Laravel
        try {
            $this->call('cache:clear');
            $this->call('config:clear');
            $this->call('view:clear');
            $this->call('route:clear');
            $cleared++;
            $this->info('✅ Cache do Laravel limpo');
        } catch (\Exception $e) {
            $errors++;
            $this->error('❌ Erro ao limpar cache do Laravel: ' . $e->getMessage());
        }

        // Limpar arquivos de build antigos
        try {
            $buildPath = public_path('build');
            if (File::exists($buildPath)) {
                $oldFiles = File::glob($buildPath . '/*.js');
                $oldFiles = array_merge($oldFiles, File::glob($buildPath . '/*.css'));
                $oldFiles = array_merge($oldFiles, File::glob($buildPath . '/*.map'));
                
                foreach ($oldFiles as $file) {
                    if (File::exists($file)) {
                        File::delete($file);
                    }
                }
                $cleared++;
                $this->info('✅ Arquivos de build antigos removidos');
            }
        } catch (\Exception $e) {
            $errors++;
            $this->error('❌ Erro ao limpar arquivos de build: ' . $e->getMessage());
        }

        // Limpar Service Worker antigo
        try {
            $swPath = public_path('sw.js');
            if (File::exists($swPath)) {
                File::delete($swPath);
                $cleared++;
                $this->info('✅ Service Worker antigo removido');
            }
        } catch (\Exception $e) {
            $errors++;
            $this->error('❌ Erro ao remover Service Worker: ' . $e->getMessage());
        }

        // Limpar manifest antigo
        try {
            $manifestPath = public_path('manifest.json');
            if (File::exists($manifestPath)) {
                File::delete($manifestPath);
                $cleared++;
                $this->info('✅ Manifest antigo removido');
            }
        } catch (\Exception $e) {
            $errors++;
            $this->error('❌ Erro ao remover manifest: ' . $e->getMessage());
        }

        // Limpar storage de sessões antigas
        try {
            $sessionPath = storage_path('framework/sessions');
            if (File::exists($sessionPath)) {
                $oldSessions = File::glob($sessionPath . '/*');
                $deletedCount = 0;
                
                foreach ($oldSessions as $session) {
                    if (File::isFile($session) && time() - File::lastModified($session) > 86400) {
                        File::delete($session);
                        $deletedCount++;
                    }
                }
                
                if ($deletedCount > 0) {
                    $cleared++;
                    $this->info("✅ {$deletedCount} sessões antigas removidas");
                }
            }
        } catch (\Exception $e) {
            $errors++;
            $this->error('❌ Erro ao limpar sessões: ' . $e->getMessage());
        }

        // Verificar permissões de diretórios
        try {
            $directories = [
                storage_path('framework/cache'),
                storage_path('framework/sessions'),
                storage_path('framework/views'),
                public_path('build')
            ];

            foreach ($directories as $dir) {
                if (!File::exists($dir)) {
                    File::makeDirectory($dir, 0755, true);
                }
                
                if (!is_writable($dir)) {
                    $this->warn("⚠️ Diretório não tem permissão de escrita: {$dir}");
                }
            }
            
            $cleared++;
            $this->info('✅ Permissões de diretórios verificadas');
        } catch (\Exception $e) {
            $errors++;
            $this->error('❌ Erro ao verificar permissões: ' . $e->getMessage());
        }

        // Resumo
        $this->newLine();
        $this->info('📊 Resumo da limpeza:');
        $this->info("✅ {$cleared} operações concluídas com sucesso");
        
        if ($errors > 0) {
            $this->error("❌ {$errors} operações falharam");
        }

        if ($errors === 0) {
            $this->info('🎉 Limpeza completa realizada!');
            $this->info('💡 Execute "npm run build" para reconstruir os assets do PWA.');
        } else {
            $this->warn('⚠️ Algumas operações falharam. Verifique os logs acima.');
        }

        return $errors === 0 ? 0 : 1;
    }
} 