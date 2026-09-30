import { mount, flushPromises } from '@vue/test-utils';
import { vi } from 'vitest';
import MyAuthorizationsPage from '../../pages/AccessControl/MyAuthorizationsPage.vue';
import ResidentAccessRequestCard from '../../components/access_control/ResidentAccessRequestCard.vue';
import RespondAccessRequestModal from '../../components/access_control/RespondAccessRequestModal.vue';
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

vi.mock('vue-router', async (importOriginal) => {
  const actual = await importOriginal();
  return {
    ...actual,
    useRouter: () => ({
      push: vi.fn(),
    }),
  };
});

describe('MyAuthorizationsPage.vue', () => {
  let mockPendingRequests;

  beforeEach(() => {
    resetMockToast();
    api.get.mockReset();
    api.post.mockReset();

    mockPendingRequests = [
      { id: 1, status: 'pending', visitor: { name: 'Visitante Pendente 1' }, request_timestamp: new Date().toISOString() },
      { id: 2, status: 'pending', visitor: { name: 'Visitante Pendente 2' }, request_timestamp: new Date().toISOString() },
    ];
  });

  const mountPage = () => mount(MyAuthorizationsPage, {
    global: {
      stubs: {
        AuthenticatedLayout: { template: '<div><slot name="page-title"/><slot/></div>' },
        HeaderCard: true,
        ResidentAccessRequestCard: false,
        RespondAccessRequestModal: false,
        NeonButton: false,
      },
    },
  });

  it('fetches and displays pending requests on mount', async () => {
    api.get.mockResolvedValue({ data: { data: mockPendingRequests } });
    const wrapper = mountPage();
    await flushPromises();

    expect(api.get).toHaveBeenCalledWith('/access-control/access-requests/my-requests');
    expect(wrapper.findAllComponents(ResidentAccessRequestCard).length).toBe(mockPendingRequests.length);
    expect(wrapper.text()).toContain('Visitante Pendente 1');
  });

  it('displays loading state initially', async () => {
    api.get.mockImplementation(() => new Promise(() => {}));
    const wrapper = mountPage();
    await wrapper.vm.$nextTick();
    expect(wrapper.vm.loading).toBe(true);
    expect(wrapper.text()).toContain('Carregando suas solicitações...');
  });

  it('displays error message on fetch failure', async () => {
    api.get.mockRejectedValue(new Error('API Error'));
    const wrapper = mountPage();
    await flushPromises();

    expect(wrapper.text()).toContain('Falha ao carregar suas solicitações pendentes.');
    expect(mockToastFns.errorToast).toHaveBeenCalledWith('Falha ao carregar suas solicitações pendentes.');
  });

  it('opens RespondAccessRequestModal when a card emits "respond-to-request"', async () => {
    api.get.mockResolvedValue({ data: { data: [mockPendingRequests[0]] } });
    const wrapper = mountPage();
    await flushPromises();

    const card = wrapper.findComponent(ResidentAccessRequestCard);
    card.vm.$emit('respond-to-request', mockPendingRequests[0]);
    await wrapper.vm.$nextTick();

    const modal = wrapper.findComponent(RespondAccessRequestModal);
    expect(modal.props('show')).toBe(true);
    expect(modal.props('requestDetails')).toEqual(mockPendingRequests[0]);
  });

  it('closes modal and re-fetches requests when modal emits "response-submitted"', async () => {
    api.get.mockResolvedValueOnce({ data: { data: [mockPendingRequests[0]] } });
    api.get.mockResolvedValueOnce({ data: { data: [] } });

    const wrapper = mountPage();
    await flushPromises();

    wrapper.vm.selectedRequestForResponse = mockPendingRequests[0];
    wrapper.vm.showResponseModal = true;
    await wrapper.vm.$nextTick();

    wrapper.findComponent(RespondAccessRequestModal).vm.$emit('response-submitted');
    await flushPromises();

    expect(wrapper.vm.showResponseModal).toBe(false);
    expect(mockToastFns.successToast).toHaveBeenCalledWith('Sua resposta foi enviada com sucesso!');
    expect(api.get).toHaveBeenCalledTimes(2);
  });

  it('closes modal when modal emits "close"', async () => {
    api.get.mockResolvedValue({ data: { data: [mockPendingRequests[0]] } });
    const wrapper = mountPage();
    await flushPromises();

    wrapper.vm.selectedRequestForResponse = mockPendingRequests[0];
    wrapper.vm.showResponseModal = true;
    await wrapper.vm.$nextTick();

    wrapper.findComponent(RespondAccessRequestModal).vm.$emit('close');
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.showResponseModal).toBe(false);
  });
});
