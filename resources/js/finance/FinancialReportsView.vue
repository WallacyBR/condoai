<template>
  <AuthenticatedLayout page-title="Relatórios Financeiros">
    <div class="space-y-6">
      <TopCard title="Relatórios Financeiros" :icon="ReportIcon" />

      <!-- Barra de Filtros -->
      <section class="grid gap-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm md:grid-cols-[1fr_1fr_auto]">
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
            Data inicial
          </label>
          <input 
            v-model="startDate" 
            type="date" 
            class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm" 
          />
        </div>
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
            Data final / data-base
          </label>
          <input 
            v-model="endDate" 
            type="date" 
            class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm" 
          />
        </div>
        <div class="flex items-end gap-2 pt-2 md:pt-0">
          <NeonButton :is-loading="loading" @click="loadReport('dre')">DRE</NeonButton>
          <NeonButton :is-loading="loading" variant="secondary" @click="loadReport('balancete')">Balancete</NeonButton>
        </div>
      </section>

      <!-- Erro -->
      <section v-if="error" class="rounded-xl border border-rose-200 dark:border-rose-800/50 bg-rose-50 dark:bg-rose-950/30 p-4 text-sm text-rose-700 dark:text-rose-300">
        {{ error }}
      </section>

      <!-- Relatório Carregado -->
      <section v-else-if="report" class="space-y-5">
        <div class="rounded-xl border border-amber-200 dark:border-amber-800/50 bg-amber-50 dark:bg-amber-950/30 p-4 text-xs font-medium text-amber-800 dark:text-amber-200">
          {{ report.disclaimer }}
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
          <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ report.title }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              {{ report.start_date ? `${report.start_date} a ${report.end_date}` : `Data-base: ${report.as_of_date}` }}
            </p>
          </div>
          <NeonButton variant="secondary" @click="downloadPdf">
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Exportar PDF
          </NeonButton>
        </div>

        <!-- DRE -->
        <div v-if="report.kind === 'gerencial_competencia'" class="grid gap-5 lg:grid-cols-2">
          <ReportGroup title="Receitas" :groups="report.revenues" :total="report.totals.revenues" tone="green" />
          <ReportGroup title="Despesas" :groups="report.expenses" :total="report.totals.expenses" tone="red" />
          <div class="lg:col-span-2 rounded-xl border border-indigo-200 dark:border-indigo-800/50 bg-indigo-50/50 dark:bg-indigo-950/20 p-6 text-center shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-700 dark:text-indigo-300">Resultado Gerencial</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ money(report.totals.result) }}</p>
          </div>
        </div>

        <!-- Balancete -->
        <div v-else class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
          <Metric v-for="(value, key) in report.totals" :key="key" :label="label(key)" :value="money(value)" />
          <div class="md:col-span-2 lg:col-span-3 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
            <table class="w-full text-sm">
              <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                  <th class="p-3.5">Banco</th>
                  <th class="p-3.5">Conta</th>
                  <th class="p-3.5 text-right">Saldo informado</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                <tr v-for="bank in report.banks" :key="`${bank.name}-${bank.account}`" class="text-slate-700 dark:text-slate-300 hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                  <td class="p-3.5 font-medium text-slate-900 dark:text-slate-100">{{ bank.name }}</td>
                  <td class="p-3.5 text-slate-500 dark:text-slate-400">{{ bank.account }}</td>
                  <td class="p-3.5 text-right font-semibold text-slate-900 dark:text-slate-100">{{ money(bank.balance) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- Estado Inicial Vazio -->
      <section v-else class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 py-16 text-center text-slate-500 dark:text-slate-400 text-sm shadow-sm">
        Escolha um relatório acima para consultar os dados financeiros do condomínio.
      </section>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed, h, ref } from 'vue';
import api from '@/utils/api';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../components/NeonButton.vue';
import TopCard from '../components/ui/TopCard.vue';

const today = new Date();
const localDate = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
const start = new Date(today); start.setDate(1);
const startDate = ref(localDate(start));
const endDate = ref(localDate(today));
const reportType = ref('dre');
const report = ref(null);
const loading = ref(false);
const error = ref('');
const money = (value) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value || 0));
const label = (key) => ({
  reported_bank_balance: 'Saldo bancário informado',
  received: 'Entradas realizadas',
  paid: 'Saídas realizadas',
  receivables_open: 'Receitas em aberto',
  payables_open: 'Despesas em aberto',
  projected_balance: 'Saldo projetado'
}[key] || key);

const ReportIcon = {
  render: () => h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' })
  ])
};

const Metric = {
  props: ['label', 'value'],
  template: '<article class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm"><p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ label }}</p><p class="mt-2 text-xl font-bold text-slate-900 dark:text-slate-100">{{ value }}</p></article>'
};

const ReportGroup = {
  props: ['title', 'groups', 'total', 'tone'],
  methods: { money },
  template: `
    <article class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
      <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800">
        <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">{{ title }}</h3>
        <span class="font-bold text-sm" :class="tone === 'green' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">{{ money(total) }}</span>
      </div>
      <div v-for="group in groups" :key="group.group" class="mt-3.5 pt-3 border-t border-slate-100 dark:border-slate-800/60 first:border-0 first:pt-0">
        <div class="flex justify-between text-xs font-semibold text-slate-800 dark:text-slate-200">
          <span>{{ group.group }}</span>
          <span>{{ money(group.amount) }}</span>
        </div>
        <div v-for="item in group.items" :key="item.account" class="mt-1 flex justify-between pl-3 text-xs text-slate-500 dark:text-slate-400">
          <span>{{ item.account }}</span>
          <span>{{ money(item.amount) }}</span>
        </div>
      </div>
    </article>
  `
};

async function loadReport(type) {
  reportType.value = type;
  loading.value = true;
  error.value = '';
  try {
    const params = type === 'dre'
      ? { start_date: startDate.value, end_date: endDate.value }
      : { as_of_date: endDate.value };
    const { data } = await api.get(`/finance/reports/${type}`, { params });
    report.value = data;
  } catch (requestError) {
    error.value = requestError.response?.data?.message || 'Não foi possível gerar o relatório.';
  } finally {
    loading.value = false;
  }
}

async function downloadPdf() {
  const params = reportType.value === 'dre'
    ? { start_date: startDate.value, end_date: endDate.value, format: 'pdf' }
    : { as_of_date: endDate.value, format: 'pdf' };
  const { data } = await api.get(`/finance/reports/${reportType.value}`, { params, responseType: 'blob' });
  const url = URL.createObjectURL(new Blob([data], { type: 'application/pdf' }));
  const anchor = document.createElement('a');
  anchor.href = url;
  anchor.download = `${reportType.value}-gerencial.pdf`;
  anchor.click();
  URL.revokeObjectURL(url);
}
</script>
