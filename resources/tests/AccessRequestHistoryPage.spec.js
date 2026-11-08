import { flushPromises, mount } from '@vue/test-utils';
import AccessRequestHistoryPage from '../js/pages/AccessControl/AccessRequestHistoryPage.vue';
import api from '../js/utils/api';

const mockErrorToast = jest.fn();

jest.mock('../js/utils/api', () => ({
  __esModule: true,
  default: {
    get: jest.fn(),
  },
}));

jest.mock('../js/composables/useToast', () => ({
  useToast: () => ({
    errorToast: mockErrorToast,
  }),
}));

describe('AccessRequestHistoryPage', () => {
  const requests = [
    {
      id: 1,
      status: 'authorized',
      visitor: { name: 'Visitante Autorizado' },
      resident: { name: 'Morador Teste' },
      request_timestamp: '2026-06-26T10:00:00Z',
    },
  ];

  function mountView() {
    return mount(AccessRequestHistoryPage, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<main><slot /></main>' },
          HeaderCard: { template: '<section><slot /></section>' },
          NeonButton: {
            props: ['loading'],
            template: '<button @click="$emit(\'click\')"><slot /></button>',
          },
          AccessRequestCard: {
            props: ['request'],
            template: '<article>{{ request.visitor.name }}</article>',
          },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('renderiza histórico quando a API retorna paginação Laravel', async () => {
    api.get.mockResolvedValueOnce({
      data: {
        data: requests,
        current_page: 1,
        last_page: 1,
      },
    });

    const wrapper = mountView();
    await flushPromises();

    expect(api.get).toHaveBeenCalledWith('/access-control/access-requests/history', {
      params: { page: 1, per_page: 15 },
    });
    expect(wrapper.text()).toContain('Visitante Autorizado');
    expect(wrapper.vm.historyRequests).toHaveLength(1);
  });

  it('renderiza histórico quando a API retorna lista direta', async () => {
    api.get.mockResolvedValueOnce({ data: requests });

    const wrapper = mountView();
    await flushPromises();

    expect(wrapper.text()).toContain('Visitante Autorizado');
    expect(wrapper.vm.historyRequests).toHaveLength(1);
  });

  it('envia filtro de status ao consultar histórico', async () => {
    api.get.mockResolvedValue({ data: { data: [] } });
    const wrapper = mountView();
    await flushPromises();

    wrapper.vm.filters.status = 'authorized';
    await wrapper.vm.fetchHistory();

    expect(api.get).toHaveBeenLastCalledWith('/access-control/access-requests/history', {
      params: { page: 1, per_page: 15, status: 'authorized' },
    });
  });

  it('exibe erro e dispara toast quando a API falha', async () => {
    api.get.mockRejectedValueOnce(new Error('API indisponível'));

    const wrapper = mountView();
    await flushPromises();

    expect(wrapper.text()).toContain('Falha ao carregar o histórico de solicitações.');
    expect(mockErrorToast).toHaveBeenCalledWith('Falha ao carregar o histórico de solicitações.');
  });
});
