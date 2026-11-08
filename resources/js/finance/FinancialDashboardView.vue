<template>
  <AuthenticatedLayout page-title="Dashboard Financeiro">
    <div class="space-y-6">
      <TopCard title="Dashboard Financeiro" :icon="DashboardIcon">
        <template #actions>
          <div class="flex items-center gap-2">
            <select 
              v-model.number="horizon" 
              class="w-32 px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm font-medium"
            >
              <option :value="30">30 dias</option>
              <option :value="90">90 dias</option>
              <option :value="365">1 ano</option>
            </select>
            <NeonButton :is-loading="loading" @click="load" variant="primary">
              <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Atualizar
            </NeonButton>
          </div>
        </template>
      </TopCard>

      <div v-if="error" class="rounded-xl border border-rose-200 dark:border-rose-800/50 bg-rose-50 dark:bg-rose-950/30 p-4 text-sm text-rose-700 dark:text-rose-300 shadow-sm">
        {{ error }}
      </div>

      <template v-else-if="data">
        <!-- Indicadores Principais -->
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
          <article 
            v-for="card in data.cards" 
            :key="card.label" 
            class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm"
          >
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ card.label }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ money(card.value) }}</p>
          </article>
        </div>

        <!-- Projeção & Orçamento -->
        <div class="grid gap-5 lg:grid-cols-3">
          <section class="lg:col-span-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
              <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Saldo Projetado</h2>
              <span class="text-xs font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-full">
                Próximos {{ horizon }} dias
              </span>
            </div>
            <div class="mt-6 flex h-48 items-end gap-px">
              <div 
                v-for="point in data.forecast" 
                :key="point.date" 
                class="group relative flex-1 rounded-t bg-indigo-500/80 hover:bg-indigo-600 transition-colors cursor-pointer" 
                :style="{ height: `${height(point.balance)}%` }"
              >
                <span class="pointer-events-none absolute bottom-full left-1/2 hidden -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900 px-2.5 py-1 text-xs font-medium shadow-lg group-hover:block z-20">
                  {{ point.date }}: {{ money(point.balance) }}
                </span>
              </div>
            </div>
          </section>

          <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
            <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100 pb-4 border-b border-slate-100 dark:border-slate-800">Orçamento do Mês</h2>
            <div class="mt-4">
              <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Realizado</p>
              <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ money(data.budget.actual) }}</p>
              <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                de <span class="font-medium text-slate-700 dark:text-slate-300">{{ money(data.budget.budgeted) }}</span> ({{ data.budget.percentage_used }}%)
              </p>
              <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <div 
                  class="h-full bg-emerald-500 rounded-full transition-all" 
                  :style="{ width: `${Math.min(data.budget.percentage_used, 100)}%` }" 
                />
              </div>
            </div>
          </section>
        </div>

        <!-- Aging / Prazos -->
        <div class="grid gap-5 md:grid-cols-2">
          <AgingCard title="A Receber" :data="data.aging.receivables" />
          <AgingCard title="A Pagar" :data="data.aging.payables" />
        </div>
      </template>

      <div v-else class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 py-16 text-center text-slate-500 dark:text-slate-400 text-sm shadow-sm">
        Carregando indicadores financeiros...
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { h, onMounted, ref } from 'vue';
import api from '@/utils/api';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../components/NeonButton.vue';
import TopCard from '../components/ui/TopCard.vue';

const horizon = ref(90);
const data = ref(null);
const error = ref('');
const loading = ref(false);

const money = (value) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value || 0));

const DashboardIcon = { 
  render: () => h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' })
  ])
};

const AgingCard = {
  props: ['title', 'data'],
  methods: { money },
  template: `
    <section class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
      <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100 pb-3 border-b border-slate-100 dark:border-slate-800">{{ title }}</h2>
      <dl class="mt-4 space-y-3.5 text-sm">
        <div class="flex justify-between items-center">
          <dt class="text-xs font-semibold uppercase tracking-wider text-rose-600 dark:text-rose-400">Vencido</dt>
          <dd class="font-bold text-rose-600 dark:text-rose-400">{{ money(data.overdue) }}</dd>
        </div>
        <div class="flex justify-between items-center">
          <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Próximos 30 dias</dt>
          <dd class="font-semibold text-slate-900 dark:text-slate-100">{{ money(data.next_30_days) }}</dd>
        </div>
        <div class="flex justify-between items-center">
          <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">31 a 90 dias</dt>
          <dd class="font-semibold text-slate-900 dark:text-slate-100">{{ money(data.next_90_days) }}</dd>
        </div>
      </dl>
    </section>
  `
};

function height(value) {
  const points = data.value?.forecast || [];
  const max = Math.max(...points.map((point) => Math.abs(Number(point.balance))), 1);
  return Math.max(4, (Math.abs(Number(value)) / max) * 100);
}

async function load() {
  loading.value = true;
  error.value = '';
  try {
    const { data: payload } = await api.get('/finance/dashboard', { params: { horizon_days: horizon.value } });
    data.value = payload;
  } catch (requestError) {
    error.value = requestError.response?.data?.message || 'Não foi possível carregar o dashboard.';
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>
