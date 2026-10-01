import { flushPromises, mount } from '@vue/test-utils';
import CommonAreaCalendar from '../js/components/CommonAreaCalendar.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('CommonAreaCalendar.vue', () => {
  const booking = {
    id: 22,
    status: 'pending',
    booking_type: 'custom',
    start_time: '2099-03-10T10:00:00.000Z',
    end_time: '2099-03-10T12:00:00.000Z',
    apartment: { number: '101' },
  };

  function mountCalendar() {
    return mount(CommonAreaCalendar, {
      props: { area: { id: 7, name: 'Churrasqueira' } },
      global: {
        stubs: {
          CommonAreaBookingForm: { template: '<form />' },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
    apiRequest.mockResolvedValue([booking]);
  });

  it('carrega reservas da area pelo cliente central', async () => {
    const wrapper = mountCalendar();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/common-areas/7/bookings');
    expect(apiRequest.mock.calls[0][0]).not.toContain('condominium_id');
    expect(wrapper.text()).toContain('101');
  });

  it('cancela reserva sem montar headers locais', async () => {
    const wrapper = mountCalendar();
    await flushPromises();

    apiRequest.mockClear();
    apiRequest.mockResolvedValueOnce(null);
    jest.spyOn(window, 'confirm').mockReturnValueOnce(true);

    await wrapper.vm.cancelBooking(22);
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/common-areas/7/bookings/22/cancel', { method: 'PATCH' });
    expect(wrapper.vm.bookings[0].status).toBe('cancelled');
    window.confirm.mockRestore();
  });
});
