<template>
  <AuthenticatedLayout page-title="Equipamentos de Manutenção">
    <TopCard 
      title="Gerenciar Equipamentos" 
      subtitle="Controle todos os equipamentos de manutenção do condomínio"
      color="cyan"
    >
      <template #actions>
        <NeonButton @click="redirectToCreate()" size="md">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          Novo Equipamento
        </NeonButton>
      </template>
    </TopCard>

    <div class="space-y-6">
      <!-- Cards de Estatísticas -->
      <div v-if="!isLoadingEquipments && !error" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <StatsCard
          label="Total de Equipamentos"
          :value="equipments.length"
          variant="blue"
          default-icon-path="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
        />
        <StatsCard
          label="Com QR Code"
          :value="equipmentsWithQr"
          variant="green"
          default-icon-path="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"
        />
        <StatsCard
          label="Acesso Público"
          :value="publicAccessEquipments"
          variant="purple"
          default-icon-path="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
        />
        <StatsCard
          label="Áreas Diferentes"
          :value="uniqueAreas"
          variant="orange"
          default-icon-path="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
        />
      </div>

      <!-- Filtros -->
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" v-model="filters.name" placeholder="Filtrar por nome do equipamento..."
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm dark:bg-gray-600 dark:text-gray-200">
            <input type="text" v-model="filters.area" placeholder="Filtrar por área..."
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm dark:bg-gray-600 dark:text-gray-200">
            <select v-model="filters.access_level" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm dark:bg-gray-600 dark:text-gray-200">
              <option value="">Todos os níveis de acesso</option>
              <option value="public">Público</option>
              <option value="internal_users">Usuários Internos</option>
              <option value="specific_users">Usuários Específicos</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoadingEquipments" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-cyan-500"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-red-300">
        {{ error }}
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredEquipments.length === 0" class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-300">Nenhum equipamento encontrado</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ filters.name || filters.area || filters.access_level ? 'Tente ajustar os filtros de busca' : 'Use o botão "Novo Equipamento" no cabeçalho para criar um novo equipamento' }}
        </p>
      </div>

      <!-- Equipment Cards Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="equipment in filteredEquipments" 
          :key="equipment.id" 
          class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2 border-cyan-500/20 hover:border-cyan-500/40 transition-all duration-300 hover:shadow-2xl hover:scale-105"
        >
          <!-- Header com Nome e Status -->
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
              <div class="h-12 w-12 rounded-full bg-cyan-100 flex items-center justify-center border-2 border-cyan-500/30">
                <svg class="h-6 w-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-white">{{ equipment.name }}</h3>
                <p class="text-sm text-gray-400">{{ equipment.maintenance_area?.name || 'Sem área' }}</p>
              </div>
            </div>
            <div class="flex flex-col items-end space-y-1">
              <span class="px-2 py-1 text-xs font-semibold rounded-full"
                    :class="getAccessLevelBadgeClass(equipment.qr_code_access_level)">
                {{ formatAccessLevel(equipment.qr_code_access_level) }}
              </span>
            </div>
          </div>

          <!-- Localização -->
          <div class="mb-4">
            <div class="flex items-center space-x-2 text-sm text-gray-300">
              <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span>{{ equipment.location_details || 'Localização não informada' }}</span>
            </div>
          </div>

          <!-- Informações Adicionais -->
          <div class="mb-6 space-y-2">
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-400">QR Code:</span>
              <span class="text-cyan-300 font-medium">{{ equipment.qr_code_hash ? 'Ativo' : 'Inativo' }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-400">Usuários com Acesso:</span>
              <span class="text-cyan-300 font-medium">{{ (equipment.qr_access_users || []).length }}</span>
            </div>
          </div>

          <!-- Ações -->
          <div class="flex space-x-2">
            <NeonButton @click="redirectToEdit(equipment.id)" size="sm" variant="secondary" class="flex-1">
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Editar
            </NeonButton>
            <NeonButton @click="viewQrCode(equipment)" size="sm" variant="info">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z" />
              </svg>
            </NeonButton>
            <NeonButton @click="confirmDeleteEquipment(equipment.id)" size="sm" variant="danger">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </NeonButton>
          </div>
        </div>
      </div>

      <!-- Paginação -->
      <div v-if="!isLoadingEquipments && pagination.total > pagination.per_page" class="flex justify-between items-center">
        <p class="text-sm text-gray-700 dark:text-gray-400">
          Mostrando {{ pagination.from || 0 }} a {{ pagination.to || 0 }} de {{ pagination.total || 0 }} resultados
        </p>
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
          <NeonButton @click="fetchEquipments(1)" :disabled="pagination.current_page === 1" size="sm" variant="secondary">
            Primeira
          </NeonButton>
          <NeonButton @click="fetchEquipments(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" size="sm" variant="secondary">
            Anterior
          </NeonButton>
          <span class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300">Página {{ pagination.current_page }} de {{ pagination.last_page }}</span>
          <NeonButton @click="fetchEquipments(pagination.current_page + 1)" :disabled="!pagination.next_page_url" size="sm" variant="secondary">
            Próxima
          </NeonButton>
          <NeonButton @click="fetchEquipments(pagination.last_page)" :disabled="pagination.current_page === pagination.last_page" size="sm" variant="secondary">
            Última
          </NeonButton>
        </nav>
      </div>
    </div>

    <Modal :show="isDeleteConfirmOpen" @close="isDeleteConfirmOpen = false" max-width="md">
      <template #title>Confirmar Exclusão</template>
      <div class="p-6">
        <p class="text-gray-300">Tem certeza que deseja excluir o equipamento "{{ equipmentToDelete?.name || 'selecionado' }}"? Esta ação não pode ser desfeita.</p>
      </div>
      <template #footer>
        <NeonButton @click="isDeleteConfirmOpen = false" size="md" variant="cancel">
          Cancelar
        </NeonButton>
        <NeonButton @click="deleteEquipment" :disabled="isDeleting" size="md" variant="danger" :loading="isDeleting">
          {{ isDeleting ? 'Excluindo...' : 'Excluir' }}
        </NeonButton>
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
            <NeonButton @click="syncQrAccessUsers" :disabled="isSyncingQrUsers" size="sm" variant="info" :loading="isSyncingQrUsers" class="mt-3">
              {{ isSyncingQrUsers ? 'Sincronizando...' : 'Sincronizar Usuários' }}
            </NeonButton>
          </div>
        </div>
      </div>
    </Modal>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import TopCard from '@/components/ui/TopCard.vue';
import StatsCard from '@/components/ui/StatsCard.vue';
import QrCodeDisplay from '@/components/QrCodeDisplay.vue';
import AccessLevelSelector from '@/components/maintenance/AccessLevelSelector.vue';
import Modal from '@/components/Modal.vue';
import NeonButton from '@/components/NeonButton.vue';
import api from '@/utils/api';

const router = useRouter();

const equipments = ref([]);
const isLoadingEquipments = ref(false);
const error = ref(null);

const filters = ref({
  name: '',
  area: '',
  access_level: ''
});

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
const publicReportBaseUrl = ref(`${window.location.origin}/api/qr`);

const availableUsers = ref([]);
const selectedQrAccessUsers = ref([]);
const isSyncingQrUsers = ref(false);

// Computed properties para estatísticas
const equipmentsWithQr = computed(() => {
    return equipments.value.filter(equipment => equipment.qr_code_hash).length;
});

const publicAccessEquipments = computed(() => {
    return equipments.value.filter(equipment => equipment.qr_code_access_level === 'public').length;
});

const uniqueAreas = computed(() => {
    const areas = new Set(equipments.value.map(equipment => equipment.maintenance_area?.id).filter(Boolean));
    return areas.size;
});

const filteredEquipments = computed(() => {
    let filtered = equipments.value;
    
    if (filters.value.name) {
        filtered = filtered.filter(equipment => 
            equipment.name.toLowerCase().includes(filters.value.name.toLowerCase())
        );
    }
    
    if (filters.value.area) {
        filtered = filtered.filter(equipment => 
            equipment.maintenance_area?.name && equipment.maintenance_area.name.toLowerCase().includes(filters.value.area.toLowerCase())
        );
    }
    
    if (filters.value.access_level) {
        filtered = filtered.filter(equipment => 
            equipment.qr_code_access_level === filters.value.access_level
        );
    }
    
    return filtered;
});

// Debounce para filtros
let debounceTimer = null;

async function fetchEquipments(page = 1) {
  isLoadingEquipments.value = true;
  error.value = null;
  try {
    const params = {
        page,
        per_page: pagination.value.per_page,
        ...filters.value
    };
    
    // Remover filtros vazios
    for (const key in params) {
        if (params[key] === '' || params[key] === null) {
            delete params[key];
        }
    }

    const response = await api.get('/maintenance/equipments', { params });
    equipments.value = response.data.data || [];

    const { current_page, last_page, from, to, total, per_page: respPerPage, next_page_url, prev_page_url } = response.data;
    pagination.value = { current_page, last_page, from, to, total, per_page: respPerPage || pagination.value.per_page, next_page_url, prev_page_url };

  } catch (err) {
    console.error('Erro ao buscar equipamentos:', err);
    error.value = 'Não foi possível carregar os equipamentos. Tente novamente mais tarde.';
    equipments.value = [];
  } finally {
    isLoadingEquipments.value = false;
  }
}

async function fetchUsers() {
  try {
    const response = await api.get('/users?per_page=1000');
    availableUsers.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erro ao buscar usuários:', error);
  }
}

function redirectToCreate() {
  router.push('/maintenance/equipment/create');
}

function redirectToEdit(id) {
  router.push(`/maintenance/equipment/${id}/edit`);
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

function getAccessLevelBadgeClass(level) {
  const classes = {
    public: 'bg-green-500/20 text-green-300 border border-green-500/30',
    internal_users: 'bg-blue-500/20 text-blue-300 border border-blue-500/30',
    specific_users: 'bg-purple-500/20 text-purple-300 border border-purple-500/30',
  };
  return classes[level] || 'bg-gray-500/20 text-gray-300 border border-gray-500/30';
}

function viewQrCode(equipment) {
  selectedEquipmentForQr.value = equipment;
  // Garantir que temos os usuários carregados
  selectedQrAccessUsers.value = (equipment.qr_access_users || []).map(u => u.id);
  isQrCodeModalOpen.value = true;
}

async function updateAccessLevel(newLevel) {
  if (!selectedEquipmentForQr.value) return;
  try {
    // Se mudando de specific_users para outro nível, limpar usuários primeiro
    if (selectedEquipmentForQr.value.qr_code_access_level === 'specific_users' && newLevel !== 'specific_users') {
      selectedQrAccessUsers.value = [];
      await api.post(`/maintenance/equipments/${selectedEquipmentForQr.value.id}/sync-qr-access-users`, { user_ids: [] });
    }
    
    // Depois atualizar o nível de acesso
    await api.put(`/maintenance/equipments/${selectedEquipmentForQr.value.id}`, { qr_code_access_level: newLevel });
    selectedEquipmentForQr.value.qr_code_access_level = newLevel;
    
    alert('Nível de acesso atualizado com sucesso!');
    fetchEquipments(pagination.value.current_page);
  } catch (error) {
    console.error('Erro ao atualizar nível de acesso:', error);
    
    // Tratar erros específicos
    let errorMessage = 'Erro ao atualizar nível de acesso.';
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.response?.status === 400) {
      errorMessage = 'Dados inválidos fornecidos para a atualização.';
    } else if (error.response?.status === 403) {
      errorMessage = 'Você não tem permissão para alterar este equipamento.';
    } else if (error.response?.status === 404) {
      errorMessage = 'Equipamento não encontrado.';
    } else if (error.response?.status >= 500) {
      errorMessage = 'Erro interno do servidor. Tente novamente em alguns instantes.';
    }
    
    alert(errorMessage);
  }
}

async function syncQrAccessUsers() {
  if (!selectedEquipmentForQr.value) return;
  isSyncingQrUsers.value = true;
  try {
    await api.post(`/maintenance/equipments/${selectedEquipmentForQr.value.id}/sync-qr-access-users`, { user_ids: selectedQrAccessUsers.value });
    alert('Usuários sincronizados com sucesso!');
    fetchEquipments(pagination.value.current_page);
  } catch (error) {
    console.error('Erro ao sincronizar usuários:', error);
    
    // Tratar erros específicos
    let errorMessage = 'Erro ao sincronizar usuários.';
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.response?.status === 400) {
      errorMessage = 'Dados inválidos ou usuários não encontrados.';
    } else if (error.response?.status === 403) {
      errorMessage = 'Você não tem permissão para alterar este equipamento.';
    } else if (error.response?.status === 404) {
      errorMessage = 'Equipamento não encontrado.';
    } else if (error.response?.status >= 500) {
      errorMessage = 'Erro interno do servidor. Tente novamente em alguns instantes.';
    }
    
    alert(errorMessage);
  } finally {
    isSyncingQrUsers.value = false;
  }
}

// Watch para filtros com debounce
watch(filters, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchEquipments(1);
    }, 500);
}, { deep: true });

onMounted(() => {
  fetchEquipments();
  fetchUsers();
});
</script>
