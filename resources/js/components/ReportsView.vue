<template>
  <AuthenticatedLayout page-title="Relatórios">
    <div class="space-y-6">
      <TopCard title="Relatórios" :icon="ReportIcon" />

      <div class="rounded-3xl border border-cyan-500/20 bg-gradient-to-br from-slate-900/90 via-slate-800/90 to-slate-900/90 p-6 shadow-2xl backdrop-blur-xl">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-200">Condomínio</label>
            <select v-model="filters.condominium" class="w-full rounded-2xl border border-cyan-500/20 bg-slate-950/60 px-4 py-2 text-white focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30">
              <option value="">Selecione</option>
              <option v-for="condo in condominiums" :key="condo.id" :value="String(condo.id)">
                {{ condo.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-200">Tipo de relatório</label>
            <select v-model="filters.type" class="w-full rounded-2xl border border-cyan-500/20 bg-slate-950/60 px-4 py-2 text-white focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30">
              <option value="financial">Orçamento anual</option>
              <option value="maintenance">Manutenção</option>
              <option value="residents">Moradores</option>
              <option value="incidents">Incidentes</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-200">Data inicial</label>
            <input v-model="filters.startDate" type="date" class="w-full rounded-2xl border border-cyan-500/20 bg-slate-950/60 px-4 py-2 text-white focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-200">Data final</label>
            <input v-model="filters.endDate" type="date" class="w-full rounded-2xl border border-cyan-500/20 bg-slate-950/60 px-4 py-2 text-white focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30" />
          </div>
        </div>
        <div class="mt-6 flex justify-end">
          <button @click="generateReport" class="rounded-2xl bg-gradient-to-r from-cyan-500 to-fuchsia-500 px-4 py-2 font-medium text-slate-950 transition hover:opacity-90">
            Gerar relatório
          </button>
        </div>
      </div>

      <div v-if="loading" class="flex items-center justify-center py-8 text-sm text-slate-300">Gerando relatório...</div>
      <div v-else-if="error" class="rounded-2xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">{{ error }}</div>

      <div v-else-if="report" class="space-y-6">
        <div class="rounded-3xl border border-cyan-500/20 bg-gradient-to-br from-slate-900/90 via-slate-800/90 to-slate-900/90 p-6 shadow-2xl backdrop-blur-xl">
          <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
            <div>
              <h3 class="text-lg font-semibold text-white">{{ report.title }}</h3>
              <p class="mt-1 text-sm text-slate-300">Período: {{ formatDate(report.startDate) }} - {{ formatDate(report.endDate) }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
              <button @click="downloadPDF" class="rounded-2xl border border-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 transition hover:bg-cyan-500/10">Exportar PDF</button>
              <button @click="downloadExcel" class="rounded-2xl border border-cyan-500/20 px-4 py-2 text-sm font-medium text-cyan-100 transition hover:bg-cyan-500/10">Exportar Excel</button>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
          <div v-for="stat in report.summary" :key="stat.label" class="rounded-3xl border border-cyan-500/20 bg-slate-900/80 p-5 shadow-xl">
            <h4 class="text-sm font-medium text-slate-400">{{ stat.label }}</h4>
            <p class="mt-2 text-2xl font-semibold text-white">{{ stat.value }}</p>
            <p class="mt-1 text-sm" :class="stat.tone">{{ stat.helper }}</p>
          </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-cyan-500/20 bg-slate-900/80 shadow-2xl">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800">
              <thead class="bg-slate-950/80">
                <tr>
                  <th v-for="header in report.tableHeaders" :key="header" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">{{ header }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800">
                <tr v-for="row in report.tableData" :key="row.account_name" class="hover:bg-slate-800/60">
                  <td class="px-6 py-4 text-sm text-slate-100">{{ row.account_name }}</td>
                  <td class="px-6 py-4 text-sm text-slate-300">{{ formatCurrency(row.budgeted_amount) }}</td>
                  <td class="px-6 py-4 text-sm text-slate-300">{{ formatCurrency(row.actual_amount) }}</td>
                  <td class="px-6 py-4 text-sm" :class="row.variance >= 0 ? 'text-emerald-300' : 'text-rose-300'">{{ formatCurrency(row.variance) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { h, onMounted, reactive, ref } from 'vue';
import api from '../utils/api';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import TopCard from './ui/TopCard.vue';

const buildFinancialReport = (rows, filters) => {
  const totalBudgeted = rows.reduce((sum, row) => sum + Number(row.budgeted_amount || 0), 0);
  const totalActual = rows.reduce((sum, row) => sum + Number(row.actual_amount || 0), 0);
  const totalVariance = rows.reduce((sum, row) => sum + Number(row.variance || 0), 0);
  const worstVariance = rows.reduce((current, row) => {
    if (!current || Number(row.variance) < Number(current.variance)) {
      return row;
    }

    return current;
  }, null);

  return {
    title: `Relatório orçamentário ${new Date(filters.startDate).getFullYear()}`,
    startDate: filters.startDate,
    endDate: filters.endDate,
    tableHeaders: ['Conta', 'Orçado', 'Realizado', 'Variação'],
    tableData: rows,
    summary: [
      {
        label: 'Total orçado',
        value: formatCurrency(totalBudgeted),
        helper: 'Soma do orçamento anual planejado.',
        tone: 'text-cyan-300'
      },
      {
        label: 'Total realizado',
        value: formatCurrency(totalActual),
        helper: 'Despesas efetivamente pagas no período.',
        tone: 'text-cyan-300'
      },
      {
        label: 'Saldo da variação',
        value: formatCurrency(totalVariance),
        helper: totalVariance >= 0 ? 'O realizado ficou abaixo do previsto.' : 'O realizado ultrapassou o previsto.',
        tone: totalVariance >= 0 ? 'text-emerald-300' : 'text-rose-300'
      },
      {
        label: 'Maior desvio',
        value: worstVariance ? worstVariance.account_name : 'Sem dados',
        helper: worstVariance ? formatCurrency(worstVariance.variance) : 'Nenhuma conta encontrada.',
        tone: worstVariance && Number(worstVariance.variance) < 0 ? 'text-rose-300' : 'text-emerald-300'
      }
    ]
  };
};

const formatCurrency = value => new Intl.NumberFormat('pt-BR', {
  style: 'currency',
  currency: 'BRL'
}).format(Number(value || 0));

export default {
  name: 'ReportsView',
  components: { AuthenticatedLayout, TopCard },
  setup() {
    const today = new Date();
    const currentYear = today.getFullYear();

    const condominiums = ref([]);
    const loading = ref(false);
    const error = ref('');
    const report = ref(null);

    const ReportIcon = {
      render() {
        return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' })
        ]);
      }
    };

    const filters = reactive({
      condominium: '',
      type: 'financial',
      startDate: `${currentYear}-01-01`,
      endDate: `${currentYear}-12-31`
    });

    const loadCondominiums = async () => {
      try {
        const { data } = await api.get('/condominiums');
        condominiums.value = Array.isArray(data) ? data : [];

        if (!filters.condominium && condominiums.value.length) {
          filters.condominium = String(condominiums.value[0].id);
        }
      } catch (requestError) {
        console.error('Erro ao carregar condomínios:', requestError);
        error.value = 'Não foi possível carregar a lista de condomínios.';
      }
    };

    const generateReport = async () => {
      loading.value = true;
      error.value = '';
      report.value = null;

      if (!filters.condominium) {
        error.value = 'Selecione um condomínio para gerar o relatório.';
        loading.value = false;
        return;
      }

      if (filters.type !== 'financial') {
        error.value = 'No momento, a tela está integrada apenas ao relatório orçamentário disponível no backend.';
        loading.value = false;
        return;
      }

      try {
        const { data } = await api.get('/finance/reports/budget', {
          params: {
            condominium_id: filters.condominium,
            year: new Date(filters.startDate).getFullYear()
          }
        });

        report.value = buildFinancialReport(Array.isArray(data) ? data : [], filters);
      } catch (requestError) {
        console.error('Erro ao gerar relatório:', requestError);
        error.value = requestError?.response?.data?.message || 'Falha ao gerar o relatório financeiro.';
      } finally {
        loading.value = false;
      }
    };

    const formatDate = date => new Date(date).toLocaleDateString('pt-BR');

    const downloadPDF = () => {
      error.value = 'A exportação desta visão consolidada ainda não está disponível nesta tela.';
    };

    const downloadExcel = () => {
      error.value = 'A exportação desta visão consolidada ainda não está disponível nesta tela.';
    };

    onMounted(loadCondominiums);

    return {
      condominiums,
      downloadExcel,
      downloadPDF,
      error,
      filters,
      formatCurrency,
      formatDate,
      generateReport,
      loading,
      report,
      ReportIcon
    };
  }
};
</script>
