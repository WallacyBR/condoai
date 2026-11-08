import { flushPromises, mount } from '@vue/test-utils';
import BookingFormPage from '../js/pages/BookingFormPage.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('BookingFormPage.vue', () => {
  function mountPage() {
    return mount(BookingFormPage, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<main><slot /></main>' },
          NeonButton: { template: '<button type="submit"><slot /></button>' },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
    apiRequest.mockImplementation((url) => {
      if (url === '/common-areas') {
        return Promise.resolve([{ id: 7, name: 'Salão de Festas', price_per_hour: 20 }]);
      }

      if (url === '/apartments') {
        return Promise.resolve([{ id: 4, number: '101', block: { name: 'A' } }]);
      }

      if (url === '/common-area-bookings') {
        return Promise.resolve({ id: 33 });
      }

      return Promise.resolve(null);
    });
  });

  it('carrega areas e apartamentos usando o tenant resolvido pelo backend', async () => {
    mountPage();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/common-areas');
    expect(apiRequest).toHaveBeenCalledWith('/apartments');
    expect(apiRequest.mock.calls.map(([url]) => url).join(' ')).not.toContain('condominium_id');
  });

  it('cria reserva sem incluir condominium_id no payload', async () => {
    const wrapper = mountPage();
    await flushPromises();

    wrapper.vm.form.common_area_id = 7;
    wrapper.vm.form.apartment_id = 4;
    wrapper.vm.form.date = '2099-01-10';
    wrapper.vm.form.start_time = '10:00';
    wrapper.vm.form.end_time = '12:00';
    wrapper.vm.bookingTypeForSelectedDate = 'hourly';

    await wrapper.vm.submitForm();
    await flushPromises();

    const [, options] = apiRequest.mock.calls.find(([url]) => url === '/common-area-bookings');
    const payload = JSON.parse(options.body);

    expect(options.method).toBe('POST');
    expect(payload).toEqual({
      common_area_id: 7,
      apartment_id: 4,
      notes: null,
      start_time: '2099-01-10T10:00:00',
      end_time: '2099-01-10T12:00:00',
      booking_type: 'hourly',
    });
    expect(payload).not.toHaveProperty('condominium_id');
    expect(global.__routerPush).toHaveBeenCalledWith({ name: 'bookings.show', params: { id: 33 } });
  });
});
