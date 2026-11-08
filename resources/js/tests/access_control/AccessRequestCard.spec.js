import { mount } from '@vue/test-utils';
import { vi } from 'vitest';
import AccessRequestCard from '../../components/access_control/AccessRequestCard.vue';
import NeonButton from '../../components/NeonButton.vue';

// Mock date-fns para evitar problemas com timezones e formatação em testes
vi.mock('date-fns', async (importOriginal) => {
  const actual = await importOriginal();
  return {
    ...actual,
    formatDistanceToNowStrict: vi.fn(() => 'há alguns segundos'),
  };
});
jest.mock('date-fns/locale', () => ({
  ptBR: {}, // Mock do locale ptBR
}));


const mockRouter = {
  push: vi.fn(),
};

vi.mock('vue-router', () => ({
  useRouter: () => mockRouter,
}));

// Mock do composable useToast
const mockSuccessToast = jest.fn();
const mockErrorToast = jest.fn();
jest.mock('../../composables/useToast', () => ({
  useToast: () => ({
    successToast: mockSuccessToast,
    errorToast: mockErrorToast,
  }),
}));


describe('AccessRequestCard.vue', () => {
  let mockRequest;

  beforeEach(() => {
    mockRequest = {
      id: 1,
      status: 'pending',
      request_timestamp: new Date().toISOString(),
      visitor: { name: 'João Visitante', document: '123.456.789-00', photo_url: 'http://example.com/photo.jpg' },
      resident: { name: 'Maria Moradora' },
      visitor_vehicle: null,
      gatehouse_observation: 'Nenhuma',
      resident_observation: null,
      parking_spot: null,
      actual_entry_timestamp: null,
      actual_exit_timestamp: null,
    };
  });

  const mountComponent = (props) => {
    return mount(AccessRequestCard, {
      props: {
        request: { ...mockRequest, ...props },
      },
    });
  };

  const findButtonByText = (wrapper, label) =>
    wrapper.findAll('button').find((button) => button.text().includes(label));

  it('renders visitor information correctly', () => {
    const wrapper = mountComponent();
    expect(wrapper.text()).toContain('João Visitante');
    expect(wrapper.text()).toContain('123.456.789-00');
    expect(wrapper.find('img[alt="Foto do Visitante"]').exists()).toBe(true);
  });

  it('renders placeholder if no visitor photo_url', () => {
    const wrapper = mountComponent({ visitor: { ...mockRequest.visitor, photo_url: null } });
    expect(wrapper.find('img[alt="Foto do Visitante"]').exists()).toBe(false);
    expect(wrapper.text()).toContain('J'); // Inicial do nome
  });

  it('renders vehicle information if present', () => {
    const vehicle = { plate: 'ABC-1234', model: 'Fusca', cor: 'Azul' };
    const wrapper = mountComponent({ visitor_vehicle: vehicle });
    expect(wrapper.text()).toContain('Veículo: ABC-1234 - Fusca (Azul)');
  });

  it('does not render vehicle information if not present', () => {
    const wrapper = mountComponent({ visitor_vehicle: null });
    expect(wrapper.text()).not.toContain('Veículo:');
  });

  it('displays correct status text and style for "pending"', () => {
    const wrapper = mountComponent({ status: 'pending' });
    expect(wrapper.text()).toContain('Pendente');
    // Exemplo de verificação de classe (pode ser mais robusto)
    expect(wrapper.find('.border-orange-500\\/50').exists()).toBe(true);
  });

  it('displays correct status text and style for "authorized"', () => {
    const wrapper = mountComponent({ status: 'authorized' });
    expect(wrapper.text()).toContain('Autorizado');
    expect(wrapper.find('.border-green-500\\/50').exists()).toBe(true);
  });


  // Testes para botões condicionais
  describe('Action Buttons', () => {
    it('shows "Registrar Entrada" button for "authorized" status', () => {
      const wrapper = mountComponent({ status: 'authorized', actual_entry_timestamp: null });
      expect(findButtonByText(wrapper, 'Registrar Entrada')).toBeTruthy();
    });

    it('does not show "Registrar Entrada" if entry already recorded', () => {
      const wrapper = mountComponent({ status: 'entry_recorded', actual_entry_timestamp: new Date().toISOString() });
      expect(findButtonByText(wrapper, 'Registrar Entrada')).toBeFalsy();
    });

    it('shows "Registrar Saída" button for "entry_recorded" status', () => {
      const wrapper = mountComponent({ status: 'entry_recorded', actual_entry_timestamp: new Date().toISOString(), actual_exit_timestamp: null });
      expect(findButtonByText(wrapper, 'Registrar Saída')).toBeTruthy();
    });

    it('shows "Cancelar" button for "pending" status', () => {
      const wrapper = mountComponent({ status: 'pending' });
      expect(findButtonByText(wrapper, 'Cancelar')).toBeTruthy();
    });

    it('shows "Detalhes" button for "cancelled" status', () => {
        const wrapper = mountComponent({ status: 'cancelled' });
        expect(findButtonByText(wrapper, 'Detalhes')).toBeTruthy();
        expect(findButtonByText(wrapper, 'Registrar Entrada')).toBeFalsy();
        expect(findButtonByText(wrapper, 'Registrar Saída')).toBeFalsy();
        expect(findButtonByText(wrapper, 'Cancelar')).toBeFalsy();
    });

    it('emits "record-entry" event when record entry button is clicked', async () => {
      const wrapper = mountComponent({ status: 'authorized' });
      const recordEntryButton = findButtonByText(wrapper, 'Registrar Entrada');
      await recordEntryButton.trigger('click');
      expect(wrapper.emitted('record-entry')).toBeTruthy();
      expect(wrapper.emitted('record-entry')[0][0]).toEqual(
        expect.objectContaining({ id: mockRequest.id, status: 'authorized' }),
      );
    });

    it('emits "cancel-request" event when cancel button is clicked', async () => {
      const wrapper = mountComponent({ status: 'pending' });
      const cancelButton = findButtonByText(wrapper, 'Cancelar');
      await cancelButton.trigger('click');
      expect(wrapper.emitted('cancel-request')).toBeTruthy();
      expect(wrapper.emitted('cancel-request')[0][0]).toEqual(mockRequest);
    });

    it('calls router.push when "Detalhes" button is clicked', async () => {
        const wrapper = mountComponent({ status: 'cancelled' });
        const detailsButton = findButtonByText(wrapper, 'Detalhes');
        await detailsButton.trigger('click');
        expect(mockRouter.push).toHaveBeenCalledWith({ name: 'AccessRequestDetails', params: { id: mockRequest.id } });
    });
  });
});
