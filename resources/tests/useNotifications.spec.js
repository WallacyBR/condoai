import { useNotifications } from '../js/composables/useNotifications.js';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

import { apiRequest } from '../js/utils/api';

describe('useNotifications composable', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    localStorage.setItem('token', 'fake-token');
  });

  test('carrega inbox e sidebar separadamente', async () => {
    apiRequest.mockImplementation((url) => {
      if (url === '/notifications/unread-count') {
        return Promise.resolve({ count: 2 });
      }
      if (url === '/notifications?unread=1&per_page=20') {
        return Promise.resolve({ data: [{ id: '1', title: 'Aviso', body: 'Corpo', action_url: '/notices' }] });
      }
      if (url === '/packages?status=pending') {
        return Promise.resolve([{ id: 10 }, { id: 11 }]);
      }
      return Promise.resolve(null);
    });

    const {
      loadNotificationsFromServer,
      inboxUnreadCount,
      inboxItems,
      notifications,
    } = useNotifications();

    await loadNotificationsFromServer();

    expect(apiRequest).toHaveBeenCalledWith('/notifications/unread-count');
    expect(apiRequest).toHaveBeenCalledWith('/notifications?unread=1&per_page=20');
    expect(apiRequest).toHaveBeenCalledWith('/packages?status=pending');

    expect(inboxUnreadCount.value).toBe(2);
    expect(inboxItems.value).toHaveLength(1);
    expect(notifications.value.packages).toBe(2);
  });

  test('markAllInboxAsRead zera contador do sino', async () => {
    apiRequest.mockResolvedValue({ message: 'ok' });

    const { markAllInboxAsRead, inboxUnreadCount, inboxItems } = useNotifications();

    await markAllInboxAsRead();

    expect(apiRequest).toHaveBeenCalledWith('/notifications/read-all', { method: 'POST' });
    expect(inboxUnreadCount.value).toBe(0);
    expect(inboxItems.value).toEqual([]);
  });
});
