import { mount, flushPromises } from '@vue/test-utils';
import { vi } from 'vitest';
import RespondAccessRequestModal from '../../components/access_control/RespondAccessRequestModal.vue';
import Modal from '../../components/Modal.vue';
import NeonButton from '../../components/NeonButton.vue';
import api from '../../utils/api';
import { mockToastFns, resetMockToast } from '../mocks/useToast';

const { mockApi } = vi.hoisted(() => ({
  mockApi: {
    post: vi.fn(),
  },
}));

vi.mock('../../utils/api', () => ({
  default: mockApi,
}));

describe('RespondAccessRequestModal.vue', () => {
  let mockRequestDetails;

  beforeEach(() => {
    resetMockToast();
    api.post.mockReset();

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

  const mountModalVisible = async (requestDetails = mockRequestDetails) => {
    const wrapper = mount(RespondAccessRequestModal, {
      props: { show: false, requestDetails },
      global: { components: { Modal, NeonButton } },
    });
    await wrapper.setProps({ show: true });
    await flushPromises();
    return wrapper;
  };

  it('does not render content when show prop is false', () => {
    const wrapper = mount(RespondAccessRequestModal, {
      props: { show: false, requestDetails: mockRequestDetails },
      global: { components: { Modal, NeonButton } },
    });
    expect(wrapper.find('.space-y-6.p-2').exists()).toBe(false);
  });

  it('renders correctly and displays request details when show becomes true', async () => {
    const wrapper = await mountModalVisible();

    expect(wrapper.findComponent(Modal).props('show')).toBe(true);
    expect(wrapper.text()).toContain('Carlos Visitante');
    expect(wrapper.text()).toContain('Documento: 987654321');
    expect(wrapper.find('img[alt="Foto do Visitante"]').attributes('src')).toBe('http://foto.com/carlos.jpg');
    expect(wrapper.text()).toContain('Veículo: XYZ-0011 - Moto (Vermelha)');
    expect(wrapper.text()).toContain('Observação da Portaria: Deixou RG na portaria.');
  });

  it('displays placeholder if visitor photo is not available', async () => {
    const requestWithoutPhoto = {
      ...mockRequestDetails,
      visitor: { ...mockRequestDetails.visitor, photo_url: null },
    };
    const wrapper = await mountModalVisible(requestWithoutPhoto);

    expect(wrapper.find('img[alt="Foto do Visitante"]').exists()).toBe(false);
    expect(wrapper.find('.h-24.w-24.rounded-lg').text()).toContain('C');
  });

  it('allows resident to type an observation', async () => {
    const wrapper = await mountModalVisible();
    const textarea = wrapper.find('textarea#resident_observation');
    await textarea.setValue('Pode liberar a entrada, por favor.');
    expect(wrapper.vm.residentObservation).toBe('Pode liberar a entrada, por favor.');
  });

  describe('Action Buttons', () => {
    const testCases = [
      { action: 'authorized', buttonText: 'Autorizar' },
      { action: 'unauthorized', buttonText: 'Não Autorizar' },
      { action: 'inform_not_home', buttonText: 'Não Estou' },
      { action: 'await_at_gatehouse', buttonText: 'Aguardar Portaria' },
    ];

    testCases.forEach(({ action, buttonText }) => {
      it(`submits with action "${action}" when "${buttonText}" button is clicked`, async () => {
        api.post.mockResolvedValue({});
        const wrapper = await mountModalVisible();
        wrapper.vm.residentObservation = 'Observação de teste';

        const actionButton = wrapper.findAllComponents(NeonButton).find((btn) => btn.text().includes(buttonText));
        await actionButton.trigger('click');
        await flushPromises();

        expect(api.post).toHaveBeenCalledWith(
          `/api/access-control/access-requests/${mockRequestDetails.id}/respond`,
          { response: action, resident_observation: 'Observação de teste' },
        );
        expect(wrapper.emitted('response-submitted')).toBeTruthy();
      });
    });

    it('handles API error on submission', async () => {
      api.post.mockRejectedValue({ response: { data: { message: 'Erro na API' } } });
      const wrapper = await mountModalVisible();

      const authorizeButton = wrapper.findAllComponents(NeonButton).find((btn) => btn.text().includes('Autorizar'));
      await authorizeButton.trigger('click');
      await flushPromises();

      expect(mockToastFns.errorToast).toHaveBeenCalledWith('Erro na API');
      expect(wrapper.emitted('response-submitted')).toBeUndefined();
    });
  });

  it('emits "close" event when Modal base component emits close', async () => {
    const wrapper = await mountModalVisible();
    wrapper.findComponent(Modal).vm.$emit('close');
    expect(wrapper.emitted('close')).toBeTruthy();
  });

  it('emits "close" event when the Modal overlay close button is clicked', async () => {
    const wrapper = await mountModalVisible();
    const modal = wrapper.findComponent(Modal);
    await modal.find('button.absolute.top-4.right-4').trigger('click');
    expect(wrapper.emitted('close')).toBeTruthy();
  });
});
