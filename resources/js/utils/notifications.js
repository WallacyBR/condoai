/**
 * Utilitário para gerenciar notificações push do PWA
 */
export class NotificationManager {
    constructor() {
        this.isSupported = 'Notification' in window;
        this.permission = this.isSupported ? Notification.permission : 'denied';
    }

    /**
     * Solicitar permissão para notificações
     */
    async requestPermission() {
        if (!this.isSupported) {
            console.warn('Notificações não são suportadas neste navegador');
            return false;
        }

        try {
            const permission = await Notification.requestPermission();
            this.permission = permission;
            return permission === 'granted';
        } catch (error) {
            console.error('Erro ao solicitar permissão para notificações:', error);
            return false;
        }
    }

    /**
     * Verificar se tem permissão para notificações
     */
    hasPermission() {
        return this.permission === 'granted';
    }

    /**
     * Enviar notificação local
     */
    sendLocalNotification(title, options = {}) {
        if (!this.hasPermission()) {
            console.warn('Sem permissão para enviar notificações');
            return null;
        }

        const defaultOptions = {
            icon: '/pwa-192x192.png',
            badge: '/pwa-192x192.png',
            vibrate: [100, 50, 100],
            requireInteraction: false,
            silent: false,
            tag: 'condoai-notification'
        };

        const notificationOptions = { ...defaultOptions, ...options };

        try {
            const notification = new Notification(title, notificationOptions);

            // Adicionar event listeners
            notification.onclick = (event) => {
                event.preventDefault();
                window.focus();
                
                // Fechar a notificação
                notification.close();

                // Navegar para a URL se especificada
                if (notificationOptions.url) {
                    window.location.href = notificationOptions.url;
                }
            };

            notification.onclose = () => {
                console.log('Notificação fechada');
            };

            return notification;
        } catch (error) {
            console.error('Erro ao enviar notificação:', error);
            return null;
        }
    }

    /**
     * Enviar notificação de novo morador
     */
    sendNewResidentNotification(residentName, apartment) {
        return this.sendLocalNotification(
            'Novo Morador Registrado',
            {
                body: `${residentName} foi registrado no apartamento ${apartment}`,
                tag: 'new-resident',
                url: '/residents'
            }
        );
    }

    /**
     * Enviar notificação de nova encomenda
     */
    sendNewPackageNotification(packageCode, recipient) {
        return this.sendLocalNotification(
            'Nova Encomenda Recebida',
            {
                body: `Encomenda ${packageCode} para ${recipient}`,
                tag: 'new-package',
                url: '/packages'
            }
        );
    }

    /**
     * Enviar notificação de reserva de área comum
     */
    sendCommonAreaBookingNotification(areaName, date, time) {
        return this.sendLocalNotification(
            'Reserva de Área Comum',
            {
                body: `${areaName} reservada para ${date} às ${time}`,
                tag: 'common-area-booking',
                url: '/common-areas'
            }
        );
    }

    /**
     * Enviar notificação de vaga de estacionamento
     */
    sendParkingSpotNotification(spotNumber, action) {
        return this.sendLocalNotification(
            'Vaga de Estacionamento',
            {
                body: `Vaga ${spotNumber} foi ${action}`,
                tag: 'parking-spot',
                url: '/parking-spots'
            }
        );
    }

    /**
     * Enviar notificação de manutenção
     */
    sendMaintenanceNotification(description, priority = 'normal') {
        const priorityText = priority === 'high' ? 'URGENTE' : 'Normal';
        return this.sendLocalNotification(
            `Manutenção - ${priorityText}`,
            {
                body: description,
                tag: 'maintenance',
                url: '/maintenance',
                requireInteraction: priority === 'high'
            }
        );
    }

    /**
     * Enviar notificação de visita
     */
    sendVisitNotification(visitorName, apartment, time) {
        return this.sendLocalNotification(
            'Nova Visita Registrada',
            {
                body: `${visitorName} visitando apartamento ${apartment} às ${time}`,
                tag: 'visit',
                url: '/visits'
            }
        );
    }

    /**
     * Limpar todas as notificações
     */
    clearAllNotifications() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then((registrations) => {
                registrations.forEach((registration) => {
                    registration.getNotifications().then((notifications) => {
                        notifications.forEach((notification) => {
                            notification.close();
                        });
                    });
                });
            });
        }
    }

    /**
     * Configurar notificações periódicas (para testes)
     */
    setupPeriodicNotifications() {
        if (!this.hasPermission()) {
            return;
        }

        // Exemplo: enviar notificação a cada 30 minutos (apenas para demonstração)
        setInterval(() => {
            this.sendLocalNotification(
                'CondoAI - Lembrete',
                {
                    body: 'Verifique as atualizações do seu condomínio',
                    tag: 'reminder'
                }
            );
        }, 30 * 60 * 1000); // 30 minutos
    }
}

// Instância singleton
export const notificationManager = new NotificationManager(); 