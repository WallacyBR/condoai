<template>
    <div v-if="showInstallBanner" class="fixed bottom-0 left-0 right-0 z-50 bg-gradient-to-r from-blue-600 to-purple-600 text-white p-4 shadow-lg">
        <div class="flex items-center justify-between max-w-4xl mx-auto">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-sm">Instalar CondoAI</h3>
                    <p class="text-xs opacity-90">Acesse rapidamente pelo seu dispositivo</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button 
                    @click="dismissBanner" 
                    class="px-3 py-1 text-xs bg-white bg-opacity-20 rounded-md hover:bg-opacity-30 transition-colors"
                >
                    Agora não
                </button>
                <button 
                    @click="installPWA" 
                    class="px-4 py-2 text-sm bg-white text-blue-600 rounded-md hover:bg-gray-100 transition-colors font-medium"
                >
                    Instalar
                </button>
            </div>
        </div>
    </div>

    <!-- Banner de atualização -->
    <div v-if="needRefresh" class="fixed top-0 left-0 right-0 z-50 bg-yellow-500 text-white p-3 shadow-lg">
        <div class="flex items-center justify-between max-w-4xl mx-auto">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium">Nova versão disponível</span>
            </div>
            <button 
                @click="updateServiceWorker" 
                class="px-3 py-1 text-xs bg-white text-yellow-600 rounded-md hover:bg-gray-100 transition-colors font-medium"
            >
                Atualizar
            </button>
        </div>
    </div>

    <!-- Indicador offline -->
    <div v-if="!isOnline" class="fixed top-0 left-0 right-0 z-50 bg-red-500 text-white p-2 shadow-lg">
        <div class="flex items-center justify-center space-x-2">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm font-medium">Modo offline - Algumas funcionalidades podem estar indisponíveis</span>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePWA } from '@/composables/usePWA';

const {
    isInstalled,
    isOnline,
    needRefresh,
    offlineReady,
    installPWA,
    updateServiceWorker
} = usePWA();

const dismissedInstallBanner = ref(false);

const showInstallBanner = computed(() => {
    return !isInstalled.value && 
           !dismissedInstallBanner.value && 
           window.deferredPrompt && 
           isOnline.value;
});

const dismissBanner = () => {
    dismissedInstallBanner.value = true;
    localStorage.setItem('pwa-banner-dismissed', 'true');
};

onMounted(() => {
    // Verificar se o banner foi descartado anteriormente
    const wasDismissed = localStorage.getItem('pwa-banner-dismissed');
    if (wasDismissed) {
        dismissedInstallBanner.value = true;
    }
});
</script> 