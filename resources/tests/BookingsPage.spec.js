import { flushPromises, mount } from '@vue/test-utils';
import BookingsPage from '../js/pages/BookingsPage.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('BookingsPage.vue', () => {
  const futureBooking = {
    id: 12,
    common_area_id: 7,
    status: 'pending',
    start_time: '2099-01-10T10:00:00.000Z',
    end_time: '2099-01-10T12:00:00.000Z',
    common_area: { id: 7, name: 'Salão de Festas' },
    apartment: { id: 4, number: '101', block: { name: 'A' } },
  };

  function mountPage() {
    return mount(BookingsPage, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<main><slot /></main>' },
          TopCard: { props: ['title', 'subtitle'], template: '<section><h1>{{ title }}</h1><slot name="actions" /></section>' },
          StatsCard: { props: ['label', 'value'], template: '<aside>{{ label }}: {{ value }}</aside>' },
          NeonButton: { template: '<button @click="$emit(\'click\')"><slot /></button>' },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
    localStorage.setItem('selectedCondominiumName', 'Residencial Teste');
    apiRequest.mockImplementation((url) => {
      if (url === '/common-area-bookings') {
        return Promise.resolve([futureBooking]);
      }

      if (url === '/common-areas') {
        return Promise.resolve([{ id: 7, name: 'Salão de Festas' }]);
      }

      return Promise.resolve(null);
    });
  });

  it('carrega reservas e areas comuns sem enviar condominium_id manual', async () => {
    const wrapper = mountPage();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/common-area-bookings');
    expect(apiRequest).toHaveBeenCalledWith('/common-areas');
    expect(apiRequest.mock.calls.map(([url]) => url).join(' ')).not.toContain('condominium_id');
    expect(wrapper.text()).toContain('Salão de Festas');
  });

  it('cancela uma reserva pelo cliente API central e recarrega a lista', async () => {
    const wrapper = mountPage();
    await flushPromises();

    apiRequest.mockClear();
    apiRequest
      .mockResolvedValueOnce(null)
      .mockResolvedValueOnce([futureBooking]);
    jest.spyOn(window, 'confirm').mockReturnValueOnce(true);

    await wrapper.vm.cancelBooking(futureBooking);
    await flushPromises();

    expect(apiRequest).toHaveBeenNthCalledWith(
      1,
      '/common-areas/7/bookings/12/cancel',
      { method: 'PATCH' }
    );
    expect(apiRequest).toHaveBeenNthCalledWith(2, '/common-area-bookings');
    window.confirm.mockRestore();
  });
});
