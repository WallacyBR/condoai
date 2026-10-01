<template>
  <AuthenticatedLayout page-title="Gerenciar Vagas">
    <div class="space-y-6">
      <TopCard title="Vagas" :icon="CarIcon">
        <template #actions>
          <div class="flex space-x-2">
            <AppButton @click="showCreateBatchModal = true" size="sm" variant="secondary">Criar em Lote</AppButton>
            <AppButton @click="goToCreatePage" size="sm" variant="primary">Nova Vaga</AppButton>
          </div>
        </template>
      </TopCard>

      <!-- Cards de Resumo Modernos -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <StatsCard
          label="Total de Vagas"
          :value="stats.total"
          variant="primary"
          :default-icon-path="'M9 20l-5.447-2.724A1 1 0 013 16.382V7.618a1 1 0 01.553-.894L9 4l6 3-6 3-6-3v7l6 3 6-3'"
        />
        <StatsCard
          label="Alugadas"
          :value="stats.rented"
          variant="green"
          :default-icon-path="'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01'"
        />
        <StatsCard
          label="Livres"
          :value="stats.vacant"
          variant="orange"
          :default-icon-path="'M5 10l7-7m0 0l7 7m-7-7v18'"
        />
      </div>

      <!-- Header Actions & Filters -->
      <div class="flex flex-wrap gap-4 items-center justify-between">
        <div class="flex flex-wrap gap-3 items-center">
          <div class="relative">
            <input
              type="text"
              v-model="search"
              placeholder="Buscar por identificador..."
              class="w-64 pl-10 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
            />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
          </div>
          <select
            v-model="filter"
            class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
          >
            <option value="all">Todas as vagas</option>
            <option value="rented">Alugadas</option>
            <option value="vacant">Livres</option>
          </select>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 rounded-xl p-4 text-rose-700 dark:text-rose-300 text-sm">
        {{ error }}
      </div>

      <!-- Empty State -->
      <EmptyState
        v-else-if="filteredSpots.length === 0"
        title="Nenhuma vaga encontrada"
        :description="search ? 'Tente ajustar sua busca ou filtro.' : 'Comece adicionando uma nova vaga.'"
      >
        <template #actions v-if="!search">
          <AppButton @click="goToCreatePage" variant="primary">
            Nova Vaga
          </AppButton>
        </template>
      </EmptyState>

      <!-- Spots Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="spot in filteredSpots"
          :key="spot.id"
          class="bg-white dark:bg-slate-900 rounded-xl shadow-sm p-6 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-colors flex flex-col justify-between"
        >
          <div>
            <div class="flex justify-between items-start mb-4">
              <div>
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Vaga {{ spot.identifier }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Proprietário: {{ formatApartmentDescription(spot.apartment) }}</p>
              </div>
              <span
                class="px-2.5 py-0.5 text-xs font-medium rounded-full"
                :class="spot.rented_to_apartment_id ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50' : 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/50'"
              >
                {{ spot.rented_to_apartment_id ? 'Alugada' : 'Livre' }}
              </span>
            </div>

            <div class="space-y-3">
              <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Alugar para:</label>
                <select
                  v-model="spot.rented_to_apartment_id"
                  @change="rentSpot(spot)"
                  class="block w-full border rounded-lg px-3 py-1.5 text-xs bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500 shadow-sm"
                >
                  <option :value="null">-- Livre --</option>
                  <option v-for="a in apartments.filter(ap => ap.id !== spot.apartment_id)" :value="a.id" :key="a.id">{{ formatApartmentDescription(a) }}</option>
                </select>
              </div>
              
              <!-- Informação de Aluguel -->
              <div v-if="spot.rented_to_apartment" class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40 rounded-lg p-3">
                <div class="flex items-center space-x-2.5">
                  <div class="w-7 h-7 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v.01" />
                    </svg>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-emerald-900 dark:text-emerald-200">
                      Alugada para: {{ formatApartmentDescription(spot.rented_to_apartment) }}
                    </p>
                    <p class="text-xs text-emerald-700 dark:text-emerald-400">Locação ativa</p>
                  </div>
                </div>
              </div>
              
              <!-- Estado Livre -->
              <div v-else class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-lg p-3">
                <div class="flex items-center space-x-2.5">
                  <div class="w-7 h-7 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                  </div>
                  <div>
                    <p class="text-xs font-medium text-slate-700 dark:text-slate-300">Disponível para aluguel</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Aguardando locatário</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex space-x-2 mt-4 pt-3 border-t border-slate-100 dark:border-slate-800">
            <AppButton @click="openEditModal(spot)" size="sm" variant="secondary" class="flex-1">
              <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
              Editar
            </AppButton>
            <AppButton @click="showDeleteConfirmation(spot)" size="sm" variant="danger" class="flex-1">
              <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              Excluir
            </AppButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Editar -->
    <Modal :show="showEditModal" @close="showEditModal = false" size="lg">
      <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-xl space-y-4">
        <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-2">Editar Vaga</h2>
        <form v-if="editingSpot" @submit.prevent="updateSpot" class="space-y-4">
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Identificador</label>
            <input v-model="editingSpot.identifier" class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Ex: G1-01" required />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Unidade Proprietária</label>
            <select v-model="editingSpot.apartment_id" class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500" required>
              <option v-for="a in apartments" :value="a.id" :key="a.id">{{ formatApartmentDescription(a) }}</option>
            </select>
          </div>
          <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <AppButton type="button" @click="showEditModal = false" variant="secondary">Cancelar</AppButton>
            <AppButton type="submit" variant="primary">Salvar</AppButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Modal Criar em Lote -->
    <Modal :show="showCreateBatchModal" @close="showCreateBatchModal = false" size="lg">
      <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-xl space-y-4">
        <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-2">Criar Vagas em Lote</h2>
        <form @submit.prevent="createBatchSpots" class="space-y-4">
          <div>
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Vagas por unidade</label>
            <input v-model.number="spotsPerApartment" type="number" min="1" class="w-full px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500" required />
          </div>
          <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <AppButton type="button" @click="showCreateBatchModal = false" variant="secondary">Cancelar</AppButton>
            <AppButton type="submit" variant="primary">Criar Vagas</AppButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Modal de Confirmação de Exclusão -->
    <div v-if="deleteConfirmation.show" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click="closeDeleteConfirmation">
      <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl p-6 border border-slate-200 dark:border-slate-800 max-w-md w-full" @click.stop>
        <div class="flex items-center space-x-3 mb-4">
          <div class="w-10 h-10 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 rounded-full flex items-center justify-center">
            <svg class="w-5 w-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Confirmar Exclusão</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Esta ação não poderá ser desfeita</p>
          </div>
        </div>

        <div class="mb-5">
          <div class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/40 rounded-lg p-3">
            <p class="text-rose-900 dark:text-rose-200 text-xs font-semibold mb-1">
              Vaga: {{ deleteConfirmation.item?.identifier }}
            </p>
            <div v-if="deleteConfirmation.item" class="space-y-0.5 text-xs text-rose-700 dark:text-rose-300">
              <div>
                <span>Proprietário:</span>
                <span class="font-medium ml-1">{{ formatApartmentDescription(deleteConfirmation.item.apartment) }}</span>
              </div>
              <div>
                <span>Status:</span>
                <span class="font-medium ml-1">
                  {{ deleteConfirmation.item.rented_to_apartment_id ? 'Alugada' : 'Livre' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="flex space-x-3">
          <AppButton 
            @click="closeDeleteConfirmation"
            variant="secondary"
            class="flex-1"
          >
            Cancelar
          </AppButton>
          <AppButton 
            @click="confirmDelete"
            variant="danger"
            class="flex-1"
            :loading="deleteConfirmation.loading"
          >
            Excluir Vaga
          </AppButton>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted, computed, h } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import Modal from './Modal.vue';
import NeonButton from './NeonButton.vue';
import AppButton from './ui/AppButton.vue';
import TopCard from './ui/TopCard.vue';
import StatsCard from './ui/StatsCard.vue';
import EmptyState from './ui/EmptyState.vue';
import { formatApartmentDescription } from '../utils/formatters.js';
import { apiRequest } from '../utils/api';

export default {
  name: 'ParkingSpotsView',
  components: { AuthenticatedLayout, Modal, NeonButton, AppButton, TopCard, StatsCard, EmptyState },
  setup() {
    const router = useRouter();

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

    const CarIcon = {
      render() {
        return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 13l2-2m0 0l7-7 7 7M5 11v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' })
        ]);
      }
    };

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
        const [apartmentsData, spotsData] = await Promise.all([
          apiRequest('/apartments'),
          apiRequest('/parking-spots')
        ]);

        apartments.value = Array.isArray(apartmentsData) ? apartmentsData : (apartmentsData?.data || []);
        spots.value = Array.isArray(spotsData) ? spotsData : (spotsData?.data || []);
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
        const payload = { spots_per_apartment: spotsPerApartment.value };

        try {
            await apiRequest('/parking-spots/batch', { method: 'POST', body: JSON.stringify(payload) });
            await loadData();
            showCreateBatchModal.value = false;
            spotsPerApartment.value = 1;
        } catch (e) {
            alert(e.message || 'Falha ao criar vagas em lote.');
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

      try {
        await apiRequest(`/parking-spots/${id}`, { method: 'PUT', body: JSON.stringify(payload) });
        await loadData();
        showEditModal.value = false;
        editingSpot.value = null;
      } catch (e) {
          alert(e.message || 'Falha ao atualizar vaga.');
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
        await apiRequest(`/parking-spots/${item.id}`, { method: 'DELETE' });
        // Remover da lista local
        spots.value = spots.value.filter(s => s.id !== item.id);
        closeDeleteConfirmation();
      } catch (error) {
        alert(error.message || 'Erro ao excluir vaga. Tente novamente.');
        deleteConfirmation.value.loading = false;
      }
    };

    const rentSpot = async (spot) => {
      const payload = { rented_to_apartment_id: spot.rented_to_apartment_id ?? null };
      try {
        const updated = await apiRequest(`/parking-spots/${spot.id}/rent`, { method: 'POST', body: JSON.stringify(payload) });
        const index = spots.value.findIndex(s => s.id === spot.id);
        if (index !== -1) {
          spots.value[index] = updated;
        }
      } catch (e) {
          alert(e.message || 'Falha ao alugar vaga.');
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
      formatApartmentDescription,
      CarIcon
    };
  }
};
</script>
