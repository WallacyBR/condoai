import { flushPromises, mount } from '@vue/test-utils';
import OccurrenceView from '../js/components/occurrences/OccurrenceView.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('OccurrenceView.vue', () => {
  const occurrence = {
    id: 12,
    title: 'Portão da garagem com falha',
    description: 'Portão travando no fechamento.',
    status: 'open',
    priority: 'high',
    created_at: '2026-06-25T10:30:00.000000Z',
    updated_at: '2026-06-25T11:00:00.000000Z',
    user: { name: 'Ana Zeladora' },
    photos: [{ id: 1, photo_path: 'occurrence_photos/demo/occurrence.png' }],
    comments: [{ id: 8, comment: 'Equipe acionada.', created_at: '2026-06-25T11:10:00.000000Z', user: { name: 'Síndico' } }],
  };

  function mountView() {
    global.__mockVueRoute.params = { id: '12' };

    return mount(OccurrenceView, {
      global: {
        stubs: {
          TopCard: { props: ['title'], template: '<section><h1>{{ title }}</h1><slot /><slot name="actions" /></section>' },
          NeonButton: { template: '<button :type="type" :disabled="disabled || loading" @click="$emit(\'click\')"><slot /></button>', props: ['type', 'disabled', 'loading'] },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
    apiRequest.mockImplementation((url, options = {}) => {
      if (url === '/occurrences/12' && !options.method) {
        return Promise.resolve(occurrence);
      }

      if (url === '/occurrences/12/comments' && options.method === 'POST') {
        return Promise.resolve({
          id: 9,
          comment: JSON.parse(options.body).comment,
          created_at: '2026-06-25T12:00:00.000000Z',
          user: { name: 'Usuário Teste' },
        });
      }

      return Promise.resolve(null);
    });
  });

  it('carrega o detalhe da ocorrencia pelo cliente API central', async () => {
    const wrapper = mountView();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/occurrences/12');
    expect(wrapper.text()).toContain('Portão da garagem com falha');
    expect(wrapper.text()).toContain('Equipe acionada.');
    expect(wrapper.find('img').attributes('src')).toBe('/storage/occurrence_photos/demo/occurrence.png');
  });

  it('adiciona comentario e atualiza a lista local', async () => {
    const wrapper = mountView();
    await flushPromises();

    await wrapper.find('textarea').setValue('Comentário novo');
    await wrapper.find('form').trigger('submit.prevent');
    await flushPromises();

    expect(apiRequest).toHaveBeenLastCalledWith('/occurrences/12/comments', {
      method: 'POST',
      body: JSON.stringify({ comment: 'Comentário novo' }),
    });
    expect(wrapper.text()).toContain('Comentário novo');
  });

  it('navega para listagem e edicao com rotas nomeadas', async () => {
    const wrapper = mountView();
    await flushPromises();

    wrapper.vm.goBack();
    wrapper.vm.goToEdit();

    expect(global.__routerPush).toHaveBeenNthCalledWith(1, { name: 'OccurrenceIndex' });
    expect(global.__routerPush).toHaveBeenNthCalledWith(2, { name: 'OccurrenceEdit', params: { id: '12' } });
  });
});
