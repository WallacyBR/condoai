import { mount, flushPromises } from '@vue/test-utils';
import { vi } from 'vitest';
import OccurrenceForm from '../../components/occurrences/OccurrenceForm.vue';
import api from '../../utils/api';
import { mockToastFns, resetMockToast } from '../mocks/useToast';

const { mockApi } = vi.hoisted(() => ({
  mockApi: {
    get: vi.fn(),
    post: vi.fn(),
  },
}));

const { mockRouter, mockRoute } = vi.hoisted(() => ({
  mockRouter: { push: vi.fn() },
  mockRoute: { params: {} },
}));

vi.mock('vue-router', () => ({
  useRouter: () => mockRouter,
  useRoute: () => mockRoute,
  RouterLink: { template: '<a><slot/></a>' },
}));

vi.mock('../../utils/api', () => ({
  default: mockApi,
}));

const FileUploadStub = {
  name: 'FileUpload',
  props: ['modelValue', 'multiple', 'accept', 'maxSize'],
  emits: ['update:modelValue'],
  template: `
    <div class="file-upload-stub">
      <input type="file" data-testid="file-upload" @change="onChange" />
      <button type="button" data-testid="clear-files" @click="$emit('update:modelValue', [])">Limpar</button>
    </div>
  `,
  methods: {
    onChange(event) {
      const files = Array.from(event.target.files || []);
      this.$emit('update:modelValue', files);
    },
  },
};

describe('OccurrenceForm.vue', () => {
  let wrapper;

  const mountComponent = (isEditing = false, occurrenceId = null) => {
    mockRoute.params = isEditing ? { id: occurrenceId || '1' } : {};
    return mount(OccurrenceForm, {
      global: {
        stubs: {
          RouterLink: true,
          FileUpload: FileUploadStub,
        },
      },
    });
  };

  beforeEach(() => {
    resetMockToast();
    mockRouter.push.mockClear();
    api.get.mockReset();
    api.post.mockReset();
    api.get.mockResolvedValue({
      data: { id: '1', title: 'Old Title', description: 'Old Desc', priority: 'low', status: 'open', photos: [] },
    });
    api.post.mockResolvedValue({ data: { id: '123', title: 'New Test Occurrence' } });
  });

  describe('Create Mode', () => {
    beforeEach(() => {
      wrapper = mountComponent(false);
    });

    it('renders the form in create mode', () => {
      expect(wrapper.find('h1').text()).toBe('Nova Ocorrência');
      expect(wrapper.find('button[type="submit"]').text()).toBe('Criar Ocorrência');
      expect(wrapper.find('select#status').exists()).toBe(false);
    });

    it('initializes form with default priority and empty fields', () => {
      expect(wrapper.vm.form.title).toBe('');
      expect(wrapper.vm.form.description).toBe('');
      expect(wrapper.vm.form.priority).toBe('medium');
      expect(wrapper.vm.form.photos).toEqual([]);
    });

    it('handles file upload via FileUpload v-model', async () => {
      const mockFile = new File(['dummy content'], 'test.png', { type: 'image/png' });
      wrapper.vm.form.photos = [mockFile];
      await wrapper.vm.$nextTick();

      expect(wrapper.vm.form.photos.length).toBe(1);
      expect(wrapper.vm.form.photos[0]).toBe(mockFile);
    });

    it('clears photos when FileUpload emits empty array', async () => {
      wrapper.vm.form.photos = [new File(['x'], 'photo.jpg', { type: 'image/jpeg' })];
      await wrapper.vm.$nextTick();

      await wrapper.find('[data-testid="clear-files"]').trigger('click');

      expect(wrapper.vm.form.photos).toEqual([]);
    });

    it('submits new occurrence data correctly (without photos)', async () => {
      api.post.mockResolvedValue({ data: { id: '123', title: 'New Test Occurrence' } });

      await wrapper.find('input#title').setValue('New Test Occurrence');
      await wrapper.find('textarea#description').setValue('Detailed description.');
      await wrapper.find('select#priority').setValue('high');
      await wrapper.find('form').trigger('submit.prevent');
      await flushPromises();

      expect(api.post).toHaveBeenCalledTimes(1);
      const formData = api.post.mock.calls[0][1];
      expect(formData.get('title')).toBe('New Test Occurrence');
      expect(formData.get('description')).toBe('Detailed description.');
      expect(formData.get('priority')).toBe('high');
      expect(formData.has('photos[0]')).toBe(false);

      expect(mockToastFns.success).toHaveBeenCalledWith('Ocorrência criada com sucesso!');
      expect(mockRouter.push).toHaveBeenCalledWith({ name: 'OccurrenceShow', params: { id: '123' } });
    });

    it('submits new occurrence data correctly (with photos)', async () => {
      api.post.mockResolvedValue({ data: { id: '124', title: 'Occurrence With Photo' } });

      const mockFile = new File(['dummy content'], 'test.png', { type: 'image/png' });
      wrapper.vm.form.photos = [mockFile];

      await wrapper.find('input#title').setValue('Occurrence With Photo');
      await wrapper.find('textarea#description').setValue('Description for photo occurrence.');
      await wrapper.find('select#priority').setValue('low');
      await wrapper.find('form').trigger('submit.prevent');
      await flushPromises();

      const formData = api.post.mock.calls[0][1];
      expect(formData.get('photos[0]').name).toBe('test.png');
      expect(mockToastFns.success).toHaveBeenCalledWith('Ocorrência criada com sucesso!');
    });

    it('shows validation errors on submit if fields are invalid', async () => {
      api.post.mockRejectedValue({ response: { status: 422, data: { errors: { title: ['Title is required'] } } } });

      await wrapper.find('input#title').setValue('x');
      await wrapper.find('textarea#description').setValue('y');
      await wrapper.find('select#priority').setValue('low');
      await wrapper.find('form').trigger('submit.prevent');
      await flushPromises();

      expect(wrapper.vm.errors.title).toEqual(['Title is required']);
      expect(wrapper.find('input#title + p.text-red-500').text()).toBe('Title is required');
      expect(mockToastFns.error).toHaveBeenCalled();
    });
  });

  describe('Edit Mode', () => {
    const occurrenceData = {
      id: '1',
      title: 'Existing Occurrence',
      description: 'This needs an update.',
      priority: 'medium',
      status: 'open',
      photos: [],
    };

    beforeEach(async () => {
      api.get.mockResolvedValue({ data: occurrenceData });
      wrapper = mountComponent(true, '1');
      await flushPromises();
    });

    it('renders the form in edit mode and loads data', () => {
      expect(wrapper.find('h1').text()).toBe('Editar Ocorrência');
      expect(wrapper.find('button[type="submit"]').text()).toBe('Atualizar Ocorrência');
      expect(api.get).toHaveBeenCalledWith('/occurrences/1');
      expect(wrapper.vm.form.title).toBe(occurrenceData.title);
      expect(wrapper.find('input[type="file"]').exists()).toBe(false);
    });

    it('submits updated occurrence data correctly', async () => {
      api.post.mockResolvedValue({ data: { ...occurrenceData, title: 'Updated Occurrence Title' } });

      await wrapper.find('input#title').setValue('Updated Occurrence Title');
      await wrapper.find('select#status').setValue('in_progress');
      await wrapper.find('form').trigger('submit.prevent');
      await flushPromises();

      const formData = api.post.mock.calls[0][1];
      expect(formData.get('title')).toBe('Updated Occurrence Title');
      expect(formData.get('status')).toBe('in_progress');
      expect(formData.get('_method')).toBe('PUT');
      expect(mockToastFns.success).toHaveBeenCalledWith('Ocorrência atualizada com sucesso!');
    });

    it('handles error when fetching occurrence data for editing', async () => {
      api.get.mockRejectedValueOnce({ response: { data: { message: 'Fetch error' } } });
      wrapper = mountComponent(true, '2');
      await flushPromises();

      expect(wrapper.vm.submitError).toBe('Não foi possível carregar os dados da ocorrência para edição.');
      expect(mockToastFns.error).toHaveBeenCalledWith('Erro ao carregar dados da ocorrência.');
    });
  });

  it('handles generic submit error', async () => {
    api.post.mockRejectedValue({ response: { data: { message: 'Server error' } } });
    wrapper = mountComponent(false);

    await wrapper.find('input#title').setValue('Error Test');
    await wrapper.find('textarea#description').setValue('Desc');
    await wrapper.find('select#priority').setValue('low');
    await wrapper.find('form').trigger('submit.prevent');
    await flushPromises();

    expect(wrapper.vm.submitError).toBe('Server error');
    expect(mockToastFns.error).toHaveBeenCalledWith('Server error');
  });
});
