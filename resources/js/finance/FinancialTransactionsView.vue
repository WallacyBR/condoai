<template>
  <AuthenticatedLayout page-title="Lançamentos Financeiros">
    <div class="space-y-6">
      <TopCard title="Lançamentos Financeiros" :icon="MoneyIcon">
        <template #actions>
          <NeonButton @click="openCreateModal('payable')" size="md" class="mr-2">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nova Despesa
          </NeonButton>
          <NeonButton @click="openCreateModal('receivable')" size="md">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nova Receita
          </NeonButton>
        </template>
      </TopCard>

      <div class="flex flex-wrap gap-4 p-4 bg-gray-800 rounded-lg shadow-lg border border-gray-700">
        <div class="relative flex-1 min-w-[200px]">
          <input
            type="text"
            v-model="filters.query"
            placeholder="Buscar por descrição..."
            class="w-full pl-10 pr-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors"
          />
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
        <select v-model="filters.type" class="p-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
          <option value="">Todos os Tipos</option>
          <option value="payable">Despesas</option>
          <option value="receivable">Receitas</option>
        </select>
        <input
          type="date"
          v-model="filters.startDate"
          class="p-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
        />
        <input
          type="date"
          v-model="filters.endDate"
          class="p-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500"
        />
        <NeonButton @click="applyFilters" size="md">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
          </svg>
          Aplicar Filtros
        </NeonButton>
      </div>

      <div v-if="transactions.length === 0" class="text-center py-8 bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-700">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-300">Nenhum lançamento encontrado</h3>
        <p class="mt-1 text-sm text-gray-500">
          Comece adicionando uma nova despesa ou receita.
        </p>
      </div>

      <ul v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <li v-for="trx in transactions" :key="trx.id" 
            class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2 transition-all duration-300 hover:shadow-2xl hover:scale-105"
            :class="{
              'border-red-500/20 hover:border-red-500/40': trx.type === 'payable',
              'border-green-500/20 hover:border-green-500/40': trx.type === 'receivable'
            }">
          <div class="flex justify-between items-start mb-4">
            <div>
              <h3 class="text-lg font-semibold text-white">{{ trx.description }}</h3>
              <p class="text-sm text-gray-400">Vencimento: {{ new Date(trx.due_date).toLocaleDateString() }}</p>
            </div>
            <span class="px-3 py-1 text-sm font-semibold rounded-full"
                  :class="{
                    'bg-red-500/20 text-red-300 border border-red-500/30': trx.type === 'payable',
                    'bg-green-500/20 text-green-300 border border-green-500/30': trx.type === 'receivable'
                  }">
              {{ trx.type === 'payable' ? 'Despesa' : 'Receita' }}
            </span>
          </div>

          <div class="bg-gray-800/50 rounded-lg p-4 mb-4 border border-gray-700/50">
            <p class="text-white text-xl font-bold mb-2">R$ {{ parseFloat(trx.amount).toFixed(2) }}</p>
            <p class="text-sm text-gray-400">Status: <span class="font-medium text-white">{{ trx.status }}</span></p>
            <p v-if="trx.chart_account" class="text-sm text-gray-400">Conta: <span class="font-medium text-white">{{ trx.chart_account.name }}</span></p>
            <p v-if="trx.bank" class="text-sm text-gray-400">Banco: <span class="font-medium text-white">{{ trx.bank.name }}</span></p>
          </div>

          <div class="flex space-x-2">
            <NeonButton @click="openEditModal(trx)" size="sm" variant="info" class="flex-1">
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Editar
            </NeonButton>
            <NeonButton @click="deleteTransaction(trx)" size="sm" variant="danger" class="flex-1">
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Excluir
            </NeonButton>
          </div>
        </li>
      </ul>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex justify-center items-center z-50">
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-2xl p-6 border-2 border-cyan-500/30 max-w-2xl w-full mx-4">
        <h2 class="text-xl font-bold mb-4 text-white">{{ isEditMode ? 'Editar' : 'Nova' }} {{ currentTransactionType === 'payable' ? 'Despesa' : 'Receita' }}</h2>
        <form @submit.prevent="saveTransaction">
          <div class="mb-4">
            <label for="description" class="block text-gray-300 text-sm font-medium mb-1">Descrição:</label>
            <input type="text" id="description" v-model="form.description" 
                   class="w-full p-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors"
                   :class="{'border-red-500': errors.description}" />
            <p v-if="errors.description" class="text-red-500 text-xs mt-1">{{ errors.description }}</p>
          </div>
          <div class="mb-4">
            <label for="amount" class="block text-gray-300 text-sm font-medium mb-1">Valor:</label>
            <input type="number" id="amount" v-model="form.amount" 
                   class="w-full p-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors"
                   step="0.01" :class="{'border-red-500': errors.amount}" />
            <p v-if="errors.amount" class="text-red-500 text-xs mt-1">{{ errors.amount }}</p>
          </div>
          <div class="mb-4">
            <label for="due_date" class="block text-gray-300 text-sm font-medium mb-1">Data de Vencimento:</label>
            <input type="date" id="due_date" v-model="form.due_date" 
                   class="w-full p-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors"
                   :class="{'border-red-500': errors.due_date}" />
            <p v-if="errors.due_date" class="text-red-500 text-xs mt-1">{{ errors.due_date }}</p>
          </div>
          <div class="mb-4">
            <label for="chart_account_id" class="block text-gray-300 text-sm font-medium mb-1">Conta Contábil:</label>
            <select id="chart_account_id" v-model="form.chart_account_id" 
                    class="w-full p-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors"
                    :class="{'border-red-500': errors.chart_account_id}">
              <option value="">Selecione uma conta</option>
              <option v-for="account in financeDataStore.chartAccounts" :key="account.id" :value="account.id">
                {{ account.name }}
              </option>
            </select>
            <p v-if="errors.chart_account_id" class="text-red-500 text-xs mt-1">{{ errors.chart_account_id }}</p>
          </div>
          <div class="mb-4">
            <label for="bank_id" class="block text-gray-300 text-sm font-medium mb-1">Banco:</label>
            <select id="bank_id" v-model="form.bank_id" 
                    class="w-full p-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors"
                    :class="{'border-red-500': errors.bank_id}">
              <option value="">Nenhum</option>
              <option v-for="bank in financeDataStore.banks" :key="bank.id" :value="bank.id">
                {{ bank.name }}
              </option>
            </select>
            <p v-if="errors.bank_id" class="text-red-500 text-xs mt-1">{{ errors.bank_id }}</p>
          </div>
          <div class="mb-4">
            <label for="status" class="block text-gray-300 text-sm font-medium mb-1">Status:</label>
            <input type="text" id="status" v-model="form.status" 
                   class="w-full p-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors"
                   :class="{'border-red-500': errors.status}" />
            <p v-if="errors.status" class="text-red-500 text-xs mt-1">{{ errors.status }}</p>
          </div>

          <div v-if="isEditMode && (currentTransactionType === 'payable' || currentTransactionType === 'receivable')" class="mb-4 p-4 bg-gray-800 rounded-lg border border-gray-700">
            <h3 class="text-lg font-semibold mb-2 text-white">Anexos</h3>
            <input type="file" @change="handleAttachmentUpload" class="mb-2 text-gray-300" />
            <ul class="space-y-2 mt-2">
              <li v-for="attachment in currentAttachments" :key="attachment.id" class="flex justify-between items-center p-2 bg-gray-700 rounded-md border border-gray-600">
                <span class="text-gray-300 text-sm">{{ attachment.original_name }}</span>
                <button @click="deleteAttachment(attachment)" class="text-red-400 hover:text-red-300 text-sm ml-4">Remover</button>
              </li>
            </ul>
          </div>

          <div class="flex justify-end mt-6">
            <NeonButton type="button" @click="showModal = false" variant="secondary" class="mr-2">Cancelar</NeonButton>
            <NeonButton type="submit">Salvar</NeonButton>
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
import NeonButton from '../components/NeonButton.vue';
import TopCard from '../components/ui/TopCard.vue';

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
const currentTransactionType = ref(''); // 'payable' or 'receivable'
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
  errors.value = {}; // Clear errors
  showModal.value = true;
}

async function openEditModal(trx) {
  isEditMode.value = true;
  currentTransactionType.value = trx.type;
  form.value = { ...trx };
  // Format date for input
  form.value.due_date = trx.due_date ? new Date(trx.due_date).toISOString().split('T')[0] : '';
  form.value.paid_at = trx.paid_at ? new Date(trx.paid_at).toISOString().split('T')[0] : null;
  form.value.received_at = trx.received_at ? new Date(trx.received_at).toISOString().split('T')[0] : null;

  // Fetch attachments if in edit mode
  if (trx.type === 'payable') {
    const { data } = await api.get(`/finance/payables/${trx.id}/attachments`);
    currentAttachments.value = data;
  } else if (trx.type === 'receivable') {
    const { data } = await api.get(`/finance/receivables/${trx.id}/attachments`);
    currentAttachments.value = data;
  }

  errors.value = {}; // Clear errors
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