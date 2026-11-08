<template>
  <div class="space-y-6">
    <TopCard
      title="Ocorrências"
      subtitle="Acompanhe, filtre e registre chamados do condomínio"
      :icon="OccurrenceIcon"
    >
      <template #actions>
        <NeonButton @click="goToCreate" size="md" variant="primary">
          <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Nova Ocorrência
        </NeonButton>
      </template>
    </TopCard>

    <!-- Indicadores de Resumo -->
    <div v-if="!loading" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
      <div
        v-for="item in summaryCards"
        :key="item.label"
        class="bg-white dark:bg-slate-900 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm transition-colors hover:border-slate-300 dark:hover:border-slate-700"
      >
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ item.label }}</p>
            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100 mt-1">{{ item.value }}</p>
          </div>
          <div class="h-10 w-10 rounded-lg flex items-center justify-center shrink-0" :class="item.iconClass">
            <component :is="item.icon" class="h-5 w-5" />
          </div>
        </div>
      </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="flex flex-wrap items-center justify-between gap-4 p-4 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
      <div class="flex flex-wrap gap-3 items-center flex-1">
        <div class="relative flex-1 min-w-[240px]">
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por título, descrição ou responsável..."
            class="w-full pl-9 pr-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
          />
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>

        <select
          v-model="filters.status"
          @change="fetchOccurrences(1)"
          class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
        >
          <option value="">Todos os status</option>
          <option v-for="option in statusOptions" :key="option.value" :value="option.value">
            {{ option.label }}
          </option>
        </select>

        <select
          v-model="filters.priority"
          @change="fetchOccurrences(1)"
          class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
        >
          <option value="">Todas as prioridades</option>
          <option v-for="option in priorityOptions" :key="option.value" :value="option.value">
            {{ option.label }}
          </option>
        </select>

        <NeonButton
          v-if="hasActiveFilters"
          @click="clearFilters"
          size="sm"
          variant="secondary"
        >
          <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Limpar
        </NeonButton>
      </div>

      <div v-if="!loading && occurrences.length" class="text-xs font-medium text-slate-500 dark:text-slate-400">
        {{ filteredOccurrences.length }} de {{ totalOccurrences }} ocorrências
      </div>
    </div>

    <!-- Tags de Filtros Ativos -->
    <div v-if="hasActiveFilters" class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl p-3.5">
      <div class="flex flex-wrap items-center gap-2 text-xs">
        <span class="text-slate-700 dark:text-slate-300 font-semibold">Filtros ativos:</span>
        <span v-if="search" class="px-2.5 py-1 rounded-full bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
          Busca: "{{ search }}"
        </span>
        <span v-if="filters.status" class="px-2.5 py-1 rounded-full bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
          Status: {{ getStatusLabel(filters.status) }}
        </span>
        <span v-if="filters.priority" class="px-2.5 py-1 rounded-full bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
          Prioridade: {{ getPriorityLabel(filters.priority) }}
        </span>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center items-center py-16">
      <div class="animate-spin rounded-full h-8 w-8 border-2 border-slate-300 border-t-indigo-600 dark:border-slate-700 dark:border-t-indigo-400"></div>
    </div>

    <div v-else-if="error" class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 rounded-xl p-4 text-sm text-rose-700 dark:text-rose-300 shadow-sm">
      {{ error }}
    </div>

    <!-- Vazio -->
    <div v-else-if="filteredOccurrences.length === 0" class="text-center py-16 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-8 shadow-sm">
      <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728M12 3a9 9 0 110 18 9 9 0 010-18z" />
      </svg>
      <h3 class="mt-3 text-sm font-semibold text-slate-900 dark:text-slate-100">Nenhuma ocorrência encontrada</h3>
      <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
        {{ hasActiveFilters ? 'Tente ajustar os filtros de busca.' : 'Use o botão "Nova Ocorrência" para registrar o primeiro chamado.' }}
      </p>
    </div>

    <!-- Grid de Ocorrências -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
      <article
        v-for="occurrence in filteredOccurrences"
        :key="occurrence.id"
        class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-colors overflow-hidden flex flex-col"
      >
        <div class="h-40 bg-slate-100 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 overflow-hidden flex items-center justify-center">
          <img
            v-if="getCoverPhoto(occurrence)"
            :src="getCoverPhoto(occurrence)"
            :alt="`Foto da ocorrência ${occurrence.title}`"
            class="h-full w-full object-cover"
          />
          <div v-else class="h-full w-full flex items-center justify-center">
            <OccurrenceIcon class="h-10 w-10 text-slate-400" />
          </div>
        </div>

        <div class="p-5 flex-1 flex flex-col justify-between">
          <div>
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 truncate">{{ occurrence.title }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ occurrence.user?.name || 'Responsável não informado' }}</p>
              </div>
              <div class="flex flex-col items-end gap-1.5 shrink-0">
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full border" :class="getStatusClass(occurrence.status)">
                  {{ getStatusLabel(occurrence.status) }}
                </span>
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full border" :class="getPriorityClass(occurrence.priority)">
                  {{ getPriorityLabel(occurrence.priority) }}
                </span>
              </div>
            </div>

            <p class="text-xs text-slate-600 dark:text-slate-300 mt-3 line-clamp-3 leading-relaxed">
              {{ occurrence.description || 'Sem descrição cadastrada.' }}
            </p>

            <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
              <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-2.5 border border-slate-200/60 dark:border-slate-700/60">
                <p class="text-slate-400 font-medium text-[11px] uppercase tracking-wider">Criada em</p>
                <p class="text-slate-800 dark:text-slate-200 font-medium mt-0.5">{{ formatDate(occurrence.created_at) }}</p>
              </div>
              <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-2.5 border border-slate-200/60 dark:border-slate-700/60">
                <p class="text-slate-400 font-medium text-[11px] uppercase tracking-wider">Atualizada</p>
                <p class="text-slate-800 dark:text-slate-200 font-medium mt-0.5">{{ formatDate(occurrence.updated_at || occurrence.created_at) }}</p>
              </div>
            </div>

            <div class="mt-3.5 flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
              <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-[11px] font-medium">
                {{ occurrence.photos?.length || 0 }} foto(s)
              </span>
              <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-[11px] font-medium">
                {{ occurrence.comments?.length || 0 }} comentário(s)
              </span>
            </div>
          </div>

          <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <NeonButton @click="goToShow(occurrence)" size="sm" variant="secondary">
              Ver
            </NeonButton>
            <NeonButton @click="goToEdit(occurrence)" size="sm" variant="secondary">
              Editar
            </NeonButton>
          </div>
        </div>
      </article>
    </div>

    <!-- Paginação -->
    <div v-if="showPagination" class="flex flex-wrap items-center justify-between gap-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
      <p class="text-xs text-slate-500 dark:text-slate-400">
        Página {{ pagination.currentPage }} de {{ pagination.lastPage }} · {{ pagination.total }} registros
      </p>
      <div class="flex gap-2">
        <NeonButton
          @click="fetchOccurrences(pagination.currentPage - 1)"
          :disabled="pagination.currentPage === 1"
          size="sm"
          variant="secondary"
        >
          Anterior
        </NeonButton>
        <NeonButton
          @click="fetchOccurrences(pagination.currentPage + 1)"
          :disabled="pagination.currentPage === pagination.lastPage"
          size="sm"
          variant="secondary"
        >
          Próxima
        </NeonButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, h, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { format, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import TopCard from '../ui/TopCard.vue';
import NeonButton from '../NeonButton.vue';
import { apiRequest } from '../../utils/api';

const router = useRouter();

const occurrences = ref([]);
const loading = ref(true);
const error = ref('');
const search = ref('');
const pagination = reactive({
  currentPage: 1,
  lastPage: 1,
  perPage: 50,
  total: 0,
});

const filters = reactive({
  status: '',
  priority: '',
});

const statusOptions = [
  { value: 'open', label: 'Aberta' },
  { value: 'in_progress', label: 'Em Progresso' },
  { value: 'resolved', label: 'Resolvida' },
  { value: 'closed', label: 'Fechada' },
  { value: 'reopened', label: 'Reaberta' },
];

const priorityOptions = [
  { value: 'low', label: 'Baixa' },
  { value: 'medium', label: 'Média' },
  { value: 'high', label: 'Alta' },
  { value: 'urgent', label: 'Urgente' },
];

const createIcon = (path, options = {}) => ({
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
        'stroke-width': options.width || 2,
        d: path,
      }),
    ]);
  },
});

const OccurrenceIcon = createIcon('M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z');
const OpenIcon = createIcon('M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z');
const ProgressIcon = createIcon('M13 10V3L4 14h7v7l9-11h-7z');
const ResolvedIcon = createIcon('M5 13l4 4L19 7');
const UrgentIcon = createIcon('M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-3L13.74 4a2 2 0 00-3.48 0L3.33 16a2 2 0 001.74 3z');

const hasActiveFilters = computed(() => Boolean(search.value || filters.status || filters.priority));
const totalOccurrences = computed(() => pagination.total || occurrences.value.length);
const showPagination = computed(() => pagination.lastPage > 1);

const filteredOccurrences = computed(() => {
  const term = search.value.trim().toLowerCase();

  if (!term) {
    return occurrences.value;
  }

  return occurrences.value.filter((occurrence) => [
    occurrence.title,
    occurrence.description,
    occurrence.user?.name,
  ].some((value) => String(value || '').toLowerCase().includes(term)));
});

const stats = computed(() => ({
  total: totalOccurrences.value,
  open: occurrences.value.filter((occurrence) => occurrence.status === 'open').length,
  inProgress: occurrences.value.filter((occurrence) => occurrence.status === 'in_progress').length,
  resolved: occurrences.value.filter((occurrence) => ['resolved', 'closed'].includes(occurrence.status)).length,
  urgent: occurrences.value.filter((occurrence) => occurrence.priority === 'urgent').length,
}));

const summaryCards = computed(() => [
  {
    label: 'Total',
    value: stats.value.total,
    icon: OccurrenceIcon,
    iconClass: 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400',
  },
  {
    label: 'Abertas',
    value: stats.value.open,
    icon: OpenIcon,
    iconClass: 'bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400',
  },
  {
    label: 'Em Progresso',
    value: stats.value.inProgress,
    icon: ProgressIcon,
    iconClass: 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400',
  },
  {
    label: 'Resolvidas',
    value: stats.value.resolved,
    icon: ResolvedIcon,
    iconClass: 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400',
  },
  {
    label: 'Urgentes',
    value: stats.value.urgent,
    icon: UrgentIcon,
    iconClass: 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400',
  },
]);

const buildListUrl = (page = 1) => {
  const params = new URLSearchParams({
    page: String(page),
    per_page: String(pagination.perPage),
  });

  if (filters.status) {
    params.set('status', filters.status);
  }

  if (filters.priority) {
    params.set('priority', filters.priority);
  }

  return `/occurrences?${params.toString()}`;
};

const normalizeResponse = (response) => {
  if (Array.isArray(response)) {
    return {
      data: response,
      currentPage: 1,
      lastPage: 1,
      total: response.length,
    };
  }

  const data = response?.data || [];

  return {
    data,
    currentPage: response?.current_page || 1,
    lastPage: response?.last_page || 1,
    total: response?.total || data.length,
  };
};

const fetchOccurrences = async (page = 1) => {
  if (page < 1 || (pagination.lastPage > 1 && page > pagination.lastPage)) {
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    const response = await apiRequest(buildListUrl(page));
    const normalized = normalizeResponse(response);

    occurrences.value = normalized.data;
    pagination.currentPage = normalized.currentPage;
    pagination.lastPage = normalized.lastPage;
    pagination.total = normalized.total;
  } catch (err) {
    error.value = err.message || 'Não foi possível carregar as ocorrências.';
  } finally {
    loading.value = false;
  }
};

const clearFilters = async () => {
  search.value = '';
  filters.status = '';
  filters.priority = '';
  await fetchOccurrences(1);
};

const goToCreate = () => {
  router.push({ name: 'OccurrenceCreate' });
};

const goToShow = (occurrence) => {
  router.push({ name: 'OccurrenceShow', params: { id: occurrence.id } });
};

const goToEdit = (occurrence) => {
  router.push({ name: 'OccurrenceEdit', params: { id: occurrence.id } });
};

const getStatusLabel = (status) => statusOptions.find((option) => option.value === status)?.label || status || 'N/A';
const getPriorityLabel = (priority) => priorityOptions.find((option) => option.value === priority)?.label || priority || 'N/A';

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
    return format(parseISO(dateString), 'dd/MM/yyyy HH:mm', { locale: ptBR });
  } catch {
    return dateString;
  }
};

const getCoverPhoto = (occurrence) => {
  const path = occurrence.photos?.[0]?.photo_path;

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

onMounted(() => {
  fetchOccurrences();
});
</script>
