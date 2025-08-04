<template>
  <AuthenticatedLayout page-title="Solicitações de Despesa">
    <div class="space-y-6">
      <TopCard title="Solicitações de Despesa" :icon="ReceiptIcon">
        <template #actions>
          <NeonButton @click="openCreateModal()" size="md">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nova Solicitação
          </NeonButton>
        </template>
      </TopCard>

      <div v-if="expenseRequests.length === 0" class="text-center py-8 bg-gray-800 rounded-lg shadow-lg p-6 border border-gray-700">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-300">Nenhuma solicitação de despesa encontrada</h3>
        <p class="mt-1 text-sm text-gray-500">
          Comece adicionando uma nova solicitação.
        </p>
      </div>

      <ul v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <li v-for="req in expenseRequests" :key="req.id" 
            class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2 transition-all duration-300 hover:shadow-2xl hover:scale-105"
            :class="{
              'border-yellow-500/20 hover:border-yellow-500/40': req.status === 'pendente',
              'border-green-500/20 hover:border-green-500/40': req.status === 'aprovado'
            }">
          <div class="flex justify-between items-start mb-4">
            <div>
              <h3 class="text-lg font-semibold text-white">{{ req.description }}</h3>
              <p class="text-sm text-gray-400">Vencimento: {{ new Date(req.due_date).toLocaleDateString() }}</p>
            </div>
            <span class="px-3 py-1 text-sm font-semibold rounded-full"
                  :class="{
                    'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30': req.status === 'pendente',
                    'bg-green-500/20 text-green-300 border border-green-500/30': req.status === 'aprovado'
                  }">
              {{ req.status === 'pendente' ? 'Pendente' : 'Aprovado' }}
            </span>
          </div>

          <div class="bg-gray-800/50 rounded-lg p-4 mb-4 border border-gray-700/50">
            <p class="text-white text-xl font-bold mb-2">R$ {{ parseFloat(req.amount).toFixed(2) }}</p>
            <p class="text-sm text-gray-400">Solicitado por: <span class="font-medium text-white">{{ req.requested_by_user?.name || req.requested_by }}</span></p>
            <p v-if="req.approved_by_user" class="text-sm text-gray-400">Aprovado por: <span class="font-medium text-white">{{ req.approved_by_user.name }}</span> em {{ new Date(req.approved_at).toLocaleDateString() }}</p>
          </div>

          <div class="flex space-x-2">
            <NeonButton @click="openEditModal(req)" size="sm" variant="info" class="flex-1">
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Editar
            </NeonButton>
            <NeonButton @click="deleteExpenseRequest(req)" size="sm" variant="danger" class="flex-1">
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Excluir
            </NeonButton>
            <NeonButton v-if="req.status !== 'aprovado'" @click="approveExpenseRequest(req)" size="sm" variant="success" class="flex-1">
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Aprovar
            </NeonButton>
          </div>
        </li>
      </ul>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex justify-center items-center z-50">
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-2xl p-6 border-2 border-cyan-500/30 max-w-2xl w-full mx-4">
        <h2 class="text-xl font-bold mb-4 text-white">{{ isEditMode ? 'Editar' : 'Nova' }} Solicitação de Despesa</h2>
        <form @submit.prevent="saveExpenseRequest">
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
            <label for="requested_by" class="block text-gray-300 text-sm font-medium mb-1">Solicitado por (ID do Usuário):</label>
            <input type="number" id="requested_by" v-model="form.requested_by" 
                   class="w-full p-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors"
                   :class="{'border-red-500': errors.requested_by}" />
            <p v-if="errors.requested_by" class="text-red-500 text-xs mt-1">{{ errors.requested_by }}</p>
          </div>
          <div class="mb-4">
            <label for="status" class="block text-gray-300 text-sm font-medium mb-1">Status:</label>
            <input type="text" id="status" v-model="form.status" 
                   class="w-full p-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors"
                   :class="{'border-red-500': errors.status}" />
            <p v-if="errors.status" class="text-red-500 text-xs mt-1">{{ errors.status }}</p>
          </div>

          <div v-if="isEditMode" class="mb-4 p-4 bg-gray-800 rounded-lg border border-gray-700">
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
import { ref, onMounted, h } from 'vue';
import { useTransactionsStore } from './stores/transactionsStore';
import api from '@/utils/api'; // Assuming you have an api utility
import { useUser } from '@/composables/useUser';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../components/NeonButton.vue';
import TopCard from '../components/ui/TopCard.vue';

const transactionsStore = useTransactionsStore();
const expenseRequests = ref([]);

const { user } = useUser();

const showModal = ref(false);
const isEditMode = ref(false);
const form = ref({
  id: null,
  condominium_id: user.value?.condominium_id,
  requested_by: null,
  amount: 0,
  due_date: '',
  description: '',
  status: 'pendente',
});

const currentAttachments = ref([]);

const errors = ref({});

const ReceiptIcon = {
  render() {
    return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' })
    ]);
  }
};

onMounted(() => {
  fetchExpenseRequests();
});

async function fetchExpenseRequests() {
  try {
    const { data } = await api.get('/finance/expense-requests');
    expenseRequests.value = data;
  } catch (e) {
    console.error('Erro ao carregar solicitações de despesa:', e);
  }
}

function openCreateModal() {
  isEditMode.value = false;
  form.value = {
    id: null,
    condominium_id: user.value?.condominium_id,
    requested_by: null,
    amount: 0,
    due_date: '',
    description: '',
    status: 'pendente',
  };
  currentAttachments.value = [];
  errors.value = {}; // Clear errors
  showModal.value = true;
}

async function openEditModal(req) {
  isEditMode.value = true;
  form.value = { ...req };
  form.value.due_date = req.due_date ? new Date(req.due_date).toISOString().split('T')[0] : '';

  try {
    const { data } = await api.get(`/finance/expense-requests/${req.id}/attachments`);
    currentAttachments.value = data;
  } catch (e) {
    console.error('Erro ao carregar anexos da solicitação de despesa:', e);
  }

  errors.value = {}; // Clear errors
  showModal.value = true;
}

async function saveExpenseRequest() {
  if (!validateForm()) {
    return;
  }
  try {
    if (isEditMode.value) {
      await transactionsStore.updateExpenseRequest(form.value.id, form.value);
    } else {
      await transactionsStore.createExpenseRequest(form.value);
    }
    showModal.value = false;
    fetchExpenseRequests();
  } catch (error) {
    console.error('Erro ao salvar solicitação de despesa:', error);
    alert('Erro ao salvar solicitação de despesa. Verifique o console para mais detalhes.');
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

  if (!form.value.requested_by) {
    errors.value.requested_by = 'O solicitante é obrigatório.';
    isValid = false;
  }

  if (!form.value.status) {
    errors.value.status = 'O status é obrigatório.';
    isValid = false;
  }

  return isValid;
}

async function deleteExpenseRequest(req) {
  if (confirm(`Tem certeza que deseja excluir esta solicitação de despesa?`)) {
    try {
      await transactionsStore.deleteExpenseRequest(req.id);
      fetchExpenseRequests();
    } catch (error) {
      console.error('Erro ao excluir solicitação de despesa:', error);
      alert('Erro ao excluir solicitação de despesa. Verifique o console para mais detalhes.');
    }
  }
}

async function approveExpenseRequest(req) {
  if (confirm(`Tem certeza que deseja aprovar esta solicitação de despesa?`)) {
    try {
      await transactionsStore.approveExpenseRequest(req.id);
      fetchExpenseRequests();
    } catch (error) {
      console.error('Erro ao aprovar solicitação de despesa:', error);
      alert('Erro ao aprovar solicitação de despesa. Verifique o console para mais detalhes.');
    }
  }
}

async function handleAttachmentUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  try {
    const newAttachment = await transactionsStore.uploadExpenseRequestAttachment(form.value.id, file);
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
      await transactionsStore.deleteExpenseRequestAttachment(form.value.id, attachment.id);
      currentAttachments.value = currentAttachments.value.filter(att => att.id !== attachment.id);
      alert('Anexo removido com sucesso!');
    } catch (error) {
      console.error('Erro ao remover anexo:', error);
      alert('Erro ao remover anexo. Verifique o console para mais detalhes.');
    }
  }
}
</script>