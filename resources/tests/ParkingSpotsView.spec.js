import { flushPromises, mount } from '@vue/test-utils';
import ParkingSpotsView from '../js/components/ParkingSpotsView.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('ParkingSpotsView.vue', () => {
  const apartment = { id: 4, number: '101', block: { name: 'A' } };
  const spot = {
    id: 9,
    identifier: 'G1',
    apartment_id: 4,
    apartment,
    rented_to_apartment_id: null,
  };

  function mountView() {
    return mount(ParkingSpotsView, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<main><slot /></main>' },
          Modal: { props: ['show'], template: '<section v-if="show"><slot /></section>' },
          NeonButton: { template: '<button @click="$emit(\'click\')"><slot /></button>' },
          TopCard: { props: ['title'], template: '<section><h1>{{ title }}</h1><slot /><slot name="actions" /></section>' },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
    apiRequest.mockImplementation((url) => {
      if (url === '/apartments') {
        return Promise.resolve([apartment]);
      }

      if (url === '/parking-spots') {
        return Promise.resolve([spot]);
      }

      return Promise.resolve(null);
    });
  });

  it('carrega apartamentos e vagas sem filtro local de condominio', async () => {
    const wrapper = mountView();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/apartments');
    expect(apiRequest).toHaveBeenCalledWith('/parking-spots');
    expect(apiRequest.mock.calls.map(([url]) => url).join(' ')).not.toContain('condominium_id');
    expect(wrapper.text()).toContain('G1');
  });

  it('cria vagas em lote sem enviar condominium_id', async () => {
    const wrapper = mountView();
    await flushPromises();

    apiRequest.mockClear();
    apiRequest
      .mockResolvedValueOnce({ created: 1 })
      .mockResolvedValueOnce([apartment])
      .mockResolvedValueOnce([spot]);

    wrapper.vm.spotsPerApartment = 2;
    await wrapper.vm.createBatchSpots();
    await flushPromises();

    const [, options] = apiRequest.mock.calls.find(([url]) => url === '/parking-spots/batch');
    const payload = JSON.parse(options.body);

    expect(options.method).toBe('POST');
    expect(payload).toEqual({ spots_per_apartment: 2 });
    expect(payload).not.toHaveProperty('condominium_id');
  });

  it('exclui vaga pelo cliente central e remove da lista local', async () => {
    const wrapper = mountView();
    await flushPromises();

    apiRequest.mockClear();
    apiRequest.mockResolvedValueOnce(null);
    wrapper.vm.showDeleteConfirmation(spot);
    await wrapper.vm.confirmDelete();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/parking-spots/9', { method: 'DELETE' });
    expect(wrapper.vm.spots).toEqual([]);
  });
});
