import { flushPromises, mount } from '@vue/test-utils';
import CommonAreaBookingForm from '../js/components/CommonAreaBookingForm.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('CommonAreaBookingForm.vue', () => {
  function mountForm() {
    return mount(CommonAreaBookingForm, {
      props: {
        area: {
          id: 7,
          name: 'Churrasqueira',
          price_per_hour: 25,
          allow_custom_periods: true,
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
    localStorage.setItem('selectedApartment', '4');
    apiRequest.mockImplementation((url) => {
      if (url === '/common-areas/7/available-slots?date=2099-03-10') {
        return Promise.resolve({
          available_slots: [
            {
              type: 'hourly',
              name: 'Manha',
              formatted_start: '10:00',
              formatted_end: '12:00',
              price: 50,
            },
          ],
        });
      }

      if (url === '/common-areas/7/bookings') {
        return Promise.resolve({ id: 22 });
      }

      return Promise.resolve(null);
    });
  });

  it('carrega horarios disponiveis via apiRequest', async () => {
    const wrapper = mountForm();

    wrapper.vm.form.date = '2099-03-10';
    await wrapper.vm.loadAvailableSlots();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/common-areas/7/available-slots?date=2099-03-10');
    expect(apiRequest.mock.calls[0][0]).not.toContain('condominium_id');
    expect(wrapper.vm.availableHourlySlots).toHaveLength(1);
  });

  it('envia reserva sem tenant manual no payload', async () => {
    const wrapper = mountForm();

    Object.assign(wrapper.vm.form, {
      date: '2099-03-10',
      start_time: '10:00',
      end_time: '12:00',
      booking_type: 'hourly',
      notes: 'Teste',
    });

    await wrapper.vm.submitBooking();
    await flushPromises();

    const [, options] = apiRequest.mock.calls.find(([url]) => url === '/common-areas/7/bookings');
    const payload = JSON.parse(options.body);

    expect(options.method).toBe('POST');
    expect(payload).toEqual({
      apartment_id: 4,
      start_time: '2099-03-10 10:00',
      end_time: '2099-03-10 12:00',
      booking_type: 'hourly',
      notes: 'Teste',
    });
    expect(payload).not.toHaveProperty('condominium_id');
    expect(wrapper.emitted('submit')[0][0]).toEqual({ id: 22 });
  });
});
