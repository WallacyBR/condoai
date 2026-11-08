<template>
    <!-- Banner de Instalação PWA para iOS -->
    <Transition 
        enter-active-class="transition-all duration-700 ease-out"
        enter-from-class="transform translate-y-full opacity-0 scale-95"
        enter-to-class="transform translate-y-0 opacity-100 scale-100"
        leave-active-class="transition-all duration-400 ease-in"
        leave-from-class="transform translate-y-0 opacity-100 scale-100"
        leave-to-class="transform translate-y-full opacity-0 scale-95"
    >
        <div v-if="showBanner" class="fixed bottom-0 left-0 right-0 z-50 p-3 sm:p-4">
            <div class="relative max-w-5xl mx-auto">
                <!-- Banner Principal iOS -->
                <div class="ios-banner relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 via-indigo-700 to-purple-800 shadow-2xl border border-white/20 backdrop-blur-md">
                    <!-- Efeito de fundo iOS-style -->
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent animate-pulse opacity-60"></div>
                    
                    <!-- Elementos decorativos iOS -->
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute -top-8 -right-8 w-32 h-32 bg-white/20 rounded-full blur-2xl animate-pulse"></div>
                        <div class="absolute -bottom-8 -left-8 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                        <div class="absolute top-1/3 left-1/3 w-20 h-20 bg-white/15 rounded-full blur-xl animate-bounce"></div>
                    </div>
                    
                    <div class="relative p-5 sm:p-7">
                        <!-- Header do Banner -->
                        <div class="flex items-start justify-between mb-6">
                            <div class="flex items-center space-x-4 flex-1">
                                <!-- Logo/Ícone iOS-style -->
                                <div class="relative">
                                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-white/30 to-white/10 backdrop-blur-sm shadow-xl flex items-center justify-center border-2 border-white/30 relative overflow-hidden group ios-icon">
                                        <div class="absolute inset-0 bg-gradient-to-br from-white/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                        <div class="text-3xl relative z-10 group-hover:scale-110 transition-transform duration-300">
                                            🍎
                                        </div>
                                    </div>
                                    <!-- Badge iOS -->
                                    <div class="absolute -top-2 -right-2 bg-gradient-to-r from-orange-400 to-red-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-lg animate-pulse">
                                        iOS
                                    </div>
                                </div>
                                
                                <!-- Conteúdo Principal -->
                                <div class="text-white flex-1">
                                    <h3 class="text-xl sm:text-2xl font-bold mb-2 bg-gradient-to-r from-white via-blue-100 to-purple-100 bg-clip-text text-transparent">
                                        📱 PrediHub para {{ deviceType }}
                                    </h3>
                                    <p class="text-blue-100 text-sm sm:text-base mb-3 leading-relaxed">
                                        Adicione à sua tela inicial para uma experiência nativa completa
                                    </p>
                                    
                                    <!-- Benefícios iOS específicos -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs sm:text-sm text-blue-200">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-green-400">⚡</span>
                                            <span>Abertura instantânea</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-purple-400">📱</span>
                                            <span>Interface sem navegador</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-blue-400">🔒</span>
                                            <span>Funciona offline</span>
                                        </div>
                                        <div class="flex items-center space-x-2" v-if="supportsPushNotifications">
                                            <span class="text-yellow-400">🔔</span>
                                            <span>Notificações push</span>
                                        </div>
                                        <div class="flex items-center space-x-2" v-else>
                                            <span class="text-gray-400">🎯</span>
                                            <span>Foco total</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botão Fechar -->
                            <button 
                                @click="dismissBanner" 
                                class="p-2 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-sm transition-colors duration-200 group"
                            >
                                <svg class="w-5 h-5 text-white/80 group-hover:text-white group-hover:scale-110 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Botões de Ação -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <!-- Botão Tutorial -->
                            <button 
                                @click="showTutorial = true" 
                                class="flex-1 relative px-6 py-4 text-base font-bold text-purple-900 bg-gradient-to-r from-white to-blue-50 hover:from-blue-50 hover:to-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center space-x-2 group overflow-hidden ios-button"
                            >
                                <!-- Efeito shimmer iOS -->
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/40 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                                
                                <span class="text-2xl group-hover:scale-110 transition-transform duration-200">🚀</span>
                                <span class="relative z-10 group-hover:scale-105 transition-transform duration-200">Ver Como Instalar</span>
                            </button>
                            
                            <!-- Botão Agora Não -->
                            <button 
                                @click="dismissBanner" 
                                class="px-4 py-3 text-sm text-white/90 hover:text-white bg-white/10 hover:bg-white/20 backdrop-blur-sm rounded-xl border border-white/20 hover:border-white/40 transition-all duration-300 font-medium flex items-center justify-center group"
                            >
                                <span class="group-hover:scale-105 transition-transform duration-200">Talvez depois</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>

    <!-- Modal Tutorial iOS -->
    <Transition 
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="showTutorial" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showTutorial = false"></div>
            
            <!-- Modal -->
            <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="bg-gradient-to-br from-white to-blue-50 rounded-3xl shadow-2xl border border-blue-200/50 overflow-hidden">
                    <!-- Header do Modal -->
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6 text-white relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent animate-pulse"></div>
                        <div class="relative flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                    <span class="text-2xl">📱</span>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold">Como Instalar no {{ deviceType }}</h2>
                                    <p class="text-blue-100 text-sm">Siga estes passos simples</p>
                                </div>
                            </div>
                            <button 
                                @click="showTutorial = false"
                                class="p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors duration-200"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Conteúdo do Tutorial -->
                    <div class="p-6 space-y-6">
                        <!-- Passos de Instalação -->
                        <div class="space-y-4">
                            <div 
                                v-for="(step, index) in installSteps" 
                                :key="index"
                                class="flex items-start space-x-4 p-4 rounded-2xl bg-white shadow-md border border-blue-100 hover:shadow-lg transition-shadow duration-200"
                            >
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 text-white flex items-center justify-center font-bold text-lg shrink-0 shadow-lg">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <span class="text-2xl">{{ step.icon }}</span>
                                        <h3 class="text-lg font-semibold text-gray-800">{{ step.text }}</h3>
                                    </div>
                                    <p class="text-gray-600 text-sm leading-relaxed">{{ step.detail }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Visual Guide -->
                        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl p-6 border border-blue-200">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <span class="text-2xl mr-2">🎯</span>
                                Dica Importante
                            </h3>
                            <div class="text-gray-700 space-y-2">
                                <p class="text-sm">
                                    <strong>📍 Ícone de Compartilhar:</strong> Procure por 
                                    <span class="inline-flex items-center px-2 py-1 rounded-lg bg-blue-100 text-blue-800 text-xs font-medium mx-1">
                                        📤
                                    </span>
                                    na barra inferior do Safari
                                </p>
                                <p class="text-sm">
                                    <strong>📋 "Adicionar à Tela Inicial":</strong> Se não aparecer imediatamente, role para baixo nas opções
                                </p>
                            </div>
                        </div>
                        
                        <!-- Limitações iOS (se houver) -->
                        <div v-if="limitations.length > 0" class="bg-orange-50 rounded-2xl p-6 border border-orange-200">
                            <h3 class="text-lg font-semibold text-orange-800 mb-4 flex items-center">
                                <span class="text-2xl mr-2">⚠️</span>
                                Informações Importantes
                            </h3>
                            <div class="space-y-3">
                                <div 
                                    v-for="limitation in limitations" 
                                    :key="limitation.feature"
                                    class="flex items-start space-x-3"
                                >
                                    <div class="w-2 h-2 rounded-full bg-orange-400 mt-2 shrink-0"></div>
                                    <div>
                                        <span class="font-medium text-orange-800">{{ limitation.feature }}:</span>
                                        <span class="text-orange-700 text-sm ml-1">{{ limitation.message }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Benefícios -->
                        <div class="bg-green-50 rounded-2xl p-6 border border-green-200">
                            <h3 class="text-lg font-semibold text-green-800 mb-4 flex items-center">
                                <span class="text-2xl mr-2">✨</span>
                                O Que Você Ganha
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div 
                                    v-for="benefit in motivationalMessages" 
                                    :key="benefit.title"
                                    class="flex items-start space-x-3"
                                >
                                    <span class="text-xl">{{ benefit.icon }}</span>
                                    <div>
                                        <h4 class="font-medium text-green-800 text-sm">{{ benefit.title }}</h4>
                                        <p class="text-green-700 text-xs">{{ benefit.description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botões de Ação -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-4">
                            <button 
                                @click="startInstallation"
                                class="flex-1 px-6 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-2xl font-bold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center space-x-2 group"
                            >
                                <span class="text-xl group-hover:scale-110 transition-transform duration-200">🚀</span>
                                <span>Começar Instalação</span>
                            </button>
                            <button 
                                @click="showTutorial = false"
                                class="px-6 py-3 text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-2xl font-medium transition-colors duration-200"
                            >
                                Fechar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { deviceDetector } from '@/utils/deviceDetection';

// Estados reativo
const showBanner = ref(false);
const showTutorial = ref(false);
const dismissed = ref(false);

// Dados do dispositivo
const deviceType = computed(() => {
    const type = deviceDetector.deviceType;
    return type === 'ipad' ? 'iPad' : type === 'iphone' ? 'iPhone' : 'iOS';
});

const supportsPushNotifications = computed(() => deviceDetector.supportsPushNotifications);

const installSteps = computed(() => deviceDetector.getInstallInstructions().steps);

const limitations = computed(() => deviceDetector.getiOSLimitations());

const motivationalMessages = computed(() => deviceDetector.getiOSMotivationalMessage());

// Computeds para exibição
const shouldShowBanner = computed(() => {
    return deviceDetector.shouldShowiOSBanner() && !dismissed.value;
});

// Métodos
const dismissBanner = () => {
    dismissed.value = true;
    showBanner.value = false;
    
    // Salvar no localStorage
    localStorage.setItem('ios-pwa-banner-dismissed', 'true');
    localStorage.setItem('ios-pwa-banner-dismissed-time', Date.now().toString());
    
    console.log('Banner iOS PWA descartado');
};

const startInstallation = () => {
    showTutorial.value = false;
    
    // Scroll para o topo da página
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    // Mostrar mensagem helper
    if (window.confirm('🍎 Agora procure pelo ícone de compartilhar (📤) na barra inferior do Safari e selecione "Adicionar à Tela Inicial".\n\nDeseja continuar?')) {
        // Analytics ou tracking aqui
        console.log('Usuário iniciou processo de instalação iOS');
    }
};

// Lifecycle
onMounted(() => {
    // Verificar se banner foi descartado recentemente
    const wasDismissed = localStorage.getItem('ios-pwa-banner-dismissed');
    const dismissedTime = localStorage.getItem('ios-pwa-banner-dismissed-time');
    
    if (wasDismissed && dismissedTime) {
        const now = Date.now();
        const dayInMs = 24 * 60 * 60 * 1000;
        const timeSinceDismissed = now - parseInt(dismissedTime);
        
        // Mostrar novamente após 7 dias
        if (timeSinceDismissed < (dayInMs * 7)) {
            dismissed.value = true;
            return;
        }
    }
    
    // Debug info
    console.log('🍎 iOS PWA Banner - Device Info:', {
        shouldShow: shouldShowBanner.value,
        deviceType: deviceType.value,
        supportsPush: supportsPushNotifications.value
    });
    
    // Mostrar banner após delay
    if (shouldShowBanner.value) {
        setTimeout(() => {
            showBanner.value = true;
        }, 3000); // 3 segundos de delay
    }
    
    // Expor funções globais para debug
    if (process.env.NODE_ENV === 'development') {
        window.forceShowiOSBanner = () => {
            localStorage.removeItem('ios-pwa-banner-dismissed');
            dismissed.value = false;
            showBanner.value = true;
        };
        console.log('💡 Debug: window.forceShowiOSBanner() disponível');
    }
});
</script>

<style scoped>
/* Animações específicas do iOS */
@keyframes ios-bounce {
    0%, 100% { transform: translateY(0px) scale(1); }
    50% { transform: translateY(-5px) scale(1.02); }
}

@keyframes ios-glow {
    0%, 100% { 
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.4), 
                    0 0 40px rgba(139, 92, 246, 0.2);
    }
    50% { 
        box-shadow: 0 0 40px rgba(59, 130, 246, 0.6), 
                    0 0 80px rgba(139, 92, 246, 0.4);
    }
}

.ios-banner {
    animation: ios-glow 4s ease-in-out infinite;
}

.ios-icon:hover {
    animation: ios-bounce 1s ease-in-out infinite;
}

.ios-button {
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

/* Glassmorphism para iOS */
.backdrop-blur-md {
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

/* Responsividade específica para dispositivos iOS */
@media (max-width: 390px) {
    /* iPhone SE e modelos menores */
    .ios-banner {
        margin: 0 8px;
        border-radius: 20px;
    }
}

@media (max-width: 430px) and (max-height: 900px) {
    /* iPhones padrão */
    .ios-banner {
        margin: 0 12px;
    }
}

@media (min-width: 768px) and (max-width: 1024px) {
    /* iPads */
    .ios-banner {
        max-width: 600px;
        margin: 0 auto;
    }
}

/* Melhorias de acessibilidade para iOS */
@media (prefers-reduced-motion: reduce) {
    .ios-banner {
        animation: none;
    }
    
    .ios-icon:hover {
        animation: none;
        transform: scale(1.05);
    }
}

/* Suporte para tema dark do iOS */
@media (prefers-color-scheme: dark) {
    .bg-gradient-to-br.from-white.to-blue-50 {
        background: linear-gradient(to bottom right, #1e293b, #334155);
    }
}
</style> 