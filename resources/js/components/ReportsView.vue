<template>
  <AuthenticatedLayout page-title="Relatórios">
    <div class="space-y-6">
      <TopCard
        title="Relatórios Operacionais"
        subtitle="Indicadores tenant-aware por período, com dados consolidados dos módulos principais."
        :icon="ReportIcon"
      >
        <template #actions>
          <NeonButton
            size="sm"
            variant="secondary"
            data-test="finance-shortcut"
            @click="goToFinancialReports"
          >
            Relatórios Financeiros
          </NeonButton>
        </template>
      </TopCard>

      <!-- Barra de Filtros -->
      <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(220px,1.2fr)_160px_160px_auto]">
          <div>
            <label for="report-type" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
              Tipo de relatório
            </label>
            <select
              id="report-type"
              v-model="filters.type"
              data-test="report-type"
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
              @change="loadReport"
            >
              <option v-for="option in reportTypes" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>

          <div>
            <label for="start-date" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
              Data inicial
            </label>
            <input
              id="start-date"
              v-model="filters.startDate"
              data-test="start-date"
              type="date"
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
              @change="loadReport"
            />
          </div>

          <div>
            <label for="end-date" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
              Data final
            </label>
            <input
              id="end-date"
              v-model="filters.endDate"
              data-test="end-date"
              type="date"
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
              @change="loadReport"
            />
          </div>

          <div class="flex items-end gap-2.5">
            <NeonButton
              data-test="refresh-report"
              variant="primary"
              :loading="loading"
              :disabled="!canLoad"
              @click="loadReport"
            >
              Atualizar
            </NeonButton>
            <NeonButton
              data-test="export-csv"
              variant="secondary"
              :loading="exporting"
              :disabled="!report || exporting"
              @click="exportCsv"
            >
              Exportar CSV
            </NeonButton>
          </div>
        </div>
      </section>

      <!-- Erro -->
      <div
        v-if="error"
        data-test="report-error"
        class="rounded-xl border border-rose-200 dark:border-rose-800/50 bg-rose-50 dark:bg-rose-950/30 p-4 text-xs font-medium text-rose-700 dark:text-rose-300 shadow-sm"
      >
        {{ error }}
      </div>

      <!-- Loading Skeleton -->
      <section v-if="loading && !report" class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div
          v-for="index in 4"
          :key="index"
          class="h-28 animate-pulse rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900"
        ></div>
      </section>

      <!-- Relatório Carregado -->
      <template v-else-if="report">
        <!-- Indicadores de Resumo -->
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
          <article
            v-for="item in report.summary"
            :key="item.label"
            data-test="summary-card"
            class="rounded-xl border bg-white dark:bg-slate-900 p-5 shadow-sm"
            :class="summaryToneClass(item.tone)"
          >
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ item.label }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ item.value }}</p>
          </article>
        </section>

        <!-- Tabela -->
        <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
          <div class="flex flex-col gap-2 border-b border-slate-100 dark:border-slate-800 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
              <h2 class="text-base font-bold text-slate-900 dark:text-slate-100">{{ report.title }}</h2>
              <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                {{ formattedPeriod }} · gerado em {{ formatDateTime(report.generated_at) }}
              </p>
            </div>
            <div class="rounded-full border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-700 dark:text-slate-300 self-start lg:self-auto">
              {{ rowCountLabel }}
            </div>
          </div>

          <div v-if="hasRows" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800" data-test="report-table">
              <thead class="bg-slate-50 dark:bg-slate-800/60">
                <tr>
                  <th
                    v-for="column in report.columns"
                    :key="column.key"
                    scope="col"
                    class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                  >
                    {{ column.label }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                <tr
                  v-for="(row, rowIndex) in report.rows"
                  :key="rowIndex"
                  class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors"
                >
                  <td
                    v-for="column in report.columns"
                    :key="column.key"
                    class="max-w-xs px-5 py-4 text-xs text-slate-700 dark:text-slate-300"
                  >
                    <span :class="cellClass(column.key, row[column.key])">
                      {{ displayCell(row[column.key]) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty Rows -->
          <div
            v-else
            data-test="empty-state"
            class="flex min-h-64 items-center justify-center px-5 py-14 text-center"
          >
            <div>
              <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-400">
                <component :is="ReportIcon" class="h-6 w-6" />
              </div>
              <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Nenhum registro encontrado</h3>
              <p class="mt-1 max-w-md text-xs text-slate-500 dark:text-slate-400">
                Ajuste o período ou selecione outro relatório operacional para conferir os dados disponíveis.
              </p>
            </div>
          </div>
        </section>
      </template>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed, h, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import NeonButton from './NeonButton.vue';
import TopCard from './ui/TopCard.vue';
import { apiRequest, authHeader } from '../utils/api';

const router = useRouter();
const loading = ref(false);
const exporting = ref(false);
const error = ref('');
const report = ref(null);

const reportTypes = [
  { value: 'overview', label: 'Visão Geral' },
  { value: 'residents', label: 'Moradores e Unidades' },
  { value: 'packages', label: 'Encomendas' },
  { value: 'occurrences', label: 'Ocorrências' },
  { value: 'parking', label: 'Vagas e Veículos' },
  { value: 'bookings', label: 'Reservas de Áreas' },
  { value: 'maintenance', label: 'Manutenção' },
  { value: 'access', label: 'Controle de Acesso' }
];

const today = new Date();
const defaultStart = new Date(today);
defaultStart.setDate(defaultStart.getDate() - 30);

const filters = reactive({
  type: 'overview',
  startDate: toDateInput(defaultStart),
  endDate: toDateInput(today)
});

const ReportIcon = {
  render() {
    return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M9 17v-6m4 6V7m4 10v-4M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z'
      })
    ]);
  }
};

const canLoad = computed(() => Boolean(filters.type && filters.startDate && filters.endDate));
const hasRows = computed(() => report.value?.rows?.length > 0);
const formattedPeriod = computed(() => {
  if (!report.value) {
    return '';
  }

  return `Período: ${formatDate(report.value.start_date)} a ${formatDate(report.value.end_date)}`;
});
const rowCountLabel = computed(() => {
  const count = report.value?.rows?.length ?? 0;
  return count === 1 ? '1 linha' : `${count} linhas`;
});

async function loadReport() {
  if (!canLoad.value) {
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    report.value = await apiRequest(`/reports/operational?${buildQueryString()}`);
  } catch (exception) {
    error.value = exception.message || 'Erro ao carregar relatório operacional.';
  } finally {
    loading.value = false;
  }
}

async function exportCsv() {
  if (!report.value || exporting.value) {
    return;
  }

  exporting.value = true;
  error.value = '';

  try {
    const response = await fetch(`/api/reports/operational/export?${buildQueryString()}`, {
      headers: {
        Accept: 'text/csv',
        ...(authHeader().headers || {})
      }
    });

    if (!response.ok) {
      throw new Error('Não foi possível exportar o CSV.');
    }

    const blob = await response.blob();
    const downloadUrl = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = csvFilename();
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(downloadUrl);
  } catch (exception) {
    error.value = exception.message || 'Erro ao exportar relatório.';
  } finally {
    exporting.value = false;
  }
}

function buildQueryString() {
  return new URLSearchParams({
    type: filters.type,
    start_date: filters.startDate,
    end_date: filters.endDate
  }).toString();
}

function goToFinancialReports() {
  router.push({ name: 'finance.reports' });
}

function toDateInput(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');

  return `${year}-${month}-${day}`;
}

function csvFilename() {
  return `relatorio-${filters.type}-${filters.startDate}-${filters.endDate}.csv`;
}

function formatDate(value) {
  if (!value) {
    return '-';
  }

  return new Date(`${value}T00:00:00`).toLocaleDateString('pt-BR');
}

function formatDateTime(value) {
  if (!value) {
    return '-';
  }

  return new Date(value).toLocaleString('pt-BR');
}

function displayCell(value) {
  return value === null || value === undefined || value === '' ? '-' : value;
}

function summaryToneClass(tone) {
  const tones = {
    blue: 'border-blue-200 dark:border-blue-800/50',
    cyan: 'border-indigo-200 dark:border-indigo-800/50',
    green: 'border-emerald-200 dark:border-emerald-800/50',
    orange: 'border-amber-200 dark:border-amber-800/50',
    red: 'border-rose-200 dark:border-rose-800/50'
  };

  return tones[tone] || 'border-slate-200 dark:border-slate-800';
}

function cellClass(key, value) {
  if (!['status', 'priority'].includes(key)) {
    return '';
  }

  const normalized = String(value || '').toLowerCase();
  const positive = ['ativo', 'retirada', 'livre', 'confirmed', 'completed', 'approved', 'authorized', 'resolvida', 'closed', 'resolved'];
  const warning = ['pendente', 'pending', 'open', 'reopened', 'alugada'];
  const danger = ['urgent', 'vencido', 'overdue', 'critical'];

  if (positive.includes(normalized)) {
    return 'rounded-full border border-emerald-200 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300';
  }

  if (danger.includes(normalized)) {
    return 'rounded-full border border-rose-200 dark:border-rose-800/50 bg-rose-50 dark:bg-rose-950/40 px-2.5 py-0.5 text-xs font-semibold text-rose-700 dark:text-rose-300';
  }

  if (warning.includes(normalized)) {
    return 'rounded-full border border-amber-200 dark:border-amber-800/50 bg-amber-50 dark:bg-amber-950/40 px-2.5 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-300';
  }

  return 'rounded-full border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 text-xs font-semibold text-slate-700 dark:text-slate-300';
}

onMounted(loadReport);

defineExpose({
  exportCsv,
  filters,
  loadReport,
  report
});
</script>
