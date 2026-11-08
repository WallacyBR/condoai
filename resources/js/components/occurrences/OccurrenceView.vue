<template>
  <div class="space-y-6">
    <TopCard
      :title="occurrence?.title || 'Ocorrência'"
      subtitle="Detalhes, fotos e comentários do chamado"
      :icon="OccurrenceIcon"
    >
      <template #actions>
        <div class="flex flex-wrap gap-2 justify-end">
          <NeonButton @click="goBack" size="sm" variant="secondary">
            Voltar
          </NeonButton>
          <NeonButton v-if="occurrence" @click="goToEdit" size="sm" variant="primary">
            Editar
          </NeonButton>
        </div>
      </template>
    </TopCard>

    <div v-if="loading" class="flex justify-center items-center py-16">
      <div class="animate-spin rounded-full h-8 w-8 border-2 border-slate-300 border-t-indigo-600 dark:border-slate-700 dark:border-t-indigo-400"></div>
    </div>

    <div v-else-if="error" class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 rounded-xl p-4 text-sm text-rose-700 dark:text-rose-300 shadow-sm">
      {{ error }}
    </div>

    <div v-else-if="occurrence" class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <section class="xl:col-span-2 space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
          <div class="h-64 sm:h-72 bg-slate-100 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden">
            <img
              v-if="coverPhoto"
              :src="coverPhoto"
              :alt="`Foto da ocorrência ${occurrence.title}`"
              class="h-full w-full object-cover cursor-pointer hover:opacity-95 transition-opacity"
              @click="openImageModal(coverPhoto)"
            />
            <div v-else class="h-full w-full flex items-center justify-center">
              <OccurrenceIcon class="h-14 w-14 text-slate-400" />
            </div>
          </div>

          <div class="p-6 space-y-5">
            <div class="flex flex-wrap items-center gap-2">
              <span class="px-2.5 py-1 text-xs font-semibold rounded-full border" :class="getStatusClass(occurrence.status)">
                {{ getStatusLabel(occurrence.status) }}
              </span>
              <span class="px-2.5 py-1 text-xs font-semibold rounded-full border" :class="getPriorityClass(occurrence.priority)">
                {{ getPriorityLabel(occurrence.priority) }}
              </span>
            </div>

            <div>
              <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ occurrence.title }}</h2>
              <p class="mt-3 text-slate-600 dark:text-slate-300 text-sm whitespace-pre-wrap leading-relaxed">
                {{ occurrence.description || 'Sem descrição cadastrada.' }}
              </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
              <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-3.5 border border-slate-200/60 dark:border-slate-700/60">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Responsável</p>
                <p class="text-sm text-slate-900 dark:text-slate-100 font-medium mt-1">{{ occurrence.user?.name || 'N/A' }}</p>
              </div>
              <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-3.5 border border-slate-200/60 dark:border-slate-700/60">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Criada em</p>
                <p class="text-sm text-slate-900 dark:text-slate-100 font-medium mt-1">{{ formatDate(occurrence.created_at) }}</p>
              </div>
              <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-3.5 border border-slate-200/60 dark:border-slate-700/60">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Atualizada</p>
                <p class="text-sm text-slate-900 dark:text-slate-100 font-medium mt-1">{{ formatDate(occurrence.updated_at || occurrence.created_at) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Fotos -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
          <div class="flex items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Fotos</h2>
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ occurrence.photos?.length || 0 }} arquivo(s)</span>
          </div>

          <div v-if="occurrence.photos?.length" class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <button
              v-for="photo in occurrence.photos"
              :key="photo.id || photo.photo_path"
              type="button"
              class="aspect-video rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 hover:border-indigo-500 transition-colors bg-slate-100 dark:bg-slate-800"
              @click="openImageModal(getPhotoUrl(photo.photo_path))"
            >
              <img :src="getPhotoUrl(photo.photo_path)" :alt="photo.description || occurrence.title" class="h-full w-full object-cover" />
            </button>
          </div>

          <p v-else class="text-xs text-slate-500 dark:text-slate-400">Nenhuma foto cadastrada nesta ocorrência.</p>
        </div>
      </section>

      <!-- Sidebar -->
      <aside class="space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
          <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">Resumo</h2>
          <div class="space-y-3.5 text-sm">
            <div class="flex justify-between items-center gap-4">
              <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</span>
              <span class="px-2 py-0.5 text-xs font-medium rounded-full" :class="getStatusClass(occurrence.status)">{{ getStatusLabel(occurrence.status) }}</span>
            </div>
            <div class="flex justify-between items-center gap-4">
              <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Prioridade</span>
              <span class="px-2 py-0.5 text-xs font-medium rounded-full" :class="getPriorityClass(occurrence.priority)">{{ getPriorityLabel(occurrence.priority) }}</span>
            </div>
            <div class="flex justify-between items-center gap-4">
              <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Fotos</span>
              <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ occurrence.photos?.length || 0 }}</span>
            </div>
            <div class="flex justify-between items-center gap-4">
              <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Comentários</span>
              <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ occurrence.comments?.length || 0 }}</span>
            </div>
          </div>
        </div>

        <!-- Comentários -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
          <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">Comentários</h2>

          <form class="space-y-3 mb-5" @submit.prevent="submitComment">
            <textarea
              v-model="newComment"
              rows="3"
              placeholder="Adicionar comentário..."
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
            ></textarea>
            <p v-if="commentError" class="text-xs text-rose-600 dark:text-rose-400">{{ commentError }}</p>
            <div class="flex justify-end">
              <NeonButton type="submit" size="sm" variant="primary" :loading="submittingComment">
                Comentar
              </NeonButton>
            </div>
          </form>

          <div v-if="occurrence.comments?.length" class="space-y-3">
            <div
              v-for="comment in occurrence.comments"
              :key="comment.id"
              class="bg-slate-50 dark:bg-slate-800/60 rounded-lg border border-slate-200/60 dark:border-slate-700/60 p-3.5"
            >
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="text-xs font-semibold text-slate-900 dark:text-slate-100">{{ comment.user?.name || 'Usuário' }}</p>
                  <p class="text-xs text-slate-400 mt-0.5">{{ formatDate(comment.created_at) }}</p>
                </div>
              </div>
              <p class="text-xs text-slate-600 dark:text-slate-300 mt-2.5 whitespace-pre-wrap leading-relaxed">{{ comment.comment }}</p>
            </div>
          </div>

          <p v-else class="text-xs text-slate-500 dark:text-slate-400">Nenhum comentário cadastrado.</p>
        </div>
      </aside>
    </div>

    <!-- Modal de Imagem Ampliada -->
    <div
      v-if="showImageModal"
      class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4"
      @click.self="closeImageModal"
    >
      <div class="relative max-w-4xl w-full">
        <button
          type="button"
          class="absolute -top-10 right-0 text-white bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5 text-xs hover:bg-slate-700 transition-colors"
          @click="closeImageModal"
        >
          Fechar
        </button>
        <img :src="currentImageModalUrl" alt="Imagem ampliada da ocorrência" class="max-h-[80vh] w-full object-contain rounded-xl border border-slate-700 bg-black" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, h, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { format, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import TopCard from '../ui/TopCard.vue';
import NeonButton from '../NeonButton.vue';
import { apiRequest } from '../../utils/api';

const route = useRoute();
const router = useRouter();

const occurrence = ref(null);
const loading = ref(true);
const error = ref('');
const newComment = ref('');
const submittingComment = ref(false);
const commentError = ref('');
const showImageModal = ref(false);
const currentImageModalUrl = ref('');

const statusOptions = {
  open: 'Aberta',
  in_progress: 'Em Progresso',
  resolved: 'Resolvida',
  closed: 'Fechada',
  reopened: 'Reaberta',
};

const priorityOptions = {
  low: 'Baixa',
  medium: 'Média',
  high: 'Alta',
  urgent: 'Urgente',
};

const OccurrenceIcon = {
  render() {
    return h('svg', {
      ...this.$attrs,
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': 2,
        d: 'M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z',
      }),
    ]);
  },
};

const occurrenceId = computed(() => route.params.id);
const coverPhoto = computed(() => getPhotoUrl(occurrence.value?.photos?.[0]?.photo_path));

const fetchOccurrence = async () => {
  loading.value = true;
  error.value = '';

  try {
    occurrence.value = await apiRequest(`/occurrences/${occurrenceId.value}`);
  } catch (err) {
    error.value = err.message || 'Não foi possível carregar os dados da ocorrência.';
  } finally {
    loading.value = false;
  }
};

const submitComment = async () => {
  const comment = newComment.value.trim();

  if (!comment) {
    commentError.value = 'O comentário não pode estar vazio.';
    return;
  }

  submittingComment.value = true;
  commentError.value = '';

  try {
    const createdComment = await apiRequest(`/occurrences/${occurrenceId.value}/comments`, {
      method: 'POST',
      body: JSON.stringify({ comment }),
    });

    occurrence.value.comments = [...(occurrence.value.comments || []), createdComment];
    newComment.value = '';
  } catch (err) {
    commentError.value = err.message || 'Não foi possível adicionar o comentário.';
  } finally {
    submittingComment.value = false;
  }
};

const goBack = () => {
  router.push({ name: 'OccurrenceIndex' });
};

const goToEdit = () => {
  router.push({ name: 'OccurrenceEdit', params: { id: occurrenceId.value } });
};

const getStatusLabel = (status) => statusOptions[status] || status || 'N/A';
const getPriorityLabel = (priority) => priorityOptions[priority] || priority || 'N/A';

const getStatusClass = (status) => ({
  open: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/50',
  in_progress: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/50',
  resolved: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50',
  closed: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
  reopened: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800/50',
}[status] || 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700');

const getPriorityClass = (priority) => ({
  low: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
  medium: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/50',
  high: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/50',
  urgent: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/50',
}[priority] || 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700');

const formatDate = (dateString) => {
  if (!dateString) {
    return 'N/A';
  }

  try {
    return format(parseISO(dateString), "dd/MM/yyyy 'às' HH:mm", { locale: ptBR });
  } catch {
    return dateString;
  }
};

const getPhotoUrl = (path) => {
  if (!path) {
    return '';
  }

  if (path.startsWith('http') || path.startsWith('/storage/') || path.startsWith('data:')) {
    return path;
  }

  if (path.startsWith('/')) {
    return path;
  }

  return `/storage/${path}`;
};

const openImageModal = (imageUrl) => {
  currentImageModalUrl.value = imageUrl;
  showImageModal.value = true;
};

const closeImageModal = () => {
  showImageModal.value = false;
  currentImageModalUrl.value = '';
};

onMounted(() => {
  fetchOccurrence();
});
</script>
