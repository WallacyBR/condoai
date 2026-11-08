<template>
  <AuthenticatedLayout page-title="Avisos do Condomínio">
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header Section -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-white">Avisos do Condomínio</h1>
          <p class="text-gray-400 mt-1">Gerencie e visualize todos os avisos do seu condomínio</p>
        </div>
        <NeonButton @click="router.push({ name: 'notices.create' })" size="md">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Novo Aviso
        </NeonButton>
      </div>

      <!-- Filter Section -->
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2 border-indigo-500/20">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="flex-1">
            <label for="filter-title" class="block text-sm font-medium text-gray-300 mb-2">
              Filtrar por título
            </label>
            <input
              type="text"
              id="filter-title"
              v-model="filterTitle"
              placeholder="Digite para buscar avisos..."
              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
            />
          </div>
          <div class="flex items-end">
            <NeonButton @click="clearFilters" variant="secondary" size="md">
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Limpar
            </NeonButton>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex justify-center items-center py-12">
        <div class="relative">
          <div class="w-16 h-16 border-4 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin"></div>
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-8 h-8 bg-indigo-500 rounded-full animate-pulse"></div>
          </div>
        </div>
        <p class="text-gray-400 ml-4">Carregando avisos...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="errors.general" class="bg-gradient-to-r from-red-900/50 to-red-800/50 border border-red-500/50 rounded-2xl p-6">
        <div class="flex items-center">
          <svg class="h-6 w-6 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-red-300">{{ errors.general }}</p>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="notices.length === 0" class="text-center py-16">
        <div class="mx-auto h-24 w-24 text-gray-600 mb-4">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-300 mb-2">Nenhum aviso encontrado</h3>
        <p class="text-gray-500 mb-6">Não há avisos para exibir no momento.</p>
        <NeonButton @click="router.push({ name: 'notices.create' })">
          Criar Primeiro Aviso
        </NeonButton>
      </div>

      <!-- Notices Grid -->
      <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="notice in notices"
          :key="notice.id"
          class="group bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2 border-transparent hover:border-indigo-500/50 transition-all duration-300 hover:transform hover:scale-105"
        >
          <!-- Notice Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-white mb-2 line-clamp-2 group-hover:text-indigo-300 transition-colors">
                {{ notice.title }}
              </h3>
              <div class="flex items-center text-sm text-gray-400 space-x-4">
                <span class="flex items-center">
                  <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ formatDate(notice.created_at) }}
                </span>
                <span class="flex items-center" v-if="notice.user?.name">
                  <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  {{ notice.user.name }}
                </span>
              </div>
            </div>
            
            <!-- Notice Type Badge -->
            <div class="ml-4">
              <span v-if="notice.send_to_all" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-300 border border-green-700">
                <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Geral
              </span>
              <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-900 text-blue-300 border border-blue-700">
                <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Específico
              </span>
            </div>
          </div>

          <!-- Notice Body Preview -->
          <div class="mb-6">
            <p class="text-gray-300 text-sm line-clamp-3">
              {{ notice.body }}
            </p>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-between pt-4 border-t border-gray-700">
            <NeonButton 
              @click="viewNotice(notice.id)" 
              variant="secondary" 
              size="sm"
            >
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              Ver Detalhes
            </NeonButton>
            
            <button 
              @click="confirmDelete(notice.id)"
              class="p-2 text-red-400 hover:text-red-300 hover:bg-red-900/30 rounded-lg transition-colors"
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
      <div v-if="pagination.total > pagination.per_page && !isLoading" class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6">
        <div class="text-sm text-gray-400">
          Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} resultados
        </div>
        
        <div class="flex items-center space-x-2">
          <NeonButton
            @click="fetchNotices(pagination.current_page - 1)"
            :disabled="!pagination.prev_page_url"
            variant="secondary"
            size="sm"
          >
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Anterior
          </NeonButton>
          
          <div class="flex space-x-1">
            <button
              v-for="pageNumber in simplePageRange"
              :key="pageNumber"
              @click="fetchNotices(pageNumber)"
              :class="[
                'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                pageNumber === pagination.current_page
                  ? 'bg-indigo-600 text-white border-2 border-indigo-500'
                  : 'text-gray-300 hover:text-white hover:bg-gray-700 border-2 border-transparent'
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
            <svg class="h-4 w-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </NeonButton>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-black bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-2 border-red-500/50">
          <div class="px-6 pt-6 pb-4">
            <div class="flex items-start">
              <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-900/50 border-2 border-red-500">
                <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">
                  Confirmar Exclusão
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-300">
                    Tem certeza que deseja excluir este aviso? Esta ação não pode ser desfeita e o aviso será removido permanentemente.
                  </p>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-gray-800/50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-3">
            <NeonButton @click="deleteNotice" variant="danger" size="md">
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Excluir Aviso
            </NeonButton>
            <NeonButton @click="cancelDelete" variant="secondary" size="md">
              Cancelar
            </NeonButton>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import NeonButton from '@/components/NeonButton.vue';
import { ref, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/utils/api';

const router = useRouter();

const notices = ref([]);
const isLoading = ref(true);
const errors = ref({});
const filterTitle = ref('');
const showDeleteModal = ref(false);
const noticeToDeleteId = ref(null);

const pagination = ref({
    current_page: 1,
    data: [],
    first_page_url: '',
    from: 0,
    last_page: 1,
    last_page_url: '',
    links: [],
    next_page_url: null,
    path: '',
    per_page: 15,
    prev_page_url: null,
    to: 0,
    total: 0,
});

async function fetchNotices(page = 1) {
  isLoading.value = true;
  errors.value = {};
  try {
    const params = { page, per_page: pagination.value.per_page };
    if (filterTitle.value) {
      params.title = filterTitle.value;
    }
    const response = await api.get('/notices', { params });
    notices.value = response.data.data;
    pagination.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar avisos:', error);
    errors.value.general = 'Não foi possível carregar os avisos. Tente novamente mais tarde.';
  } finally {
    isLoading.value = false;
  }
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateString).toLocaleDateString('pt-BR', options);
}

function viewNotice(id) {
  router.push({ name: 'notices.view', params: { id } });
}

function confirmDelete(id) {
    noticeToDeleteId.value = id;
    showDeleteModal.value = true;
}

function cancelDelete() {
    showDeleteModal.value = false;
    noticeToDeleteId.value = null;
}

async function deleteNotice() {
    if (!noticeToDeleteId.value) return;
    try {
        await api.delete(`/notices/${noticeToDeleteId.value}`);
        showDeleteModal.value = false;
        fetchNotices(pagination.value.current_page);
        // TODO: Adicionar notificação de sucesso
    } catch (error) {
        console.error('Erro ao excluir aviso:', error);
        showDeleteModal.value = false;
        errors.value.general = 'Não foi possível excluir o aviso.';
    } finally {
        noticeToDeleteId.value = null;
    }
}

function clearFilters() {
    filterTitle.value = '';
    fetchNotices(1);
}

const simplePageRange = computed(() => {
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;
    const delta = 2;
    const left = current - delta;
    const right = current + delta + 1;
    const range = [];
    const rangeWithDots = [];
    let l;

    for (let i = 1; i <= last; i++) {
        if (i === 1 || i === last || (i >= left && i < right)) {
            range.push(i);
        }
    }

    for (let i of range) {
        if (l) {
            if (i - l === 2) {
                rangeWithDots.push(l + 1);
            } else if (i - l !== 1) {
                rangeWithDots.push('...');
            }
        }
        rangeWithDots.push(i);
        l = i;
    }
    return rangeWithDots.filter(p => p !== '...');
});

watch(filterTitle, (newValue, oldValue) => {
  if (newValue !== oldValue) {
    // TODO: Adicionar debounce
    fetchNotices(1);
  }
});

onMounted(() => {
  fetchNotices();
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
