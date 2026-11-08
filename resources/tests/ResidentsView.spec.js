import { flushPromises, mount } from '@vue/test-utils';
import ResidentsView from '../js/components/ResidentsView.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('ResidentsView.vue', () => {
  const resident = {
    id: 7,
    name: 'Ana Moradora',
    email: 'ana@example.com',
    cell_phone: '(11) 99999-0000',
    classification: 'morador',
    active: true,
  };

  function mountView() {
    return mount(ResidentsView, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<main><slot /></main>' },
          TopCard: { props: ['title'], template: '<section><h1>{{ title }}</h1><slot /><slot name="actions" /></section>' },
          NeonButton: { template: '<button @click="$emit(\'click\')"><slot /></button>' },
          ResidentPhoto: { props: ['name'], template: '<div>{{ name }}</div>' },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
    window.history.pushState({}, '', '/residents');
    localStorage.setItem('selectedCondominiumName', 'Condomínio Teste');
    apiRequest.mockImplementation((url) => {
      if (url === '/residents') return Promise.resolve([resident]);
      if (url === '/condominiums') return Promise.resolve([{ id: 1, name: 'Condomínio Teste' }]);
      return Promise.resolve(null);
    });
  });

  it('carrega moradores pelo cliente central sem rota web ou tenant manual', async () => {
    const wrapper = mountView();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/residents');
    expect(apiRequest.mock.calls.map(([url]) => url).join(' ')).not.toContain('/api');
    expect(apiRequest.mock.calls.map(([url]) => url).join(' ')).not.toContain('condominium_id');
    expect(wrapper.text()).toContain('Ana Moradora');
  });

  it('remove morador pelo cliente API central e atualiza a lista local', async () => {
    const wrapper = mountView();
    await flushPromises();

    apiRequest.mockClear();
    apiRequest.mockResolvedValueOnce(null);
    wrapper.vm.showDeleteConfirmation(resident);
    await wrapper.vm.confirmDelete();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/residents/7', { method: 'DELETE' });
    expect(wrapper.vm.residents).toEqual([]);
  });
});
