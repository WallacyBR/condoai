<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Documentos do Condomínio</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">

            <!-- Filtros -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
              <input type="text" v-model="filters.file_name" placeholder="Filtrar por nome do arquivo..."
                     class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-600 dark:text-gray-200">
              <input type="text" v-model="filters.notice_title" placeholder="Filtrar por título do aviso..."
                     class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-600 dark:text-gray-200">
              <input type="date" v-model="filters.notice_date"
                     class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-600 dark:text-gray-200">
            </div>

            <div v-if="isLoading" class="text-center py-4">
              <p class="text-gray-500 dark:text-gray-400">Carregando documentos...</p>
            </div>
            <div v-else-if="error" class="text-red-600 dark:text-red-400 p-4 border border-red-400 rounded">
                {{ error }}
            </div>
            <div v-else-if="documents.length === 0" class="text-center py-4">
              <p class="text-gray-500 dark:text-gray-400">Nenhum documento encontrado.</p>
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nome do Arquivo</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aviso Original</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data do Aviso</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="doc in documents" :key="doc.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ doc.original_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ doc.file_type }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                      <router-link v-if="doc.notice_id" :to="{ name: 'notices.view', params: { id: doc.notice_id } }" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200">
                        {{ doc.notice_title || `Aviso ID: ${doc.notice_id}` }}
                      </router-link>
                      <span v-else>N/A</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ formatDate(doc.notice_created_at) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <a :href="getAttachmentUrl(doc.file_path)" target="_blank"
                         class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold rounded-md transition-colors">
                        Ver / Baixar
                      </a>
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
                    <button @click="fetchDocuments(pagination.current_page - 1)"
                            :disabled="!pagination.prev_page_url"
                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700 disabled:opacity-50">
                        Anterior
                    </button>
                    <button @click="fetchDocuments(pagination.current_page + 1)"
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
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import { ref, reactive, onMounted, watch } from 'vue';
import api from '@/utils/api';
// import documentService from '@/services/documentService'; // Idealmente

const documents = ref([]);
const isLoading = ref(true);
const error = ref(null);

const filters = reactive({
  file_name: '',
  notice_title: '',
  notice_date: ''
});

const pagination = ref({
    current_page: 1,
    data: [],
    from: 0,
    last_page: 1,
    next_page_url: null,
    per_page: 15,
    prev_page_url: null,
    to: 0,
    total: 0,
});

// Debounce para filtros
let debounceTimer = null;

async function fetchDocuments(page = 1) {
  isLoading.value = true;
  error.value = null;
  try {
    const params = {
        page,
        per_page: pagination.value.per_page,
        ...filters
    };
    // Remover filtros vazios
    for (const key in params) {
        if (params[key] === '' || params[key] === null) {
            delete params[key];
        }
    }

    // const response = await documentService.getDocuments(params);
    const response = await api.get('/api/documents', { params });
    documents.value = response.data.data;
    pagination.value = response.data;
  } catch (err) {
    console.error('Erro ao buscar documentos:', err);
    error.value = 'Não foi possível carregar os documentos. Tente novamente mais tarde.';
  } finally {
    isLoading.value = false;
  }
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: 'short', day: 'numeric' }; // Simplificado para data
  return new Date(dateString).toLocaleDateString('pt-BR', options);
}

function getAttachmentUrl(filePath) {
  if (!filePath) return '#';
  return `/storage/${filePath}`;
}

watch(filters, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchDocuments(1); // Resetar para a primeira página ao filtrar
    }, 500); // 500ms debounce
}, { deep: true });


onMounted(() => {
  fetchDocuments();
});
</script>

<style scoped>
/* Estilos podem ser adicionados aqui se necessário */
</style>
