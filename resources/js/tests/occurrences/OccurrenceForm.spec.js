import { shallowMount, mount, flushPromises } from '@vue/test-utils';
import OccurrenceForm from '../../components/occurrences/OccurrenceForm.vue';
import api from '../../utils/api';
import { useToast } from '../../composables/useToast';
import { vi } from 'vitest';

// Mock vue-router
const mockRouter = {
  push: vi.fn(),
};
const mockRoute = {
  params: {}, // Default to create mode
};

vi.mock('vue-router', () => ({
  useRouter: () => mockRouter,
  useRoute: () => mockRoute,
  RouterLink: { template: '<a><slot/></a>' }
}));

// Mock useToast
vi.mock('../../composables/useToast', () => ({
  useToast: () => ({
    success: vi.fn(),
    error: vi.fn(),
  }),
}));

// Mock api
vi.mock('../../utils/api');

describe('OccurrenceForm.vue', () => {
  let wrapper;
  const toast = useToast(); // Get mocked toast instance

  const mountComponent = (isEditing = false, occurrenceId = null) => {
    mockRoute.params = isEditing ? { id: occurrenceId || '1' } : {};
    return mount(OccurrenceForm, { // Use mount for better interaction testing
      global: {
        stubs: {
          RouterLink: true, // Stub RouterLink if not testing its navigation directly
        },
      },
    });
  };

  beforeEach(() => {
    vi.clearAllMocks();
    api.get.mockResolvedValue({ data: { id: '1', title: 'Old Title', description: 'Old Desc', priority: 'low', status: 'open', photos: [] } });
  });

  // Testes para o modo de Criação
  describe('Create Mode', () => {
    beforeEach(() => {
      wrapper = mountComponent(false);
    });

    it('renders the form in create mode', () => {
      expect(wrapper.find('h1').text()).toBe('Nova Ocorrência');
      expect(wrapper.find('button[type="submit"]').text()).toBe('Criar Ocorrência');
      // Status field should not be present in create mode by default in this component's logic
      expect(wrapper.find('select#status').exists()).toBe(false);
    });

    it('initializes form with default priority and empty fields', () => {
      expect(wrapper.vm.form.title).toBe('');
      expect(wrapper.vm.form.description).toBe('');
      expect(wrapper.vm.form.priority).toBe('medium'); // Default set in onMounted
      expect(wrapper.vm.form.photos).toEqual([]);
      expect(wrapper.vm.photoPreviews).toEqual([]);
    });

    it('handles file upload and preview correctly', async () => {
      const fileInput = wrapper.find('input[type="file"]');
      const mockFile = new File(['dummy content'], 'test.png', { type: 'image/png' });

      // Simular a seleção do arquivo
      // Como o input[type=file] é difícil de manipular diretamente, vamos chamar o método handler
      // e simular o evento que o handler esperaria.
      const event = { target: { files: [mockFile] } };
      await wrapper.vm.handleFileUpload(event); // Chama o método diretamente

      expect(wrapper.vm.form.photos.length).toBe(1);
      expect(wrapper.vm.form.photos[0]).toBe(mockFile);
      expect(wrapper.vm.photoPreviews.length).toBe(1);
      expect(wrapper.vm.photoPreviews[0].file).toBe(mockFile);
      expect(wrapper.vm.photoPreviews[0].url).toContain('blob:'); // URL.createObjectURL
    });

    it('removes a photo preview and from form data', async () => {
        const mockFile = new File(['dummy content'], 'photo.jpg', { type: 'image/jpeg' });
        // Adiciona uma foto para poder remover
        await wrapper.vm.handleFileUpload({ target: { files: [mockFile] } });
        expect(wrapper.vm.photoPreviews.length).toBe(1);
        expect(wrapper.vm.form.photos.length).toBe(1);

        await wrapper.find('.relative button').trigger('click'); // Botão de remover foto

        expect(wrapper.vm.photoPreviews.length).toBe(0);
        expect(wrapper.vm.form.photos.length).toBe(0);
    });


    it('submits new occurrence data correctly (without photos)', async () => {
      api.post.mockResolvedValue({ data: { id: '123', title: 'New Test Occurrence' } });

      await wrapper.find('input#title').setValue('New Test Occurrence');
      await wrapper.find('textarea#description').setValue('Detailed description.');
      await wrapper.find('select#priority').setValue('high');

      await wrapper.find('form').trigger('submit.prevent');
      await flushPromises();

      expect(api.post).toHaveBeenCalledTimes(1);
      const formData = api.post.mock.calls[0][1]; // FormData é o segundo argumento
      expect(formData.get('title')).toBe('New Test Occurrence');
      expect(formData.get('description')).toBe('Detailed description.');
      expect(formData.get('priority')).toBe('high');
      expect(formData.has('photos[0]')).toBe(false); // Sem fotos neste teste

      expect(toast.success).toHaveBeenCalledWith('Ocorrência criada com sucesso!');
      expect(mockRouter.push).toHaveBeenCalledWith({ name: 'OccurrenceShow', params: { id: '123' } });
    });

    it('submits new occurrence data correctly (with photos and descriptions)', async () => {
        api.post.mockResolvedValue({ data: { id: '124', title: 'Occurrence With Photo' } });

        const mockFile = new File(['dummy content'], 'test.png', { type: 'image/png' });
        await wrapper.vm.handleFileUpload({ target: { files: [mockFile] } });

        // Adiciona descrição à foto preview
        wrapper.vm.photoPreviews[0].description = 'Descrição da foto de teste';

        await wrapper.find('input#title').setValue('Occurrence With Photo');
        await wrapper.find('textarea#description').setValue('Description for photo occurrence.');
        await wrapper.find('select#priority').setValue('low');

        await wrapper.find('form').trigger('submit.prevent');
        await flushPromises();

        expect(api.post).toHaveBeenCalledTimes(1);
        const formData = api.post.mock.calls[0][1];
        expect(formData.get('title')).toBe('Occurrence With Photo');
        expect(formData.get('photos[0]').name).toBe('test.png');
        expect(formData.get('photo_descriptions[0]')).toBe('Descrição da foto de teste');

        expect(toast.success).toHaveBeenCalledWith('Ocorrência criada com sucesso!');
        expect(mockRouter.push).toHaveBeenCalledWith({ name: 'OccurrenceShow', params: { id: '124' } });
    });


    it('shows validation errors on submit if fields are invalid', async () => {
      api.post.mockRejectedValue({ response: { status: 422, data: { errors: { title: ['Title is required'] } } } });

      await wrapper.find('form').trigger('submit.prevent');
      await flushPromises();

      expect(api.post).toHaveBeenCalledTimes(1);
      expect(wrapper.vm.errors.title).toEqual(['Title is required']);
      expect(wrapper.find('input#title + p.text-red-500').text()).toBe('Title is required');
      expect(toast.error).toHaveBeenCalled();
    });
  });

  // Testes para o modo de Edição
  describe('Edit Mode', () => {
    const occurrenceData = {
      id: '1',
      title: 'Existing Occurrence',
      description: 'This needs an update.',
      priority: 'medium',
      status: 'open',
      photos: [] // Fotos são gerenciadas separadamente na edição neste form
    };

    beforeEach(async () => {
      api.get.mockResolvedValue({ data: occurrenceData });
      wrapper = mountComponent(true, '1');
      await flushPromises(); // Esperar o fetchOccurrence no onMounted
    });

    it('renders the form in edit mode and loads data', () => {
      expect(wrapper.find('h1').text()).toBe('Editar Ocorrência');
      expect(wrapper.find('button[type="submit"]').text()).toBe('Atualizar Ocorrência');
      expect(api.get).toHaveBeenCalledWith('/occurrences/1');
      expect(wrapper.vm.form.title).toBe(occurrenceData.title);
      expect(wrapper.vm.form.description).toBe(occurrenceData.description);
      expect(wrapper.vm.form.priority).toBe(occurrenceData.priority);
      expect(wrapper.vm.form.status).toBe(occurrenceData.status);
      // Upload de fotos não deve estar visível na edição, conforme lógica do componente
      expect(wrapper.find('input[type="file"]').exists()).toBe(false);
    });

    it('submits updated occurrence data correctly', async () => {
      api.post.mockResolvedValue({ data: { ...occurrenceData, title: 'Updated Occurrence Title' } }); // api.post por causa do _method: PUT

      await wrapper.find('input#title').setValue('Updated Occurrence Title');
      await wrapper.find('select#status').setValue('in_progress');

      await wrapper.find('form').trigger('submit.prevent');
      await flushPromises();

      expect(api.post).toHaveBeenCalledTimes(1); // api.post com _method: 'PUT'
      const formData = api.post.mock.calls[0][1];
      expect(formData.get('title')).toBe('Updated Occurrence Title');
      expect(formData.get('status')).toBe('in_progress');
      expect(formData.get('_method')).toBe('PUT');

      expect(toast.success).toHaveBeenCalledWith('Ocorrência atualizada com sucesso!');
      expect(mockRouter.push).toHaveBeenCalledWith({ name: 'OccurrenceShow', params: { id: occurrenceData.id } });
    });

    it('handles error when fetching occurrence data for editing', async () => {
        api.get.mockRejectedValueOnce({ response: { data: { message: "Fetch error" } } });
        wrapper = mountComponent(true, '2'); // Montar com novo ID para disparar fetch
        await flushPromises();

        expect(wrapper.vm.submitError).toBe("Não foi possível carregar os dados da ocorrência para edição.");
        expect(toast.error).toHaveBeenCalledWith("Erro ao carregar dados da ocorrência.");
    });
  });

  it('handles generic submit error', async () => {
    api.post.mockRejectedValue({ response: { data: { message: 'Server error' } } });
    wrapper = mountComponent(false); // Modo criação

    await wrapper.find('input#title').setValue('Error Test');
    await wrapper.find('textarea#description').setValue('Desc');
    await wrapper.find('select#priority').setValue('low');
    await wrapper.find('form').trigger('submit.prevent');
    await flushPromises();

    expect(wrapper.vm.submitError).toBe('Server error');
    expect(toast.error).toHaveBeenCalledWith('Server error');
  });
});
