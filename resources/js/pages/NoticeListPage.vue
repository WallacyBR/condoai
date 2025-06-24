<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Listagem de Avisos</h2>
        <router-link :to="{ name: 'notices.create' }"
                     class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150">
          Novo Aviso
        </router-link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
            <!-- Filtros (opcional) -->
            <div class="mb-4">
              <input type="text" v-model="filterTitle" placeholder="Filtrar por título..."
                     class="mt-1 block w-full md:w-1/3 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-600 dark:text-gray-200">
            </div>

            <div v-if="isLoading" class="text-center py-4">
              <p class="text-gray-500 dark:text-gray-400">Carregando avisos...</p>
              <!-- Pode adicionar um spinner aqui -->
            </div>
            <div v-else-if="errors.general" class="text-red-600 dark:text-red-400 p-4 border border-red-400 rounded">
                {{ errors.general }}
            </div>
            <div v-else-if="notices.length === 0" class="text-center py-4">
              <p class="text-gray-500 dark:text-gray-400">Nenhum aviso encontrado.</p>
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Título</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Criado por</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data de Criação</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="notice in notices" :key="notice.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ notice.title }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ notice.user?.name || 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ formatDate(notice.created_at) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <router-link :to="{ name: 'notices.view', params: { id: notice.id } }" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200 mr-3">
                        Ver
                      </router-link>
                      <button @click="confirmDelete(notice.id)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200">
                        Excluir
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Paginação -->
            <div v-if="pagination.total > pagination.per_page && !isLoading" class="mt-6 flex justify-between items-center">
                <p class="text-sm text-gray-700 dark:text-gray-400">
                    Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} resultados
                </p>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <button @click="fetchNotices(pagination.current_page - 1)"
                            :disabled="!pagination.prev_page_url"
                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700 disabled:opacity-50">
                        Anterior
                    </button>
                    <!-- Números da página (simplificado) -->
                    <button v-for="pageNumber in simplePageRange" :key="pageNumber"
                            @click="fetchNotices(pageNumber)"
                            :class="['relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium',
                                     pageNumber === pagination.current_page ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600 dark:bg-indigo-900 dark:border-indigo-700 dark:text-white' : 'bg-white dark:bg-gray-800 text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700']">
                        {{ pageNumber }}
                    </button>
                    <button @click="fetchNotices(pagination.current_page + 1)"
                            :disabled="!pagination.next_page_url"
                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700 disabled:opacity-50">
                        Próximo
                    </button>
                </nav>
            </div>

          </div>
        </div>
      </div>
    </div>
     <!-- Modal de Confirmação de Exclusão (simplificado) -->
    <div v-if="showDeleteModal" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                            <!-- Heroicon name: outline/exclamation -->
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">Excluir Aviso</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Tem certeza que deseja excluir este aviso? Esta ação não pode ser desfeita.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="deleteNotice" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Excluir
                    </button>
                    <button @click="showDeleteModal = false; noticeToDeleteId = null;" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-500 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import { ref, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/utils/api'; // Supondo que api.js configure o axios globalmente
// import noticeService from '@/services/noticeService'; // Idealmente

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
    // const response = await noticeService.getNotices(params);
    const response = await api.get('/api/notices', { params });
    notices.value = response.data.data;
    pagination.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar avisos:', error);
    errors.value.general = 'Não foi possível carregar os avisos. Tente novamente mais tarde.';
    // Tratar erro, talvez mostrar uma notificação para o usuário
  } finally {
    isLoading.value = false;
  }
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateString).toLocaleDateString('pt-BR', options);
}

function confirmDelete(id) {
    noticeToDeleteId.value = id;
    showDeleteModal.value = true;
}

async function deleteNotice() {
    if (!noticeToDeleteId.value) return;
    try {
        // await noticeService.deleteNotice(noticeToDeleteId.value);
        await api.delete(`/api/notices/${noticeToDeleteId.value}`);
        showDeleteModal.value = false;
        // Atualizar a lista após a exclusão
        fetchNotices(pagination.value.current_page);
        // Idealmente, mostrar notificação de sucesso
    } catch (error) {
        console.error('Erro ao excluir aviso:', error);
        showDeleteModal.value = false;
        // Mostrar erro para o usuário
        errors.value.general = 'Não foi possível excluir o aviso.';
    } finally {
        noticeToDeleteId.value = null;
    }
}

const simplePageRange = computed(() => {
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;
    const delta = 2; // Quantos links antes e depois da página atual
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
                rangeWithDots.push('...'); // Placeholder para "..."
            }
        }
        rangeWithDots.push(i);
        l = i;
    }
    return rangeWithDots.filter(p => p !== '...'); // Simplificando para não ter "..." por enquanto
});


watch(filterTitle, (newValue, oldValue) => {
  if (newValue !== oldValue) {
    // Adicionar debounce para evitar muitas chamadas API
    // setTimeout(() => fetchNotices(1), 500);
    fetchNotices(1); // Simples por agora
  }
});

onMounted(() => {
  fetchNotices();
});
</script>

<style scoped>
/* Estilos para tabela e paginação podem ser ajustados conforme necessário */
</style>
