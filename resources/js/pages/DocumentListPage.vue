<template>
  <AuthenticatedLayout page-title="Documentos do Condomínio">
    <div class="space-y-6">
      <TopCard title="Documentos do Condomínio" :icon="DocIcon" />

      <!-- Filtros -->
      <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
              Nome do Arquivo
            </label>
            <input 
              type="text" 
              v-model="filters.file_name" 
              placeholder="Filtrar por nome do arquivo..."
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
              Título do Aviso
            </label>
            <input 
              type="text" 
              v-model="filters.notice_title" 
              placeholder="Filtrar por título do aviso..."
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
            />
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">
              Data do Aviso
            </label>
            <input 
              type="date" 
              v-model="filters.notice_date"
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
            />
          </div>
        </div>
      </div>

      <!-- Tabela e Estados -->
      <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div v-if="isLoading" class="text-center py-16">
          <div class="animate-spin rounded-full h-8 w-8 border-2 border-slate-300 border-t-indigo-600 dark:border-slate-700 dark:border-t-indigo-400 mx-auto"></div>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-3">Carregando documentos...</p>
        </div>

        <div v-else-if="error" class="p-6">
          <div class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 rounded-xl p-4 text-xs text-rose-700 dark:text-rose-300">
            {{ error }}
          </div>
        </div>

        <div v-else-if="documents.length === 0" class="text-center py-16 p-6">
          <svg class="h-10 w-10 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Nenhum documento encontrado.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
            <thead class="bg-slate-50 dark:bg-slate-800/60">
              <tr>
                <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nome do Arquivo</th>
                <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tipo</th>
                <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aviso Original</th>
                <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Data do Aviso</th>
                <th scope="col" class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-sm">
              <tr 
                v-for="doc in documents" 
                :key="doc.id"
                class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors"
              >
                <td class="px-5 py-4 whitespace-nowrap font-medium text-slate-900 dark:text-slate-100">{{ doc.original_name }}</td>
                <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">{{ doc.file_type }}</td>
                <td class="px-5 py-4 whitespace-nowrap text-xs">
                  <router-link 
                    v-if="doc.notice_id" 
                    :to="{ name: 'notices.view', params: { id: doc.notice_id } }" 
                    class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium"
                  >
                    {{ doc.notice_title || `Aviso ID: ${doc.notice_id}` }}
                  </router-link>
                  <span v-else class="text-slate-400">N/A</span>
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">{{ formatDate(doc.notice_created_at) }}</td>
                <td class="px-5 py-4 whitespace-nowrap text-right">
                  <a 
                    :href="getAttachmentUrl(doc.file_path)" 
                    target="_blank"
                    class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/60 dark:text-indigo-300 text-xs font-semibold rounded-lg transition-colors border border-indigo-200 dark:border-indigo-800/50"
                  >
                    <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Ver / Baixar
                  </a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginação -->
        <div v-if="pagination.total > pagination.per_page && !isLoading" class="p-4 border-t border-slate-100 dark:border-slate-800 flex flex-wrap justify-between items-center gap-3">
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} resultados
          </p>
          <div class="flex items-center gap-2">
            <button 
              @click="fetchDocuments(pagination.current_page - 1)"
              :disabled="!pagination.prev_page_url"
              class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-50 transition-colors shadow-sm"
            >
              Anterior
            </button>
            <button 
              @click="fetchDocuments(pagination.current_page + 1)"
              :disabled="!pagination.next_page_url"
              class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-50 transition-colors shadow-sm"
            >
              Próximo
            </button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import { ref, reactive, onMounted, watch, h } from 'vue';
import api from '@/utils/api';
import TopCard from '@/components/ui/TopCard.vue';

const DocIcon = {
  render() {
    return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' })
    ]);
  }
};

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
    for (const key in params) {
      if (params[key] === '' || params[key] === null) {
        delete params[key];
      }
    }
    const response = await api.get('/documents', { params });
    documents.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      from: response.data.from,
      last_page: response.data.last_page,
      next_page_url: response.data.next_page_url,
      per_page: response.data.per_page,
      prev_page_url: response.data.prev_page_url,
      to: response.data.to,
      total: response.data.total,
    };
  } catch (err) {
    console.error('Erro ao buscar documentos:', err);
    error.value = 'Falha ao carregar documentos. Tente novamente mais tarde.';
  } finally {
    isLoading.value = false;
  }
}

watch(filters, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchDocuments(1);
  }, 500);
}, { deep: true });

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function getAttachmentUrl(filePath) {
  return `/storage/${filePath}`;
}

onMounted(() => {
  fetchDocuments();
});
</script>
