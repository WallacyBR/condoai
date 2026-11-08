import { flushPromises, mount } from '@vue/test-utils';
import OccurrenceForm from '../js/components/occurrences/OccurrenceForm.vue';
import api from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  __esModule: true,
  default: {
    get: jest.fn(),
    post: jest.fn(),
  },
}));

jest.mock('../js/composables/useToast', () => ({
  useToast: () => ({
    success: jest.fn(),
    error: jest.fn(),
  }),
}));

describe('OccurrenceForm.vue', () => {
  function mountView(routeParams = {}) {
    global.__mockVueRoute.params = routeParams;

    return mount(OccurrenceForm, {
      global: {
        stubs: {
          TopCard: { props: ['title'], template: '<section><h1>{{ title }}</h1><slot /><slot name="actions" /></section>' },
          NeonButton: { template: '<button :type="type" :disabled="disabled || loading" @click="$emit(\'click\')"><slot /></button>', props: ['type', 'disabled', 'loading'] },
          FileUpload: { template: '<div>Upload de fotos</div>' },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('renderiza formulario de nova ocorrencia no padrao visual da tela', async () => {
    const wrapper = mountView();
    await flushPromises();

    expect(wrapper.text()).toContain('Nova Ocorrência');
    expect(wrapper.text()).toContain('Upload de fotos');
    expect(wrapper.find('input#title').exists()).toBe(true);
    expect(wrapper.find('textarea#description').exists()).toBe(true);
  });

  it('carrega dados na edicao e permite voltar para a listagem', async () => {
    api.get.mockResolvedValueOnce({
      data: {
        title: 'Portão com falha',
        description: 'Descrição carregada',
        priority: 'high',
        status: 'open',
      },
    });

    const wrapper = mountView({ id: '12' });
    await flushPromises();

    expect(api.get).toHaveBeenCalledWith('/occurrences/12');
    expect(wrapper.find('input#title').element.value).toBe('Portão com falha');

    wrapper.vm.goBack();
    expect(global.__routerPush).toHaveBeenCalledWith({ name: 'OccurrenceIndex' });
  });
});
