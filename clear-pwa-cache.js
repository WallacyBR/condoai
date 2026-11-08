// Script para limpar caches do PWA e Service Worker
console.log('🧹 Iniciando limpeza de caches do PWA...');

// Função para limpar caches do Service Worker
async function clearServiceWorkerCaches() {
    if ('caches' in window) {
        try {
            const cacheNames = await caches.keys();
            console.log('📋 Caches encontrados:', cacheNames);
            
            const deletedCaches = await Promise.all(
                cacheNames.map(cacheName => {
                    console.log(`🗑️ Removendo cache: ${cacheName}`);
                    return caches.delete(cacheName);
                })
            );
            
            console.log(`✅ ${deletedCaches.filter(Boolean).length} caches removidos`);
            return true;
        } catch (error) {
            console.error('❌ Erro ao limpar caches:', error);
            return false;
        }
    } else {
        console.warn('⚠️ API de Cache não suportada');
        return false;
    }
}

// Função para desregistrar Service Workers
async function unregisterServiceWorkers() {
    if ('serviceWorker' in navigator) {
        try {
            const registrations = await navigator.serviceWorker.getRegistrations();
            console.log(`📋 ${registrations.length} Service Workers encontrados`);
            
            const unregistered = await Promise.all(
                registrations.map(registration => {
                    console.log(`🗑️ Desregistrando Service Worker:`, registration);
                    return registration.unregister();
                })
            );
            
            console.log(`✅ ${unregistered.filter(Boolean).length} Service Workers desregistrados`);
            return true;
        } catch (error) {
            console.error('❌ Erro ao desregistrar Service Workers:', error);
            return false;
        }
    } else {
        console.warn('⚠️ Service Worker não suportado');
        return false;
    }
}

// Função para limpar localStorage e sessionStorage
function clearStorage() {
    try {
        const localStorageKeys = Object.keys(localStorage);
        const sessionStorageKeys = Object.keys(sessionStorage);
        
        console.log(`📋 ${localStorageKeys.length} itens no localStorage`);
        console.log(`📋 ${sessionStorageKeys.length} itens no sessionStorage`);
        
        // Manter apenas tokens essenciais
        const essentialKeys = ['token', 'selectedCondominium', 'user'];
        const keysToRemove = localStorageKeys.filter(key => !essentialKeys.includes(key));
        
        keysToRemove.forEach(key => {
            console.log(`🗑️ Removendo do localStorage: ${key}`);
            localStorage.removeItem(key);
        });
        
        sessionStorage.clear();
        console.log('✅ Storage limpo');
        return true;
    } catch (error) {
        console.error('❌ Erro ao limpar storage:', error);
        return false;
    }
}

// Função para limpar IndexedDB
async function clearIndexedDB() {
    if ('indexedDB' in window) {
        try {
            const databases = await indexedDB.databases();
            console.log(`📋 ${databases.length} bancos IndexedDB encontrados`);
            
            for (const db of databases) {
                if (db.name) {
                    console.log(`🗑️ Removendo IndexedDB: ${db.name}`);
                    indexedDB.deleteDatabase(db.name);
                }
            }
            
            console.log('✅ IndexedDB limpo');
            return true;
        } catch (error) {
            console.error('❌ Erro ao limpar IndexedDB:', error);
            return false;
        }
    } else {
        console.warn('⚠️ IndexedDB não suportado');
        return false;
    }
}

// Função principal
async function clearPWACache() {
    console.log('🚀 Iniciando limpeza completa do PWA...');
    
    const results = await Promise.all([
        clearServiceWorkerCaches(),
        unregisterServiceWorkers(),
        clearStorage(),
        clearIndexedDB(),
        clearPWABannerState()
    ]);
    
    const successCount = results.filter(Boolean).length;
    console.log(`\n📊 Resumo da limpeza:`);
    console.log(`✅ ${successCount}/5 operações concluídas com sucesso`);
    
    if (successCount === 5) {
        console.log('🎉 Limpeza completa realizada! Recarregue a página.');
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    } else {
        console.log('⚠️ Algumas operações falharam. Tente novamente.');
    }
}

// Nova função para limpar estado do banner PWA
async function clearPWABannerState() {
    try {
        // Limpar localStorage do banner
        localStorage.removeItem('pwa-banner-dismissed');
        localStorage.removeItem('pwa-banner-dismissed-time');
        
        // Resetar deferredPrompt se existir
        if (window.deferredPrompt) {
            window.deferredPrompt = null;
        }
        
        console.log('✅ Estado do banner PWA limpo');
        return true;
    } catch (error) {
        console.error('❌ Erro ao limpar estado do banner PWA:', error);
        return false;
    }
}

// Função específica para forçar exibição do banner PWA
function forceShowPWABanner() {
    console.log('🔄 Forçando exibição do banner de instalação PWA...');
    
    // Limpar localStorage
    localStorage.removeItem('pwa-banner-dismissed');
    localStorage.removeItem('pwa-banner-dismissed-time');
    
    // Disparar evento customizado se o prompt estiver disponível
    if (window.deferredPrompt) {
        console.log('✅ deferredPrompt disponível, disparando evento...');
        window.dispatchEvent(new CustomEvent('pwa-prompt-available'));
    } else {
        console.log('⚠️ deferredPrompt não está disponível');
        console.log('💡 Possíveis soluções:');
        console.log('1. Recarregue a página');
        console.log('2. Certifique-se de que está em HTTPS');
        console.log('3. Verifique se o PWA não está já instalado');
        console.log('4. Teste em uma aba privada/incógnita');
    }
    
    return !localStorage.getItem('pwa-banner-dismissed');
}

// Executar limpeza
clearPWACache();

// Exportar funções para uso global
window.clearPWACache = clearPWACache;
window.forceShowPWABanner = forceShowPWABanner;
window.clearPWABannerState = clearPWABannerState; 