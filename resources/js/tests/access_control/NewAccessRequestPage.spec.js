import { mount } from '@vue/test-utils';
import NewAccessRequestPage from '../../pages/AccessControl/NewAccessRequestPage.vue';
import AuthenticatedLayout from '../../components/layouts/AuthenticatedLayout.vue';
import HeaderCard from '../../components/layouts/HeaderCard.vue';
import NeonButton from '../../components/NeonButton.vue';
import FileUpload from '../../components/FileUpload.vue';
import api from '../../utils/api';

// Mocks
jest.mock('../../utils/api', () => ({
  get: jest.fn(),
  post: jest.fn(),
}));

const mockRouter = {
  push: jest.fn(),
  back: jest.fn(),
};
jest.mock('vue-router', () => ({
  ...jest.requireActual('vue-router'),
  useRouter: () => mockRouter,
}));

const mockSuccessToast = jest.fn();
const mockErrorToast = jest.fn();
jest.mock('../../composables/useToast', () => ({
  useToast: () => ({
    successToast: mockSuccessToast,
    errorToast: mockErrorToast,
  }),
}));

describe('NewAccessRequestPage.vue', () => {
  let wrapper;
  const mockResidents = [
    { id: 1, name: 'Morador Alfa', email: 'alfa@condo.ai' },
    { id: 2, name: 'Morador Beta', email: 'beta@condo.ai' },
  ];

  beforeEach(async () => {
    api.get.mockImplementation((url) => {
      if (url.startsWith('/users')) { // Mock para fetchResidents
        return Promise.resolve({ data: { data: mockResidents } });
      }
      if (url.startsWith('/access-control/visitors/search-by-document')) {
        // Será mockado individualmente nos testes de busca
        return Promise.resolve({ data: {} });
      }
      return Promise.reject(new Error(`Unhandled GET request to ${url}`));
    });
    api.post.mockResolvedValue({ data: { message: 'Success' } }); // Mock genérico para POST

    wrapper = mount(NewAccessRequestPage, {
      global: {
        components: { AuthenticatedLayout, HeaderCard, NeonButton, FileUpload },
         stubs: {
            AuthenticatedLayout: { template: '<div><slot/></div>' }, // Stub simples para não renderizar o layout inteiro
            HeaderCard: true
        }
      },
    });
    // Aguardar o onMounted (fetchResidents)
    await wrapper.vm.$nextTick();
    await new Promise(resolve => setTimeout(resolve, 0)); // Garante que as promises resolvam
  });

  afterEach(() => {
    jest.clearAllMocks();
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
      api.get.mockResolvedValueOnce({ data: mockVisitor }); // Específico para este teste

      await wrapper.find('#visitor_document_search').setValue('123');
      // Encontrar o botão de busca dentro da seção correta
      const searchSection = wrapper.findAll('div').find(d => d.text().includes('Buscar Visitante Existente'));
      const searchButton = searchSection.findComponent(NeonButton);
      await searchButton.trigger('click');

      await wrapper.vm.$nextTick();
       await new Promise(resolve => setTimeout(resolve, 0));


      expect(api.get).toHaveBeenCalledWith('/access-control/visitors/search-by-document?document=123');
      expect(wrapper.vm.form.visitor_id).toBe(mockVisitor.id);
      expect(wrapper.vm.form.visitor_name).toBe(mockVisitor.name);
      expect(mockSuccessToast).toHaveBeenCalledWith(`Visitante "${mockVisitor.name}" encontrado.`);
    });

    it('handles visitor not found', async () => {
      api.get.mockRejectedValueOnce({ response: { status: 404 } });

      await wrapper.find('#visitor_document_search').setValue('404');
      const searchSection = wrapper.findAll('div').find(d => d.text().includes('Buscar Visitante Existente'));
      const searchButton = searchSection.findComponent(NeonButton);
      await searchButton.trigger('click');
      await wrapper.vm.$nextTick();
      await new Promise(resolve => setTimeout(resolve, 0));

      expect(wrapper.vm.searchError).toBe('Visitante não encontrado. Preencha os dados para cadastrá-lo.');
      expect(wrapper.vm.form.visitor_id).toBeNull();
    });
  });

  describe('Form Submission', () => {
    beforeEach(async () => {
        // Preencher dados mínimos para o formulário
        wrapper.vm.form.visitor_name = 'Teste Visitante';
        wrapper.vm.form.visitor_document = 'DOC999';
        wrapper.vm.form.resident_id = mockResidents[0].id;
        await wrapper.vm.$nextTick();
    });

    it('submits form data for new visitor without vehicle', async () => {
      await wrapper.find('form').trigger('submit.prevent');

      expect(api.post).toHaveBeenCalledTimes(1);
      const calledUrl = api.post.mock.calls[0][0];
      const calledData = api.post.mock.calls[0][1];

      expect(calledUrl).toBe('/access-control/access-requests');
      expect(calledData.get('visitor_name')).toBe('Teste Visitante');
      expect(calledData.get('visitor_document')).toBe('DOC999');
      expect(calledData.get('resident_id')).toBe(String(mockResidents[0].id));
      expect(calledData.get('has_vehicle')).toBe('0');
      expect(mockSuccessToast).toHaveBeenCalledWith('Solicitação de acesso criada com sucesso!');
      expect(mockRouter.push).toHaveBeenCalledWith({ name: 'AccessControlDashboard' });
    });

    it('submits form data with vehicle', async () => {
      await wrapper.find('#has_vehicle').setValue(true);
      wrapper.vm.form.vehicle_plate = 'XYZ-7890';
      wrapper.vm.form.vehicle_model = 'Moto X';
      wrapper.vm.form.vehicle_color = 'Preta';
      await wrapper.vm.$nextTick();

      await wrapper.find('form').trigger('submit.prevent');

      const calledData = api.post.mock.calls[0][1];
      expect(calledData.get('has_vehicle')).toBe('1');
      expect(calledData.get('vehicle_plate')).toBe('XYZ-7890');
    });

    it('submits form data with visitor photo', async () => {
        const mockFile = new File(['dummyphoto'], 'photo.jpg', { type: 'image/jpeg' });
        // Simular a emissão do FileUpload
        wrapper.vm.visitorPhotoFile = [mockFile]; // O FileUpload usa um array
        await wrapper.vm.$nextTick();

        await wrapper.find('form').trigger('submit.prevent');

        const calledData = api.post.mock.calls[0][1];
        expect(calledData.get('visitor_photo')).toBeInstanceOf(File);
        expect(calledData.get('visitor_photo').name).toBe('photo.jpg');
    });

    it('handles submission error from API (e.g., validation)', async () => {
      api.post.mockRejectedValueOnce({
        response: {
          data: {
            message: 'Validation Error',
            errors: { visitor_name: ['Name is too short.'] }
          }
        }
      });

      await wrapper.find('form').trigger('submit.prevent');
      expect(mockErrorToast).toHaveBeenCalledWith('Name is too short.');
    });

    it('handles submission error for protective measure active', async () => {
      api.post.mockRejectedValueOnce({
        response: {
          data: {
            message: 'ALERTA: Medida Protetiva Ativa!',
            medida_protetiva_ativa: true
          }
        }
      });

      await wrapper.find('form').trigger('submit.prevent');
      expect(wrapper.vm.protectiveMeasureAlert).toBe(true);
      expect(mockErrorToast).toHaveBeenCalledWith('ALERTA: Medida Protetiva Ativa!');
    });
  });

  it('disables submit button if protectiveMeasureAlert is true', async () => {
    wrapper.vm.protectiveMeasureAlert = true;
    await wrapper.vm.$nextTick();

    const submitButton = wrapper.findAllComponents(NeonButton).find(btn => btn.text().includes('Enviar Solicitação'));
    expect(submitButton.props('disabled')).toBe(true);
  });

});
