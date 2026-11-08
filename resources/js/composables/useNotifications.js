import { ref, computed } from 'vue';

// Estado global das notificações
const notifications = ref({
  packages: 3,
  notices: 2,
  maintenance: 5,
  maintenanceTasks: 3,
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

  // Função para simular atualização de notificações (útil para testes)
  const simulateNotificationUpdate = () => {
    // Simular chegada de novas correspondências
    setTimeout(() => {
      incrementNotification('packages');
    }, 5000);

    // Simular novo aviso
    setTimeout(() => {
      incrementNotification('notices');
    }, 10000);

    // Simular nova tarefa de manutenção
    setTimeout(() => {
      incrementNotification('maintenanceTasks');
    }, 15000);
  };

  // Função para carregar notificações do servidor (placeholder)
  const loadNotificationsFromServer = async () => {
    try {
      const token = localStorage.getItem('token');
      if (!token) return;

      // Aqui você pode implementar chamadas para APIs específicas
      // Por exemplo:
      // const response = await fetch('/api/notifications/counts', {
      //   headers: { Authorization: `Bearer ${token}` }
      // });
      // const data = await response.json();
      // Object.assign(notifications.value, data);
      
      console.log('Notificações carregadas do servidor (placeholder)');
    } catch (error) {
      console.error('Erro ao carregar notificações:', error);
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
    simulateNotificationUpdate,
    loadNotificationsFromServer,
    toggleNotifications
  };
} 