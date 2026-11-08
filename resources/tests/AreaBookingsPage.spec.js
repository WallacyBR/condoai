import { flushPromises, mount } from '@vue/test-utils';
import AreaBookingsPage from '../js/pages/AreaBookingsPage.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('AreaBookingsPage.vue', () => {
  const booking = {
    id: 22,
    common_area_id: 7,
    status: 'pending',
    start_time: '2099-03-10T10:00:00.000Z',
    end_time: '2099-03-10T12:00:00.000Z',
    apartment: { number: '101' },
  };

  function mountPage() {
    global.__mockVueRoute.params = { id: '7' };

    return mount(AreaBookingsPage, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<main><slot /></main>' },
          StatsCard: { props: ['label', 'value'], template: '<aside>{{ label }}: {{ value }}</aside>' },
          NeonButton: { template: '<button @click="$emit(\'click\')"><slot /></button>' },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
    apiRequest.mockImplementation((url) => {
      if (url === '/common-areas/7') {
        return Promise.resolve({ id: 7, name: 'Churrasqueira' });
      }

      if (url === '/common-areas/7/bookings') {
        return Promise.resolve([booking]);
      }

      return Promise.resolve(null);
    });
  });

  it('carrega area e reservas sem tenant manual', async () => {
    const wrapper = mountPage();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/common-areas/7');
    expect(apiRequest).toHaveBeenCalledWith('/common-areas/7/bookings');
    expect(apiRequest.mock.calls.map(([url]) => url).join(' ')).not.toContain('condominium_id');
    expect(wrapper.text()).toContain('Churrasqueira');
  });

  it('cancela reserva e recarrega a lista', async () => {
    const wrapper = mountPage();
    await flushPromises();

    apiRequest.mockClear();
    apiRequest.mockResolvedValueOnce(null).mockResolvedValueOnce([booking]);
    jest.spyOn(window, 'confirm').mockReturnValueOnce(true);

    await wrapper.vm.cancelBooking(booking);
    await flushPromises();

    expect(apiRequest).toHaveBeenNthCalledWith(1, '/common-areas/7/bookings/22/cancel', { method: 'PATCH' });
    expect(apiRequest).toHaveBeenNthCalledWith(2, '/common-areas/7/bookings');
    window.confirm.mockRestore();
  });
});
