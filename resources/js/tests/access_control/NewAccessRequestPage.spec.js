import { mount, flushPromises } from '@vue/test-utils';
import { vi } from 'vitest';
import NewAccessRequestPage from '../fixtures/NewAccessRequestPage.fixture.vue';
import NeonButton from '../../components/NeonButton.vue';
import api from '../../utils/api';
import { mockToastFns, resetMockToast } from '../mocks/useToast';

const { mockApi } = vi.hoisted(() => ({
  mockApi: {
    get: vi.fn(),
    post: vi.fn(),
  },
}));

const { mockRouter } = vi.hoisted(() => ({
  mockRouter: {
    push: vi.fn(),
    back: vi.fn(),
  },
}));

vi.mock('../../utils/api', () => ({
  default: mockApi,
}));

vi.mock('vue-router', async (importOriginal) => {
  const actual = await importOriginal();
  return {
    ...actual,
    useRouter: () => mockRouter,
  };
});

const FileUploadStub = {
  name: 'FileUpload',
  props: ['modelValue'],
  emits: ['update:modelValue'],
  template: '<input data-testid="visitor-photo-upload" type="file" @change="onChange" />',
  methods: {
    onChange(event) {
      this.$emit('update:modelValue', Array.from(event.target.files || []));
    },
  },
};

describe('NewAccessRequestPage.vue', () => {
  let wrapper;
  const mockResidents = [
    { id: 1, name: 'Morador Alfa', email: 'alfa@condo.ai' },
    { id: 2, name: 'Morador Beta', email: 'beta@condo.ai' },
  ];

  beforeEach(async () => {
    resetMockToast();
    mockRouter.push.mockClear();

    api.get.mockImplementation((url) => {
      if (url.startsWith('/users')) {
        return Promise.resolve({ data: { data: mockResidents } });
      }
      if (url.includes('/access-control/visitors/search-by-document') || url.includes('/api/access-control/visitors/search-by-document')) {
        return Promise.resolve({ data: {} });
      }
      if (url.startsWith('/access-control/check-protective-measure')) {
        return Promise.resolve({ data: { has_protective_measure: false } });
      }
      return Promise.reject(new Error(`Unhandled GET request to ${url}`));
    });
    api.post.mockResolvedValue({ data: { message: 'Success' } });

    wrapper = mount(NewAccessRequestPage, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<div><slot/></div>' },
          HeaderCard: true,
          FileUpload: FileUploadStub,
        },
      },
    });
    await flushPromises();
  });

  it('renders the form correctly', () => {
    expect(wrapper.find('form').exists()).toBe(true);
    expect(wrapper.text()).toContain('Buscar Visitante Existente');
    expect(wrapper.text()).toContain('Dados do Visitante');
    expect(wrapper.text()).toContain('Dados do Veículo (Opcional)');
    expect(wrapper.text()).toContain('Destino e Observações');
  });

  it('fetches residents on mount', () => {
    expect(api.get).toHaveBeenCalledWith('/users?profile_type=resident&per_page=1000');
    expect(wrapper.vm.residents.length).toBe(mockResidents.length);
  });

  describe('Visitor Search', () => {
    it('searches for visitor when document is entered and search button is clicked', async () => {
      const mockVisitor = { id: 10, name: 'Visitante Encontrado', document: '123', phone: '111', observations: 'Obs' };
      api.get.mockImplementation((url) => {
        if (url.startsWith('/users')) return Promise.resolve({ data: { data: mockResidents } });
        if (url.includes('search-by-document')) return Promise.resolve({ data: mockVisitor });
        if (url.startsWith('/access-control/check-protective-measure')) {
          return Promise.resolve({ data: { has_protective_measure: false } });
        }
        return Promise.reject(new Error(`Unhandled GET: ${url}`));
      });

      await wrapper.find('#visitor_document_search').setValue('123');
      const searchSection = wrapper.findAll('div').find((d) => d.text().includes('Buscar Visitante Existente'));
      await searchSection.findComponent(NeonButton).trigger('click');
      await flushPromises();

      expect(api.get).toHaveBeenCalledWith('/api/access-control/visitors/search-by-document?document=123');
      expect(wrapper.vm.form.visitor_id).toBe(mockVisitor.id);
      expect(wrapper.vm.form.visitor_name).toBe(mockVisitor.name);
      expect(mockToastFns.successToast).toHaveBeenCalledWith(`Visitante "${mockVisitor.name}" encontrado.`);
    });

    it('handles visitor not found', async () => {
      api.get.mockImplementation((url) => {
        if (url.startsWith('/users')) return Promise.resolve({ data: { data: mockResidents } });
        if (url.includes('search-by-document')) {
          return Promise.reject({ response: { status: 404 } });
        }
        return Promise.reject(new Error(`Unhandled GET: ${url}`));
      });

      await wrapper.find('#visitor_document_search').setValue('404');
      const searchSection = wrapper.findAll('div').find((d) => d.text().includes('Buscar Visitante Existente'));
      await searchSection.findComponent(NeonButton).trigger('click');
      await flushPromises();

      expect(wrapper.vm.searchError).toBe('Visitante não encontrado. Preencha os dados para cadastrá-lo.');
      expect(wrapper.vm.form.visitor_id).toBeNull();
    });
  });

  describe('Form Submission', () => {
    beforeEach(async () => {
      wrapper.vm.form.visitor_name = 'Teste Visitante';
      wrapper.vm.form.visitor_document = 'DOC999';
      wrapper.vm.form.resident_id = mockResidents[0].id;
      await wrapper.vm.$nextTick();
    });

    it('submits form data for new visitor without vehicle', async () => {
      await wrapper.find('form').trigger('submit.prevent');
      await flushPromises();

      expect(api.post).toHaveBeenCalledTimes(1);
      const calledUrl = api.post.mock.calls[0][0];
      const calledData = api.post.mock.calls[0][1];

      expect(calledUrl).toBe('/access-control/access-requests');
      expect(calledData.get('visitor_name')).toBe('Teste Visitante');
      expect(calledData.get('visitor_document')).toBe('DOC999');
      expect(calledData.get('resident_id')).toBe(String(mockResidents[0].id));
      expect(calledData.get('has_vehicle')).toBe('0');
      expect(mockToastFns.successToast).toHaveBeenCalledWith('Solicitação de acesso criada com sucesso!');
      expect(mockRouter.push).toHaveBeenCalledWith({ name: 'AccessControlDashboard' });
    });

    it('submits form data with vehicle', async () => {
      Object.assign(wrapper.vm.form, {
        has_vehicle: true,
        vehicle_plate: 'XYZ-7890',
        vehicle_model: 'Moto X',
        vehicle_color: 'Preta',
      });
      await wrapper.vm.$nextTick();

      await wrapper.vm.submitNewAccessRequest();
      await flushPromises();

      const calledData = api.post.mock.calls.at(-1)[1];
      expect(calledData.get('has_vehicle')).toBe('1');
      expect(calledData.get('vehicle_plate')).toBe('XYZ-7890');
    });

    it('submits form data with visitor photo', async () => {
      const mockFile = new File(['dummyphoto'], 'photo.jpg', { type: 'image/jpeg' });
      const input = wrapper.find('[data-testid="visitor-photo-upload"]');
      Object.defineProperty(input.element, 'files', { value: [mockFile] });
      await input.trigger('change');
      await wrapper.vm.$nextTick();

      await wrapper.vm.submitNewAccessRequest();
      await flushPromises();

      const calledData = api.post.mock.calls.at(-1)[1];
      expect(calledData.get('visitor_photo')).toBeInstanceOf(File);
      expect(calledData.get('visitor_photo').name).toBe('photo.jpg');
    });

    it('handles submission error from API (e.g., validation)', async () => {
      api.post.mockRejectedValueOnce({
        response: {
          data: {
            message: 'Validation Error',
            errors: { visitor_name: ['Name is too short.'] },
          },
        },
      });

      await wrapper.find('form').trigger('submit.prevent');
      await flushPromises();

      expect(mockToastFns.errorToast).toHaveBeenCalledWith('Name is too short.');
    });

    it('handles submission error for protective measure active', async () => {
      api.post.mockRejectedValueOnce({
        response: {
          data: {
            message: 'ALERTA: Medida Protetiva Ativa!',
            medida_protetiva_ativa: true,
          },
        },
      });

      await wrapper.find('form').trigger('submit.prevent');
      await flushPromises();

      expect(wrapper.vm.protectiveMeasureAlert).toBe(true);
      expect(mockToastFns.errorToast).toHaveBeenCalledWith('ALERTA: Medida Protetiva Ativa!');
    });
  });

  it('disables submit button if protectiveMeasureAlert is true', async () => {
    wrapper.vm.protectiveMeasureAlert = true;
    await wrapper.vm.$nextTick();

    const submitButton = wrapper.findAllComponents(NeonButton).find((btn) => btn.text().includes('Enviar Solicitação'));
    expect(submitButton.props('disabled')).toBe(true);
  });
});
