import { shallowMount, mount, flushPromises } from '@vue/test-utils';
import OccurrenceView from '../../components/occurrences/OccurrenceView.vue';
import api from '../../utils/api';
import { useToast } from '../../composables/useToast';
import { useAuthStore } from '../../stores/authStore'; // Importe o store
import { vi } from 'vitest';

// Mock vue-router
const mockRouter = {
  push: vi.fn(),
};
const mockRoute = {
  params: { id: '1' }, // Default to viewing occurrence with ID 1
};

vi.mock('vue-router', () => ({
  useRouter: () => mockRouter,
  useRoute: () => mockRoute,
  RouterLink: { template: '<a :to="to"><slot/></a>', props: ['to'] }
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

// Mock do authStore
const mockUser = { id: 1, name: 'Test User', condominium_id: 1, is_super_admin: false };
vi.mock('../../stores/authStore', () => ({
  useAuthStore: () => ({
    user: mockUser,
    hasPermission: (permission) => {
      if (permission === 'edit_occurrences') return true; // Simula que o usuário tem essa permissão
      return false;
    },
  }),
}));


const mockOccurrence = {
  id: '1',
  title: 'Leaky Faucet',
  description: 'The kitchen faucet is leaking.',
  status: 'open',
  priority: 'high',
  user: { id: 1, name: 'John Doe' },
  condominium: { id: 1, name: 'Condo Alpha' },
  created_at: '2023-01-15T10:00:00Z',
  updated_at: '2023-01-15T10:00:00Z',
  photos: [{ id: 1, photo_path: 'photos/faucet.jpg', description: 'Leaky faucet photo' }],
  comments: [{ id: 1, comment: 'Needs urgent fix', user: { name: 'Jane Smith' }, created_at: '2023-01-15T11:00:00Z' }],
};

describe('OccurrenceView.vue', () => {
  let wrapper;
  const toast = useToast();
  const authStore = useAuthStore();


  const mountComponent = (occurrenceData = mockOccurrence) => {
    api.get.mockResolvedValue({ data: occurrenceData });
    // Usar mount para interações mais complexas e componentes filhos como Modal (se não for stubbed)
    return mount(OccurrenceView, {
      global: {
        stubs: {
          // RouterLink: true, // Já mockado globalmente
          Modal: { // Stubbing Modal para simplificar
            template: '<div v-if="show"><h3 v-if="title">{{ title }}</h3><slot/><button @click="$emit(\'close\')">Close</button></div>',
            props: ['show', 'title', 'maxWidth']
          }
        },
      },
    });
  };

  beforeEach(async () => {
    vi.clearAllMocks();
    // Resetar o usuário do authStore para cada teste se necessário, ou configurar especificamente
    authStore.user = { ...mockUser, id: 1, is_super_admin: false }; // Usuário padrão para testes
    wrapper = mountComponent();
    await flushPromises();
  });

  it('renders occurrence details correctly', () => {
    expect(wrapper.find('h1').text()).toBe(mockOccurrence.title);
    expect(wrapper.html()).toContain(mockOccurrence.description);
    expect(wrapper.html()).toContain('Aberta'); // Status label
    expect(wrapper.html()).toContain('Alta'); // Priority label
    expect(wrapper.find('img').attributes('src')).toContain(mockOccurrence.photos[0].photo_path);
    expect(wrapper.html()).toContain(mockOccurrence.comments[0].comment);
  });

  it('shows loading state initially', async () => {
    api.get.mockReturnValueOnce(new Promise(() => {})); // Promise que nunca resolve
    wrapper = mountComponent();
    // Não use flushPromises aqui, pois queremos testar o estado de carregamento
    expect(wrapper.find('p.text-lg.text-gray-600').text()).toBe('Carregando dados da ocorrência...');
    // Restaurar mock para não afetar outros testes
    api.get.mockResolvedValue({data: mockOccurrence});
  });

  it('shows error message if fetching occurrence fails', async () => {
    const errorMessage = 'Failed to load';
    api.get.mockRejectedValueOnce({ response: { data: { message: errorMessage }, status: 500 } });
    wrapper = mountComponent();
    await flushPromises();
    expect(wrapper.find('.bg-red-100 span').text()).toBe(errorMessage);
    expect(toast.error).toHaveBeenCalledWith(errorMessage);
  });

  it('shows 404 error message if occurrence not found', async () => {
    const errorMessage = 'Ocorrência não encontrada.';
    api.get.mockRejectedValueOnce({ response: { data: { message: errorMessage }, status: 404 } });
    wrapper = mountComponent();
    await flushPromises();
    expect(wrapper.find('.bg-red-100 span').text()).toBe(errorMessage);
    expect(toast.error).toHaveBeenCalledWith(errorMessage);
  });

  it('allows adding a comment', async () => {
    const newCommentText = 'This is a new comment.';
    api.post.mockResolvedValueOnce({ data: { id: 2, comment: newCommentText, user: { name: 'Test User' }, created_at: new Date().toISOString() } });

    await wrapper.find('textarea').setValue(newCommentText);
    await wrapper.find('form').trigger('submit.prevent');
    await flushPromises();

    expect(api.post).toHaveBeenCalledWith(`/occurrences/${mockOccurrence.id}/comments`, { comment: newCommentText });
    expect(wrapper.vm.occurrence.comments.length).toBe(2); // mockOccurrence.comments + novo comentário
    expect(wrapper.html()).toContain(newCommentText);
    expect(toast.success).toHaveBeenCalledWith('Comentário adicionado com sucesso!');
    expect(wrapper.vm.newComment).toBe(''); // Textarea should be cleared
  });

  it('shows error if adding comment fails', async () => {
    const errorMsg = "Failed to add comment";
    api.post.mockRejectedValueOnce({response: {data: {message: errorMsg}}});
    await wrapper.find('textarea').setValue("Test comment");
    await wrapper.find('form').trigger('submit.prevent');
    await flushPromises();
    expect(wrapper.vm.commentError).toBe(errorMsg);
    expect(toast.error).toHaveBeenCalledWith(errorMsg);
  });

  it('allows deleting a comment if user is authorized', async () => {
    // Assume que o usuário atual (ID 1) é o criador do comentário ou admin
    authStore.user = { ...mockUser, id: mockOccurrence.comments[0].user_id || 1 }; // Garante que o usuário pode deletar
    api.delete.mockResolvedValueOnce({});
    global.confirm = () => true; // Mock window.confirm

    // Re-mount ou re-evaluate computed properties se necessário, ou assegure que `canDeleteComment` funcione com o novo authStore.user
    // Neste caso, como o authStore é mockado no topo, a instância usada por `canDeleteComment` já deve ter o usuário correto.

    const commentToDelete = wrapper.vm.occurrence.comments[0];
    // Encontrar o botão de remover do primeiro comentário
    const deleteButton = wrapper.findAll('.bg-gray-50 button').at(0); // Pode precisar de um seletor mais específico
    expect(deleteButton.text()).toContain('Remover');

    await deleteButton.trigger('click');
    await flushPromises();

    expect(api.delete).toHaveBeenCalledWith(`/occurrences/${mockOccurrence.id}/comments/${commentToDelete.id}`);
    expect(wrapper.vm.occurrence.comments.length).toBe(0);
    expect(toast.success).toHaveBeenCalledWith('Comentário excluído com sucesso!');
  });

  // Testes para Adicionar/Remover Fotos (assumindo que o usuário tem permissão)
  describe('Photo Management (with permission)', () => {
    beforeEach(async () => {
        // Garante que o usuário tem permissão para editar (e portanto, gerenciar fotos)
        authStore.user = { ...mockUser, id: mockOccurrence.user.id, is_super_admin: false }; // User é o criador
        // Força a reavaliação do computed property se necessário, ou re-mount
        wrapper = mountComponent(); // Re-mount para garantir que computed properties sejam atualizadas
        await flushPromises();

        // Verifica se o botão de adicionar foto está visível
        expect(wrapper.find('button.bg-blue-500').exists()).toBe(true);
    });

    it('opens add photo modal', async () => {
        await wrapper.find('button.bg-blue-500').trigger('click'); // Botão "Adicionar Foto"
        expect(wrapper.vm.showAddPhotoModal).toBe(true);
        // Verifica se o modal (stubbed) está visível
        const modalWrapper = wrapper.findComponent({ name: 'Modal' }); // Acessa o stub do Modal
        expect(modalWrapper.props('show')).toBe(true);
        expect(modalWrapper.props('title')).toBe('Adicionar Nova Foto');
    });

    it('adds a new photo', async () => {
        api.post.mockResolvedValueOnce({ data: { id: 2, photo_path: 'photos/new.jpg', description: 'New one' } });
        const mockFile = new File(['new photo'], 'new.jpg', { type: 'image/jpeg' });

        await wrapper.vm.onFileChange({ target: { files: [mockFile] } }); // Simula seleção de arquivo
        wrapper.vm.addPhotoForm.description = 'New one';

        // Simula submissão do formulário do modal
        // Como o modal é stubbed, chamamos o método diretamente para teste
        await wrapper.vm.handleAddPhoto();
        await flushPromises();

        expect(api.post).toHaveBeenCalled();
        const formData = api.post.mock.calls[0][1]; // Pega o FormData
        expect(formData.get('photo').name).toBe('new.jpg');
        expect(formData.get('description')).toBe('New one');

        expect(wrapper.vm.occurrence.photos.length).toBe(2);
        expect(toast.success).toHaveBeenCalledWith('Foto adicionada com sucesso!');
        expect(wrapper.vm.showAddPhotoModal).toBe(false);
    });

    it('shows error if adding photo fails validation (no file)', async () => {
        wrapper.vm.showAddPhotoModal = true; // Abre o modal
        await flushPromises();

        // Chama handleAddPhoto sem um arquivo selecionado
        await wrapper.vm.handleAddPhoto();
        await flushPromises();

        expect(wrapper.vm.addPhotoForm.errors.photo).toEqual(['Selecione um arquivo de imagem.']);
        // Não deve chamar api.post
        expect(api.post).not.toHaveBeenCalled();
    });

    it('deletes a photo', async () => {
        api.delete.mockResolvedValueOnce({});
        global.confirm = () => true;
        const photoToDelete = wrapper.vm.occurrence.photos[0];

        // Encontra o botão de deletar da primeira foto
        const deletePhotoButton = wrapper.find('.relative.group button');
        await deletePhotoButton.trigger('click');
        await flushPromises();

        expect(api.delete).toHaveBeenCalledWith(`/occurrences/${mockOccurrence.id}/photos/${photoToDelete.id}`);
        expect(wrapper.vm.occurrence.photos.length).toBe(0);
        expect(toast.success).toHaveBeenCalledWith('Foto excluída com sucesso!');
    });
  });

  describe('Permissions', () => {
    it('hides edit button if user is not authorized', async () => {
        authStore.user = { ...mockUser, id: 999, is_super_admin: false }; // Usuário diferente, sem ser super admin
        // Mock para hasPermission retornar false para 'edit_occurrences' para este usuário
        authStore.hasPermission = (permission) => permission !== 'edit_occurrences';

        wrapper = mountComponent({ ...mockOccurrence, user: { id: 2 } }); // Ocorrência criada por outro usuário
        await flushPromises();

        expect(wrapper.find('router-link[to*="edit"]').exists()).toBe(false); // Link de edição
        expect(wrapper.find('button.bg-blue-500').exists()).toBe(false); // Botão Adicionar Foto
    });

    it('shows edit button if user is super_admin', async () => {
        authStore.user = { ...mockUser, is_super_admin: true };
        wrapper = mountComponent({ ...mockOccurrence, user: { id: 2 } }); // Ocorrência de outro user
        await flushPromises();
        expect(wrapper.find('a[to*="edit"]').exists()).toBe(true);
    });

    it('shows edit button if user is the creator', async () => {
        authStore.user = { ...mockUser, id: mockOccurrence.user.id }; // Usuário é o criador
        wrapper = mountComponent(); // mockOccurrence já tem user.id = 1
        await flushPromises();
        expect(wrapper.find('a[to*="edit"]').exists()).toBe(true);
    });

    it('shows edit button if user has edit_occurrences permission for the condominium', async () => {
        // User tem condominium_id = 1, occurrence tem condominium_id = 1
        // authStore.hasPermission('edit_occurrences') já mockado para true
        authStore.user = { ...mockUser, id: 999, condominium_id: mockOccurrence.condominium.id }; // Outro user, mesmo condomínio
        wrapper = mountComponent();
        await flushPromises();
        expect(wrapper.find('a[to*="edit"]').exists()).toBe(true);
    });
  });

  it('opens and closes image modal', async () => {
    const photoUrl = wrapper.vm.getPhotoUrl(mockOccurrence.photos[0].photo_path);
    await wrapper.find('img.cursor-pointer').trigger('click'); // Clica na imagem da ocorrência
    expect(wrapper.vm.showImageModal).toBe(true);
    expect(wrapper.vm.currentImageModalUrl).toBe(photoUrl);

    // Simula o fechamento do modal (o stub do modal emite 'close')
    const modalStub = wrapper.findComponent({ name: 'Modal' }); // Acessa o stub do Modal
    await modalStub.vm.$emit('close'); // Emite o evento close
    expect(wrapper.vm.showImageModal).toBe(false);
  });

});
