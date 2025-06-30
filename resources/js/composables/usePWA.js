import { ref, onMounted, onUnmounted } from 'vue';
import { registerSW } from 'virtual:pwa-register';

export function usePWA() {
    const isInstalled = ref(false);
    const isOnline = ref(navigator.onLine);
    const updateSW = ref(null);
    const needRefresh = ref(false);
    const offlineReady = ref(false);
    const swRegistration = ref(null);

    // Registrar Service Worker usando VitePWA
    const registerServiceWorker = () => {
        if ('serviceWorker' in navigator) {
            try {
                updateSW.value = registerSW({
                    onNeedRefresh() {
                        needRefresh.value = true;
                        console.log('Atualização do Service Worker disponível');
                    },
                    onOfflineReady() {
                        offlineReady.value = true;
                        console.log('Aplicação pronta para uso offline');
                    },
                    onRegistered(registration) {
                        swRegistration.value = registration;
                        console.log('Service Worker registrado:', registration);
                    },
                    onRegisterError(error) {
                        console.error('Erro ao registrar Service Worker:', error);
                    },
                    immediate: true
                });
            } catch (error) {
                console.error('Erro ao configurar Service Worker:', error);
            }
        } else {
            console.warn('Service Worker não suportado neste navegador');
        }
    };

    // Verificar se o app está instalado
    const checkInstallation = () => {
        if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) {
            isInstalled.value = true;
            console.log('Aplicação instalada como PWA');
        }
    };

    // Gerenciar estado online/offline
    const handleOnline = () => {
        isOnline.value = true;
        console.log('Conexão restaurada');
    };

    const handleOffline = () => {
        isOnline.value = false;
        console.log('Conexão perdida');
    };

    // Solicitar permissão para notificações
    const requestNotificationPermission = async () => {
        if ('Notification' in window) {
            try {
                const permission = await Notification.requestPermission();
                console.log('Permissão de notificação:', permission);
                return permission === 'granted';
            } catch (error) {
                console.error('Erro ao solicitar permissão de notificação:', error);
                return false;
            }
        }
        return false;
    };

    // Enviar notificação local
    const sendNotification = (title, options = {}) => {
        if ('Notification' in window && Notification.permission === 'granted') {
            try {
                const notification = new Notification(title, {
                    icon: '/pwa-192x192.png',
                    badge: '/pwa-192x192.png',
                    ...options
                });

                notification.onclick = () => {
                    window.focus();
                    notification.close();
                };

                return notification;
            } catch (error) {
                console.error('Erro ao enviar notificação:', error);
                return null;
            }
        }
        return null;
    };

    // Instalar PWA
    const installPWA = () => {
        console.log('installPWA called, deferredPrompt:', !!window.deferredPrompt);
        
        if (window.deferredPrompt) {
            try {
                console.log('Showing install prompt...');
                window.deferredPrompt.prompt();
                window.deferredPrompt.userChoice.then((choiceResult) => {
                    console.log('User choice:', choiceResult.outcome);
                    if (choiceResult.outcome === 'accepted') {
                        console.log('PWA instalado com sucesso');
                        isInstalled.value = true;
                    } else {
                        console.log('Instalação do PWA cancelada pelo usuário');
                    }
                    window.deferredPrompt = null;
                });
            } catch (error) {
                console.error('Erro ao instalar PWA:', error);
            }
        } else {
            console.log('Prompt de instalação não disponível. Possíveis motivos:');
            console.log('- App já está instalado');
            console.log('- Navegador não suporta');
            console.log('- Critérios de instalação não atendidos');
            console.log('- Evento beforeinstallprompt ainda não foi disparado');
        }
    };

    // Função para verificar critérios de instalação PWA
    const checkInstallCriteria = () => {
        const criteria = {
            hasServiceWorker: 'serviceWorker' in navigator,
            hasManifest: document.querySelector('link[rel="manifest"]') !== null,
            isHTTPS: location.protocol === 'https:' || location.hostname === 'localhost',
            hasIcons: true, // Presumimos que sim baseado no manifest
            isInstalled: isInstalled.value,
            deferredPromptAvailable: !!window.deferredPrompt
        };
        
        console.log('PWA Installation Criteria:', criteria);
        return criteria;
    };
    
    // Função para forçar verificação do prompt
    const forceCheckPrompt = () => {
        console.log('Forcing beforeinstallprompt check...');
        checkInstallCriteria();
        
        // Verificar se estamos em um contexto válido para PWA
        if (!window.deferredPrompt) {
            console.log('💡 Dica: O evento beforeinstallprompt só dispara quando:');
            console.log('1. A página atende aos critérios de PWA');
            console.log('2. O usuário não instalou o app ainda');
            console.log('3. O usuário visitou o site pelo menos uma vez');
            console.log('4. O navegador suporta PWA (Chrome, Edge, Safari, Firefox)');
        }
    };

    // Atualizar Service Worker
    const updateServiceWorker = () => {
        if (updateSW.value) {
            try {
                updateSW.value();
                needRefresh.value = false;
                console.log('Service Worker atualizado');
            } catch (error) {
                console.error('Erro ao atualizar Service Worker:', error);
            }
        }
    };

    // Sincronizar dados em background
    const syncInBackground = () => {
        if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
            try {
                navigator.serviceWorker.ready.then((registration) => {
                    registration.sync.register('background-sync');
                    console.log('Sincronização em background registrada');
                });
            } catch (error) {
                console.error('Erro ao registrar sincronização em background:', error);
            }
        }
    };

    // Verificar conectividade
    const checkConnectivity = () => {
        return new Promise((resolve) => {
            const timeout = setTimeout(() => {
                console.log('Timeout na verificação de conectividade');
                resolve(false);
            }, 5000);

            fetch('/api/health', { 
                method: 'HEAD',
                cache: 'no-cache'
            })
                .then(() => {
                    clearTimeout(timeout);
                    console.log('Conectividade verificada com sucesso');
                    resolve(true);
                })
                .catch((error) => {
                    clearTimeout(timeout);
                    console.log('Erro na verificação de conectividade:', error);
                    resolve(false);
                });
        });
    };

    // Limpar cache do Service Worker
    const clearCache = async () => {
        if ('caches' in window) {
            try {
                const cacheNames = await caches.keys();
                await Promise.all(
                    cacheNames.map(cacheName => caches.delete(cacheName))
                );
                console.log('Cache limpo com sucesso');
                return true;
            } catch (error) {
                console.error('Erro ao limpar cache:', error);
                return false;
            }
        }
        return false;
    };

    // Inicializar PWA
    const initPWA = () => {
        registerServiceWorker();
        checkInstallation();

        // Event listeners para online/offline
        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);

        // Event listener para instalação do PWA
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('beforeinstallprompt event fired!', e);
            e.preventDefault();
            window.deferredPrompt = e;
            console.log('Prompt de instalação do PWA disponível - deferredPrompt armazenado');
            
            // Dispatch evento customizado para notificar componentes
            window.dispatchEvent(new CustomEvent('pwa-prompt-available'));
        });

        // Event listener para quando o PWA é instalado
        window.addEventListener('appinstalled', () => {
            isInstalled.value = true;
            console.log('PWA instalado com sucesso');
        });
    };

    // Cleanup
    const cleanup = () => {
        window.removeEventListener('online', handleOnline);
        window.removeEventListener('offline', handleOffline);
    };

    // Lifecycle hooks
    onMounted(() => {
        initPWA();
    });

    onUnmounted(() => {
        cleanup();
    });

    return {
        isInstalled,
        isOnline,
        needRefresh,
        offlineReady,
        swRegistration,
        updateSW,
        registerServiceWorker,
        checkInstallation,
        requestNotificationPermission,
        sendNotification,
        installPWA,
        updateServiceWorker,
        syncInBackground,
        checkConnectivity,
        clearCache,
        checkInstallCriteria,
        forceCheckPrompt
    };
} 