<template>
  <AuthenticatedLayout page-title="Detalhes do Registro de Manutenção">
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="bg-gray-800/50 backdrop-blur-md shadow-xl rounded-2xl p-6 border border-cyan-500/30">
        <h2 class="text-xl font-semibold text-white mb-6">Detalhes do Registro #{{ recordId }}</h2>
        <div v-if="isLoading" class="text-center text-gray-400 py-10">Carregando detalhes do registro...</div>
        <div v-else-if="!record" class="text-center text-gray-400 py-10">Registro não encontrado.</div>
        <div v-else class="space-y-4 text-gray-300">
          <p><strong>Equipamento:</strong> {{ record.schedule?.equipment?.name || 'N/A' }}</p>
          <p><strong>Agendamento:</strong> {{ record.schedule?.title || 'N/A' }}</p>
          <p><strong>Realizado por:</strong> {{ record.user?.name || 'N/A' }}</p>
          <p><strong>Data da Realização:</strong> {{ formatDateTime(record.performed_at) }}</p>
          <p><strong>Status:</strong> <span :class="statusClass(record.status)">{{ formatStatus(record.status) }}</span></p>
          <p v-if="record.reviewer"><strong>Revisado por:</strong> {{ record.reviewer.name }} em {{ formatDateTime(record.reviewed_at) }}</p>
          <div>
            <h3 class="text-md font-semibold text-gray-200 mt-4 mb-2">Notas:</h3>
            <p class="whitespace-pre-wrap p-3 bg-gray-700/50 rounded-md border border-gray-600">
              {{ record.notes || 'Nenhuma nota.' }}
            </p>
          </div>

          <div v-if="record.photos && record.photos.length > 0">
            <h3 class="text-md font-semibold text-gray-200 mt-4 mb-2">Fotos:</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
              <img v-for="photo in record.photos" :key="photo.id" :src="`/storage/${photo.file_path}`" alt="Maintenance Photo" class="w-full h-32 object-cover rounded-md shadow-md cursor-pointer" @click="viewImage(`/storage/${photo.file_path}`)">
            </div>
          </div>

          <div v-if="record.signature">
            <h3 class="text-md font-semibold text-gray-200 mt-4 mb-2">Assinatura:</h3>
            <img :src="`/storage/${record.signature.signature_path}`" alt="Signature" class="w-48 h-auto border border-gray-600 rounded-md shadow-md">
          </div>

          <div class="mt-6 flex space-x-3">
            <button @click="updateRecordStatus('reopened')" :disabled="isUpdatingStatus" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg shadow-md disabled:opacity-50">Reabrir</button>
            <button @click="updateRecordStatus('under_review')" :disabled="isUpdatingStatus" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow-md disabled:opacity-50">Para Revisão</button>
            <button @click="updateRecordStatus('archived')" :disabled="isUpdatingStatus" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg shadow-md disabled:opacity-50">Arquivar</button>
            <button @click="updateRecordStatus('completed')" :disabled="isUpdatingStatus" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow-md disabled:opacity-50">Marcar como Concluído</button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import { useRoute } from 'vue-router';
import { ref, onMounted } from 'vue';
import api from '@/utils/api';
import { format, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';

const route = useRoute();
const recordId = ref(route.params.id);
const record = ref(null);
const isLoading = ref(true);
const isUpdatingStatus = ref(false);

const fetchRecordDetails = async () => {
  isLoading.value = true;
  try {
    const response = await api.get(`/maintenance/records/${recordId.value}`);
    record.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar detalhes do registro:', error);
    record.value = null;
  } finally {
    isLoading.value = false;
  }
};

const formatDateTime = (dateTimeString) => {
  if (!dateTimeString) return 'N/A';
  return format(parseISO(dateTimeString), "dd/MM/yyyy HH:mm", { locale: ptBR });
};

const formatStatus = (status) => {
  const statuses = {
    pending: 'Pendente',
    completed: 'Concluído',
    overdue: 'Atrasado',
    under_review: 'Em Revisão',
    reopened: 'Reaberto',
    archived: 'Arquivado',
  };
  return statuses[status] || status;
};

const statusClass = (status) => {
  switch (status) {
    case 'completed': return 'px-2 py-1 text-xs font-semibold rounded-full bg-green-600 text-green-100';
    case 'pending': return 'px-2 py-1 text-xs font-semibold rounded-full bg-yellow-500 text-yellow-100';
    case 'overdue': return 'px-2 py-1 text-xs font-semibold rounded-full bg-red-600 text-red-100';
    case 'under_review': return 'px-2 py-1 text-xs font-semibold rounded-full bg-blue-500 text-blue-100';
    case 'reopened': return 'px-2 py-1 text-xs font-semibold rounded-full bg-orange-500 text-orange-100';
    case 'archived': return 'px-2 py-1 text-xs font-semibold rounded-full bg-gray-500 text-gray-100';
    default: return '';
  }
};

const updateRecordStatus = async (newStatus) => {
  isUpdatingStatus.value = true;
  try {
    const response = await api.post(`/maintenance/records/${recordId.value}/update-status`, { status: newStatus });
    record.value = response.data; // Atualiza o registro com a resposta do servidor
    alert(`Status atualizado para: ${formatStatus(newStatus)}`);
  } catch (error) {
    console.error('Erro ao atualizar status:', error);
    alert('Erro ao atualizar o status do registro.');
  } finally {
    isUpdatingStatus.value = false;
  }
};

const viewImage = (imageUrl) => {
  window.open(imageUrl, '_blank');
};

onMounted(() => {
  fetchRecordDetails();
});
</script>
