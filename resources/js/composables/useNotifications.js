import { ref, computed } from 'vue';
import { apiRequest } from '../utils/api';

// Badges da sidebar (domínio operacional — separados do sino/inbox)
const sidebarNotifications = ref({
  packages: 0,
  notices: 0,
  maintenance: 0,
  maintenanceTasks: 0,
  vehicles: 0,
  pets: 0,
  areas: 0
});

// Inbox in-app (fonte de verdade: API /notifications)
const inboxUnreadCount = ref(0);
const inboxItems = ref([]);

const showNotifications = ref(true);

export function useNotifications() {

  const updateNotification = (key, value) => {
    if (Object.prototype.hasOwnProperty.call(sidebarNotifications.value, key)) {
      sidebarNotifications.value[key] = value;
    }
  };

  const incrementNotification = (key) => {
    if (Object.prototype.hasOwnProperty.call(sidebarNotifications.value, key)) {
      sidebarNotifications.value[key]++;
    }
  };

  const decrementNotification = (key) => {
    if (Object.prototype.hasOwnProperty.call(sidebarNotifications.value, key) && sidebarNotifications.value[key] > 0) {
      sidebarNotifications.value[key]--;
    }
  };

  const clearNotification = (key) => {
    if (Object.prototype.hasOwnProperty.call(sidebarNotifications.value, key)) {
      sidebarNotifications.value[key] = 0;
    }
  };

  const clearAllSidebarNotifications = () => {
    Object.keys(sidebarNotifications.value).forEach(key => {
      sidebarNotifications.value[key] = 0;
    });
  };

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

  const loadSidebarNotificationsFromServer = async () => {
    try {
      const token = localStorage.getItem('token');
      if (!token) return;

      const pendingPackages = await apiRequest('/packages?status=pending');
      sidebarNotifications.value.packages = Array.isArray(pendingPackages) ? pendingPackages.length : 0;
    } catch {
      sidebarNotifications.value.packages = 0;
    }
  };

  const loadInboxFromServer = async () => {
    try {
      const token = localStorage.getItem('token');
      if (!token) return;

      const [countResponse, listResponse] = await Promise.all([
        apiRequest('/notifications/unread-count'),
        apiRequest('/notifications?unread=1&per_page=20'),
      ]);

      inboxUnreadCount.value = typeof countResponse?.count === 'number' ? countResponse.count : 0;
      inboxItems.value = Array.isArray(listResponse?.data) ? listResponse.data : [];
    } catch {
      inboxUnreadCount.value = 0;
      inboxItems.value = [];
    }
  };

  const loadNotificationsFromServer = async () => {
    await Promise.all([
      loadSidebarNotificationsFromServer(),
      loadInboxFromServer(),
    ]);
  };

  const markInboxNotificationRead = async (notificationId) => {
    await apiRequest(`/notifications/${notificationId}/read`, { method: 'POST' });
    inboxItems.value = inboxItems.value.filter(item => item.id !== notificationId);
    if (inboxUnreadCount.value > 0) {
      inboxUnreadCount.value--;
    }
  };

  const markAllInboxAsRead = async () => {
    await apiRequest('/notifications/read-all', { method: 'POST' });
    inboxUnreadCount.value = 0;
    inboxItems.value = [];
  };

  const hasInboxNotifications = computed(() => inboxUnreadCount.value > 0);

  const hasSidebarNotifications = computed(() => {
    return Object.values(sidebarNotifications.value).some(count => count > 0);
  });

  const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
  };

  return {
    notifications: computed(() => sidebarNotifications.value),
    inboxUnreadCount: computed(() => inboxUnreadCount.value),
    inboxItems: computed(() => inboxItems.value),
    showNotifications: computed(() => showNotifications.value),
    hasInboxNotifications,
    hasSidebarNotifications,
    updateNotification,
    incrementNotification,
    decrementNotification,
    clearNotification,
    clearAllSidebarNotifications,
    getBadgeColor,
    loadNotificationsFromServer,
    loadSidebarNotificationsFromServer,
    loadInboxFromServer,
    markInboxNotificationRead,
    markAllInboxAsRead,
    toggleNotifications
  };
}
