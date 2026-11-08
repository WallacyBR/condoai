<template>
  <AuthenticatedLayout page-title="Veículos">
    <div class="space-y-6">
      <!-- Header Card - OBRIGATÓRIO conforme DESIGN_MANUAL.md -->
      <div class="bg-gradient-to-r from-purple-600/20 to-pink-600/20 rounded-2xl p-6 border-2 border-purple-500/30">
        <div class="flex items-center space-x-4">
          <div class="h-16 w-16 rounded-full bg-purple-100 flex items-center justify-center border-2 border-purple-500/30">
            <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM21 17a2 2 0 11-4 0 2 2 0 014 0zM13 6h5l3 4v6a1 1 0 01-1 1h-2m-10 0H3a1 1 0 01-1-1v-6l3-4h5m0 0V3a1 1 0 011-1h2a1 1 0 011 1v3" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-white">Gerenciar Veículos</h1>
            <p class="text-purple-300">Controle todos os veículos cadastrados no condomínio</p>
          </div>
        </div>
      </div>

      <!-- Actions Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center space-x-2">
          <NeonButton @click="router.push({ name: 'vehicles.create' })" size="md">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Novo Veículo
          </NeonButton>
        </div>
        
        <!-- Filters -->
        <div class="flex items-center space-x-2">
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por placa ou modelo..."
            class="px-4 py-2 bg-gray-800/50 border border-gray-600/50 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500/50 backdrop-blur-sm"
          />
          <NeonButton @click="clearFilters" size="sm" variant="secondary">
            Limpar
          </NeonButton>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <StatsCard
          title="Total de Veículos"
          :value="stats.total"
          icon="car"
          color="blue"
        />
        <StatsCard
          title="Com Vaga Fixa"
          :value="stats.withParkingSpot"
          icon="parking"
          color="green"
        />
        <StatsCard
          title="Sem Vaga"
          :value="stats.withoutParkingSpot"
          icon="warning"
          color="orange"
        />
        <StatsCard
          title="Apartamentos"
          :value="stats.uniqueApartments"
          icon="building"
          color="purple"
        />
      </div>

      <!-- Vehicles Grid -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-500"></div>
      </div>

      <div v-else-if="filteredVehicles.length === 0" class="text-center py-12">
        <div class="bg-gray-800/50 backdrop-blur-md rounded-2xl p-8 border border-gray-700/50">
          <svg class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM21 17a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <h3 class="text-xl font-medium text-white mb-2">Nenhum veículo encontrado</h3>
          <p class="text-gray-400 mb-4">
            {{ search ? 'Tente ajustar os filtros de busca' : 'Comece cadastrando o primeiro veículo' }}
          </p>
          <NeonButton @click="router.push({ name: 'vehicles.create' })" size="md" v-if="!search">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Cadastrar Primeiro Veículo
          </NeonButton>
        </div>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="vehicle in filteredVehicles"
          :key="vehicle.id"
          class="bg-gradient-to-br from-gray-900/90 via-gray-800/90 to-gray-900/90 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50 hover:border-purple-500/50 transition-all duration-300 hover:shadow-xl hover:shadow-purple-500/10"
        >
          <!-- Vehicle Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
              <div class="h-10 w-10 rounded-full bg-purple-500/20 flex items-center justify-center">
                <svg class="h-5 w-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM21 17a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-white">{{ vehicle.license_plate }}</h3>
                <p class="text-sm text-gray-400">{{ vehicle.model }}</p>
              </div>
            </div>
            
            <!-- Actions Dropdown -->
            <div class="relative">
              <button
                @click="toggleDropdown(vehicle.id)"
                class="p-2 rounded-lg hover:bg-gray-700/50 transition-colors"
              >
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
              </button>
              
              <div
                v-if="openDropdowns[vehicle.id]"
                class="absolute right-0 mt-2 w-48 bg-gray-800/95 backdrop-blur-xl rounded-xl shadow-xl border border-gray-700/50 z-10"
              >
                <button
                  @click="editVehicle(vehicle)"
                  class="w-full text-left px-4 py-3 text-sm text-gray-300 hover:bg-gray-700/50 hover:text-white rounded-t-xl transition-colors flex items-center space-x-2"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  <span>Editar</span>
                </button>
                <button
                  @click="confirmDelete(vehicle)"
                  class="w-full text-left px-4 py-3 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 rounded-b-xl transition-colors flex items-center space-x-2"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  <span>Excluir</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Vehicle Details -->
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-400">Cor:</span>
              <span class="text-sm font-medium text-white">{{ vehicle.color }}</span>
            </div>
            
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-400">Apartamento:</span>
              <span class="text-sm font-medium text-white">
                {{ formatApartmentDescription(vehicle.apartment) }}
              </span>
            </div>
            
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-400">Vaga:</span>
              <span class="text-sm font-medium" :class="vehicle.parking_spot ? 'text-green-400' : 'text-orange-400'">
                {{ vehicle.parking_spot ? `Vaga ${vehicle.parking_spot.identifier}` : 'Sem vaga fixa' }}
              </span>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="mt-4 pt-4 border-t border-gray-700/50">
            <div class="flex space-x-2">
              <NeonButton @click="editVehicle(vehicle)" size="sm" variant="secondary" class="flex-1">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
                </svg>
                Editar
              </NeonButton>
              <NeonButton @click="confirmDelete(vehicle)" size="sm" variant="danger" class="flex-1">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7" />
                </svg>
                Excluir
              </NeonButton>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Modal :show="deleteConfirmation.show" @close="closeDeleteConfirmation">
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 backdrop-blur-sm rounded-2xl p-6 border border-red-500/30">
        <div class="flex items-center space-x-4 mb-6">
          <div class="h-12 w-12 rounded-full bg-red-500/20 flex items-center justify-center">
            <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-semibold text-white">Confirmar Exclusão</h3>
            <p class="text-gray-400">Esta ação não pode ser desfeita</p>
          </div>
        </div>

        <div v-if="deleteConfirmation.item" class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 mb-6">
          <h4 class="font-medium text-red-300 mb-2">Veículo a ser excluído:</h4>
          <div class="space-y-1 text-sm text-red-200">
            <p><strong>Placa:</strong> {{ deleteConfirmation.item.license_plate }}</p>
            <p><strong>Modelo:</strong> {{ deleteConfirmation.item.model }}</p>
            <p><strong>Apartamento:</strong> {{ formatApartmentDescription(deleteConfirmation.item.apartment) }}</p>
          </div>
        </div>

        <div class="flex space-x-4">
          <NeonButton 
            @click="closeDeleteConfirmation" 
            size="md" 
            variant="secondary" 
            class="flex-1"
            :disabled="deleteConfirmation.loading"
          >
            Cancelar
          </NeonButton>
          <NeonButton 
            @click="deleteVehicle" 
            size="md" 
            variant="danger" 
            class="flex-1"
            :loading="deleteConfirmation.loading"
          >
            Excluir Veículo
          </NeonButton>
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
import Modal from './Modal.vue';
import StatsCard from './ui/StatsCard.vue';
import { formatApartmentDescription } from '../utils/formatters.js';

export default {
  name: 'VehiclesView',
  components: { 
    AuthenticatedLayout, 
    NeonButton, 
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
      const token = localStorage.getItem('token');
      const condo = localStorage.getItem('selectedCondominium');
      const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

      try {
        const vehiclesRes = await fetch(`/api/vehicles?condominium_id=${condo}`, { headers });
        if (vehiclesRes.ok) {
          vehicles.value = await vehiclesRes.json();
        }
      } catch (e) {
        console.error('Erro ao carregar dados:', e);
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
      const token = localStorage.getItem('token');
      const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

      try {
        const response = await fetch(`/api/vehicles/${deleteConfirmation.value.item.id}`, {
          method: 'DELETE',
          headers
        });

        if (response.ok) {
          vehicles.value = vehicles.value.filter(v => v.id !== deleteConfirmation.value.item.id);
          closeDeleteConfirmation();
        } else {
          console.error('Erro ao excluir veículo');
        }
      } catch (e) {
        console.error('Erro ao excluir veículo:', e);
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
