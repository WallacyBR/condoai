<template>
  <div class="container mx-auto p-4">
    <div v-if="loading" class="text-center py-10">
      <p class="text-lg text-gray-600">Carregando dados da ocorrência...</p>
      <!-- Poderia adicionar um spinner aqui -->
    </div>
    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
      <strong class="font-bold">Erro!</strong>
      <span class="block sm:inline">{{ error }}</span>
    </div>
    <div v-else-if="occurrence" class="bg-white shadow-xl rounded-lg overflow-hidden">
      <!-- Header da Ocorrência -->
      <div class="bg-gray-800 text-white p-6">
        <div class="flex justify-between items-start">
          <h1 class="text-3xl font-bold">{{ occurrence.title }}</h1>
          <router-link
            :to="{ name: 'OccurrenceEdit', params: { id: occurrence.id } }"
            class="py-2 px-4 text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600 rounded-md transition duration-150 ease-in-out"
            v-if="canEditOccurrence"
          >
            Editar
          </router-link>
        </div>
        <p class="text-sm text-gray-400 mt-1">
          Criado por: {{ occurrence.user?.name || 'Desconhecido' }} em {{ formatDate(occurrence.created_at) }}
        </p>
        <p v-if="occurrence.updated_at !== occurrence.created_at" class="text-sm text-gray-400">
          Última atualização: {{ formatDate(occurrence.updated_at) }}
        </p>
      </div>

      <!-- Detalhes da Ocorrência -->
      <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2">
          <h2 class="text-xl font-semibold text-gray-700 mb-2">Descrição</h2>
          <p class="text-gray-600 whitespace-pre-wrap">{{ occurrence.description }}</p>
        </div>
        <div>
          <h2 class="text-xl font-semibold text-gray-700 mb-2">Detalhes</h2>
          <div class="space-y-2">
            <p><strong>Status:</strong> <span :class="getStatusClass(occurrence.status)">{{ getStatusLabel(occurrence.status) }}</span></p>
            <p><strong>Prioridade:</strong> <span :class="getPriorityClass(occurrence.priority)">{{ getPriorityLabel(occurrence.priority) }}</span></p>
            <p><strong>Condomínio:</strong> {{ occurrence.condominium?.name || 'N/A' }}</p>
          </div>
        </div>
      </div>

      <!-- Seção de Fotos -->
      <div v-if="occurrence.photos && occurrence.photos.length > 0" class="p-6 border-t border-gray-200">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-gray-700">Fotos</h2>
            <button @click="showAddPhotoModal = true" v-if="canEditOccurrence"
                    class="py-1 px-3 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-md transition duration-150 ease-in-out">
                Adicionar Foto
            </button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <div v-for="photo in occurrence.photos" :key="photo.id" class="relative group">
            <img :src="getPhotoUrl(photo.photo_path)" alt="Foto da ocorrência"
                 class="w-full h-48 object-cover rounded-lg shadow-md cursor-pointer"
                 @click="openImageModal(getPhotoUrl(photo.photo_path))">
            <p v-if="photo.description" class="text-xs text-gray-600 mt-1">{{ photo.description }}</p>
            <button v-if="canEditOccurrence" @click="confirmDeletePhoto(photo.id)"
                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                &times;
            </button>
          </div>
        </div>
      </div>
       <!-- Seção para adicionar foto se não houver fotos e pode editar -->
      <div v-else-if="canEditOccurrence" class="p-6 border-t border-gray-200">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-gray-700">Fotos</h2>
        </div>
        <p class="text-gray-500 mb-2">Nenhuma foto adicionada a esta ocorrência.</p>
        <button @click="showAddPhotoModal = true"
                class="py-1 px-3 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-md transition duration-150 ease-in-out">
            Adicionar Foto
        </button>
      </div>


      <!-- Seção de Comentários -->
      <div class="p-6 border-t border-gray-200">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Comentários ({{ occurrence.comments?.length || 0 }})</h2>
        <div v-if="occurrence.comments && occurrence.comments.length > 0" class="space-y-4 mb-4 max-h-96 overflow-y-auto">
          <div v-for="comment in occurrence.comments" :key="comment.id" class="bg-gray-50 p-3 rounded-lg shadow-sm">
            <div class="flex justify-between items-start">
                <p class="text-gray-800">{{ comment.comment }}</p>
                <button v-if="canDeleteComment(comment)" @click="confirmDeleteComment(comment.id)"
                        class="text-red-500 hover:text-red-700 text-xs ml-2">&times; Remover</button>
            </div>
            <p class="text-xs text-gray-500 mt-1">
              Por: {{ comment.user?.name || 'Desconhecido' }} em {{ formatDate(comment.created_at) }}
            </p>
          </div>
        </div>
        <p v-else class="text-gray-500 mb-4">Nenhum comentário ainda.</p>

        <!-- Formulário para Novo Comentário -->
        <form @submit.prevent="submitComment">
          <textarea v-model="newComment" rows="3" placeholder="Adicionar um comentário..." required
                    class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
          <div class="mt-2 flex justify-end">
            <button type="submit" :disabled="submittingComment"
                    class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
              {{ submittingComment ? 'Enviando...' : 'Enviar Comentário' }}
            </button>
          </div>
           <p v-if="commentError" class="text-xs text-red-500 mt-1">{{ commentError }}</p>
        </form>
      </div>

      <div class="p-6 border-t border-gray-200 flex justify-end">
        <router-link :to="{ name: 'OccurrenceIndex' }" class="text-indigo-600 hover:text-indigo-800 transition duration-150 ease-in-out">
          &larr; Voltar para a lista de ocorrências
        </router-link>
      </div>
    </div>

    <!-- Modal para Adicionar Foto -->
    <Modal :show="showAddPhotoModal" @close="showAddPhotoModal = false" title="Adicionar Nova Foto">
        <form @submit.prevent="handleAddPhoto">
            <div class="mt-2">
                <label for="photoFile" class="block text-sm font-medium text-gray-700">Arquivo da Foto</label>
                <input type="file" id="photoFile" @change="onFileChange" required accept="image/*"
                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
                <p v-if="addPhotoForm.errors.photo" class="text-xs text-red-500 mt-1">{{ addPhotoForm.errors.photo.join(', ') }}</p>
            </div>
            <div class="mt-4">
                <label for="photoDescription" class="block text-sm font-medium text-gray-700">Descrição (opcional)</label>
                <input type="text" id="photoDescription" v-model="addPhotoForm.description"
                       class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div class="mt-6 flex justify-end space-x-2">
                <button type="button" @click="showAddPhotoModal = false"
                        class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" :disabled="submittingPhoto"
                        class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50">
                    {{ submittingPhoto ? 'Enviando...' : 'Adicionar Foto' }}
                </button>
            </div>
             <p v-if="addPhotoForm.submitError" class="text-xs text-red-500 mt-1">{{ addPhotoForm.submitError }}</p>
        </form>
    </Modal>

    <!-- Modal para Visualizar Imagem -->
    <Modal :show="showImageModal" @close="showImageModal = false" :title="'Visualizar Imagem'" :maxWidth="'3xl'">
        <img :src="currentImageModalUrl" alt="Foto da ocorrência em tamanho maior" class="max-w-full max-h-[80vh] mx-auto">
         <div class="mt-4 flex justify-end">
            <button @click="showImageModal = false" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                Fechar
            </button>
        </div>
    </Modal>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../utils/api';
import { format, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { useToast } from '../../composables/useToast';
import Modal from '../Modal.vue'; // Assume que você tem um componente Modal global
import { useAuthStore } from '../../stores/authStore'; // Para verificar permissões

const route = useRoute();
const router = useRouter();
const toast = useToast();
const authStore = useAuthStore();

const occurrence = ref(null);
const loading = ref(true);
const error = ref(null);
const occurrenceId = route.params.id;

const newComment = ref('');
const submittingComment = ref(false);
const commentError = ref('');

const showAddPhotoModal = ref(false);
const submittingPhoto = ref(false);
const addPhotoForm = reactive({
    photo: null,
    description: '',
    errors: {},
    submitError: '',
});

const showImageModal = ref(false);
const currentImageModalUrl = ref('');

const statusOptions = { open: 'Aberta', in_progress: 'Em Progresso', resolved: 'Resolvida', closed: 'Fechada', reopened: 'Reaberta' };
const priorityOptions = { low: 'Baixa', medium: 'Média', high: 'Alta', urgent: 'Urgente' };

const currentUser = computed(() => authStore.user);

const canEditOccurrence = computed(() => {
    if (!occurrence.value || !currentUser.value) return false;
    if (currentUser.value.is_super_admin) return true; // Super admin pode editar tudo
    // Verifica se o usuário tem permissão geral de edição E a ocorrência é do seu condomínio
    // OU se o usuário é o criador da ocorrência
    return (authStore.hasPermission('edit_occurrences') && currentUser.value.condominium_id === occurrence.value.condominium_id) ||
           currentUser.value.id === occurrence.value.user_id;
});

const canDeleteComment = (comment) => {
    if (!currentUser.value) return false;
    if (currentUser.value.is_super_admin) return true;
    // Usuário pode deletar seu próprio comentário OU se tem permissão de editar ocorrências no condomínio
    return currentUser.value.id === comment.user_id ||
           (authStore.hasPermission('edit_occurrences') && currentUser.value.condominium_id === occurrence.value.condominium_id);
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
  // Assume que as fotos estão na pasta public/storage/
  // A URL base pode precisar ser ajustada dependendo da configuração do seu servidor Laravel
  // Se o path já for uma URL completa (ex: de um S3), apenas retorne-o.
  if (path.startsWith('http')) {
    return path;
  }
  // Certifique-se que o APP_URL está configurado no .env ou use um path relativo se servido pelo mesmo domínio
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

const onFileChange = (e) => {
    addPhotoForm.photo = e.target.files[0];
    addPhotoForm.errors.photo = null; // Limpa erro anterior ao selecionar novo arquivo
};

const handleAddPhoto = async () => {
    if (!addPhotoForm.photo) {
        addPhotoForm.errors.photo = ['Selecione um arquivo de imagem.'];
        return;
    }
    submittingPhoto.value = true;
    addPhotoForm.errors = {};
    addPhotoForm.submitError = '';

    const formData = new FormData();
    formData.append('photo', addPhotoForm.photo);
    if (addPhotoForm.description) {
        formData.append('description', addPhotoForm.description);
    }

    try {
        const response = await api.post(`/occurrences/${occurrenceId}/photos`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        if (!occurrence.value.photos) {
            occurrence.value.photos = [];
        }
        occurrence.value.photos.push(response.data);
        toast.success('Foto adicionada com sucesso!');
        showAddPhotoModal.value = false;
        addPhotoForm.photo = null;
        addPhotoForm.description = '';
    } catch (err) {
        console.error("Erro ao adicionar foto:", err);
        if (err.response && err.response.status === 422) {
            addPhotoForm.errors = err.response.data.errors;
            addPhotoForm.submitError = "Por favor, corrija os erros.";
        } else {
            addPhotoForm.submitError = err.response?.data?.message || 'Não foi possível adicionar a foto.';
        }
        toast.error(addPhotoForm.submitError);
    } finally {
        submittingPhoto.value = false;
    }
};

const confirmDeletePhoto = async (photoId) => {
    if (confirm('Tem certeza que deseja excluir esta foto?')) {
        try {
            await api.delete(`/occurrences/${occurrenceId}/photos/${photoId}`);
            occurrence.value.photos = occurrence.value.photos.filter(p => p.id !== photoId);
            toast.success('Foto excluída com sucesso!');
        } catch (err) {
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
