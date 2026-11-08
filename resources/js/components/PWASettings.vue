<template>
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Configurações do App</h2>
        
        <!-- Status do PWA -->
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-700 mb-3">Status do Aplicativo</h3>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Instalado como PWA:</span>
                    <span :class="isInstalled ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                        {{ isInstalled ? 'Sim' : 'Não' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Conexão:</span>
                    <span :class="isOnline ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                        {{ isOnline ? 'Online' : 'Offline' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Service Worker:</span>
                    <span class="text-green-600 text-sm font-medium">Ativo</span>
                </div>
            </div>
        </div>

        <!-- Configurações de Notificações -->
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-700 mb-3">Notificações</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Permitir notificações</p>
                        <p class="text-xs text-gray-500">Receba alertas sobre atividades do condomínio</p>
                    </div>
                    <button
                        @click="toggleNotifications"
                        :class="notificationPermission === 'granted' ? 'bg-green-600' : 'bg-gray-300'"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        <span
                            :class="notificationPermission === 'granted' ? 'translate-x-6' : 'translate-x-1'"
                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                        />
                    </button>
                </div>

                <div v-if="notificationPermission === 'granted'" class="bg-green-50 border border-green-200 rounded-md p-3">
                    <div class="flex">
                        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">Notificações ativadas</p>
                            <p class="text-sm text-green-700">Você receberá alertas sobre atividades importantes</p>
                        </div>
                    </div>
                </div>

                <div v-else-if="notificationPermission === 'denied'" class="bg-red-50 border border-red-200 rounded-md p-3">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">Notificações bloqueadas</p>
                            <p class="text-sm text-red-700">Ative as notificações nas configurações do navegador</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configurações de Sincronização -->
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-700 mb-3">Sincronização</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Sincronização automática</p>
                        <p class="text-xs text-gray-500">Sincronize dados quando a conexão for restaurada</p>
                    </div>
                    <button
                        @click="syncInBackground"
                        :disabled="!isOnline"
                        class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
                    >
                        Sincronizar
                    </button>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-800">Modo offline disponível</p>
                            <p class="text-sm text-blue-700">Acesse dados básicos mesmo sem conexão</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ações do PWA -->
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-700 mb-3">Ações</h3>
            <div class="space-y-2">
                <button
                    v-if="!isInstalled && window.deferredPrompt"
                    @click="installPWA"
                    class="w-full px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-md hover:from-blue-700 hover:to-purple-700 transition-all duration-200 font-medium"
                >
                    Instalar Aplicativo
                </button>
                
                <button
                    v-if="needRefresh"
                    @click="updateServiceWorker"
                    class="w-full px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 transition-colors font-medium"
                >
                    Atualizar Aplicativo
                </button>

                <button
                    @click="clearCache"
                    class="w-full px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors font-medium"
                >
                    Limpar Cache
                </button>
            </div>
        </div>

        <!-- Informações do App -->
        <div class="border-t pt-4">
            <h3 class="text-lg font-medium text-gray-700 mb-3">Informações</h3>
            <div class="space-y-2 text-sm text-gray-600">
                <div class="flex justify-between">
                    <span>Versão:</span>
                    <span>1.0.0</span>
                </div>
                <div class="flex justify-between">
                    <span>Última atualização:</span>
                    <span>{{ lastUpdate }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Tamanho do cache:</span>
                    <span>{{ cacheSize }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePWA } from '@/composables/usePWA';
import { notificationManager } from '@/utils/notifications';

const {
    isInstalled,
    isOnline,
    needRefresh,
    offlineReady,
    installPWA,
    updateServiceWorker,
    syncInBackground,
    checkConnectivity
} = usePWA();

const notificationPermission = ref(Notification.permission);
const lastUpdate = ref('Hoje');
const cacheSize = ref('Calculando...');

// Toggle de notificações
const toggleNotifications = async () => {
    if (notificationPermission.value === 'default') {
        const granted = await notificationManager.requestPermission();
        notificationPermission.value = granted ? 'granted' : 'denied';
    } else if (notificationPermission.value === 'denied') {
        // Tentar solicitar novamente
        const granted = await notificationManager.requestPermission();
        notificationPermission.value = granted ? 'granted' : 'denied';
    }
};

// Limpar cache
const clearCache = async () => {
    if ('caches' in window) {
        try {
            const cacheNames = await caches.keys();
            await Promise.all(
                cacheNames.map(cacheName => caches.delete(cacheName))
            );
            cacheSize.value = '0 MB';
            alert('Cache limpo com sucesso!');
        } catch (error) {
            console.error('Erro ao limpar cache:', error);
            alert('Erro ao limpar cache');
        }
    }
};

// Calcular tamanho do cache
const calculateCacheSize = async () => {
    if ('caches' in window) {
        try {
            const cacheNames = await caches.keys();
            let totalSize = 0;
            
            for (const cacheName of cacheNames) {
                const cache = await caches.open(cacheName);
                const requests = await cache.keys();
                
                for (const request of requests) {
                    const response = await cache.match(request);
                    if (response) {
                        const blob = await response.blob();
                        totalSize += blob.size;
                    }
                }
            }
            
            const sizeInMB = (totalSize / (1024 * 1024)).toFixed(2);
            cacheSize.value = `${sizeInMB} MB`;
        } catch (error) {
            console.error('Erro ao calcular tamanho do cache:', error);
            cacheSize.value = 'N/A';
        }
    }
};

onMounted(() => {
    calculateCacheSize();
    
    // Atualizar permissão de notificação quando mudar
    if ('Notification' in window) {
        const checkPermission = () => {
            notificationPermission.value = Notification.permission;
        };
        
        // Verificar a cada 5 segundos (para mudanças externas)
        setInterval(checkPermission, 5000);
    }
});
</script> 