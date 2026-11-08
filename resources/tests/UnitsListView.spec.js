import { flushPromises, mount } from '@vue/test-utils';
import UnitsListView from '../js/components/UnitsListView.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('UnitsListView.vue', () => {
  const unit = {
    id: 12,
    number: '101',
    floor: 1,
    active: true,
    owner: null,
    tenant: null,
    tower: { id: 1, name: 'Torre A' },
    block: { id: 2, name: 'Bloco 1' },
  };

  function mountView() {
    return mount(UnitsListView, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<main><slot /></main>' },
          NeonButton: { template: '<button @click="$emit(\'click\')"><slot /></button>' },
          TopCard: { props: ['title'], template: '<section><h1>{{ title }}</h1><slot /><slot name="actions" /></section>' },
          StatsCard: { props: ['label', 'value'], template: '<aside>{{ label }}: {{ value }}</aside>' },
          ListFilters: { template: '<div />' },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
    localStorage.setItem('selectedCondominiumName', 'Residencial Teste');
    apiRequest.mockImplementation((url) => {
      if (url === '/apartments') return Promise.resolve([unit]);
      if (url === '/towers') return Promise.resolve([{ id: 1, name: 'Torre A' }]);
      if (url === '/blocks') return Promise.resolve([{ id: 2, name: 'Bloco 1' }]);
      return Promise.resolve(null);
    });
  });

  it('carrega lista de unidades sem filtro local de condominio', async () => {
    const wrapper = mountView();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/apartments');
    expect(apiRequest).toHaveBeenCalledWith('/towers');
    expect(apiRequest).toHaveBeenCalledWith('/blocks');
    expect(apiRequest.mock.calls.map(([url]) => url).join(' ')).not.toContain('condominium_id');
    expect(wrapper.vm.units).toEqual([unit]);
  });
});
