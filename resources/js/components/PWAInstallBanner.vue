<template>
    <!-- Banner de Instalação PWA - Redesign Moderno -->
    <Transition 
        enter-active-class="transition-all duration-500 ease-out"
        enter-from-class="transform translate-y-full opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition-all duration-300 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform translate-y-full opacity-0"
    >
        <div v-if="showInstallBanner" class="fixed bottom-0 left-0 right-0 z-50 p-4 sm:p-6">
            <div class="relative max-w-6xl mx-auto">
                                 <!-- Banner Principal -->
                 <div class="pwa-banner relative overflow-hidden rounded-2xl shadow-2xl border border-white/10 backdrop-blur-sm dynamic-shadow particle-bg">
                    <!-- Efeito de brilho animado -->
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent animate-pulse"></div>
                    
                    <!-- Padrão de fundo decorativo -->
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/20 rounded-full blur-xl"></div>
                        <div class="absolute -bottom-6 -left-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                        <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-white/15 rounded-full blur-lg"></div>
                    </div>
                    
                    <div class="relative p-6 sm:p-8">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                            <!-- Conteúdo Principal -->
                            <div class="flex items-center space-x-4 flex-1">
                                                                 <!-- Ícone da Aplicação -->
                                 <div class="relative">
                                     <div class="app-icon w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/20 backdrop-blur-sm shadow-lg flex items-center justify-center border border-white/30 relative overflow-hidden group glow-button">
                                        <div class="absolute inset-0 bg-gradient-to-br from-white/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                        <svg class="w-8 h-8 sm:w-10 sm:h-10 text-white relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <!-- Badge "Novo" -->
                                    <div class="absolute -top-2 -right-2 bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-lg animate-bounce">
                                        NOVO
                                    </div>
                                </div>
                                
                                <!-- Texto -->
                                <div class="text-white">
                                    <h3 class="text-xl sm:text-2xl font-bold mb-2 bg-gradient-to-r from-white to-blue-100 bg-clip-text text-transparent">
                                        📱 Instale o CondoAI
                                    </h3>
                                    <p class="text-blue-100 text-sm sm:text-base mb-2 max-w-md">
                                        Tenha acesso rápido e offline às funcionalidades do seu condomínio
                                    </p>
                                    <div class="flex items-center space-x-4 text-xs sm:text-sm text-blue-200">
                                        <div class="flex items-center space-x-1">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            <span>Funciona offline</span>
                                        </div>
                                        <div class="flex items-center space-x-1">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            <span>Notificações push</span>
                                        </div>
                                        <div class="flex items-center space-x-1">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            <span>Acesso rápido</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botões de Ação -->
                            <div class="flex items-center space-x-3 sm:space-x-4 w-full sm:w-auto">
                                <!-- Botão Agora Não -->
                                <button 
                                    @click="dismissBanner" 
                                    class="px-4 py-2 text-sm text-white/90 hover:text-white bg-white/10 hover:bg-white/20 backdrop-blur-sm rounded-xl border border-white/20 hover:border-white/40 transition-all duration-300 font-medium min-w-[100px] flex items-center justify-center group"
                                >
                                    <span>Agora não</span>
                                </button>
                                
                                                                 <!-- Botão Instalar - Destaque -->
                                 <button 
                                     @click="installPWA" 
                                     class="shimmer-effect glow-button relative px-6 py-3 text-base font-bold text-purple-900 bg-gradient-to-r from-white to-blue-50 hover:from-blue-50 hover:to-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 min-w-[120px] flex items-center justify-center space-x-2 group overflow-hidden"
                                 >
                                    <!-- Efeito shimmer -->
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/40 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                                    
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="relative z-10">Instalar App</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
         </Transition>

     <!-- Banner específico para iOS -->
     <iOSInstallBanner />

     <!-- Banner de Atualização - Melhorado -->
    <Transition 
        enter-active-class="transition-all duration-500 ease-out"
        enter-from-class="transform -translate-y-full opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition-all duration-300 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform -translate-y-full opacity-0"
    >
        <div v-if="needRefresh" class="fixed top-0 left-0 right-0 z-50 p-4">
            <div class="max-w-4xl mx-auto">
                <div class="bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl shadow-2xl border border-orange-300/20 backdrop-blur-sm overflow-hidden">
                    <div class="relative p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-lg">✨ Nova versão disponível!</h4>
                                    <p class="text-orange-100 text-sm">Atualize para ter acesso às melhorias mais recentes</p>
                                </div>
                            </div>
                            <button 
                                @click="updateServiceWorker" 
                                class="px-4 py-2 bg-white text-orange-600 rounded-lg hover:bg-orange-50 transition-all duration-200 font-bold shadow-lg hover:shadow-xl hover:scale-105"
                            >
                                Atualizar Agora
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Indicador Offline - Melhorado -->
    <Transition 
        enter-active-class="transition-all duration-500 ease-out"
        enter-from-class="transform -translate-y-full opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition-all duration-300 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform -translate-y-full opacity-0"
    >
        <div v-if="!isOnline" class="fixed top-0 left-0 right-0 z-50 p-4">
            <div class="max-w-4xl mx-auto">
                <div class="bg-gradient-to-r from-red-600 to-pink-600 text-white rounded-xl shadow-2xl border border-red-300/20 backdrop-blur-sm">
                    <div class="p-3 sm:p-4">
                        <div class="flex items-center justify-center space-x-3">
                            <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="text-center">
                                <span class="font-bold text-sm sm:text-base">🌐 Modo Offline Ativo</span>
                                <p class="text-red-100 text-xs sm:text-sm mt-1">Algumas funcionalidades podem estar limitadas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { usePWA } from '@/composables/usePWA';
import { deviceDetector } from '@/utils/deviceDetection';
import iOSInstallBanner from './iOSInstallBanner.vue';

const {
    isInstalled,
    isOnline,
    needRefresh,
    offlineReady,
    installPWA,
    updateServiceWorker
} = usePWA();

const dismissedInstallBanner = ref(false);

// Computed melhorado com detecção de iOS
const showInstallBanner = computed(() => {
    // Se é iOS, não mostrar o banner padrão (será mostrado o banner específico iOS)
    if (deviceDetector.isiOS) {
        return false;
    }
    
    const conditions = {
        isInstalled: isInstalled.value,
        dismissedBanner: dismissedInstallBanner.value,
        hasDeferredPrompt: !!window.deferredPrompt,
        isOnline: isOnline.value,
        isiOS: deviceDetector.isiOS
    };
    
    console.log('PWA Install Banner Conditions:', conditions);
    
    return !conditions.isInstalled && 
           !conditions.dismissedBanner && 
           conditions.hasDeferredPrompt && 
           conditions.isOnline &&
           !conditions.isiOS; // Não mostrar para iOS
});

// Função para forçar a exibição do banner (para debug)
const forceShowBanner = () => {
    localStorage.removeItem('pwa-banner-dismissed');
    dismissedInstallBanner.value = false;
    console.log('Banner de instalação PWA forcado a aparecer');
};

// Função para verificar se o prompt está disponível
const checkPromptAvailability = () => {
    console.log('Verificando disponibilidade do prompt PWA:', {
        deferredPrompt: !!window.deferredPrompt,
        isInstalled: isInstalled.value,
        userAgent: navigator.userAgent
    });
};

const dismissBanner = () => {
    dismissedInstallBanner.value = true;
    localStorage.setItem('pwa-banner-dismissed', 'true');
    console.log('Banner de instalação PWA descartado');
};

// Watch para mudanças no deferredPrompt
watch(() => window.deferredPrompt, (newValue) => {
    console.log('deferredPrompt changed:', !!newValue);
    if (newValue) {
        // Se o prompt está disponível e o banner não foi descartado há muito tempo, mostrar novamente
        const dismissedTime = localStorage.getItem('pwa-banner-dismissed-time');
        const now = Date.now();
        const dayInMs = 24 * 60 * 60 * 1000;
        
        if (!dismissedTime || (now - parseInt(dismissedTime)) > dayInMs) {
            localStorage.removeItem('pwa-banner-dismissed');
            dismissedInstallBanner.value = false;
        }
    }
});

onMounted(() => {
    // Verificar se o banner foi descartado anteriormente
    const wasDismissed = localStorage.getItem('pwa-banner-dismissed');
    if (wasDismissed) {
        dismissedInstallBanner.value = true;
    }
    
    // Debug inicial
    checkPromptAvailability();
    
    // Verificar novamente após um delay (para dar tempo do evento beforeinstallprompt)
    setTimeout(() => {
        checkPromptAvailability();
        
        // Log específico para iOS
        if (deviceDetector.isiOS) {
            console.log('🍎 iOS detectado:', {
                deviceType: deviceDetector.deviceType,
                iosVersion: deviceDetector.iosVersion?.string,
                supportsPWA: deviceDetector.supportsPWAInstallation,
                supportsPush: deviceDetector.supportsPushNotifications,
                isStandalone: deviceDetector.isStandalone
            });
        }
        
        // Se estivermos em desenvolvimento, expor função global para debug
        if (process.env.NODE_ENV === 'development') {
            window.forceShowPWABanner = forceShowBanner;
            console.log('💡 Para mostrar o banner PWA forcadamente, use: window.forceShowPWABanner()');
        }
    }, 2000);
    
    // Listener para mudanças no localStorage de outras abas
    window.addEventListener('storage', (e) => {
        if (e.key === 'pwa-banner-dismissed') {
            dismissedInstallBanner.value = !!e.newValue;
        }
    });
    
    // Listener para evento customizado quando prompt fica disponível
    window.addEventListener('pwa-prompt-available', () => {
        console.log('PWA prompt available event received!');
        checkPromptAvailability();
    });
});
</script>

<style scoped>
/* Animações customizadas para o banner PWA */
@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

@keyframes glow {
    0%, 100% { box-shadow: 0 0 5px rgba(255, 255, 255, 0.3); }
    50% { box-shadow: 0 0 20px rgba(255, 255, 255, 0.6), 0 0 30px rgba(255, 255, 255, 0.4); }
}

@keyframes pulse-glow {
    0%, 100% { 
        box-shadow: 0 0 10px rgba(147, 51, 234, 0.4), 0 0 20px rgba(147, 51, 234, 0.2);
        transform: scale(1);
    }
    50% { 
        box-shadow: 0 0 20px rgba(147, 51, 234, 0.6), 0 0 40px rgba(147, 51, 234, 0.4);
        transform: scale(1.02);
    }
}

@keyframes gradient-shift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Efeitos do banner principal */
.pwa-banner {
    animation: pulse-glow 3s ease-in-out infinite;
    background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #f5576c);
    background-size: 400% 400%;
    animation: gradient-shift 8s ease infinite, pulse-glow 4s ease-in-out infinite;
}

/* Efeito de hover no ícone do app */
.app-icon:hover {
    animation: float 2s ease-in-out infinite;
}

/* Efeito shimmer no botão de instalação */
.shimmer-effect {
    position: relative;
    overflow: hidden;
}

.shimmer-effect::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: left 0.7s;
}

.shimmer-effect:hover::before {
    left: 100%;
}

/* Efeito de brilho nos botões */
.glow-button {
    position: relative;
    overflow: hidden;
}

.glow-button::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.3) 50%, transparent 70%);
    transform: translateX(-100%);
    transition: transform 0.6s;
}

.glow-button:hover::after {
    transform: translateX(100%);
}

/* Responsividade melhorada */
@media (max-width: 640px) {
    .pwa-banner {
        margin: 0 16px;
        border-radius: 16px;
    }
}

/* Efeitos de transição suaves */
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.fade-slide-enter-from {
    opacity: 0;
    transform: translateY(30px) scale(0.95);
}

.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-30px) scale(0.95);
}

/* Melhorias de acessibilidade */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Efeito de partículas de fundo (sutil) */
.particle-bg::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
        radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.1) 1px, transparent 1px),
        radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
    background-size: 50px 50px;
    animation: float 6s ease-in-out infinite;
    pointer-events: none;
}

/* Sombras dinâmicas */
.dynamic-shadow {
    filter: drop-shadow(0 10px 25px rgba(0, 0, 0, 0.3));
    transition: filter 0.3s ease;
}

.dynamic-shadow:hover {
    filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.4));
}
</style> 