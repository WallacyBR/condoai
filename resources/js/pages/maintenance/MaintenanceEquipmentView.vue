<template>
  <AuthenticatedLayout page-title="Equipamentos de Manutenção">
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="bg-gray-800/50 backdrop-blur-md shadow-xl rounded-2xl p-6 border border-cyan-500/30">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold text-white">Gerenciar Equipamentos</h2>
          <button @click="openFormModal()"
                  class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg shadow-md transition-colors">
            Novo Equipamento
          </button>
        </div>

        <!-- Tabela de Equipamentos -->
        <div v-if="isLoadingEquipments" class="text-center text-gray-400 py-8">Carregando equipamentos...</div>
        <div v-else-if="equipments.length === 0" class="text-center text-gray-400 py-8">Nenhum equipamento encontrado.</div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-700/50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Nome</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Área</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Localização</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Acesso QR</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-gray-800/30 divide-y divide-gray-700">
              <tr v-for="equipment in equipments" :key="equipment.id" class="hover:bg-gray-700/60 transition-colors duration-150">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">{{ equipment.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ equipment.maintenance_area?.name || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ equipment.location_details || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ formatAccessLevel(equipment.qr_code_access_level) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                  <button @click="openFormModal(equipment)" class="text-cyan-400 hover:text-cyan-300 transition-colors">Editar</button>
                  <button @click="confirmDeleteEquipment(equipment.id)" class="text-red-500 hover:text-red-400 transition-colors">Excluir</button>
                  <button @click="viewQrCode(equipment)" class="text-blue-400 hover:text-blue-300 transition-colors">Ver QR</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginação -->
        <div v-if="!isLoadingEquipments && pagination.total > pagination.per_page" class="mt-6 flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
            <p class="text-sm text-gray-400">
                Mostrando {{ pagination.from || 0 }} a {{ pagination.to || 0 }} de {{ pagination.total || 0 }} resultados.
            </p>
            <div class="flex space-x-1">
                <button @click="fetchEquipments(1)" :disabled="pagination.current_page === 1"
                        class="px-3 py-1 text-sm bg-gray-600 hover:bg-gray-500 rounded disabled:opacity-50 transition-colors">
                    Primeira
                </button>
                <button @click="fetchEquipments(pagination.current_page - 1)" :disabled="!pagination.prev_page_url"
                        class="px-3 py-1 text-sm bg-gray-600 hover:bg-gray-500 rounded disabled:opacity-50 transition-colors">
                    Anterior
                </button>
                 <span class="px-3 py-1 text-sm text-gray-300">Página {{ pagination.current_page }} de {{ pagination.last_page }}</span>
                <button @click="fetchEquipments(pagination.current_page + 1)" :disabled="!pagination.next_page_url"
                        class="px-3 py-1 text-sm bg-gray-600 hover:bg-gray-500 rounded disabled:opacity-50 transition-colors">
                    Próxima
                </button>
                 <button @click="fetchEquipments(pagination.last_page)" :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-1 text-sm bg-gray-600 hover:bg-gray-500 rounded disabled:opacity-50 transition-colors">
                    Última
                </button>
            </div>
        </div>
      </div>
    </div>

    <MaintenanceEquipmentForm
      :show="isFormModalOpen"
      :equipment-data="selectedEquipment"
      @close="closeFormModal"
      @saved="handleSave"
    />

    <Modal :show="isDeleteConfirmOpen" @close="isDeleteConfirmOpen = false" max-width="md">
        <template #title>Confirmar Exclusão</template>
        <div class="p-6">
            <p class="text-gray-300">Tem certeza que deseja excluir o equipamento "{{ equipmentToDelete?.name || 'selecionado' }}"? Esta ação não pode ser desfeita.</p>
        </div>
        <template #footer>
            <button @click="isDeleteConfirmOpen = false" class="px-4 py-2 text-gray-300 bg-gray-600 hover:bg-gray-500 rounded-md shadow-sm transition-colors">Cancelar</button>
            <button @click="deleteEquipment" :disabled="isDeleting" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md ml-3 shadow-sm transition-colors disabled:opacity-50">
              {{ isDeleting ? 'Excluindo...' : 'Excluir' }}
            </button>
        </template>
    </Modal>

    <Modal :show="isQrCodeModalOpen" @close="isQrCodeModalOpen = false" max-width="sm">
      <template #title>QR Code do Equipamento</template>
      <div class="p-6 text-center">
        <p class="text-gray-300 mb-4">Escaneie o QR Code para acessar o relatório público deste equipamento.</p>
        <QrCodeDisplay
          v-if="selectedEquipmentForQr"
          :value="`${publicReportBaseUrl}/${selectedEquipmentForQr.qr_code_hash}`"
          :size="250"
          :filename-prefix="`qrcode_${selectedEquipmentForQr.name.replace(/\s+/g, '_')}`"
        />
        <div class="mt-4">
          <h4 class="text-md font-medium text-gray-200 mb-2">Nível de Acesso: {{ formatAccessLevel(selectedEquipmentForQr?.qr_code_access_level) }}</h4>
          <AccessLevelSelector
            v-if="selectedEquipmentForQr"
            :current-level="selectedEquipmentForQr.qr_code_access_level"
            @update:level="updateAccessLevel"
          />
          <div v-if="selectedEquipmentForQr?.qr_code_access_level === 'specific_users'" class="mt-4">
            <h5 class="text-sm font-medium text-gray-200 mb-2">Usuários com Acesso Específico:</h5>
            <select multiple v-model="selectedQrAccessUsers" class="mt-1 block w-full h-32 bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm">
              <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
            <button @click="syncQrAccessUsers" :disabled="isSyncingQrUsers" class="mt-3 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md shadow-sm transition-colors disabled:opacity-50">
              {{ isSyncingQrUsers ? 'Sincronizando...' : 'Sincronizar Usuários' }}
            </button>
          </div>
        </div>
      </div>
    </Modal>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import MaintenanceEquipmentForm from '@/components/maintenance/MaintenanceEquipmentForm.vue';
import QrCodeDisplay from '@/components/QrCodeDisplay.vue';
import AccessLevelSelector from '@/components/maintenance/AccessLevelSelector.vue';
import Modal from '@/components/Modal.vue';
import api from '@/utils/api';

const equipments = ref([]);
const isLoadingEquipments = ref(false);
const isFormModalOpen = ref(false);
const selectedEquipment = ref(null);

const pagination = ref({
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0,
    total: 0,
    per_page: 15,
    next_page_url: null,
    prev_page_url: null,
});

const isDeleteConfirmOpen = ref(false);
const equipmentToDeleteId = ref(null);
const isDeleting = ref(false);

const equipmentToDelete = computed(() => {
    return equipments.value.find(e => e.id === equipmentToDeleteId.value);
});

const isQrCodeModalOpen = ref(false);
const selectedEquipmentForQr = ref(null);
const publicReportBaseUrl = ref(`${window.location.origin}/api/qr`); // Ajuste conforme sua rota pública

const availableUsers = ref([]);
const selectedQrAccessUsers = ref([]);
const isSyncingQrUsers = ref(false);

async function fetchEquipments(page = 1) {
  isLoadingEquipments.value = true;
  try {
    const response = await api.get(`/maintenance/equipments?page=${page}&per_page=${pagination.value.per_page}`);
    equipments.value = response.data.data || [];

    const { current_page, last_page, from, to, total, per_page: respPerPage, next_page_url, prev_page_url } = response.data;
    pagination.value = { current_page, last_page, from, to, total, per_page: respPerPage || pagination.value.per_page, next_page_url, prev_page_url };

  } catch (error) {
    console.error('Erro ao buscar equipamentos:', error);
    equipments.value = [];
  } finally {
    isLoadingEquipments.value = false;
  }
}

async function fetchUsers() {
  try {
    const response = await api.get('/users?per_page=1000'); // Buscar todos os usuários para seleção
    availableUsers.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erro ao buscar usuários:', error);
  }
}

function openFormModal(equipment = null) {
  selectedEquipment.value = equipment ? JSON.parse(JSON.stringify(equipment)) : null;
  isFormModalOpen.value = true;
}

function closeFormModal() {
  isFormModalOpen.value = false;
  selectedEquipment.value = null;
}

function handleSave() {
  fetchEquipments(pagination.value.current_page);
  closeFormModal();
}

function confirmDeleteEquipment(id) {
    equipmentToDeleteId.value = id;
    isDeleteConfirmOpen.value = true;
}

async function deleteEquipment() {
    if (!equipmentToDeleteId.value) return;
    isDeleting.value = true;
    try {
        await api.delete(`/maintenance/equipments/${equipmentToDeleteId.value}`);
        let currentPage = pagination.value.current_page;
        if (equipments.value.length === 1 && currentPage > 1) {
            currentPage--;
        }
        fetchEquipments(currentPage);
    } catch (error) {
        console.error('Erro ao excluir equipamento:', error);
        if (error.response && error.response.data.message) {
            alert(`Erro ao excluir: ${error.response.data.message}`);
        } else {
            alert('Ocorreu um erro ao excluir o equipamento.');
        }
    } finally {
        isDeleteConfirmOpen.value = false;
        equipmentToDeleteId.value = null;
        isDeleting.value = false;
    }
}

function formatAccessLevel(level) {
  const levels = {
    public: 'Público',
    internal_users: 'Usuários Internos',
    specific_users: 'Usuários Específicos',
  };
  return levels[level] || level;
}

function viewQrCode(equipment) {
  selectedEquipmentForQr.value = equipment;
  selectedQrAccessUsers.value = equipment.qr_access_users?.map(u => u.id) || [];
  isQrCodeModalOpen.value = true;
}

async function updateAccessLevel(newLevel) {
  if (!selectedEquipmentForQr.value) return;
  try {
    await api.put(`/maintenance/equipments/${selectedEquipmentForQr.value.id}`, { qr_code_access_level: newLevel });
    selectedEquipmentForQr.value.qr_code_access_level = newLevel;
    // Se mudar para algo diferente de specific_users, limpar a lista de usuários
    if (newLevel !== 'specific_users') {
      selectedQrAccessUsers.value = [];
      await api.post(`/maintenance/equipments/${selectedEquipmentForQr.value.id}/sync-qr-access-users`, { user_ids: [] });
    }
    alert('Nível de acesso atualizado com sucesso!');
    fetchEquipments(pagination.value.current_page); // Refresh list
  } catch (error) {
    console.error('Erro ao atualizar nível de acesso:', error);
    alert('Erro ao atualizar nível de acesso.');
  }
}

async function syncQrAccessUsers() {
  if (!selectedEquipmentForQr.value) return;
  isSyncingQrUsers.value = true;
  try {
    await api.post(`/maintenance/equipments/${selectedEquipmentForQr.value.id}/sync-qr-access-users`, { user_ids: selectedQrAccessUsers.value });
    alert('Usuários sincronizados com sucesso!');
    fetchEquipments(pagination.value.current_page); // Refresh list
  } catch (error) {
    console.error('Erro ao sincronizar usuários:', error);
    alert('Erro ao sincronizar usuários.');
  } finally {
    isSyncingQrUsers.value = false;
  }
}

onMounted(() => {
  fetchEquipments();
  fetchUsers();
});
</script>
