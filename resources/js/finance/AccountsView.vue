<template>
  <AuthenticatedLayout page-title="Contas Financeiras">
    <div class="space-y-6">
      <TopCard title="Contas Financeiras" :icon="BankIcon">
        <template #actions>
          <div class="flex items-center gap-2">
            <AppButton @click="openCreateBankModal()" variant="primary" size="md">
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Nova Conta Bancária
            </AppButton>
            <AppButton @click="openCreateChartAccountModal()" variant="secondary" size="md">
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Nova Conta Contábil
            </AppButton>
          </div>
        </template>
      </TopCard>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Bank Accounts Section -->
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-800">
          <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Contas Bancárias</h2>
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
              {{ financeDataStore.banks.length }} {{ financeDataStore.banks.length === 1 ? 'conta' : 'contas' }}
            </span>
          </div>
          
          <div v-if="financeDataStore.banks.length === 0" class="text-center py-8 text-slate-500 dark:text-slate-400 text-sm">
            Nenhuma conta bancária cadastrada.
          </div>
          <ul v-else class="space-y-3">
            <li 
              v-for="bank in financeDataStore.banks" 
              :key="bank.id" 
              class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-4 border border-slate-200/80 dark:border-slate-700/60 flex justify-between items-center transition-colors hover:border-slate-300 dark:hover:border-slate-600"
            >
              <div>
                <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm">{{ bank.name }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Ag: {{ bank.agency }} • Cc: {{ bank.account_number }}</p>
                <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 mt-1">
                  Saldo: R$ {{ parseFloat(bank.balance).toFixed(2) }}
                </p>
              </div>
              <div class="flex space-x-1.5 shrink-0">
                <AppButton @click="openEditBankModal(bank)" size="sm" variant="ghost" title="Editar">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </AppButton>
                <AppButton @click="deleteBank(bank)" size="sm" variant="danger" title="Excluir">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </AppButton>
              </div>
            </li>
          </ul>
        </div>

        <!-- Chart of Accounts Section -->
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-800">
          <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Plano de Contas</h2>
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
              {{ financeDataStore.chartAccounts.length }} {{ financeDataStore.chartAccounts.length === 1 ? 'conta' : 'contas' }}
            </span>
          </div>
          
          <div v-if="financeDataStore.chartAccounts.length === 0" class="text-center py-8 text-slate-500 dark:text-slate-400 text-sm">
            Nenhuma conta contábil cadastrada.
          </div>
          <ul v-else class="space-y-3">
            <li 
              v-for="account in financeDataStore.chartAccounts" 
              :key="account.id" 
              class="bg-slate-50 dark:bg-slate-800/60 rounded-lg p-4 border border-slate-200/80 dark:border-slate-700/60 flex justify-between items-center transition-colors hover:border-slate-300 dark:hover:border-slate-600"
            >
              <div>
                <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm">{{ account.name }}</p>
                <div class="flex items-center gap-2 mt-0.5">
                  <span class="px-2 py-0.5 text-xs rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-medium">
                    {{ account.type }}
                  </span>
                  <span v-if="account.account_number" class="text-xs text-slate-500 dark:text-slate-400">
                    Nº {{ account.account_number }}
                  </span>
                </div>
              </div>
              <div class="flex space-x-1.5 shrink-0">
                <AppButton @click="openEditChartAccountModal(account)" size="sm" variant="ghost" title="Editar">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </AppButton>
                <AppButton @click="deleteChartAccount(account)" size="sm" variant="danger" title="Excluir">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </AppButton>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Bank Account Modal -->
    <div v-if="showBankModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex justify-center items-center z-50 p-4">
      <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl p-6 border border-slate-200 dark:border-slate-800 max-w-md w-full">
        <h2 class="text-base font-semibold mb-4 text-slate-900 dark:text-slate-100">{{ isEditBankMode ? 'Editar' : 'Nova' }} Conta Bancária</h2>
        <form @submit.prevent="saveBank" class="space-y-4">
          <div>
            <label for="bank_name" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold mb-1.5 uppercase tracking-wider">Nome:</label>
            <input 
              type="text" 
              id="bank_name" 
              v-model="bankForm.name" 
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
              :class="{'!border-rose-500': bankErrors.name}" 
            />
            <p v-if="bankErrors.name" class="text-rose-600 dark:text-rose-400 text-xs mt-1">{{ bankErrors.name }}</p>
          </div>
          <div>
            <label for="bank_agency" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold mb-1.5 uppercase tracking-wider">Agência:</label>
            <input 
              type="text" 
              id="bank_agency" 
              v-model="bankForm.agency" 
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
              :class="{'!border-rose-500': bankErrors.agency}" 
            />
            <p v-if="bankErrors.agency" class="text-rose-600 dark:text-rose-400 text-xs mt-1">{{ bankErrors.agency }}</p>
          </div>
          <div>
            <label for="bank_account_number" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold mb-1.5 uppercase tracking-wider">Número da Conta:</label>
            <input 
              type="text" 
              id="bank_account_number" 
              v-model="bankForm.account_number" 
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
              :class="{'!border-rose-500': bankErrors.account_number}" 
            />
            <p v-if="bankErrors.account_number" class="text-rose-600 dark:text-rose-400 text-xs mt-1">{{ bankErrors.account_number }}</p>
          </div>
          <div>
            <label for="bank_balance" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold mb-1.5 uppercase tracking-wider">Saldo Inicial:</label>
            <input 
              type="number" 
              id="bank_balance" 
              v-model="bankForm.balance" 
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
              step="0.01" 
              :class="{'!border-rose-500': bankErrors.balance}" 
            />
            <p v-if="bankErrors.balance" class="text-rose-600 dark:text-rose-400 text-xs mt-1">{{ bankErrors.balance }}</p>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <AppButton type="button" @click="showBankModal = false" variant="secondary">Cancelar</AppButton>
            <AppButton type="submit" variant="primary">Salvar</AppButton>
          </div>
        </form>
      </div>
    </div>

    <!-- Chart Account Modal -->
    <div v-if="showChartAccountModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex justify-center items-center z-50 p-4">
      <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl p-6 border border-slate-200 dark:border-slate-800 max-w-md w-full">
        <h2 class="text-base font-semibold mb-4 text-slate-900 dark:text-slate-100">{{ isEditChartAccountMode ? 'Editar' : 'Nova' }} Conta Contábil</h2>
        <form @submit.prevent="saveChartAccount" class="space-y-4">
          <div>
            <label for="chart_account_name" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold mb-1.5 uppercase tracking-wider">Nome:</label>
            <input 
              type="text" 
              id="chart_account_name" 
              v-model="chartAccountForm.name" 
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
              :class="{'!border-rose-500': chartAccountErrors.name}" 
            />
            <p v-if="chartAccountErrors.name" class="text-rose-600 dark:text-rose-400 text-xs mt-1">{{ chartAccountErrors.name }}</p>
          </div>
          <div>
            <label for="chart_account_number" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold mb-1.5 uppercase tracking-wider">Número da Conta:</label>
            <input 
              type="text" 
              id="chart_account_number" 
              v-model="chartAccountForm.account_number" 
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
              :class="{'!border-rose-500': chartAccountErrors.account_number}" 
            />
            <p v-if="chartAccountErrors.account_number" class="text-rose-600 dark:text-rose-400 text-xs mt-1">{{ chartAccountErrors.account_number }}</p>
          </div>
          <div>
            <label for="chart_account_type" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold mb-1.5 uppercase tracking-wider">Tipo:</label>
            <select 
              id="chart_account_type" 
              v-model="chartAccountForm.type" 
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
              :class="{'!border-rose-500': chartAccountErrors.type}"
            >
              <option value="">Selecione um tipo</option>
              <option value="receita">Receita</option>
              <option value="despesa">Despesa</option>
              <option value="ativo">Ativo</option>
              <option value="passivo">Passivo</option>
              <option value="patrimonio_liquido">Patrimônio Líquido</option>
            </select>
            <p v-if="chartAccountErrors.type" class="text-rose-600 dark:text-rose-400 text-xs mt-1">{{ chartAccountErrors.type }}</p>
          </div>
          <div>
            <label for="chart_account_parent" class="block text-slate-700 dark:text-slate-300 text-xs font-semibold mb-1.5 uppercase tracking-wider">Conta Pai (Opcional):</label>
            <select 
              id="chart_account_parent" 
              v-model="chartAccountForm.parent_id" 
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
            >
              <option value="">Nenhuma</option>
              <option v-for="account in financeDataStore.chartAccounts" :key="account.id" :value="account.id">
                {{ account.name }}
              </option>
            </select>
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <AppButton type="button" @click="showChartAccountModal = false" variant="secondary">Cancelar</AppButton>
            <AppButton type="submit" variant="primary">Salvar</AppButton>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, h } from 'vue';
import { useFinanceDataStore } from './stores/financeDataStore';
import api from '@/utils/api';
import { useUser } from '@/composables/useUser';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import AppButton from '../components/ui/AppButton.vue';
import NeonButton from '../components/NeonButton.vue';
import TopCard from '../components/ui/TopCard.vue';

const financeDataStore = useFinanceDataStore();

const { user } = useUser();

const showBankModal = ref(false);
const isEditBankMode = ref(false);
const bankForm = ref({
  id: null,
  condominium_id: user.value?.condominium_id,
  name: '',
  agency: '',
  account_number: '',
  balance: 0,
});

const bankErrors = ref({});

const showChartAccountModal = ref(false);
const isEditChartAccountMode = ref(false);
const chartAccountForm = ref({
  id: null,
  condominium_id: user.value?.condominium_id,
  parent_id: null,
  name: '',
  type: '',
  account_number: '',
});

const chartAccountErrors = ref({});

const BankIcon = {
  render() {
    return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 7h18M3 12h18M3 17h18' })
    ]);
  }
};

onMounted(() => {
  financeDataStore.fetchBanks();
  financeDataStore.fetchChartAccounts();
});

// Bank Account Functions
function openCreateBankModal() {
  isEditBankMode.value = false;
  bankForm.value = {
    id: null,
    condominium_id: user.value?.condominium_id,
    name: '',
    agency: '',
    account_number: '',
    balance: 0,
  };
  bankErrors.value = {}; // Clear errors
  showBankModal.value = true;
}

function openEditBankModal(bank) {
  isEditBankMode.value = true;
  bankForm.value = { ...bank };
  bankErrors.value = {}; // Clear errors
  showBankModal.value = true;
}

async function saveBank() {
  bankErrors.value = {};
  let isValid = true;

  if (!bankForm.value.name) {
    bankErrors.value.name = 'O nome é obrigatório.';
    isValid = false;
  }

  if (!bankForm.value.account_number) {
    bankErrors.value.account_number = 'O número da conta é obrigatório.';
    isValid = false;
  }

  if (bankForm.value.balance < 0) {
    bankErrors.value.balance = 'O saldo inicial não pode ser negativo.';
    isValid = false;
  }

  if (!isValid) {
    return;
  }

  try {
    if (isEditBankMode.value) {
      await api.put(`/finance/banks/${bankForm.value.id}`, bankForm.value);
    } else {
      await api.post('/finance/banks', bankForm.value);
    }
    showBankModal.value = false;
    financeDataStore.fetchBanks();
  } catch (error) {
    console.error('Erro ao salvar conta bancária:', error);
    alert('Erro ao salvar conta bancária. Verifique o console para mais detalhes.');
  }
}

async function deleteBank(bank) {
  if (confirm(`Tem certeza que deseja excluir a conta bancária ${bank.name}?`)) {
    try {
      await api.delete(`/finance/banks/${bank.id}`);
      financeDataStore.fetchBanks();
    } catch (error) {
      console.error('Erro ao excluir conta bancária:', error);
      alert('Erro ao excluir conta bancária. Verifique o console para mais detalhes.');
    }
  }
}

// Chart of Account Functions
function openCreateChartAccountModal() {
  isEditChartAccountMode.value = false;
  chartAccountForm.value = {
    id: null,
    condominium_id: user.value?.condominium_id,
    parent_id: null,
    name: '',
    type: '',
    account_number: '',
  };
  chartAccountErrors.value = {}; // Clear errors
  showChartAccountModal.value = true;
}

function openEditChartAccountModal(account) {
  isEditChartAccountMode.value = true;
  chartAccountForm.value = { ...account };
  chartAccountErrors.value = {}; // Clear errors
  showChartAccountModal.value = true;
}

async function saveChartAccount() {
  chartAccountErrors.value = {};
  let isValid = true;

  if (!chartAccountForm.value.name) {
    chartAccountErrors.value.name = 'O nome é obrigatório.';
    isValid = false;
  }

  if (!chartAccountForm.value.type) {
    chartAccountErrors.value.type = 'O tipo é obrigatório.';
    isValid = false;
  }

  if (!isValid) {
    return;
  }

  try {
    if (isEditChartAccountMode.value) {
      await api.put(`/finance/chart-accounts/${chartAccountForm.value.id}`, chartAccountForm.value);
    } else {
      await api.post('/finance/chart-accounts', chartAccountForm.value);
    }
    showChartAccountModal.value = false;
    financeDataStore.fetchChartAccounts();
  } catch (error) {
    console.error('Erro ao salvar conta contábil:', error);
    alert('Erro ao salvar conta contábil. Verifique o console para mais detalhes.');
  }
}

async function deleteChartAccount(account) {
  if (confirm(`Tem certeza que deseja excluir a conta contábil ${account.name}?`)) {
    try {
      await api.delete(`/finance/chart-accounts/${account.id}`);
      financeDataStore.fetchChartAccounts();
    } catch (error) {
      console.error('Erro ao excluir conta contábil:', error);
      alert('Erro ao excluir conta contábil. Verifique o console para mais detalhes.');
    }
  }
}
</script>
