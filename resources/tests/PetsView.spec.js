import { flushPromises, mount } from '@vue/test-utils';
import PetsView from '../js/components/PetsView.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('PetsView.vue', () => {
  const pet = {
    id: 5,
    name: 'Rex',
    species: 'Cão',
    breed: 'SRD',
    apartment_id: 4,
    apartment: { number: '101', block: { name: 'A' } },
  };

  function mountView() {
    return mount(PetsView, {
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
    apiRequest.mockResolvedValue([pet]);
  });

  it('carrega pets pelo cliente central sem filtro local de condominio', async () => {
    const wrapper = mountView();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/pets');
    expect(apiRequest.mock.calls[0][0]).not.toContain('condominium_id');
    expect(wrapper.text()).toContain('Rex');
  });

  it('remove pet pela API central e atualiza a lista local', async () => {
    const wrapper = mountView();
    await flushPromises();

    apiRequest.mockClear();
    apiRequest.mockResolvedValueOnce(null);
    wrapper.vm.confirmDelete(pet);
    await wrapper.vm.deletePet();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/pets/5', { method: 'DELETE' });
    expect(wrapper.vm.pets).toEqual([]);
  });
});
