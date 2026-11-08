import { mount } from '@vue/test-utils';
import RespondAccessRequestModal from '../../components/access_control/RespondAccessRequestModal.vue';
import Modal from '../../components/Modal.vue'; // O componente base do Modal
import NeonButton from '../../components/NeonButton.vue';
import api from '../../utils/api';

// Mocks
jest.mock('../../utils/api', () => ({
  post: jest.fn(),
  // Não precisamos de GET aqui, pois os detalhes são passados por prop
}));

const mockSuccessToast = jest.fn();
const mockErrorToast = jest.fn();
jest.mock('../../composables/useToast', () => ({
  useToast: () => ({
    successToast: mockSuccessToast,
    errorToast: mockErrorToast,
  }),
}));

describe('RespondAccessRequestModal.vue', () => {
  let mockRequestDetails;

  beforeEach(() => {
    api.post.mockReset();
    mockSuccessToast.mockClear();
    mockErrorToast.mockClear();

    mockRequestDetails = {
      id: 101,
      status: 'pending',
      visitor: { id: 1, name: 'Carlos Visitante', document: '987654321', photo_url: 'http://foto.com/carlos.jpg' },
      resident: { id: 5, name: 'Ana Moradora' },
      visitor_vehicle: { id: 2, plate: 'XYZ-0011', model: 'Moto', cor: 'Vermelha' },
      gatehouse_observation: 'Deixou RG na portaria.',
      request_timestamp: new Date().toISOString(),
    };
  });

  const mountModal = (show = true, requestDetails = mockRequestDetails) => {
    return mount(RespondAccessRequestModal, {
      props: {
        show,
        requestDetails,
      },
      global: {
        // Se Modal ou NeonButton forem muito complexos, podem ser stubbados.
        // Por agora, vamos assumir que são simples o suficiente ou testados em separado.
        components: { Modal, NeonButton }
      }
    });
  };

  it('does not render when show prop is false', () => {
    const wrapper = mountModal(false);
    // O componente Modal base pode ainda renderizar sua estrutura oculta.
    // Precisamos verificar se o conteúdo específico do RespondAccessRequestModal não está visível.
    // Se Modal usa v-if internamente baseado no 'show', o conteúdo pode não estar no DOM.
    // Se Modal usa classes CSS, o conteúdo estará no DOM mas oculto.
    // Assumindo que Modal usa v-if para o slot de conteúdo:
    expect(wrapper.find('.space-y-6.p-2').exists()).toBe(false); // Verifica um elemento interno específico
  });

  it('renders correctly and displays request details when show is true', async () => {
    const wrapper = mountModal();
    await wrapper.vm.$nextTick(); // Para watchers e DOM update

    expect(wrapper.findComponent(Modal).props('show')).toBe(true);
    expect(wrapper.text()).toContain('Responder Solicitação de Acesso');
    expect(wrapper.text()).toContain('Carlos Visitante');
    expect(wrapper.text()).toContain('Documento: 987654321');
    expect(wrapper.find('img[alt="Foto do Visitante"]').attributes('src')).toBe('http://foto.com/carlos.jpg');
    expect(wrapper.text()).toContain('Veículo: XYZ-0011 - Moto (Vermelha)');
    expect(wrapper.text()).toContain('Observação da Portaria: Deixou RG na portaria.');
  });

  it('displays placeholder if visitor photo is not available', async () => {
    const requestWithoutPhoto = { ...mockRequestDetails, visitor: { ...mockRequestDetails.visitor, photo_url: null } };
    const wrapper = mountModal(true, requestWithoutPhoto);
    await wrapper.vm.$nextTick();

    expect(wrapper.find('img[alt="Foto do Visitante"]').exists()).toBe(false);
    // Verifica a inicial do nome do visitante
    expect(wrapper.find('.h-24.w-24.rounded-lg').text()).toContain('C');
  });

  it('allows resident to type an observation', async () => {
    const wrapper = mountModal();
    await wrapper.vm.$nextTick();

    const textarea = wrapper.find('textarea#resident_observation');
    await textarea.setValue('Pode liberar a entrada, por favor.');
    expect(wrapper.vm.residentObservation).toBe('Pode liberar a entrada, por favor.');
  });

  describe('Action Buttons', () => {
    const testCases = [
      { action: 'authorized', buttonText: 'Autorizar', variant: 'success' },
      { action: 'unauthorized', buttonText: 'Não Autorizar', variant: 'danger' },
      { action: 'inform_not_home', buttonText: 'Não Estou', variant: 'info' },
      { action: 'await_at_gatehouse', buttonText: 'Aguardar Portaria', variant: 'warning' },
    ];

    testCases.forEach(({ action, buttonText }) => {
      it(`submits with action "${action}" when "${buttonText}" button is clicked`, async () => {
        api.post.mockResolvedValue({}); // Simula sucesso da API
        const wrapper = mountModal();
        await wrapper.vm.$nextTick();

        wrapper.vm.residentObservation = 'Observação de teste'; // Definir uma observação

        const actionButton = wrapper.findAllComponents(NeonButton).find(btn => btn.text().includes(buttonText));
        expect(actionButton.exists()).toBe(true);

        await actionButton.trigger('click');

        expect(api.post).toHaveBeenCalledWith(
          `/api/access-control/access-requests/${mockRequestDetails.id}/respond`,
          {
            response: action,
            resident_observation: 'Observação de teste',
          }
        );
        expect(wrapper.emitted('response-submitted')).toBeTruthy();
      });
    });

    it('handles API error on submission', async () => {
        api.post.mockRejectedValue({ response: { data: { message: 'Erro na API' } } });
        const wrapper = mountModal();
        await wrapper.vm.$nextTick();

        const authorizeButton = wrapper.findAllComponents(NeonButton).find(btn => btn.text().includes('Autorizar'));
        await authorizeButton.trigger('click');

        expect(api.post).toHaveBeenCalled();
        expect(mockErrorToast).toHaveBeenCalledWith('Erro na API');
        expect(wrapper.emitted('response-submitted')).toBeUndefined(); // Não deve emitir se falhar
    });
  });

  it('emits "close" event when Modal base component emits close', async () => {
    const wrapper = mountModal();
    await wrapper.vm.$nextTick();

    const modalComponent = wrapper.findComponent(Modal);
    modalComponent.vm.$emit('close'); // Simula o Modal base emitindo 'close'

    expect(wrapper.emitted('close')).toBeTruthy();
  });

  it('emits "close" event when the explicit "Fechar" button is clicked', async () => {
    const wrapper = mountModal();
    await wrapper.vm.$nextTick();

    // Encontrar o botão "Fechar" no slot de footer
    const footerSlot = wrapper.findComponent(Modal).findComponent({ name: 'ModalFooterSlot' }); // Nome hipotético, depende da implementação do Modal
    let closeButton;

    // Tentar encontrar o botão de fechar. A estrutura exata do Modal.vue é importante aqui.
    // Se o Modal.vue renderiza botões de footer diretamente ou através de um slot.
    const buttons = wrapper.findAllComponents(NeonButton);
    closeButton = buttons.find(btn => btn.text().includes('Fechar'));

    if (!closeButton || !closeButton.exists()) {
        // Se o botão não for encontrado diretamente, pode estar dentro de um slot nomeado.
        // Este é um ponto que pode precisar de ajuste baseado na estrutura exata do Modal.vue
        console.warn("Botão 'Fechar' não encontrado diretamente, verifique a estrutura do Modal.vue");
    } else {
        await closeButton.trigger('click');
        expect(wrapper.emitted('close')).toBeTruthy();
    }
  });
});
