import { ref, computed } from 'vue';
import { apiRequest } from '../utils/api';

// Estado global das notificações
const notifications = ref({
  packages: 0,
  notices: 0,
  maintenance: 0,
  maintenanceTasks: 0,
  vehicles: 0,
  pets: 0,
  areas: 0
});

// Estado para controlar se as notificações devem ser mostradas
const showNotifications = ref(true);

export function useNotifications() {
  
  // Função para atualizar uma notificação específica
  const updateNotification = (key, value) => {
    if (notifications.value.hasOwnProperty(key)) {
      notifications.value[key] = value;
    }
  };

  // Função para incrementar uma notificação
  const incrementNotification = (key) => {
    if (notifications.value.hasOwnProperty(key)) {
      notifications.value[key]++;
    }
  };

  // Função para decrementar uma notificação
  const decrementNotification = (key) => {
    if (notifications.value.hasOwnProperty(key) && notifications.value[key] > 0) {
      notifications.value[key]--;
    }
  };

  // Função para limpar uma notificação
  const clearNotification = (key) => {
    if (notifications.value.hasOwnProperty(key)) {
      notifications.value[key] = 0;
    }
  };

  // Função para limpar todas as notificações
  const clearAllNotifications = () => {
    Object.keys(notifications.value).forEach(key => {
      notifications.value[key] = 0;
    });
  };

  // Função para obter a cor do badge baseada no tipo de notificação
  const getBadgeColor = (type, count) => {
    if (count === 0) return 'bg-gray-500';
    
    switch (type) {
      case 'packages':
        return count > 5 ? 'bg-red-500' : 'bg-orange-500';
      case 'notices':
        return 'bg-yellow-500';
      case 'maintenance':
      case 'maintenanceTasks':
        return count > 3 ? 'bg-red-500' : 'bg-orange-500';
      case 'vehicles':
        return 'bg-blue-500';
      case 'pets':
        return 'bg-green-500';
      case 'areas':
        return 'bg-purple-500';
      default:
        return 'bg-gray-500';
    }
  };

  // Função para carregar notificações do servidor
  const loadNotificationsFromServer = async () => {
    try {
      const token = localStorage.getItem('token');
      if (!token) return;

      const pendingPackages = await apiRequest('/packages?status=pending');
      notifications.value.packages = Array.isArray(pendingPackages) ? pendingPackages.length : 0;
    } catch {
      notifications.value.packages = 0;
    }
  };

  // Computed para verificar se há notificações
  const hasNotifications = computed(() => {
    return Object.values(notifications.value).some(count => count > 0);
  });

  // Computed para total de notificações
  const totalNotifications = computed(() => {
    return Object.values(notifications.value).reduce((total, count) => total + count, 0);
  });

  // Função para alternar visibilidade das notificações
  const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
  };

  return {
    notifications: computed(() => notifications.value),
    showNotifications: computed(() => showNotifications.value),
    hasNotifications,
    totalNotifications,
    updateNotification,
    incrementNotification,
    decrementNotification,
    clearNotification,
    clearAllNotifications,
    getBadgeColor,
    loadNotificationsFromServer,
    toggleNotifications
  };
}
