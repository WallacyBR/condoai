import { mount } from '@vue/test-utils';
import MyAuthorizationsPage from '../../pages/AccessControl/MyAuthorizationsPage.vue';
import ResidentAccessRequestCard from '../../components/access_control/ResidentAccessRequestCard.vue';
import RespondAccessRequestModal from '../../components/access_control/RespondAccessRequestModal.vue';
import NeonButton from '../../components/NeonButton.vue';
import api from '../../utils/api';

const mockSuccessToast = jest.fn();
const mockErrorToast = jest.fn();
jest.mock('../../composables/useToast', () => ({
  useToast: () => ({
    successToast: mockSuccessToast,
    errorToast: mockErrorToast,
  }),
}));

// Mock do vue-router, se necessário para HeaderCard ou outros componentes
jest.mock('vue-router', () => ({
  ...jest.requireActual('vue-router'),
  useRouter: () => ({
    push: jest.fn(),
  }),
}));


describe('MyAuthorizationsPage.vue', () => {
  let mockPendingRequests;

  beforeEach(() => {
    api.get.mockReset();
    api.post.mockReset(); // Resetar se o modal fizer chamadas
    mockSuccessToast.mockClear();
    mockErrorToast.mockClear();

    mockPendingRequests = [
      { id: 1, status: 'pending', visitor: { name: 'Visitante Pendente 1' }, request_timestamp: new Date().toISOString() },
      { id: 2, status: 'pending', visitor: { name: 'Visitante Pendente 2' }, request_timestamp: new Date().toISOString() },
    ];
  });

  const mountPage = (props) => {
    return mount(MyAuthorizationsPage, {
      props,
      global: {
        stubs: { // Stubbing para focar no teste da página
          AuthenticatedLayout: { template: '<div><slot name="page-title"/><slot/></div>' },
          HeaderCard: true,
          ResidentAccessRequestCard: false, // Não stubbar para testar a emissão de evento
          RespondAccessRequestModal: false, // Não stubbar para testar a abertura/fechamento
          NeonButton: false, // Se usado diretamente na página
        }
      }
    });
  };

  it('fetches and displays pending requests on mount', async () => {
    api.get.mockResolvedValue({ data: { data: mockPendingRequests } });
    const wrapper = mountPage();

    expect(api.get).toHaveBeenCalledWith('/access-control/access-requests/my-requests');
    await wrapper.vm.$nextTick(); // Esperar a resolução da promise
    await new Promise(resolve => setTimeout(resolve,0)); // Forçar ciclo de eventos

    const cards = wrapper.findAllComponents(ResidentAccessRequestCard);
    expect(cards.length).toBe(mockPendingRequests.length);
    expect(wrapper.text()).toContain('Visitante Pendente 1');
  });

  it('displays loading state initially', async () => {
    api.get.mockReturnValue(new Promise(() => {})); // Promise que nunca resolve
    const wrapper = mountPage();
    expect(wrapper.text()).toContain('Carregando suas solicitações...');
  });

  it('displays error message on fetch failure', async () => {
    api.get.mockRejectedValue(new Error('API Error'));
    const wrapper = mountPage();
    await wrapper.vm.$nextTick();
    await new Promise(resolve => setTimeout(resolve,0));

    expect(wrapper.text()).toContain('Falha ao carregar suas solicitações pendentes.');
    expect(mockErrorToast).toHaveBeenCalledWith('Falha ao carregar suas solicitações pendentes.');
  });

  it('opens RespondAccessRequestModal when a card emits "respond-to-request"', async () => {
    api.get.mockResolvedValue({ data: { data: [mockPendingRequests[0]] } });
    const wrapper = mountPage();
    await wrapper.vm.$nextTick();
    await new Promise(resolve => setTimeout(resolve,0));

    const card = wrapper.findComponent(ResidentAccessRequestCard);
    expect(card.exists()).toBe(true);

    // Simular emissão do evento pelo card
    card.vm.$emit('respond-to-request', mockPendingRequests[0]);
    await wrapper.vm.$nextTick();

    const modal = wrapper.findComponent(RespondAccessRequestModal);
    expect(modal.props('show')).toBe(true);
    expect(modal.props('requestDetails')).toEqual(mockPendingRequests[0]);
    expect(wrapper.vm.selectedRequestForResponse).toEqual(mockPendingRequests[0]);
  });

  it('closes modal and re-fetches requests when modal emits "response-submitted"', async () => {
    api.get.mockResolvedValueOnce({ data: { data: [mockPendingRequests[0]] } }); // Primeiro fetch
    api.get.mockResolvedValueOnce({ data: { data: [] } }); // Segundo fetch (após submissão)

    const wrapper = mountPage();
    await wrapper.vm.$nextTick(); // Esperar primeiro fetch
    await new Promise(resolve => setTimeout(resolve,0));

    // Abrir o modal
    wrapper.vm.selectedRequestForResponse = mockPendingRequests[0];
    wrapper.vm.showResponseModal = true;
    await wrapper.vm.$nextTick();

    const modal = wrapper.findComponent(RespondAccessRequestModal);
    expect(modal.props('show')).toBe(true);

    // Simular submissão do modal
    modal.vm.$emit('response-submitted');
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.showResponseModal).toBe(false);
    expect(mockSuccessToast).toHaveBeenCalledWith('Sua resposta foi enviada com sucesso!');
    expect(api.get).toHaveBeenCalledTimes(2); // Chamado no mount e após submissão
  });

  it('closes modal when modal emits "close"', async () => {
    api.get.mockResolvedValue({ data: { data: [mockPendingRequests[0]] } });
    const wrapper = mountPage();
    await wrapper.vm.$nextTick();
    await new Promise(resolve => setTimeout(resolve,0));

    wrapper.vm.selectedRequestForResponse = mockPendingRequests[0];
    wrapper.vm.showResponseModal = true;
    await wrapper.vm.$nextTick();

    const modal = wrapper.findComponent(RespondAccessRequestModal);
    modal.vm.$emit('close');
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.showResponseModal).toBe(false);
  });

});
