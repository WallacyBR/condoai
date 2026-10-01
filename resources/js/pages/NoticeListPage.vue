<template>
  <AuthenticatedLayout page-title="Avisos do Condomínio">
    <div class="space-y-6">
      <TopCard title="Avisos" :icon="NoticeIcon" subtitle="Gerencie e visualize todos os avisos do seu condomínio">
        <template #actions>
          <NeonButton @click="router.push({ name: 'notices.create' })" size="md" variant="primary">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Novo Aviso
          </NeonButton>
        </template>
      </TopCard>

      <!-- Barra de Filtros -->
      <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm p-5 border border-slate-200 dark:border-slate-800">
        <div class="flex flex-col sm:flex-row gap-4 items-end">
          <div class="flex-1 w-full">
            <label for="filter-title" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
              Filtrar por título
            </label>
            <input
              type="text"
              id="filter-title"
              v-model="filterTitle"
              placeholder="Digite para buscar avisos..."
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
            />
          </div>
          <div>
            <NeonButton @click="clearFilters" variant="secondary" size="md">
              <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Limpar
            </NeonButton>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex justify-center items-center py-16">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-slate-300 border-t-indigo-600 dark:border-slate-700 dark:border-t-indigo-400"></div>
        <p class="text-xs text-slate-500 dark:text-slate-400 ml-3">Carregando avisos...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="errors.general" class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 rounded-xl p-4 shadow-sm">
        <div class="flex items-center">
          <svg class="h-5 w-5 text-rose-600 dark:text-rose-400 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-xs text-rose-700 dark:text-rose-300 font-medium">{{ errors.general }}</p>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="notices.length === 0" class="text-center py-16 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-8 shadow-sm">
        <div class="mx-auto h-12 w-12 text-slate-400 mb-3">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">Nenhum aviso encontrado</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-5">Não há avisos para exibir no momento.</p>
        <NeonButton @click="router.push({ name: 'notices.create' })" variant="primary" size="sm">
          Criar Primeiro Aviso
        </NeonButton>
      </div>

      <!-- Notices Grid -->
      <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="notice in notices"
          :key="notice.id"
          class="bg-white dark:bg-slate-900 rounded-xl shadow-sm p-5 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-colors flex flex-col justify-between"
        >
          <div>
            <!-- Notice Header -->
            <div class="flex items-start justify-between gap-3 mb-3">
              <div class="flex-1 min-w-0">
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-1 line-clamp-2">
                  {{ notice.title }}
                </h3>
                <div class="flex flex-wrap items-center text-xs text-slate-500 dark:text-slate-400 gap-x-3 gap-y-1">
                  <span class="flex items-center">
                    <svg class="h-3.5 w-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ formatDate(notice.created_at) }}
                  </span>
                  <span class="flex items-center" v-if="notice.user?.name">
                    <svg class="h-3.5 w-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ notice.user.name }}
                  </span>
                </div>
              </div>
              
              <!-- Notice Type Badge -->
              <div class="shrink-0">
                <span 
                  v-if="notice.send_to_all" 
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50"
                >
                  Geral
                </span>
                <span 
                  v-else 
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/50"
                >
                  Específico
                </span>
              </div>
            </div>

            <!-- Notice Body Preview -->
            <div class="mb-5">
              <p class="text-slate-600 dark:text-slate-300 text-xs line-clamp-3 leading-relaxed">
                {{ notice.body }}
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-between pt-3.5 border-t border-slate-100 dark:border-slate-800">
            <NeonButton 
              @click="viewNotice(notice.id)" 
              variant="secondary" 
              size="sm"
            >
              <svg class="h-3.5 w-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              Ver Detalhes
            </NeonButton>
            
            <button 
              @click="confirmDelete(notice.id)"
              class="p-1.5 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 rounded-lg transition-colors"
              title="Excluir aviso"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > pagination.per_page && !isLoading" class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-4 border-t border-slate-200 dark:border-slate-800">
        <div class="text-xs text-slate-500 dark:text-slate-400">
          Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} resultados
        </div>
        
        <div class="flex items-center space-x-2">
          <NeonButton
            @click="fetchNotices(pagination.current_page - 1)"
            :disabled="!pagination.prev_page_url"
            variant="secondary"
            size="sm"
          >
            Anterior
          </NeonButton>
          
          <div class="flex space-x-1">
            <button
              v-for="pageNumber in simplePageRange"
              :key="pageNumber"
              @click="fetchNotices(pageNumber)"
              :class="[
                'px-2.5 py-1 text-xs font-semibold rounded-lg transition-colors',
                pageNumber === pagination.current_page
                  ? 'bg-indigo-600 text-white'
                  : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'
              ]"
            >
              {{ pageNumber }}
            </button>
          </div>
          
          <NeonButton
            @click="fetchNotices(pagination.current_page + 1)"
            :disabled="!pagination.next_page_url"
            variant="secondary"
            size="sm"
          >
            Próximo
          </NeonButton>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-center justify-center min-h-screen p-4 text-center">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" @click="cancelDelete"></div>
        
        <div class="relative bg-white dark:bg-slate-900 rounded-xl text-left overflow-hidden shadow-xl p-6 sm:max-w-md w-full border border-slate-200 dark:border-slate-800">
          <div class="flex items-start">
            <div class="flex items-center justify-center h-10 w-10 rounded-full bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 shrink-0 mr-3">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div>
              <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100" id="modal-title">
                Confirmar Exclusão
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Tem certeza que deseja excluir este aviso? Esta ação não pode ser desfeita e o aviso será removido permanentemente.
              </p>
            </div>
          </div>
          
          <div class="mt-6 flex justify-end gap-2.5">
            <NeonButton @click="cancelDelete" variant="secondary" size="sm">
              Cancelar
            </NeonButton>
            <NeonButton @click="deleteNotice" variant="danger" size="sm">
              Excluir Aviso
            </NeonButton>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch, h } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import TopCard from '@/components/ui/TopCard.vue';
import NeonButton from '@/components/NeonButton.vue';
import api from '@/utils/api';
import { useToast } from '@/composables/useToast';

const router = useRouter();
const toast = useToast();

const NoticeIcon = {
  render() {
    return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' })
    ]);
  }
};

const notices = ref([]);
const isLoading = ref(true);
const errors = reactive({
  general: null
});

const filterTitle = ref('');
let debounceTimer = null;

const pagination = ref({
  current_page: 1,
  data: [],
  from: 0,
  last_page: 1,
  next_page_url: null,
  per_page: 9,
  prev_page_url: null,
  to: 0,
  total: 0
});

const showDeleteModal = ref(false);
const noticeToDelete = ref(null);

const simplePageRange = computed(() => {
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;
  const delta = 2;
  const range = [];
  
  for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
    range.push(i);
  }
  
  if (current - delta > 2) {
    range.unshift('...');
  }
  if (current + delta < last - 1) {
    range.push('...');
  }
  
  range.unshift(1);
  if (last > 1) {
    range.push(last);
  }
  
  return range.filter((x, i, a) => a.indexOf(x) === i);
});

async function fetchNotices(page = 1) {
  isLoading.value = true;
  errors.general = null;
  
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      title: filterTitle.value || undefined
    };
    
    const response = await api.get('/notices', { params });
    notices.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      from: response.data.from,
      last_page: response.data.last_page,
      next_page_url: response.data.next_page_url,
      per_page: response.data.per_page,
      prev_page_url: response.data.prev_page_url,
      to: response.data.to,
      total: response.data.total
    };
  } catch (error) {
    console.error('Erro ao buscar avisos:', error);
    errors.general = 'Falha ao carregar avisos. Tente novamente mais tarde.';
  } finally {
    isLoading.value = false;
  }
}

watch(filterTitle, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchNotices(1);
  }, 500);
});

function clearFilters() {
  filterTitle.value = '';
  fetchNotices(1);
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('pt-BR', { 
    day: '2-digit', 
    month: '2-digit', 
    year: 'numeric' 
  });
}

function viewNotice(id) {
  router.push({ name: 'notices.view', params: { id } });
}

function confirmDelete(id) {
  noticeToDelete.value = id;
  showDeleteModal.value = true;
}

function cancelDelete() {
  noticeToDelete.value = null;
  showDeleteModal.value = false;
}

async function deleteNotice() {
  if (!noticeToDelete.value) return;
  
  try {
    await api.delete(`/notices/${noticeToDelete.value}`);
    toast.success('Aviso excluído com sucesso!');
    showDeleteModal.value = false;
    noticeToDelete.value = null;
    fetchNotices(pagination.value.current_page);
  } catch (error) {
    console.error('Erro ao excluir aviso:', error);
    toast.error('Erro ao excluir aviso.');
  }
}

onMounted(() => {
  fetchNotices();
});
</script>
