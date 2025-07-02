import { mount } from '@vue/test-utils';
import SolicitacoesAcessoPainel from '../../components/access_control/SolicitacoesAcessoPainel.vue';
import AccessRequestCard from '../../components/access_control/AccessRequestCard.vue';
import NeonButton from '../../components/NeonButton.vue';
import ConfirmActionModal from '../../components/Modal.vue';
import RecordEntryModal from '../../components/access_control/RecordEntryModal.vue';
import api from '../../utils/api'; // Mock para api

// Mock do composable useToast
const mockSuccessToast = jest.fn();
const mockErrorToast = jest.fn();
jest.mock('../../composables/useToast', () => ({
  useToast: () => ({
    successToast: mockSuccessToast,
    errorToast: mockErrorToast,
  }),
}));

// Mock do api.js
jest.mock('../../utils/api', () => ({
  get: jest.fn(),
  post: jest.fn(),
}));

// Mock para componentes filhos profundos se necessário, ou use shallowMount e stub
// Por exemplo, se AccessRequestCard tiver muita lógica interna que não queremos testar aqui.
// Aqui, vamos usar `mount` para testar a interação, mas `AccessRequestCard` será renderizado.

describe('SolicitacoesAcessoPainel.vue', () => {
  let mockRequests;

  beforeEach(() => {
    // Reset mocks
    api.get.mockReset();
    api.post.mockReset();
    mockSuccessToast.mockClear();
    mockErrorToast.mockClear();

    mockRequests = [
      { id: 1, status: 'pending', visitor: { name: 'Visitante 1' }, request_timestamp: new Date().toISOString() },
      { id: 2, status: 'authorized', visitor: { name: 'Visitante 2' }, request_timestamp: new Date().toISOString() },
    ];
  });

  it('fetches and displays access requests on mount', async () => {
    api.get.mockResolvedValue({ data: { data: mockRequests } });
    const wrapper = mount(SolicitacoesAcessoPainel, {
        global: {
            stubs: { // Usar stubs para componentes complexos que não são o foco do teste
                AccessRequestCard: true, // Stubbing para não renderizar o card inteiro
                ConfirmActionModal: true,
                RecordEntryModal: true,
                NeonButton: true, // Se NeonButton for simples, pode deixar, senão, stub também
            }
        }
    });

    expect(api.get).toHaveBeenCalledWith('/access-control/access-requests/gatehouse?ativas=true&per_page=50');

    // Aguardar a resolução da Promise e atualização do DOM
    await wrapper.vm.$nextTick(); // Para Vue 2 ou Options API
    // Para Composition API com async setup, pode ser necessário esperar Promises explícitas ou usar flushPromises
    // Como estamos mockando api.get para resolver imediatamente, o estado deve atualizar.
    // No entanto, para ter certeza, vamos esperar um pouco mais com flushPromises se disponível no seu setup de teste.
    // Se não, um simples await resolve(); pode funcionar em alguns casos.
    // await new Promise(resolve => setTimeout(resolve,0)); // Forçar um ciclo de eventos

    // Após o setup assíncrono e chamadas de API, verificar se os cards (ou stubs) foram renderizados
    // O wrapper.findAllComponents pode precisar de tempo para atualizar após chamadas assíncronas no setup
    // Se o componente AccessRequestCard fosse renderizado, poderíamos fazer:
    // expect(wrapper.findAllComponents(AccessRequestCard).length).toBe(mockRequests.length);
    // Como estamos stubbing, vamos verificar o estado interno ou um elemento que indica a presença dos dados
    expect(wrapper.vm.accessRequests.length).toBe(mockRequests.length);
  });

  it('displays loading state initially', async () => {
    api.get.mockReturnValue(new Promise(() => {})); // Promise que nunca resolve para simular loading
    const wrapper = mount(SolicitacoesAcessoPainel, {
        global: { stubs: { AccessRequestCard: true, ConfirmActionModal: true, RecordEntryModal: true, NeonButton: true } }
    });
    expect(wrapper.text()).toContain('Carregando solicitações...');
  });

  it('displays error message on fetch failure', async () => {
    api.get.mockRejectedValue(new Error('API Error'));
    const wrapper = mount(SolicitacoesAcessoPainel, {
        global: { stubs: { AccessRequestCard: true, ConfirmActionModal: true, RecordEntryModal: true, NeonButton: true } }
    });

    await wrapper.vm.$nextTick();
    await wrapper.vm.$nextTick(); // Esperar mais um tick para a propagação do erro

    expect(wrapper.text()).toContain('Falha ao carregar as solicitações. Tente novamente.');
    expect(mockErrorToast).toHaveBeenCalledWith('Falha ao carregar as solicitações. Tente novamente.');
  });

  it('displays no requests message when list is empty', async () => {
    api.get.mockResolvedValue({ data: { data: [] } });
    const wrapper = mount(SolicitacoesAcessoPainel, {
        global: { stubs: { AccessRequestCard: true, ConfirmActionModal: true, RecordEntryModal: true, NeonButton: true } }
    });
    await wrapper.vm.$nextTick();
    expect(wrapper.text()).toContain('Nenhuma solicitação de acesso encontrada.');
  });

  // Testes de interação com modais (exemplo para cancelamento)
  // Estes testes são mais complexos pois envolvem interação com componentes filhos (cards e modais)
  // Seria melhor testar os modais e cards em isolamento e aqui focar na lógica do painel.

  // Teste simplificado para a lógica de abrir o modal de cancelamento
  it('sets up requestToCancel when handleCancelRequest is called from a card', async () => {
    api.get.mockResolvedValue({ data: { data: mockRequests } });
    const wrapper = mount(SolicitacoesAcessoPainel, {
        // Não vamos stubbar AccessRequestCard aqui para poder emitir o evento
        global: {
            stubs: { ConfirmActionModal: true, RecordEntryModal: true } // Stub apenas os modais
        }
    });
    await wrapper.vm.$nextTick(); // Esperar o fetch inicial

    const firstCard = wrapper.findComponent(AccessRequestCard); // Pega o primeiro card
    if (firstCard.exists()) {
        firstCard.vm.$emit('cancel-request', mockRequests[0]); // Simula emissão do evento do card
        await wrapper.vm.$nextTick();
        expect(wrapper.vm.requestToCancel).toEqual(mockRequests[0]);
        expect(wrapper.vm.showConfirmCancelModal).toBe(true);
    } else {
        throw new Error("AccessRequestCard não encontrado para o teste de cancelamento.");
    }
  });

  it('calls cancel API and re-fetches on confirmCancelRequest', async () => {
    api.get.mockResolvedValue({ data: { data: mockRequests } }); // Fetch inicial
    api.post.mockResolvedValue({}); // Mock para a chamada de cancelamento

    const wrapper = mount(SolicitacoesAcessoPainel, {
        global: { stubs: { AccessRequestCard: true, RecordEntryModal: true } } // Stub RecordEntryModal
    });
    await wrapper.vm.$nextTick();

    wrapper.vm.requestToCancel = mockRequests[0]; // Simula que o modal foi aberto com este request
    wrapper.vm.showConfirmCancelModal = true;
    await wrapper.vm.$nextTick();

    const confirmModal = wrapper.findComponent(ConfirmActionModal);
    await confirmModal.vm.$emit('confirm'); // Simula a confirmação do modal

    expect(api.post).toHaveBeenCalledWith(`/api/access-control/access-requests/${mockRequests[0].id}/cancel`, expect.anything());
    expect(mockSuccessToast).toHaveBeenCalledWith('Solicitação cancelada com sucesso!');
    expect(api.get).toHaveBeenCalledTimes(2); // Uma no mount, outra após cancelamento
    expect(wrapper.vm.showConfirmCancelModal).toBe(false);
  });

});
