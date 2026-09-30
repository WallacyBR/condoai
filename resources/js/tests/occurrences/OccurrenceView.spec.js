import { mount, flushPromises } from '@vue/test-utils';
import { vi } from 'vitest';
import OccurrenceView from '../../components/occurrences/OccurrenceView.vue';
import api from '../../utils/api';
import { mockToastFns, resetMockToast } from '../mocks/useToast';

const { mockApi } = vi.hoisted(() => ({
  mockApi: {
    get: vi.fn(),
    post: vi.fn(),
    delete: vi.fn(),
  },
}));

const { userRef } = vi.hoisted(() => ({
  userRef: {
    value: {
      id: 1,
      name: 'Test User',
      condominium_id: 1,
      is_super_admin: false,
      profile: { permissions: ['edit_occurrences'] },
    },
  },
}));

const mockUser = {
  id: 1,
  name: 'Test User',
  condominium_id: 1,
  is_super_admin: false,
  profile: { permissions: ['edit_occurrences'] },
};

const { mockRouter, mockRoute } = vi.hoisted(() => ({
  mockRouter: { push: vi.fn() },
  mockRoute: { params: { id: '1' } },
}));

vi.mock('vue-router', () => ({
  useRouter: () => mockRouter,
  useRoute: () => mockRoute,
  RouterLink: { template: '<a :to="to"><slot/></a>', props: ['to'] },
}));

vi.mock('../../utils/api', () => ({
  default: mockApi,
}));

vi.mock('../../composables/useUser', () => ({
  useUser: () => ({ user: userRef }),
}));

const mockOccurrence = {
  id: '1',
  title: 'Leaky Faucet',
  description: 'The kitchen faucet is leaking.',
  status: 'open',
  priority: 'high',
  user: { id: 1, name: 'John Doe' },
  user_id: 1,
  condominium_id: 1,
  condominium: { id: 1, name: 'Condo Alpha' },
  created_at: '2023-01-15T10:00:00Z',
  updated_at: '2023-01-15T10:00:00Z',
  photos: [{ id: 1, photo_path: 'photos/faucet.jpg', description: 'Leaky faucet photo' }],
  comments: [{
    id: 1,
    comment: 'Needs urgent fix',
    user_id: 1,
    user: { id: 1, name: 'Jane Smith' },
    created_at: '2023-01-15T11:00:00Z',
  }],
};

describe('OccurrenceView.vue', () => {
  let wrapper;

  const mountComponent = (occurrenceData = mockOccurrence) => {
    api.get.mockResolvedValue({ data: occurrenceData });
    return mount(OccurrenceView, {
      global: {
        stubs: {
          Modal: true,
          FileUpload: true,
        },
      },
    });
  };

  beforeEach(async () => {
    resetMockToast();
    vi.clearAllMocks();
    window.confirm = vi.fn(() => true);
    api.delete.mockResolvedValue({});
    userRef.value = { ...mockUser };
    wrapper = mountComponent();
    await flushPromises();
  });

  it('renders temporary stub template while full UI is disabled', () => {
    expect(wrapper.text()).toContain('Conteúdo da Ocorrência (temporário)');
  });

  it('fetches occurrence on mount', () => {
    expect(api.get).toHaveBeenCalledWith('/occurrences/1');
    expect(wrapper.vm.occurrence.title).toBe(mockOccurrence.title);
    expect(wrapper.vm.occurrence.description).toBe(mockOccurrence.description);
  });

  it('shows loading state initially', async () => {
    api.get.mockReturnValueOnce(new Promise(() => {}));
    wrapper = mountComponent();
    expect(wrapper.vm.loading).toBe(true);
    api.get.mockResolvedValue({ data: mockOccurrence });
  });

  it('shows error message if fetching occurrence fails', async () => {
    const errorMessage = 'Failed to load';
    api.get.mockRejectedValueOnce({ response: { data: { message: errorMessage }, status: 500 } });
    wrapper = mountComponent();
    await flushPromises();

    expect(wrapper.vm.error).toBe(errorMessage);
    expect(mockToastFns.error).toHaveBeenCalledWith(errorMessage);
  });

  it('shows 404 error message if occurrence not found', async () => {
    api.get.mockRejectedValueOnce({ response: { data: { message: 'Not found' }, status: 404 } });
    wrapper = mountComponent();
    await flushPromises();

    expect(wrapper.vm.error).toBe('Ocorrência não encontrada.');
    expect(mockToastFns.error).toHaveBeenCalledWith('Ocorrência não encontrada.');
  });

  it('allows adding a comment', async () => {
    const newCommentText = 'This is a new comment.';
    api.post.mockResolvedValueOnce({
      data: { id: 2, comment: newCommentText, user: { name: 'Test User' }, created_at: new Date().toISOString() },
    });

    wrapper.vm.newComment = newCommentText;
    await wrapper.vm.submitComment();
    await flushPromises();

    expect(api.post).toHaveBeenCalledWith('/occurrences/1/comments', { comment: newCommentText });
    expect(wrapper.vm.occurrence.comments.length).toBe(2);
    expect(mockToastFns.success).toHaveBeenCalledWith('Comentário adicionado com sucesso!');
    expect(wrapper.vm.newComment).toBe('');
  });

  it('shows error if adding comment fails', async () => {
    const errorMsg = 'Failed to add comment';
    api.post.mockRejectedValueOnce({ response: { data: { message: errorMsg } } });

    wrapper.vm.newComment = 'Test comment';
    await wrapper.vm.submitComment();
    await flushPromises();

    expect(wrapper.vm.commentError).toBe(errorMsg);
    expect(mockToastFns.error).toHaveBeenCalledWith(errorMsg);
  });

  it('allows deleting a comment if user is authorized', async () => {
    userRef.value = { ...mockUser, id: mockOccurrence.comments[0].user_id };
    api.delete.mockResolvedValueOnce({});
    global.confirm = vi.fn(() => true);

    const commentId = wrapper.vm.occurrence.comments[0].id;
    await wrapper.vm.confirmDeleteComment(commentId);
    await flushPromises();

    expect(api.delete).toHaveBeenCalledWith(`/occurrences/1/comments/${commentId}`);
    expect(mockToastFns.success).toHaveBeenCalledWith('Comentário excluído com sucesso!');
    expect(wrapper.vm.occurrence.comments.some((c) => c.id === commentId)).toBe(false);
  });

  describe('Photo Management (with permission)', () => {
    beforeEach(async () => {
      userRef.value = { ...mockUser, id: mockOccurrence.user.id };
      wrapper = mountComponent();
      await flushPromises();
      expect(wrapper.vm.canEditOccurrence).toBe(true);
    });

    it('adds new photos via handleAddPhotos', async () => {
      api.post.mockResolvedValueOnce({ data: { id: 2, photo_path: 'photos/new.jpg', description: 'New one' } });
      const mockFile = new File(['new photo'], 'new.jpg', { type: 'image/jpeg' });

      wrapper.vm.newPhotos = [mockFile];
      await wrapper.vm.handleAddPhotos();
      await flushPromises();

      expect(api.post).toHaveBeenCalled();
      const formData = api.post.mock.calls[0][1];
      expect(formData.get('photo').name).toBe('new.jpg');
      expect(wrapper.vm.occurrence.photos.length).toBe(2);
      expect(mockToastFns.success).toHaveBeenCalledWith('Todas as fotos foram adicionadas com sucesso!');
      expect(wrapper.vm.showAddPhotoSection).toBe(false);
    });

    it('shows error if adding photo fails validation (no file)', async () => {
      wrapper.vm.newPhotos = [];
      await wrapper.vm.handleAddPhotos();
      await flushPromises();

      expect(wrapper.vm.addPhotoError).toBe('Selecione ao menos uma foto para adicionar.');
      expect(api.post).not.toHaveBeenCalled();
      expect(mockToastFns.error).toHaveBeenCalledWith('Selecione ao menos uma foto para adicionar.');
    });

    it('deletes a photo', async () => {
      api.delete.mockResolvedValueOnce({});
      global.confirm = vi.fn(() => true);
      const photoId = wrapper.vm.occurrence.photos[0].id;

      await wrapper.vm.confirmDeletePhoto(photoId);
      await flushPromises();

      expect(api.delete).toHaveBeenCalledWith(`/occurrences/1/photos/${photoId}`);
      expect(mockToastFns.success).toHaveBeenCalledWith('Foto excluída com sucesso!');
      expect(wrapper.vm.occurrence.photos.some((p) => p.id === photoId)).toBe(false);
    });
  });

  describe('Permissions', () => {
    it('canEditOccurrence is false when user is not authorized', async () => {
      userRef.value = { ...mockUser, id: 999, is_super_admin: false, profile: { permissions: [] } };
      wrapper = mountComponent({ ...mockOccurrence, user: { id: 2 }, user_id: 2 });
      await flushPromises();
      expect(wrapper.vm.canEditOccurrence).toBe(false);
    });

    it('canEditOccurrence is true when user is super_admin', async () => {
      userRef.value = { ...mockUser, is_super_admin: true };
      wrapper = mountComponent({ ...mockOccurrence, user: { id: 2 }, user_id: 2 });
      await flushPromises();
      expect(wrapper.vm.canEditOccurrence).toBe(true);
    });

    it('canEditOccurrence is true when user is the creator', async () => {
      userRef.value = { ...mockUser, id: mockOccurrence.user.id };
      wrapper = mountComponent();
      await flushPromises();
      expect(wrapper.vm.canEditOccurrence).toBe(true);
    });

    it('canEditOccurrence is true when user has edit_occurrences permission', async () => {
      userRef.value = { ...mockUser, id: 999, condominium_id: mockOccurrence.condominium.id };
      wrapper = mountComponent();
      await flushPromises();
      expect(wrapper.vm.canEditOccurrence).toBe(true);
    });
  });

  it('opens and closes image modal via script API', async () => {
    const photoUrl = wrapper.vm.getPhotoUrl(mockOccurrence.photos[0].photo_path);
    wrapper.vm.openImageModal(photoUrl);
    expect(wrapper.vm.showImageModal).toBe(true);
    expect(wrapper.vm.currentImageModalUrl).toBe(photoUrl);

    wrapper.vm.showImageModal = false;
    expect(wrapper.vm.showImageModal).toBe(false);
  });
});
