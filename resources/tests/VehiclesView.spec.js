import { flushPromises, mount } from '@vue/test-utils';
import VehiclesView from '../js/components/VehiclesView.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('VehiclesView.vue', () => {
  const vehicle = {
    id: 3,
    license_plate: 'ABC-1234',
    model: 'Civic',
    color: 'Preto',
    apartment_id: 4,
    parking_spot_id: 9,
    apartment: { number: '101', block: { name: 'A' } },
    parking_spot: { id: 9, identifier: 'G1' },
  };

  function mountView() {
    return mount(VehiclesView, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<main><slot /></main>' },
          NeonButton: { template: '<button @click="$emit(\'click\')"><slot /></button>' },
          Modal: { props: ['show'], template: '<section v-if="show"><slot /></section>' },
          StatsCard: { props: ['title', 'value'], template: '<aside>{{ title }}: {{ value }}</aside>' },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
    apiRequest.mockResolvedValue([vehicle]);
  });

  it('carrega veiculos pelo cliente central sem filtro local de condominio', async () => {
    const wrapper = mountView();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/vehicles');
    expect(apiRequest.mock.calls[0][0]).not.toContain('condominium_id');
    expect(wrapper.text()).toContain('ABC-1234');
  });

  it('remove veiculo pela API central e atualiza a lista local', async () => {
    const wrapper = mountView();
    await flushPromises();

    apiRequest.mockClear();
    apiRequest.mockResolvedValueOnce(null);
    wrapper.vm.confirmDelete(vehicle);
    await wrapper.vm.deleteVehicle();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/vehicles/3', { method: 'DELETE' });
    expect(wrapper.vm.vehicles).toEqual([]);
  });
});
