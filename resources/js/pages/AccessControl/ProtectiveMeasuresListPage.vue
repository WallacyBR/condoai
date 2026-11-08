<template>
  <AuthenticatedLayout page-title="Medidas Protetivas">
    <HeaderCard
      title="Gestão de Medidas Protetivas"
      description="Consulte, cadastre e gerencie as medidas protetivas."
      icon-path="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
      icon-color="orange"
    />

    <div class="mt-6 mb-6 flex justify-end">
      <NeonButton @click="goToNewProtectiveMeasurePage" size="md" variant="primary_action">
        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        Nova Medida Protetiva
      </NeonButton>
    </div>

    <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-xl p-6">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold text-white">Lista de Medidas Protetivas</h3>
        <NeonButton @click="fetchProtectiveMeasures" :loading="loading" size="sm" variant="secondary">
          <svg class="h-4 w-4" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
          <span v-if="!loading" class="ml-2">Atualizar</span>
        </NeonButton>
      </div>

      <div v-if="loading && measures.length === 0" class="text-center py-10">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-400 mx-auto"></div>
        <p class="text-gray-400 mt-3">Carregando medidas protetivas...</p>
      </div>

      <div v-if="!loading && error" class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-center">
          <p class="text-red-300">{{ error }}</p>
      </div>

      <div v-if="!loading && measures.length === 0 && !error" class="text-center py-10">
        <svg class="h-12 w-12 mx-auto text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        <p class="text-gray-400 mt-3">Nenhuma medida protetiva encontrada.</p>
      </div>

      <div v-if="measures.length > 0" class="space-y-4">
        <ProtectiveMeasureCard
          v-for="measure in measures"
          :key="measure.id"
          :measure="measure"
          @edit-measure="editMeasure"
          @toggle-measure-status="showToggleStatusModal"
          @delete-measure="showDeleteModal"
        />
      </div>
    </div>

    <!-- Modal de Confirmação para Ativar/Desativar -->
    <ConfirmActionModal
      :show="confirmToggleStatusModal"
      :title="`${selectedMeasureForStatusToggle?.is_active ? 'Desativar' : 'Ativar'} Medida Protetiva`"
      :message="`Tem certeza que deseja ${selectedMeasureForStatusToggle?.is_active ? 'DESATIVAR' : 'ATIVAR'} esta medida protetiva?`"
      :confirm-button-text="`Sim, ${selectedMeasureForStatusToggle?.is_active ? 'Desativar' : 'Ativar'}`"
      :confirm-button-variant="selectedMeasureForStatusToggle?.is_active ? 'danger' : 'success'"
      :is-loading="togglingStatus"
      @confirm="confirmToggleStatus"
      @close="confirmToggleStatusModal = false"
    />

    <!-- Modal de Confirmação para Deletar -->
    <ConfirmActionModal
      :show="confirmDeleteModal"
      title="Excluir Medida Protetiva"
      message="Tem certeza que deseja excluir permanentemente esta medida protetiva? Esta ação não pode ser desfeita."
      confirm-button-text="Sim, Excluir"
      confirm-button-variant="danger"
      :is-loading="deletingMeasure"
      @confirm="confirmDelete"
      @close="confirmDeleteModal = false"
    />

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from '../../components/layouts/AuthenticatedLayout.vue';
import HeaderCard from '../../components/layouts/HeaderCard.vue';
import NeonButton from '../../components/NeonButton.vue';
import ProtectiveMeasureCard from '../../components/access_control/ProtectiveMeasureCard.vue'; // Será criado
import ConfirmActionModal from '../../components/Modal.vue';
import api from '../../utils/api';
import { useToast } from '../../composables/useToast';

const router = useRouter();
const { successToast, errorToast } = useToast();

const measures = ref([]);
const loading = ref(false);
const error = ref('');

const confirmToggleStatusModal = ref(false);
const selectedMeasureForStatusToggle = ref(null);
const togglingStatus = ref(false);

const confirmDeleteModal = ref(false);
const selectedMeasureForDelete = ref(null);
const deletingMeasure = ref(false);

const fetchProtectiveMeasures = async () => {
  loading.value = true;
  error.value = '';
  try {
    const response = await api.get('/api/access-control/protective-measures');
    measures.value = response.data || [];
  } catch (e) {
    console.error("Erro ao buscar medidas protetivas:", e);
    error.value = 'Falha ao carregar as medidas protetivas.';
    errorToast(error.value);
  } finally {
    loading.value = false;
  }
};

const goToNewProtectiveMeasurePage = () => {
  router.push({ name: 'NewProtectiveMeasure' });
};

const editMeasure = (measure) => {
  router.push({ name: 'EditProtectiveMeasure', params: { id: measure.id } });
};

const showToggleStatusModal = (measure) => {
  selectedMeasureForStatusToggle.value = measure;
  confirmToggleStatusModal.value = true;
};

const confirmToggleStatus = async () => {
  if (!selectedMeasureForStatusToggle.value) return;
  togglingStatus.value = true;
  try {
    const measure = selectedMeasureForStatusToggle.value;
    const newStatus = !measure.is_active;
    await api.put(`/api/access-control/protective-measures/${measure.id}`, { is_active: newStatus });
    successToast(`Medida protetiva ${newStatus ? 'ativada' : 'desativada'} com sucesso!`);
    fetchProtectiveMeasures();
  } catch (e) {
    console.error('Erro ao alterar status da medida:', e);
    errorToast(e.response?.data?.message || 'Falha ao alterar status da medida.');
  } finally {
    togglingStatus.value = false;
    confirmToggleStatusModal.value = false;
    selectedMeasureForStatusToggle.value = null;
  }
};

const showDeleteModal = (measure) => {
  selectedMeasureForDelete.value = measure;
  confirmDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!selectedMeasureForDelete.value) return;
  deletingMeasure.value = true;
  try {
    await api.delete(`/api/access-control/protective-measures/${selectedMeasureForDelete.value.id}`);
    successToast('Medida protetiva excluída com sucesso!');
    fetchProtectiveMeasures();
  } catch (e) {
    console.error('Erro ao excluir medida:', e);
    errorToast(e.response?.data?.message || 'Falha ao excluir medida.');
  } finally {
    deletingMeasure.value = false;
    confirmDeleteModal.value = false;
    selectedMeasureForDelete.value = null;
  }
};

onMounted(fetchProtectiveMeasures);
</script>
