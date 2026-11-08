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
      <div class="p-6 border-t border-gray-200">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-xl font-semibold text-gray-700">Fotos</h2>
            <button
                @click="showAddPhotoSection = !showAddPhotoSection"
                v-if="canEditOccurrence"
                class="py-1 px-3 text-sm font-medium text-white rounded-md transition duration-150 ease-in-out"
                :class="showAddPhotoSection ? 'bg-red-500 hover:bg-red-600' : 'bg-blue-500 hover:bg-blue-600'"
            >
                {{ showAddPhotoSection ? 'Cancelar Adição' : 'Adicionar Novas Fotos' }}
            </button>
        </div>

        <!-- Seção Inline para Adicionar Novas Fotos -->
        <div v-if="showAddPhotoSection && canEditOccurrence" class="my-4 p-4 border border-gray-300 rounded-lg bg-gray-50">
          <h3 class="text-lg font-medium text-gray-800 mb-2">Adicionar Novas Fotos</h3>
          <form @submit.prevent="handleAddPhotos">
            <FileUpload
              v-model="newPhotos"
              :multiple="true"
              accept="image/*"
              :maxSize="5"
            />
            <!-- Poderia adicionar um campo de descrição geral para o lote de fotos aqui se necessário -->
            <!-- <textarea v-model="addPhotoDescription" placeholder="Descrição para as novas fotos (opcional)" class="mt-2 w-full ..."></textarea> -->
            <div class="mt-4 flex justify-end">
              <button type="submit" :disabled="submittingPhoto || newPhotos.length === 0"
                      class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:opacity-50">
                {{ submittingPhoto ? 'Enviando...' : 'Salvar Novas Fotos' }}
              </button>
            </div>
            <p v-if="addPhotoError" class="text-xs text-red-500 mt-1">{{ addPhotoError }}</p>
          </form>
        </div>

        <div v-if="occurrence.photos && occurrence.photos.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4">
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
       <!-- Mensagem se não houver fotos e não estiver adicionando -->
      <div v-else-if="!showAddPhotoSection && !(occurrence.photos && occurrence.photos.length > 0)" class="mt-4 text-gray-500">
        Nenhuma foto adicionada a esta ocorrência.
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

    <!-- Modal para Visualizar Imagem (mantido, pois é para visualização) -->
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
import Modal from '../Modal.vue';
import FileUpload from '../FileUpload.vue'; // Importar FileUpload
import { useAuthStore } from '../../stores/authStore';

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

// Variáveis para a nova seção inline de adicionar fotos
const showAddPhotoSection = ref(false);
const newPhotos = ref([]); // Para o v-model do FileUpload
const submittingPhoto = ref(false);
const addPhotoError = ref('');
// addPhotoForm não é mais necessário da mesma forma, FileUpload lida com o arquivo.
// Descrições podem ser tratadas separadamente se necessário após seleção/upload.
// const addPhotoDescription = ref(''); // Para uma descrição geral das novas fotos, se aplicável, pode ser adicionado depois.


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

// onFileChange não é mais necessário pois FileUpload lida com isso.

const handleAddPhotos = async () => { // Renomeado para plural e para nova lógica
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
        // Descrições são gerenciadas individualmente por foto no backend/modelo OccurrencePhoto.
        // Se uma descrição geral fosse adicionada para o lote:
        // if (addPhotoDescription.value) formData.append('description', addPhotoDescription.value);

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
            // Continuar tentando enviar as outras fotos
        }
    }

    if (allSucceeded) {
        toast.success('Todas as fotos foram adicionadas com sucesso!');
        showAddPhotoSection.value = false;
        newPhotos.value = []; // Limpar após o upload
        // addPhotoDescription.value = ''; // Limpar descrição geral se usada
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
