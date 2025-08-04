<template>
  <div>
    <!-- Conteúdo de OccurrenceView.vue temporariamente desativado para depuração de build -->
    <p>Conteúdo da Ocorrência (temporário)</p>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../utils/api';
import { format, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { useToast } from '../../composables/useToast';
import Modal from '../Modal.vue';
import FileUpload from '../FileUpload.vue';
import { useUser } from '../../composables/useUser';

const route = useRoute();
const router = useRouter();
const toast = useToast();


const occurrence = ref(null);
const loading = ref(true);
const error = ref(null);
const occurrenceId = route.params.id;

const newComment = ref('');
const submittingComment = ref(false);
const commentError = ref('');

const showAddPhotoSection = ref(false);
const newPhotos = ref([]);
const submittingPhoto = ref(false);
const addPhotoError = ref('');

const showImageModal = ref(false);
const currentImageModalUrl = ref('');

const statusOptions = { open: 'Aberta', in_progress: 'Em Progresso', resolved: 'Resolvida', closed: 'Fechada', reopened: 'Reaberta' };
const priorityOptions = { low: 'Baixa', medium: 'Média', high: 'Alta', urgent: 'Urgente' };

const { user: currentUser } = useUser();

const canEditOccurrence = computed(() => {
    if (!occurrence.value || !currentUser.value) return false;
    if (currentUser.value.is_super_admin) return true;
    return (currentUser.value.profile.permissions.includes('edit_occurrences') && currentUser.value.condominium_id === occurrence.value.condominium_id) ||
           currentUser.value.id === occurrence.value.user_id;
});

const canDeleteComment = (comment) => {
    if (!currentUser.value) return false;
    if (currentUser.value.is_super_admin) return true;
    return currentUser.value.id === comment.user_id ||
           (currentUser.value.profile.permissions.includes('edit_occurrences') && currentUser.value.condominium_id === occurrence.value.condominium_id);
};

const fetchOccurrence = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await api.get(`/occurrences/${occurrenceId}`);
    occurrence.value = response.data;
  } catch (err) {
    console.error("Erro ao buscar ocorrência:", err);
    error.value = err.response?.data?.message || 'Não foi possível carregar os dados da ocorrência.';
    if(err.response?.status === 404) {
        error.value = 'Ocorrência não encontrada.';
    }
    toast.error(error.value);
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  try {
    return format(parseISO(dateString), "dd/MM/yyyy 'às' HH:mm", { locale: ptBR });
  } catch (e) {
    return dateString;
  }
};

const getStatusLabel = (status) => statusOptions[status] || status;
const getPriorityLabel = (priority) => priorityOptions[priority] || priority;

const getStatusClass = (status) => {
  const classes = {
    open: 'bg-blue-100 text-blue-800',
    in_progress: 'bg-yellow-100 text-yellow-800',
    resolved: 'bg-green-100 text-green-800',
    closed: 'bg-gray-100 text-gray-800',
    reopened: 'bg-purple-100 text-purple-800',
  };
  return `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${classes[status] || 'bg-gray-100 text-gray-800'}`;
};

const getPriorityClass = (priority) => {
  const classes = {
    low: 'bg-green-100 text-green-800',
    medium: 'bg-yellow-100 text-yellow-800',
    high: 'bg-orange-100 text-orange-800',
    urgent: 'bg-red-100 text-red-800',
  };
  return `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${classes[priority] || 'bg-gray-100 text-gray-800'}`;
};

const getPhotoUrl = (path) => {
  if (path.startsWith('http')) {
    return path;
  }
  const baseUrl = import.meta.env.VITE_API_BASE_URL || window.location.origin;
  return `${baseUrl.replace('/api', '')}/storage/${path}`;
};

const submitComment = async () => {
  if (!newComment.value.trim()) {
    commentError.value = "O comentário não pode estar vazio.";
    return;
  }
  submittingComment.value = true;
  commentError.value = '';
  try {
    const response = await api.post(`/occurrences/${occurrenceId}/comments`, {
      comment: newComment.value,
    });
    occurrence.value.comments.push(response.data);
    newComment.value = '';
    toast.success('Comentário adicionado com sucesso!');
  } catch (err) {
    console.error("Erro ao adicionar comentário:", err);
    commentError.value = err.response?.data?.message || 'Não foi possível adicionar o comentário.';
    toast.error(commentError.value);
  } finally {
    submittingComment.value = false;
  }
};

const confirmDeleteComment = async (commentId) => {
    if (confirm('Tem certeza que deseja excluir este comentário?')) {
        try {
            await api.delete(`/occurrences/${occurrenceId}/comments/${commentId}`);
            occurrence.value.comments = occurrence.value.comments.filter(c => c.id !== commentId);
            toast.success('Comentário excluído com sucesso!');
        } catch (err) {
            console.error("Erro ao excluir comentário:", err);
            toast.error(err.response?.data?.message || 'Não foi possível excluir o comentário.');
        }
    }
};

const handleAddPhotos = async () => {
    if (newPhotos.value.length === 0) {
        addPhotoError.value = 'Selecione ao menos uma foto para adicionar.';
        toast.error(addPhotoError.value);
        return;
    }
    submittingPhoto.value = true;
    addPhotoError.value = '';
    let allSucceeded = true;

    for (const file of newPhotos.value) {
        const formData = new FormData();
        formData.append('photo', file);

        try {
            const response = await api.post(`/occurrences/${occurrenceId}/photos`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            if (!occurrence.value.photos) {
                occurrence.value.photos = [];
            }
            occurrence.value.photos.push(response.data);
        } catch (err) {
            allSucceeded = false;
            console.error("Erro ao adicionar foto:", file.name, err);
            const specificError = err.response?.data?.errors?.photo?.join(', ') || err.response?.data?.message || `Erro ao enviar ${file.name}.`;
            addPhotoError.value += `${specificError}\n`;
            toast.error(`Erro ao adicionar ${file.name}.`);
        }
    }

    if (allSucceeded) {
        toast.success('Todas as fotos foram adicionadas com sucesso!');
        showAddPhotoSection.value = false;
        newPhotos.value = [];
    } else {
        toast.warning('Algumas fotos não puderam ser adicionadas. Verifique os erros.');
    }
    submittingPhoto.value = false;
};

const confirmDeletePhoto = async (photoId) => {
    if (confirm('Tem certeza que deseja excluir esta foto?')) {
        try {
            await api.delete(`/occurrences/${occurrenceId}/photos/${photoId}`);
            occurrence.value.photos = occurrence.value.photos.filter(p => p.id !== photoId);
            toast.success('Foto excluída com sucesso!');
        }
        catch (err) {
            console.error("Erro ao excluir foto:", err);
            toast.error(err.response?.data?.message || 'Não foi possível excluir a foto.');
        }
    }
};

const openImageModal = (imageUrl) => {
    currentImageModalUrl.value = imageUrl;
    showImageModal.value = true;
};

onMounted(() => {
  fetchOccurrence();
});
</script>

<style scoped>
/* Adicionar estilos específicos se necessário */
.whitespace-pre-wrap {
  white-space: pre-wrap; /* Garante que quebras de linha e espaços sejam preservados */
}
</style>
