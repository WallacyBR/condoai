import { flushPromises, mount } from '@vue/test-utils';
import OccurrenceList from '../js/components/occurrences/OccurrenceList.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('OccurrenceList.vue', () => {
  const occurrence = {
    id: 12,
    title: 'Portão da garagem com falha',
    description: 'Portão travando no fechamento.',
    status: 'open',
    priority: 'urgent',
    created_at: '2026-06-25T10:30:00.000000Z',
    updated_at: '2026-06-25T11:00:00.000000Z',
    user: { name: 'Ana Zeladora' },
    photos: [{ photo_path: 'occurrence_photos/demo/occurrence.png' }],
    comments: [{ id: 1 }],
  };

  function mountView() {
    return mount(OccurrenceList, {
      global: {
        stubs: {
          TopCard: { props: ['title'], template: '<section><h1>{{ title }}</h1><slot /><slot name="actions" /></section>' },
          NeonButton: { template: '<button :disabled="disabled" @click="$emit(\'click\')"><slot /></button>', props: ['disabled'] },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
    apiRequest.mockResolvedValue({
      data: [occurrence],
      current_page: 1,
      last_page: 1,
      total: 1,
    });
  });

  it('carrega ocorrencias pelo cliente API central e exibe os dados no padrao da tela', async () => {
    const wrapper = mountView();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/occurrences?page=1&per_page=50');
    expect(wrapper.text()).toContain('Ocorrências');
    expect(wrapper.text()).toContain('Portão da garagem com falha');
    expect(wrapper.text()).toContain('Urgente');
    expect(wrapper.find('img').attributes('src')).toBe('/storage/occurrence_photos/demo/occurrence.png');
  });

  it('recarrega a API quando filtros de status e prioridade mudam', async () => {
    const wrapper = mountView();
    await flushPromises();

    const selects = wrapper.findAll('select');
    await selects[0].setValue('open');
    await flushPromises();
    await selects[1].setValue('urgent');
    await flushPromises();

    expect(apiRequest).toHaveBeenLastCalledWith('/occurrences?page=1&per_page=50&status=open&priority=urgent');
  });

  it('filtra localmente por busca e limpa os filtros', async () => {
    const wrapper = mountView();
    await flushPromises();

    await wrapper.find('input[type="text"]').setValue('inexistente');
    expect(wrapper.text()).toContain('Nenhuma ocorrência encontrada');

    await wrapper.find('input[type="text"]').setValue('garagem');
    expect(wrapper.text()).toContain('Portão da garagem com falha');

    apiRequest.mockClear();
    await wrapper.vm.clearFilters();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/occurrences?page=1&per_page=50');
    expect(wrapper.vm.search).toBe('');
  });

  it('navega para criacao, detalhe e edicao usando rotas nomeadas', async () => {
    const wrapper = mountView();
    await flushPromises();

    wrapper.vm.goToCreate();
    wrapper.vm.goToShow(occurrence);
    wrapper.vm.goToEdit(occurrence);

    expect(global.__routerPush).toHaveBeenNthCalledWith(1, { name: 'OccurrenceCreate' });
    expect(global.__routerPush).toHaveBeenNthCalledWith(2, { name: 'OccurrenceShow', params: { id: 12 } });
    expect(global.__routerPush).toHaveBeenNthCalledWith(3, { name: 'OccurrenceEdit', params: { id: 12 } });
  });
});
