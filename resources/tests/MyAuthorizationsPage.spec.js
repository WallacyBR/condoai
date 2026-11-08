import { flushPromises, mount } from '@vue/test-utils';
import MyAuthorizationsPage from '../js/pages/AccessControl/MyAuthorizationsPage.vue';
import api from '../js/utils/api';

const mockSuccessToast = jest.fn();
const mockErrorToast = jest.fn();

jest.mock('../js/utils/api', () => ({
  __esModule: true,
  default: {
    get: jest.fn(),
  },
}));

jest.mock('../js/composables/useToast', () => ({
  useToast: () => ({
    successToast: mockSuccessToast,
    errorToast: mockErrorToast,
  }),
}));

describe('MyAuthorizationsPage', () => {
  const requests = [
    {
      id: 1,
      status: 'pending',
      visitor: { name: 'Visitante Pendente' },
      request_timestamp: '2026-06-26T10:00:00Z',
    },
  ];

  function mountView() {
    return mount(MyAuthorizationsPage, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<main><slot /></main>' },
          HeaderCard: { template: '<section><slot /></section>' },
          NeonButton: { template: '<button @click="$emit(\'click\')"><slot /></button>' },
          ResidentAccessRequestCard: {
            props: ['request'],
            template: '<article>{{ request.visitor.name }}</article>',
          },
          RespondAccessRequestModal: {
            props: ['show', 'requestDetails'],
            template: '<div v-if="show">{{ requestDetails?.id }}</div>',
          },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
  });

  it('renderiza solicitações quando a API retorna objeto com data', async () => {
    api.get.mockResolvedValueOnce({ data: { data: requests } });

    const wrapper = mountView();
    await flushPromises();

    expect(api.get).toHaveBeenCalledWith('/access-control/access-requests/my-requests');
    expect(wrapper.text()).toContain('Visitante Pendente');
    expect(wrapper.vm.myRequests).toHaveLength(1);
  });

  it('renderiza solicitações quando a API retorna uma lista direta', async () => {
    api.get.mockResolvedValueOnce({ data: requests });

    const wrapper = mountView();
    await flushPromises();

    expect(wrapper.text()).toContain('Visitante Pendente');
    expect(wrapper.vm.myRequests).toHaveLength(1);
  });

  it('exibe erro e dispara toast quando a API falha', async () => {
    api.get.mockRejectedValueOnce(new Error('API indisponível'));

    const wrapper = mountView();
    await flushPromises();

    expect(wrapper.text()).toContain('Falha ao carregar suas solicitações pendentes.');
    expect(mockErrorToast).toHaveBeenCalledWith('Falha ao carregar suas solicitações pendentes.');
  });
});
