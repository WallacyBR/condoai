import { flushPromises, mount } from '@vue/test-utils';
import NewBookingModal from '../js/components/NewBookingModal.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('NewBookingModal.vue', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    apiRequest.mockResolvedValue([
      { id: 7, name: 'Churrasqueira', active: true, price_per_hour: 10 },
    ]);
  });

  it('carrega areas ativas sem depender de selectedCondominium local', async () => {
    mount(NewBookingModal, {
      props: { show: true },
      global: {
        stubs: {
          NeonButton: { template: '<button><slot /></button>' },
        },
      },
    });
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/common-areas?active=1');
    expect(apiRequest.mock.calls[0][0]).not.toContain('condominium_id');
  });
});
