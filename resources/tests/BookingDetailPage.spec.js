import { flushPromises, mount } from '@vue/test-utils';
import BookingDetailPage from '../js/pages/BookingDetailPage.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('BookingDetailPage.vue', () => {
  const booking = {
    id: 22,
    common_area_id: 7,
    status: 'pending',
    start_time: '2099-03-10T10:00:00.000Z',
    end_time: '2099-03-10T12:00:00.000Z',
    common_area: { id: 7, name: 'Churrasqueira' },
    apartment: { number: '101' },
  };

  function mountPage() {
    global.__mockVueRoute.params = { id: '22' };

    return mount(BookingDetailPage, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<main><slot /></main>' },
          NeonButton: { template: '<button @click="$emit(\'click\')"><slot /></button>' },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
    apiRequest.mockImplementation((url) => {
      if (url === '/common-area-bookings/22') {
        return Promise.resolve(booking);
      }

      return Promise.resolve(null);
    });
  });

  it('carrega detalhe da reserva pelo cliente central', async () => {
    const wrapper = mountPage();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/common-area-bookings/22');
    expect(apiRequest.mock.calls[0][0]).not.toContain('condominium_id');
    expect(wrapper.text()).toContain('Reserva #22');
  });

  it('cancela reserva e recarrega detalhe', async () => {
    const wrapper = mountPage();
    await flushPromises();

    apiRequest.mockClear();
    apiRequest.mockResolvedValueOnce(null).mockResolvedValueOnce({ ...booking, status: 'cancelled' });
    jest.spyOn(window, 'confirm').mockReturnValueOnce(true);

    await wrapper.vm.cancelBooking();
    await flushPromises();

    expect(apiRequest).toHaveBeenNthCalledWith(1, '/common-areas/7/bookings/22/cancel', { method: 'PATCH' });
    expect(apiRequest).toHaveBeenNthCalledWith(2, '/common-area-bookings/22');
    window.confirm.mockRestore();
  });
});
