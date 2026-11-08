/**
 * Utilitário para detecção de dispositivos e navegadores
 * Focado especialmente em iOS e suas limitações PWA
 */

export class DeviceDetector {
    constructor() {
        this.userAgent = navigator.userAgent || '';
        this.platform = navigator.platform || '';
        
        // Cache dos resultados para performance
        this._isiOS = null;
        this._isSafari = null;
        this._isStandalone = null;
        this._deviceType = null;
        this._osVersion = null;
    }

    /**
     * Detectar se é dispositivo iOS (iPhone, iPad, iPod)
     */
    get isiOS() {
        if (this._isiOS !== null) return this._isiOS;
        
        this._isiOS = /iPad|iPhone|iPod/.test(this.userAgent) ||
                     (this.platform === 'MacIntel' && navigator.maxTouchPoints > 1) || // iPad no iOS 13+
                     /iPhone|iPad|iPod|Mac/.test(this.platform);
        
        return this._isiOS;
    }

    /**
     * Detectar se é Safari
     */
    get isSafari() {
        if (this._isSafari !== null) return this._isSafari;
        
        this._isSafari = /Safari/.test(this.userAgent) && 
                        !/Chrome|CriOS|FxiOS|EdgiOS/.test(this.userAgent);
        
        return this._isSafari;
    }

    /**
     * Detectar se está em modo standalone (PWA instalado)
     */
    get isStandalone() {
        if (this._isStandalone !== null) return this._isStandalone;
        
        this._isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
                            window.navigator.standalone === true || // iOS específico
                            document.referrer.includes('android-app://');
        
        return this._isStandalone;
    }

    /**
     * Detectar tipo de dispositivo iOS
     */
    get deviceType() {
        if (this._deviceType !== null) return this._deviceType;
        
        if (!this.isiOS) {
            this._deviceType = 'not-ios';
            return this._deviceType;
        }

        if (/iPad/.test(this.userAgent) || 
            (this.platform === 'MacIntel' && navigator.maxTouchPoints > 1)) {
            this._deviceType = 'ipad';
        } else if (/iPhone/.test(this.userAgent)) {
            this._deviceType = 'iphone';
        } else if (/iPod/.test(this.userAgent)) {
            this._deviceType = 'ipod';
        } else {
            this._deviceType = 'ios-unknown';
        }
        
        return this._deviceType;
    }

    /**
     * Obter versão do iOS
     */
    get iosVersion() {
        if (this._osVersion !== null) return this._osVersion;
        
        if (!this.isiOS) {
            this._osVersion = null;
            return this._osVersion;
        }

        const match = this.userAgent.match(/OS (\d+)_(\d+)_?(\d+)?/);
        if (match) {
            this._osVersion = {
                major: parseInt(match[1], 10),
                minor: parseInt(match[2], 10),
                patch: parseInt(match[3] || '0', 10),
                string: `${match[1]}.${match[2]}${match[3] ? '.' + match[3] : ''}`
            };
        } else {
            this._osVersion = null;
        }
        
        return this._osVersion;
    }

    /**
     * Verificar se o iOS suporta PWA instalação
     */
    get supportsPWAInstallation() {
        if (!this.isiOS) return true; // Android e desktop normalmente suportam
        
        const version = this.iosVersion;
        if (!version) return false;
        
        // iOS 11.3+ suporta PWA básico
        return version.major > 11 || (version.major === 11 && version.minor >= 3);
    }

    /**
     * Verificar se suporta notificações push
     */
    get supportsPushNotifications() {
        if (!this.isiOS) return 'Notification' in window;
        
        const version = this.iosVersion;
        if (!version) return false;
        
        // iOS 16.4+ suporta notificações push para PWA
        return version.major > 16 || (version.major === 16 && version.minor >= 4);
    }

    /**
     * Verificar se está em modo private/incognito
     */
    get isPrivateMode() {
        try {
            if (this.isiOS && this.isSafari) {
                // Teste específico para Safari iOS
                localStorage.setItem('_pwa_test', '1');
                localStorage.removeItem('_pwa_test');
                return false;
            }
        } catch (e) {
            return true;
        }
        
        // Outros navegadores
        return false;
    }

    /**
     * Verificar se pode mostrar prompt de instalação
     */
    get canShowInstallPrompt() {
        // Se não é iOS, depende do deferredPrompt
        if (!this.isiOS) {
            return !!window.deferredPrompt;
        }
        
        // Para iOS, verifica se não está instalado e suporta PWA
        return !this.isStandalone && 
               this.supportsPWAInstallation && 
               this.isSafari && 
               !this.isPrivateMode;
    }

    /**
     * Obter instruções de instalação específicas do dispositivo
     */
    getInstallInstructions() {
        if (!this.isiOS) {
            return {
                type: 'automatic',
                title: 'Instalar App',
                description: 'Clique no botão de instalação que aparecerá',
                steps: []
            };
        }

        const baseSteps = [
            {
                icon: '📱',
                text: 'Abra este site no Safari',
                detail: 'Certifique-se de estar usando o Safari (não Chrome ou outros)'
            },
            {
                icon: '📤',
                text: 'Toque no botão de Compartilhar',
                detail: 'Encontre o ícone de compartilhar na barra inferior'
            },
            {
                icon: '📋',
                text: 'Selecione "Adicionar à Tela Inicial"',
                detail: 'Role para baixo nas opções se necessário'
            },
            {
                icon: '✅',
                text: 'Confirme a instalação',
                detail: 'Toque em "Adicionar" para finalizar'
            }
        ];

        return {
            type: 'manual',
            title: `Instalar no ${this.deviceType === 'ipad' ? 'iPad' : 'iPhone'}`,
            description: 'Siga estes passos para adicionar o app à sua tela inicial',
            steps: baseSteps,
            limitations: this.getiOSLimitations()
        };
    }

    /**
     * Obter limitações do iOS para PWA
     */
    getiOSLimitations() {
        const version = this.iosVersion;
        const limitations = [];

        if (!this.supportsPushNotifications) {
            limitations.push({
                feature: 'Notificações Push',
                status: 'limited',
                message: `Disponível apenas no iOS 16.4+. Sua versão: ${version?.string || 'desconhecida'}`
            });
        }

        // Outras limitações conhecidas do iOS
        limitations.push(
            {
                feature: 'Instalação Automática',
                status: 'unavailable',
                message: 'iOS não suporta prompts automáticos de instalação'
            },
            {
                feature: 'Web Share API',
                status: version?.major >= 13 ? 'available' : 'limited',
                message: 'Funcionalidade de compartilhamento nativo'
            },
            {
                feature: 'Background Sync',
                status: 'limited',
                message: 'Sincronização em background limitada no iOS'
            }
        );

        return limitations;
    }

    /**
     * Obter informações completas do dispositivo
     */
    getDeviceInfo() {
        return {
            platform: {
                isiOS: this.isiOS,
                isSafari: this.isSafari,
                isStandalone: this.isStandalone,
                deviceType: this.deviceType,
                osVersion: this.iosVersion,
                userAgent: this.userAgent.substring(0, 100) + '...'
            },
            pwa: {
                supportsPWAInstallation: this.supportsPWAInstallation,
                supportsPushNotifications: this.supportsPushNotifications,
                canShowInstallPrompt: this.canShowInstallPrompt,
                isPrivateMode: this.isPrivateMode
            },
            install: this.getInstallInstructions()
        };
    }

    /**
     * Verificar se deve mostrar banner iOS específico
     */
    shouldShowiOSBanner() {
        return this.isiOS && 
               this.isSafari && 
               !this.isStandalone && 
               this.supportsPWAInstallation && 
               !this.isPrivateMode;
    }

    /**
     * Obter mensagem motivacional específica para iOS
     */
    getiOSMotivationalMessage() {
        const messages = [
            {
                icon: '⚡',
                title: 'Acesso Ultra-Rápido',
                description: 'Abra o app direto da tela inicial, sem abrir o Safari'
            },
            {
                icon: '📱',
                title: 'Experiência Nativa',
                description: 'Visual completo sem as barras do navegador'
            },
            {
                icon: '🔒',
                title: 'Dados Seguros',
                description: 'Funciona offline e mantém seus dados sincronizados'
            },
            {
                icon: '🎯',
                title: 'Sem Distrações',
                description: 'Foco total na gestão do seu condomínio'
            }
        ];

        if (this.supportsPushNotifications) {
            messages.unshift({
                icon: '🔔',
                title: 'Notificações Inteligentes',
                description: 'Receba alertas importantes mesmo com o app fechado'
            });
        }

        return messages;
    }
}

// Instância singleton para uso global
export const deviceDetector = new DeviceDetector();

// Funções helper para uso rápido
export const isiOS = () => deviceDetector.isiOS;
export const isSafari = () => deviceDetector.isSafari;
export const isStandalone = () => deviceDetector.isStandalone;
export const shouldShowiOSBanner = () => deviceDetector.shouldShowiOSBanner();
export const getiOSInstallInstructions = () => deviceDetector.getInstallInstructions();

// Debug function para desenvolvimento
if (process.env.NODE_ENV === 'development') {
    window.deviceDetector = deviceDetector;
    console.log('🔍 Device Detection disponível em window.deviceDetector');
} 