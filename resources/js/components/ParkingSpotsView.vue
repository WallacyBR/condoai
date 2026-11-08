<template>
  <AuthenticatedLayout page-title="Gerenciar Vagas">
    <div class="space-y-6">
      <!-- Cards de Resumo -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Total de Vagas -->
        <div class="bg-gradient-to-br from-blue-900/50 via-blue-800/50 to-blue-900/50 rounded-2xl p-6 border-2 border-blue-500/30 shadow-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-300 text-sm font-medium">Total de Vagas</p>
              <p class="text-2xl font-bold text-blue-100">{{ stats.total }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-blue-500/20 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V7.618a1 1 0 01.553-.894L9 4l6 3-6 3-6-3v7l6 3 6-3" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Vagas Alugadas -->
        <div class="bg-gradient-to-br from-green-900/50 via-green-800/50 to-green-900/50 rounded-2xl p-6 border-2 border-green-500/30 shadow-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-green-300 text-sm font-medium">Alugadas</p>
              <p class="text-2xl font-bold text-green-100">{{ stats.rented }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-green-500/20 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Vagas Livres -->
        <div class="bg-gradient-to-br from-orange-900/50 via-orange-800/50 to-orange-900/50 rounded-2xl p-6 border-2 border-orange-500/30 shadow-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-orange-300 text-sm font-medium">Livres</p>
              <p class="text-2xl font-bold text-orange-100">{{ stats.vacant }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-orange-500/20 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Header Actions -->
      <div class="flex justify-between items-center">
        <div class="flex space-x-4">
          <div class="relative">
            <input type="text" v-model="search" placeholder="Buscar por identificador..." class="w-64 pl-10 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 transition-colors" />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
          </div>
          <select v-model="filter" class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 transition-colors">
            <option value="all">Todas</option>
            <option value="rented">Alugadas</option>
            <option value="vacant">Livres</option>
          </select>
        </div>
        <div class="flex space-x-2">
            <NeonButton @click="showCreateBatchModal = true" size="md">Criar em Lote</NeonButton>
            <NeonButton @click="goToCreatePage" size="md">Nova Vaga</NeonButton>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-red-300">
        {{ error }}
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredSpots.length === 0" class="text-center py-8">
        <h3 class="mt-2 text-sm font-medium text-gray-300">Nenhuma vaga encontrada</h3>
        <p class="mt-1 text-sm text-gray-500">Comece adicionando uma nova vaga.</p>
      </div>

      <!-- Spots Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="spot in filteredSpots" :key="spot.id" class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2 border-indigo-500/20 hover:border-indigo-500/40 transition-all duration-300">
          <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="text-lg font-semibold text-white">Vaga {{ spot.identifier }}</h3>
                <p class="text-sm text-gray-400">Proprietário: {{ formatApartmentDescription(spot.apartment) }}</p>
            </div>
            <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="spot.rented_to_apartment_id ? 'bg-green-500/20 text-green-300' : 'bg-orange-500/20 text-orange-300'">
                {{ spot.rented_to_apartment_id ? 'Alugada' : 'Livre' }}
            </span>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-300">Alugar para:</label>
              <select v-model="spot.rented_to_apartment_id" @change="rentSpot(spot)" class="mt-1 block w-full border rounded px-3 py-2 bg-gray-800 border-gray-700 text-white focus:ring-2 focus:ring-indigo-500">
                <option :value="null">-- Livre --</option>
                <option v-for="a in apartments.filter(ap => ap.id !== spot.apartment_id)" :value="a.id" :key="a.id">{{ formatApartmentDescription(a) }}</option>
              </select>
            </div>
            
            <!-- Informação de Aluguel Melhorada -->
            <div v-if="spot.rented_to_apartment" class="bg-green-500/10 border border-green-500/30 rounded-lg p-4">
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-green-500/20 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01" />
                    </svg>
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center space-x-2">
                    <span class="text-sm font-medium text-green-300">Alugada para:</span>
                    <span class="text-sm text-green-100 font-semibold">{{ formatApartmentDescription(spot.rented_to_apartment) }}</span>
                  </div>
                  <div class="flex items-center space-x-2 mt-1">
                    <svg class="w-3 h-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-xs text-green-400">Status ativo</span>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Estado Livre Melhorado -->
            <div v-else class="bg-orange-500/10 border border-orange-500/30 rounded-lg p-4">
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-orange-500/20 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center space-x-2">
                    <span class="text-sm font-medium text-orange-300">Disponível para aluguel</span>
                  </div>
                  <div class="flex items-center space-x-2 mt-1">
                    <svg class="w-3 h-3 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-xs text-orange-400">Aguardando locatário</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex space-x-2 mt-6">
            <NeonButton @click="openEditModal(spot)" size="sm" class="flex-1 bg-blue-600/40 hover:bg-blue-600/60">
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
              Editar
            </NeonButton>
            <NeonButton @click="showDeleteConfirmation(spot)" size="sm" class="flex-1 bg-red-600/40 hover:bg-red-600/60">
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              Excluir
            </NeonButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals (styles updated to match theme) -->
    <Modal :show="showEditModal" @close="showEditModal = false" title="Editar Vaga">
      <form v-if="editingSpot" @submit.prevent="updateSpot" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-300">Identificador</label>
          <input v-model="editingSpot.identifier" class="mt-1 block w-full bg-gray-900 border-gray-700 rounded px-3 py-2 text-white" placeholder="Ex: G1-01" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-300">Unidade Proprietária</label>
          <select v-model="editingSpot.apartment_id" class="mt-1 block w-full bg-gray-900 border-gray-700 rounded px-3 py-2 text-white" required>
            <option v-for="a in apartments" :value="a.id" :key="a.id">{{ formatApartmentDescription(a) }}</option>
          </select>
        </div>
        <div class="flex justify-end space-x-4 pt-4">
          <NeonButton type="button" @click="showEditModal = false" class="bg-gray-600/40 hover:bg-gray-600/60">Cancelar</NeonButton>
          <NeonButton type="submit">Salvar</NeonButton>
        </div>
      </form>
    </Modal>

    <Modal :show="showCreateBatchModal" @close="showCreateBatchModal = false" title="Criar Vagas em Lote">
      <form @submit.prevent="createBatchSpots" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-300">Vagas por unidade</label>
          <input v-model.number="spotsPerApartment" type="number" min="1" class="mt-1 block w-full bg-gray-900 border-gray-700 rounded px-3 py-2 text-white" required />
        </div>
        <div class="flex justify-end space-x-4 pt-4">
            <NeonButton type="button" @click="showCreateBatchModal = false" class="bg-gray-600/40 hover:bg-gray-600/60">Cancelar</NeonButton>
            <NeonButton type="submit" class="bg-blue-600/40 hover:bg-blue-600/60">Criar</NeonButton>
        </div>
      </form>
    </Modal>

    <!-- Modal de Confirmação de Exclusão -->
    <div v-if="deleteConfirmation.show" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50" @click="closeDeleteConfirmation">
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-2xl p-6 border-2 border-red-500/30 max-w-md w-full mx-4" @click.stop>
        <div class="flex items-center mb-4">
          <div class="w-12 h-12 bg-red-500/20 rounded-full flex items-center justify-center mr-4">
            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-white">Confirmar Exclusão</h3>
            <p class="text-sm text-gray-400">Esta ação não pode ser desfeita</p>
          </div>
        </div>

        <div class="mb-6">
          <p class="text-gray-300 mb-2">
            Tem certeza que deseja excluir esta vaga de estacionamento?
          </p>
          
          <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-3">
            <p class="text-red-300 text-sm font-medium">
              Vaga: {{ deleteConfirmation.item?.identifier }}
            </p>
            <!-- Informações adicionais da vaga -->
            <div v-if="deleteConfirmation.item" class="mt-2 pt-2 border-t border-red-500/20">
              <div class="space-y-1 text-xs text-gray-400">
                <div>
                  <span class="text-gray-500">Proprietário:</span>
                  <span class="text-gray-300 ml-1">{{ formatApartmentDescription(deleteConfirmation.item.apartment) }}</span>
                </div>
                <div>
                  <span class="text-gray-500">Status:</span>
                  <span class="text-gray-300 ml-1" :class="deleteConfirmation.item.rented_to_apartment_id ? 'text-green-400' : 'text-orange-400'">
                    {{ deleteConfirmation.item.rented_to_apartment_id ? 'Alugada' : 'Livre' }}
                  </span>
                </div>
                <div v-if="deleteConfirmation.item.rented_to_apartment">
                  <span class="text-gray-500">Alugada para:</span>
                  <span class="text-gray-300 ml-1">{{ formatApartmentDescription(deleteConfirmation.item.rented_to_apartment) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="flex gap-3">
          <button 
            @click="closeDeleteConfirmation"
            class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors"
          >
            Cancelar
          </button>
          <button 
            @click="confirmDelete"
            :disabled="deleteConfirmation.loading"
            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-800 disabled:cursor-not-allowed text-white rounded-lg transition-colors flex items-center justify-center"
          >
            <span v-if="!deleteConfirmation.loading">Excluir</span>
            <div v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import Modal from './Modal.vue';
import NeonButton from './NeonButton.vue';
import { formatApartmentDescription } from '../utils/formatters.js';

export default {
  name: 'ParkingSpotsView',
  components: { AuthenticatedLayout, Modal, NeonButton },
  setup() {
    const router = useRouter();
    const token = localStorage.getItem('token');
    const condo = localStorage.getItem('selectedCondominium');
    const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

    const spots = ref([]);
    const apartments = ref([]);
    const editingSpot = ref(null);
    const spotsPerApartment = ref(1);

    const loading = ref(true);
    const error = ref('');
    const search = ref('');
    const filter = ref('all'); // all, rented, vacant

    const showEditModal = ref(false);
    const showCreateBatchModal = ref(false);

    // Estado para modal de confirmação de exclusão
    const deleteConfirmation = ref({
      show: false,
      item: null,
      loading: false
    });

    const stats = computed(() => {
        const total = spots.value.length;
        const rented = spots.value.filter(s => !!s.rented_to_apartment_id).length;
        const vacant = total - rented;
        return { total, rented, vacant };
    });

    const filteredSpots = computed(() => {
        return spots.value.filter(spot => {
            const matchesSearch = !search.value || spot.identifier.toLowerCase().includes(search.value.toLowerCase());
            
            let matchesFilter = false;
            if (filter.value === 'all') {
                matchesFilter = true;
            } else if (filter.value === 'rented') {
                matchesFilter = !!spot.rented_to_apartment_id;
            } else if (filter.value === 'vacant') {
                matchesFilter = !spot.rented_to_apartment_id;
            }
            
            return matchesSearch && matchesFilter;
        });
    });

    const goToCreatePage = () => {
      router.push({ name: 'parking-spots.create' });
    };

    const loadData = async () => {
      loading.value = true;
      error.value = '';
      try {
        const respApts = await fetch(`/api/apartments?condominium_id=${condo}`, { headers });
        if (!respApts.ok) throw new Error('Falha ao carregar apartamentos.');
        apartments.value = await respApts.json();

        const respSpots = await fetch(`/api/parking-spots?condominium_id=${condo}`, { headers });
        if (!respSpots.ok) throw new Error('Falha ao carregar vagas.');
        spots.value = await respSpots.json();
      } catch (e) {
          error.value = e.message;
      } finally {
          loading.value = false;
      }
    };

    const createBatchSpots = async () => {
        if (spotsPerApartment.value < 1) {
            alert('O número de vagas por unidade deve ser ao menos 1.');
            return;
        }
        const payload = { condominium_id: condo, spots_per_apartment: spotsPerApartment.value };
        const resp = await fetch('/api/parking-spots/batch', { method: 'POST', headers, body: JSON.stringify(payload) });

        if (resp.ok) {
            await loadData();
            showCreateBatchModal.value = false;
            spotsPerApartment.value = 1;
        } else {
            alert('Falha ao criar vagas em lote.');
        }
    };

    const openEditModal = (spot) => {
      editingSpot.value = { ...spot, apartment_id: spot.apartment.id };
      showEditModal.value = true;
    };

    const updateSpot = async () => {
      if (!editingSpot.value) return;
      const { id, identifier, apartment_id } = editingSpot.value;
      const payload = { identifier, apartment_id };
      const resp = await fetch(`/api/parking-spots/${id}`, { method: 'PUT', headers, body: JSON.stringify(payload) });

      if (resp.ok) {
        await loadData();
        showEditModal.value = false;
        editingSpot.value = null;
      } else {
          alert('Falha ao atualizar vaga.');
      }
    };

    // Funções do modal de confirmação de exclusão
    const showDeleteConfirmation = (spot) => {
      deleteConfirmation.value = {
        show: true,
        item: spot,
        loading: false
      };
    };

    const closeDeleteConfirmation = () => {
      deleteConfirmation.value.show = false;
      deleteConfirmation.value.loading = false;
    };

    const confirmDelete = async () => {
      const { item } = deleteConfirmation.value;
      deleteConfirmation.value.loading = true;

      try {
        const resp = await fetch(`/api/parking-spots/${item.id}`, { 
          method: 'DELETE', 
          headers 
        });
        
        if (resp.ok) {
          // Remover da lista local
          spots.value = spots.value.filter(s => s.id !== item.id);
          closeDeleteConfirmation();
        } else {
          alert('Erro ao excluir vaga. Tente novamente.');
          deleteConfirmation.value.loading = false;
        }
      } catch (error) {
        alert('Erro ao excluir vaga. Verifique sua conexão.');
        deleteConfirmation.value.loading = false;
      }
    };

    const rentSpot = async (spot) => {
      const payload = { rented_to_apartment_id: spot.rented_to_apartment_id ?? null };
      const resp = await fetch(`/api/parking-spots/${spot.id}/rent`, { method: 'POST', headers, body: JSON.stringify(payload) });
      if (resp.ok) {
        const updated = await resp.json();
        const index = spots.value.findIndex(s => s.id === spot.id);
        if (index !== -1) {
          spots.value[index] = updated;
        }
      } else {
          alert('Falha ao alugar vaga.');
          await loadData(); // Reverte a alteração visual em caso de falha
      }
    };

    onMounted(loadData);

    return {
      spots,
      apartments,
      editingSpot,
      showEditModal,
      showCreateBatchModal,
      openEditModal,
      updateSpot,
      showDeleteConfirmation,
      closeDeleteConfirmation,
      confirmDelete,
      deleteConfirmation,
      createBatchSpots,
      spotsPerApartment,
      loading,
      error,
      search,
      filter,
      stats,
      filteredSpots,
      goToCreatePage,
      rentSpot,
      formatApartmentDescription
    };
  }
};
</script>
