import { flushPromises, mount } from '@vue/test-utils';
import ParkingSpotCreateView from '../js/components/ParkingSpotCreateView.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('ParkingSpotCreateView.vue', () => {
  function mountView() {
    return mount(ParkingSpotCreateView, {
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
      if (url === '/apartments') {
        return Promise.resolve([{ id: 4, number: '101', block: { name: 'A' } }]);
      }

      if (url === '/parking-spots') {
        return Promise.resolve({ id: 9 });
      }

      return Promise.resolve(null);
    });
  });

  it('carrega apartamentos sem condominium_id manual', async () => {
    mountView();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/apartments');
    expect(apiRequest.mock.calls[0][0]).not.toContain('condominium_id');
  });

  it('cria vaga sem enviar condominium_id no payload', async () => {
    const wrapper = mountView();
    await flushPromises();

    wrapper.vm.newSpot.identifier = 'G1';
    wrapper.vm.newSpot.apartment_id = 4;

    await wrapper.vm.createSpot();
    await flushPromises();

    const [, options] = apiRequest.mock.calls.find(([url]) => url === '/parking-spots');
    const payload = JSON.parse(options.body);

    expect(options.method).toBe('POST');
    expect(payload).toEqual({ identifier: 'G1', apartment_id: 4 });
    expect(payload).not.toHaveProperty('condominium_id');
    expect(global.__routerPush).toHaveBeenCalledWith({ name: 'parking-spots.index' });
  });
});
