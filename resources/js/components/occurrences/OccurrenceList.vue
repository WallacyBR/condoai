<template>
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Lista de Ocorrências</h1>

    <!-- Filtros -->
    <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label for="statusFilter" class="block text-sm font-medium text-gray-700">Status</label>
        <select id="statusFilter" v-model="filters.status" @change="fetchOccurrences" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
          <option value="">Todos</option>
          <option v-for="(label, value) in statusOptions" :key="value" :value="value">{{ label }}</option>
        </select>
      </div>
      <div>
        <label for="priorityFilter" class="block text-sm font-medium text-gray-700">Prioridade</label>
        <select id="priorityFilter" vmodel="filters.priority" @change="fetchOccurrences" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
          <option value="">Todas</option>
          <option v-for="(label, value) in priorityOptions" :key="value" :value="value">{{ label }}</option>
        </select>
      </div>
      <div class="flex items-end">
        <button @click="clearFilters" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
          Limpar Filtros
        </button>
      </div>
    </div>

    <div class="flex justify-end mb-4">
      <router-link :to="{ name: 'OccurrenceCreate' }" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        Nova Ocorrência
      </router-link>
    </div>

    <div v-if="loading" class="text-center">
      <p>Carregando ocorrências...</p>
    </div>
    <div v-else-if="error" class="text-center text-red-500">
      <p>Erro ao carregar ocorrências: {{ error }}</p>
    </div>
    <div v-else>
      <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioridade</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado por</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado em</th>
              <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Ações</span>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="occurrences.length === 0">
              <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Nenhuma ocorrência encontrada.</td>
            </tr>
            <tr v-for="occurrence in occurrences" :key="occurrence.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ occurrence.title }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <span :class="getStatusClass(occurrence.status)">
                  {{ getStatusLabel(occurrence.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <span :class="getPriorityClass(occurrence.priority)">
                  {{ getPriorityLabel(occurrence.priority) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ occurrence.user?.name || 'N/A' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(occurrence.created_at) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <router-link :to="{ name: 'OccurrenceShow', params: { id: occurrence.id } }" class="text-indigo-600 hover:text-indigo-900 mr-2">Ver</router-link>
                <!-- <router-link :to="{ name: 'OccurrenceEdit', params: { id: occurrence.id } }" class="text-yellow-600 hover:text-yellow-900">Editar</router-link> -->
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginação -->
      <div v-if="pagination.total > pagination.per_page" class="mt-4 flex justify-between items-center">
        <div>
          <p class="text-sm text-gray-700">
            Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} resultados
          </p>
        </div>
        <div>
          <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
            >
              Anterior
            </button>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
            >
              Próximo
            </button>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue';
import api from '../../utils/api';
import { format, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';

const occurrences = ref([]);
const loading = ref(true);
const error = ref(null);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0,
});

const filters = reactive({
  status: '',
  priority: '',
});

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

const fetchOccurrences = async (page = 1) => {
  loading.value = true;
  error.value = null;
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      ...filters,
    };
    // Remove empty filters
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null) {
        delete params[key];
      }
    });

    const response = await api.get('/occurrences', { params });
    occurrences.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      from: response.data.from,
      to: response.data.to,
    };
  } catch (err) {
    console.error("Erro ao buscar ocorrências:", err);
    error.value = err.response?.data?.message || 'Não foi possível carregar os dados.';
  } finally {
    loading.value = false;
  }
};

const clearFilters = () => {
  filters.status = '';
  filters.priority = '';
  fetchOccurrences();
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchOccurrences(page);
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  try {
    return format(parseISO(dateString), "dd/MM/yyyy HH:mm", { locale: ptBR });
  } catch (e) {
    console.error("Erro ao formatar data:", e);
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

onMounted(() => {
  fetchOccurrences();
});
</script>

<style scoped>
/* Estilos adicionais podem ser adicionados aqui, se necessário */
</style>
