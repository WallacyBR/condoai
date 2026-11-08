<template>
  <AuthenticatedLayout page-title="Veículos">
    <div class="space-y-6">
      <!-- Header unificado -->
      <PageHeader
        title="Veículos"
        subtitle="Controle todos os veículos cadastrados no condomínio"
      >
        <template #actions>
          <AppButton @click="router.push({ name: 'vehicles.create' })" variant="primary">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Novo Veículo
          </AppButton>
        </template>
      </PageHeader>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <StatsCard
          title="Total de Veículos"
          :value="stats.total"
          icon="car"
          variant="primary"
        />
        <StatsCard
          title="Com Vaga Fixa"
          :value="stats.withParkingSpot"
          icon="parking"
          variant="green"
        />
        <StatsCard
          title="Sem Vaga"
          :value="stats.withoutParkingSpot"
          icon="warning"
          variant="orange"
        />
        <StatsCard
          title="Apartamentos"
          :value="stats.uniqueApartments"
          icon="building"
          variant="default"
        />
      </div>

      <!-- Actions & Filters -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center space-x-2 w-full sm:w-auto">
          <div class="relative flex-1 sm:w-72">
            <input
              v-model="search"
              type="text"
              placeholder="Buscar por placa ou modelo..."
              class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
            />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
          <AppButton v-if="search" @click="clearFilters" size="sm" variant="ghost">
            Limpar
          </AppButton>
        </div>
      </div>

      <!-- Vehicles Grid -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      </div>

      <EmptyState
        v-else-if="filteredVehicles.length === 0"
        title="Nenhum veículo encontrado"
        :description="search ? 'Tente ajustar os filtros de busca.' : 'Comece cadastrando o primeiro veículo.'"
      >
        <template #actions v-if="!search">
          <AppButton @click="router.push({ name: 'vehicles.create' })" variant="primary">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Cadastrar Primeiro Veículo
          </AppButton>
        </template>
      </EmptyState>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="vehicle in filteredVehicles"
          :key="vehicle.id"
          class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-colors shadow-sm flex flex-col justify-between"
        >
          <div>
            <!-- Vehicle Header -->
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 flex items-center justify-center">
                  <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM21 17a2 2 0 11-4 0 2 2 0 014 0zM13 6h5l3 4v6a1 1 0 01-1 1h-2m-10 0H3a1 1 0 01-1-1v-6l3-4h5m0 0V3a1 1 0 011-1h2a1 1 0 011 1v3" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ vehicle.license_plate }}</h3>
                  <p class="text-xs text-slate-500 dark:text-slate-400">{{ vehicle.model }}</p>
                </div>
              </div>
              
              <!-- Actions Dropdown -->
              <div class="relative">
                <button
                  @click="toggleDropdown(vehicle.id)"
                  class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                  </svg>
                </button>
                
                <div
                  v-if="openDropdowns[vehicle.id]"
                  class="absolute right-0 mt-1 w-44 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-10"
                >
                  <button
                    @click="editVehicle(vehicle)"
                    class="w-full text-left px-4 py-2 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors flex items-center space-x-2"
                  >
                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Editar</span>
                  </button>
                  <button
                    @click="confirmDelete(vehicle)"
                    class="w-full text-left px-4 py-2 text-xs text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors flex items-center space-x-2"
                  >
                    <svg class="h-3.5 w-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Excluir</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Vehicle Details -->
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-3.5 space-y-2 border border-slate-100 dark:border-slate-800 text-xs">
              <div class="flex items-center justify-between">
                <span class="text-slate-500 dark:text-slate-400">Cor:</span>
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ vehicle.color }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-slate-500 dark:text-slate-400">Apartamento:</span>
                <span class="font-medium text-slate-800 dark:text-slate-200">
                  {{ formatApartmentDescription(vehicle.apartment) }}
                </span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-slate-500 dark:text-slate-400">Vaga:</span>
                <span class="font-medium" :class="vehicle.parking_spot ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'">
                  {{ vehicle.parking_spot ? `Vaga ${vehicle.parking_spot.identifier}` : 'Sem vaga fixa' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex space-x-2">
            <AppButton @click="editVehicle(vehicle)" size="sm" variant="secondary" class="flex-1">
              <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
              </svg>
              Editar
            </AppButton>
            <AppButton @click="confirmDelete(vehicle)" size="sm" variant="danger" class="flex-1">
              <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7" />
              </svg>
              Excluir
            </AppButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Modal :show="deleteConfirmation.show" @close="closeDeleteConfirmation">
      <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-xl max-w-md mx-auto">
        <div class="flex items-center space-x-3 mb-4">
          <div class="h-10 w-10 rounded-full bg-rose-50 dark:bg-rose-950/50 flex items-center justify-center border border-rose-200 dark:border-rose-800">
            <svg class="h-5 w-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </div>
          <div>
            <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Confirmar Exclusão</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Esta ação não poderá ser desfeita</p>
          </div>
        </div>

        <div v-if="deleteConfirmation.item" class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/40 rounded-lg p-3 mb-5">
          <h4 class="text-xs font-semibold text-rose-900 dark:text-rose-200 mb-1">Veículo a ser excluído:</h4>
          <div class="space-y-0.5 text-xs text-rose-700 dark:text-rose-300">
            <p><strong>Placa:</strong> {{ deleteConfirmation.item.license_plate }}</p>
            <p><strong>Modelo:</strong> {{ deleteConfirmation.item.model }}</p>
            <p><strong>Apartamento:</strong> {{ formatApartmentDescription(deleteConfirmation.item.apartment) }}</p>
          </div>
        </div>

        <div class="flex space-x-3">
          <AppButton 
            @click="closeDeleteConfirmation" 
            size="md" 
            variant="secondary" 
            class="flex-1"
            :disabled="deleteConfirmation.loading"
          >
            Cancelar
          </AppButton>
          <AppButton 
            @click="deleteVehicle" 
            size="md" 
            variant="danger" 
            class="flex-1"
            :loading="deleteConfirmation.loading"
          >
            Excluir Veículo
          </AppButton>
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import NeonButton from './NeonButton.vue';
import AppButton from './ui/AppButton.vue';
import PageHeader from './ui/PageHeader.vue';
import EmptyState from './ui/EmptyState.vue';
import Modal from './Modal.vue';
import StatsCard from './ui/StatsCard.vue';
import { formatApartmentDescription } from '../utils/formatters.js';
import { apiRequest } from '../utils/api';

export default {
  name: 'VehiclesView',
  components: { 
    AuthenticatedLayout, 
    NeonButton,
    AppButton,
    PageHeader,
    EmptyState,
    Modal, 
    StatsCard 
  },
  setup() {
    const router = useRouter();
    const vehicles = ref([]);
    const loading = ref(true);
    const search = ref('');
    const openDropdowns = ref({});

    // Delete confirmation state
    const deleteConfirmation = ref({
      show: false,
      item: null,
      loading: false
    });

    const loadData = async () => {
      loading.value = true;

      try {
        const data = await apiRequest('/vehicles');
        vehicles.value = Array.isArray(data) ? data : (data?.data || []);
      } catch (e) {
        vehicles.value = [];
      } finally {
        loading.value = false;
      }
    };

    const filteredVehicles = computed(() => {
      if (!search.value) return vehicles.value;
      
      const searchTerm = search.value.toLowerCase();
      return vehicles.value.filter(vehicle => 
        vehicle.license_plate.toLowerCase().includes(searchTerm) ||
        vehicle.model.toLowerCase().includes(searchTerm) ||
        vehicle.color.toLowerCase().includes(searchTerm)
      );
    });

    const stats = computed(() => {
      const total = vehicles.value.length;
      const withParkingSpot = vehicles.value.filter(v => v.parking_spot_id).length;
      const withoutParkingSpot = total - withParkingSpot;
      const uniqueApartments = new Set(vehicles.value.map(v => v.apartment_id)).size;
      
      return {
        total,
        withParkingSpot,
        withoutParkingSpot,
        uniqueApartments
      };
    });

    const clearFilters = () => {
      search.value = '';
    };

    const toggleDropdown = (vehicleId) => {
      // Close all other dropdowns
      Object.keys(openDropdowns.value).forEach(id => {
        if (id !== vehicleId.toString()) {
          openDropdowns.value[id] = false;
        }
      });
      
      openDropdowns.value[vehicleId] = !openDropdowns.value[vehicleId];
    };

    const editVehicle = (vehicle) => {
      router.push({ name: 'vehicles.edit', params: { id: vehicle.id } });
      openDropdowns.value = {};
    };

    const confirmDelete = (vehicle) => {
      deleteConfirmation.value = {
        show: true,
        item: vehicle,
        loading: false
      };
      openDropdowns.value = {};
    };

    const closeDeleteConfirmation = () => {
      deleteConfirmation.value = {
        show: false,
        item: null,
        loading: false
      };
    };

    const deleteVehicle = async () => {
      if (!deleteConfirmation.value.item) return;
      
      deleteConfirmation.value.loading = true;

      try {
        await apiRequest(`/vehicles/${deleteConfirmation.value.item.id}`, { method: 'DELETE' });
        vehicles.value = vehicles.value.filter(v => v.id !== deleteConfirmation.value.item.id);
        closeDeleteConfirmation();
      } catch (e) {
        deleteConfirmation.value.loading = false;
      } finally {
        deleteConfirmation.value.loading = false;
      }
    };

    // Close dropdowns when clicking outside
    const handleClickOutside = (event) => {
      if (!event.target.closest('.relative')) {
        openDropdowns.value = {};
      }
    };

    onMounted(() => {
      loadData();
      document.addEventListener('click', handleClickOutside);
    });

    return {
      router,
      vehicles,
      loading,
      search,
      filteredVehicles,
      stats,
      openDropdowns,
      deleteConfirmation,
      clearFilters,
      toggleDropdown,
      editVehicle,
      confirmDelete,
      closeDeleteConfirmation,
      deleteVehicle,
      formatApartmentDescription
    };
  }
};
</script>
