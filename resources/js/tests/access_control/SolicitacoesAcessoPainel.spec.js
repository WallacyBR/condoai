import { mount, flushPromises } from '@vue/test-utils';
import { vi } from 'vitest';
import SolicitacoesAcessoPainel from '../../components/access_control/SolicitacoesAcessoPainel.vue';
import AccessRequestCard from '../../components/access_control/AccessRequestCard.vue';
import ConfirmActionModal from '../../components/ConfirmActionModal.vue';
import api from '../../utils/api';
import { mockToastFns, resetMockToast } from '../mocks/useToast';

const { mockApi } = vi.hoisted(() => ({
  mockApi: {
    get: vi.fn(),
    post: vi.fn(),
  },
}));

vi.mock('../../utils/api', () => ({
  default: mockApi,
}));

describe('SolicitacoesAcessoPainel.vue', () => {
  let mockRequests;

  const defaultStubs = {
    AccessRequestCard: true,
    ConfirmActionModal: true,
    RecordEntryModal: true,
    NeonButton: true,
  };

  beforeEach(() => {
    resetMockToast();
    api.get.mockReset();
    api.post.mockReset();

    mockRequests = [
      { id: 1, status: 'pending', visitor: { name: 'Visitante 1' }, request_timestamp: new Date().toISOString() },
      { id: 2, status: 'authorized', visitor: { name: 'Visitante 2' }, request_timestamp: new Date().toISOString() },
    ];
  });

  it('fetches and displays access requests on mount', async () => {
    api.get.mockResolvedValue({ data: { data: mockRequests } });
    const wrapper = mount(SolicitacoesAcessoPainel, { global: { stubs: defaultStubs } });
    await flushPromises();

    expect(api.get).toHaveBeenCalledWith('/access-control/access-requests/gatehouse?ativas=true&per_page=50');
    expect(wrapper.vm.accessRequests.length).toBe(mockRequests.length);
  });

  it('displays loading state initially', async () => {
    api.get.mockImplementation(() => new Promise(() => {}));
    const wrapper = mount(SolicitacoesAcessoPainel, { global: { stubs: defaultStubs } });
    await wrapper.vm.$nextTick();
    expect(wrapper.vm.loading).toBe(true);
    expect(wrapper.text()).toContain('Carregando solicitações...');
  });

  it('displays error message on fetch failure', async () => {
    api.get.mockRejectedValue(new Error('API Error'));
    const wrapper = mount(SolicitacoesAcessoPainel, { global: { stubs: defaultStubs } });
    await flushPromises();

    expect(wrapper.text()).toContain('Falha ao carregar as solicitações. Tente novamente.');
    expect(mockToastFns.errorToast).toHaveBeenCalledWith('Falha ao carregar as solicitações. Tente novamente.');
  });

  it('displays no requests message when list is empty', async () => {
    api.get.mockResolvedValue({ data: { data: [] } });
    const wrapper = mount(SolicitacoesAcessoPainel, { global: { stubs: defaultStubs } });
    await flushPromises();

    expect(wrapper.text()).toContain('Nenhuma solicitação de acesso encontrada.');
  });

  it('sets up requestToCancel when handleCancelRequest is called from a card', async () => {
    api.get.mockResolvedValue({ data: { data: mockRequests } });
    const wrapper = mount(SolicitacoesAcessoPainel, {
      global: { stubs: { ConfirmActionModal: true, RecordEntryModal: true } },
    });
    await flushPromises();

    const firstCard = wrapper.findComponent(AccessRequestCard);
    expect(firstCard.exists()).toBe(true);

    firstCard.vm.$emit('cancel-request', mockRequests[0]);
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.requestToCancel).toEqual(mockRequests[0]);
    expect(wrapper.vm.showConfirmCancelModal).toBe(true);
  });

  it('calls cancel API and re-fetches on confirmCancelRequest', async () => {
    api.get.mockResolvedValue({ data: { data: mockRequests } });
    api.post.mockResolvedValue({});

    const wrapper = mount(SolicitacoesAcessoPainel, {
      global: { stubs: { AccessRequestCard: true, RecordEntryModal: true } },
    });
    await flushPromises();

    wrapper.vm.requestToCancel = mockRequests[0];
    wrapper.vm.showConfirmCancelModal = true;
    await wrapper.vm.$nextTick();

    await wrapper.vm.confirmCancelRequest();
    await flushPromises();

    expect(api.post).toHaveBeenCalledWith(`/api/access-control/access-requests/${mockRequests[0].id}/cancel`, expect.anything());
    expect(mockToastFns.successToast).toHaveBeenCalledWith('Solicitação cancelada com sucesso!');
    expect(api.get).toHaveBeenCalledTimes(2);
    expect(wrapper.vm.showConfirmCancelModal).toBe(false);
  });
});
