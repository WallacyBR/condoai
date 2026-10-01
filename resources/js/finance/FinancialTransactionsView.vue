<template>
  <AuthenticatedLayout page-title="Lançamentos Financeiros">
    <div class="space-y-6">
      <TopCard title="Lançamentos Financeiros" :icon="MoneyIcon">
        <template #actions>
          <div class="flex items-center gap-2">
            <AppButton @click="openCreateModal('payable')" variant="danger" size="md">
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Nova Despesa
            </AppButton>
            <AppButton @click="openCreateModal('receivable')" variant="primary" size="md">
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Nova Receita
            </AppButton>
          </div>
        </template>
      </TopCard>

      <!-- Barra de Filtros -->
      <div class="flex flex-wrap items-center gap-3 p-4 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="relative flex-1 min-w-[200px]">
          <input
            type="text"
            v-model="filters.query"
            placeholder="Buscar por descrição..."
            class="w-full pl-9 pr-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
          />
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>

        <select 
          v-model="filters.type" 
          class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
        >
          <option value="">Todos os Tipos</option>
          <option value="payable">Despesas</option>
          <option value="receivable">Receitas</option>
        </select>

        <input
          type="date"
          v-model="filters.startDate"
          class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
        />
        <input
          type="date"
          v-model="filters.endDate"
          class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
        />

        <AppButton @click="applyFilters" variant="secondary" size="md">
          <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
          </svg>
          Filtrar
        </AppButton>
      </div>

      <!-- Estado Vazio -->
      <div v-if="transactions.length === 0" class="text-center py-12 bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
        <svg class="mx-auto h-10 w-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <h3 class="mt-2 text-sm font-semibold text-slate-900 dark:text-slate-100">Nenhum lançamento encontrado</h3>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
          Comece adicionando uma nova despesa ou receita.
        </p>
      </div>

      <!-- Grid de Lançamentos -->
      <ul v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <li 
          v-for="trx in transactions" 
          :key="trx.id" 
          class="bg-white dark:bg-slate-900 rounded-xl shadow-sm p-5 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-colors"
        >
          <div class="flex justify-between items-start mb-3 gap-3">
            <div>
              <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ trx.description }}</h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Vencimento: {{ new Date(trx.due_date).toLocaleDateString() }}</p>
            </div>
            <span 
              class="px-2.5 py-0.5 text-xs font-semibold rounded-full shrink-0"
              :class="trx.type === 'payable' 
                ? 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/50' 
                : 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50'"
            >
              {{ trx.type === 'payable' ? 'Despesa' : 'Receita' }}
            </span>
          </div>

          <div class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-3.5 mb-4 border border-slate-200/60 dark:border-slate-700/60 space-y-1">
            <p 
              class="text-lg font-bold"
              :class="trx.type === 'payable' ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'"
            >
              R$ {{ parseFloat(trx.amount).toFixed(2) }}
            </p>
            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 pt-1">
              <span>Status: <strong class="text-slate-700 dark:text-slate-300 font-medium">{{ trx.status }}</strong></span>
              <span v-if="trx.bank">{{ trx.bank.name }}</span>
            </div>
            <p v-if="trx.chart_account" class="text-xs text-slate-500 dark:text-slate-400">
              Conta: <span class="text-slate-700 dark:text-slate-300 font-medium">{{ trx.chart_account.name }}</span>
            </p>
          </div>

          <div class="flex space-x-2 pt-2 border-t border-slate-100 dark:border-slate-800">
            <AppButton @click="openEditModal(trx)" size="sm" variant="secondary" class="flex-1">
              <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Editar
            </AppButton>
            <AppButton @click="deleteTransaction(trx)" size="sm" variant="ghost" class="text-rose-600 hover:text-rose-700 dark:text-rose-400">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </AppButton>
          </div>
        </li>
      </ul>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex justify-center items-center z-50 p-4">
      <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl p-6 border border-slate-200 dark:border-slate-800 max-w-2xl w-full">
        <h2 class="text-base font-semibold mb-4 text-slate-900 dark:text-slate-100">
          {{ isEditMode ? 'Editar' : 'Nova' }} {{ currentTransactionType === 'payable' ? 'Despesa' : 'Receita' }}
        </h2>
        <form @submit.prevent="saveTransaction" class="space-y-4">
          <div>
            <label for="description" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold mb-1.5 uppercase tracking-wider">Descrição:</label>
            <input 
              type="text" 
              id="description" 
              v-model="form.description" 
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
              :class="{'!border-rose-500': errors.description}" 
            />
            <p v-if="errors.description" class="text-rose-600 dark:text-rose-400 text-xs mt-1">{{ errors.description }}</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="amount" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold mb-1.5 uppercase tracking-wider">Valor:</label>
              <input 
                type="number" 
                id="amount" 
                v-model="form.amount" 
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
                step="0.01" 
                :class="{'!border-rose-500': errors.amount}" 
              />
              <p v-if="errors.amount" class="text-rose-600 dark:text-rose-400 text-xs mt-1">{{ errors.amount }}</p>
            </div>
            <div>
              <label for="due_date" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold mb-1.5 uppercase tracking-wider">Data de Vencimento:</label>
              <input 
                type="date" 
                id="due_date" 
                v-model="form.due_date" 
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
                :class="{'!border-rose-500': errors.due_date}" 
              />
              <p v-if="errors.due_date" class="text-rose-600 dark:text-rose-400 text-xs mt-1">{{ errors.due_date }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="chart_account_id" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold mb-1.5 uppercase tracking-wider">Conta Contábil:</label>
              <select 
                id="chart_account_id" 
                v-model="form.chart_account_id" 
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
                :class="{'!border-rose-500': errors.chart_account_id}"
              >
                <option value="">Selecione uma conta</option>
                <option v-for="account in financeDataStore.chartAccounts" :key="account.id" :value="account.id">
                  {{ account.name }}
                </option>
              </select>
              <p v-if="errors.chart_account_id" class="text-rose-600 dark:text-rose-400 text-xs mt-1">{{ errors.chart_account_id }}</p>
            </div>
            <div>
              <label for="bank_id" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold mb-1.5 uppercase tracking-wider">Banco:</label>
              <select 
                id="bank_id" 
                v-model="form.bank_id" 
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
                :class="{'!border-rose-500': errors.bank_id}"
              >
                <option value="">Nenhum</option>
                <option v-for="bank in financeDataStore.banks" :key="bank.id" :value="bank.id">
                  {{ bank.name }}
                </option>
              </select>
              <p v-if="errors.bank_id" class="text-rose-600 dark:text-rose-400 text-xs mt-1">{{ errors.bank_id }}</p>
            </div>
          </div>

          <div>
            <label for="status" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold mb-1.5 uppercase tracking-wider">Status:</label>
            <input 
              type="text" 
              id="status" 
              v-model="form.status" 
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
              :class="{'!border-rose-500': errors.status}" 
            />
            <p v-if="errors.status" class="text-rose-600 dark:text-rose-400 text-xs mt-1">{{ errors.status }}</p>
          </div>

          <div v-if="isEditMode && (currentTransactionType === 'payable' || currentTransactionType === 'receivable')" class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Anexos</h3>
            <input type="file" @change="handleAttachmentUpload" class="text-xs text-slate-600 dark:text-slate-400 mb-2" />
            <ul class="space-y-2 mt-2">
              <li v-for="attachment in currentAttachments" :key="attachment.id" class="flex justify-between items-center p-2 bg-white dark:bg-slate-800 rounded-md border border-slate-200 dark:border-slate-700">
                <span class="text-slate-700 dark:text-slate-300 text-xs">{{ attachment.original_name }}</span>
                <button type="button" @click="deleteAttachment(attachment)" class="text-rose-600 hover:text-rose-700 dark:text-rose-400 text-xs ml-4">Remover</button>
              </li>
            </ul>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <AppButton type="button" @click="showModal = false" variant="secondary">Cancelar</AppButton>
            <AppButton type="submit" variant="primary">Salvar</AppButton>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed, h } from 'vue';
import { useTransactionsStore } from './stores/transactionsStore';
import { useFinanceDataStore } from './stores/financeDataStore';
import { useUser } from '@/composables/useUser';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import AppButton from '../components/ui/AppButton.vue';
import NeonButton from '../components/NeonButton.vue';
import TopCard from '../components/ui/TopCard.vue';
import api from '@/utils/api';

const transactionsStore = useTransactionsStore();
const financeDataStore = useFinanceDataStore();
const { user } = useUser();

const transactions = computed(() => transactionsStore.transactions);

const filters = ref({
  query: '',
  type: '',
  startDate: '',
  endDate: '',
});

const showModal = ref(false);
const isEditMode = ref(false);
const currentTransactionType = ref('');
const form = ref({
  id: null,
  condominium_id: user.value?.condominium_id,
  chart_account_id: null,
  bank_id: null,
  due_date: '',
  amount: 0,
  status: '',
  description: '',
  paid_at: null,
  received_at: null,
});

const currentAttachments = ref([]);
const errors = ref({});

const MoneyIcon = {
  render() {
    return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0-6a2 2 0 00-2 2v1H9a1 1 0 000 2h1v2H9a1 1 0 100 2h1v2H9a1 1 0 100 2h1v1a2 2 0 104 0v-1h1a1 1 0 100-2h-1v-2h1a1 1 0 100-2h-1V7h1a1 1 0 100-2h-1V4a2 2 0 00-2-2z' })
    ]);
  }
};

onMounted(() => {
  applyFilters();
  financeDataStore.fetchChartAccounts();
  financeDataStore.fetchBanks();
});

async function applyFilters() {
  await transactionsStore.fetchTransactions(filters.value);
}

function openCreateModal(type) {
  isEditMode.value = false;
  currentTransactionType.value = type;
  form.value = {
    id: null,
    condominium_id: user.value?.condominium_id,
    chart_account_id: null,
    bank_id: null,
    due_date: '',
    amount: 0,
    status: '',
    description: '',
    paid_at: null,
    received_at: null,
  };
  currentAttachments.value = [];
  errors.value = {};
  showModal.value = true;
}

async function openEditModal(trx) {
  isEditMode.value = true;
  currentTransactionType.value = trx.type;
  form.value = { ...trx };
  form.value.due_date = trx.due_date ? new Date(trx.due_date).toISOString().split('T')[0] : '';
  form.value.paid_at = trx.paid_at ? new Date(trx.paid_at).toISOString().split('T')[0] : null;
  form.value.received_at = trx.received_at ? new Date(trx.received_at).toISOString().split('T')[0] : null;

  if (trx.type === 'payable') {
    const { data } = await api.get(`/finance/payables/${trx.id}/attachments`);
    currentAttachments.value = data;
  } else if (trx.type === 'receivable') {
    const { data } = await api.get(`/finance/receivables/${trx.id}/attachments`);
    currentAttachments.value = data;
  }

  errors.value = {};
  showModal.value = true;
}

async function saveTransaction() {
  if (!validateForm()) {
    return;
  }
  try {
    if (isEditMode.value) {
      if (currentTransactionType.value === 'payable') {
        await transactionsStore.updatePayable(form.value.id, form.value);
      } else {
        await transactionsStore.updateReceivable(form.value.id, form.value);
      }
    } else {
      await transactionsStore.createTransaction({ ...form.value, type: currentTransactionType.value });
    }
    showModal.value = false;
    applyFilters();
  } catch (error) {
    console.error('Erro ao salvar lançamento:', error);
    alert('Erro ao salvar lançamento. Verifique o console para mais detalhes.');
  }
}

function validateForm() {
  errors.value = {};
  let isValid = true;

  if (!form.value.description) {
    errors.value.description = 'A descrição é obrigatória.';
    isValid = false;
  }

  if (!form.value.amount || form.value.amount <= 0) {
    errors.value.amount = 'O valor deve ser um número positivo.';
    isValid = false;
  }

  if (!form.value.due_date) {
    errors.value.due_date = 'A data de vencimento é obrigatória.';
    isValid = false;
  }

  if (!form.value.chart_account_id) {
    errors.value.chart_account_id = 'A conta contábil é obrigatória.';
    isValid = false;
  }

  if (!form.value.status) {
    errors.value.status = 'O status é obrigatório.';
    isValid = false;
  }

  return isValid;
}

async function deleteTransaction(trx) {
  if (confirm(`Tem certeza que deseja excluir este(a) ${trx.type === 'payable' ? 'despesa' : 'receita'}?`)) {
    try {
      if (trx.type === 'payable') {
        await transactionsStore.deletePayable(trx.id);
      } else {
        await transactionsStore.deleteReceivable(trx.id);
      }
      applyFilters();
    } catch (error) {
      console.error('Erro ao excluir lançamento:', error);
      alert('Erro ao excluir lançamento. Verifique o console para mais detalhes.');
    }
  }
}

async function handleAttachmentUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  try {
    let newAttachment;
    if (currentTransactionType.value === 'payable') {
      newAttachment = await transactionsStore.uploadPayableAttachment(form.value.id, file);
    } else {
      newAttachment = await transactionsStore.uploadReceivableAttachment(form.value.id, file);
    }
    currentAttachments.value.push(newAttachment);
    alert('Anexo enviado com sucesso!');
  } catch (error) {
    console.error('Erro ao enviar anexo:', error);
    alert('Erro ao enviar anexo. Verifique o console para mais detalhes.');
  }
}

async function deleteAttachment(attachment) {
  if (confirm(`Tem certeza que deseja remover o anexo ${attachment.original_name}?`)) {
    try {
      if (currentTransactionType.value === 'payable') {
        await transactionsStore.deletePayableAttachment(form.value.id, attachment.id);
      } else {
        await transactionsStore.deleteReceivableAttachment(form.value.id, attachment.id);
      }
      currentAttachments.value = currentAttachments.value.filter(att => att.id !== attachment.id);
      alert('Anexo removido com sucesso!');
    } catch (error) {
      console.error('Erro ao remover anexo:', error);
      alert('Erro ao remover anexo. Verifique o console para mais detalhes.');
    }
  }
}
</script>