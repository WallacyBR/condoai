<template>
  <AuthenticatedLayout page-title="Pets">
    <div class="space-y-6">
      <!-- Header Card - OBRIGATÓRIO conforme DESIGN_MANUAL.md -->
      <div class="bg-gradient-to-r from-green-600/20 to-emerald-600/20 rounded-2xl p-6 border-2 border-green-500/30">
        <div class="flex items-center space-x-4">
          <div class="h-16 w-16 rounded-full bg-green-100 flex items-center justify-center border-2 border-green-500/30">
            <svg class="h-8 w-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM16.5 6C17.6 6 18.5 6.9 18.5 8C18.5 9.1 17.6 10 16.5 10C15.4 10 14.5 9.1 14.5 8C14.5 6.9 15.4 6 16.5 6ZM7.5 6C8.6 6 9.5 6.9 9.5 8C9.5 9.1 8.6 10 7.5 10C6.4 10 5.5 9.1 5.5 8C5.5 6.9 6.4 6 7.5 6ZM12 11.5C15 11.5 17.5 14 17.5 17.5C17.5 20 15.5 22 12 22C8.5 22 6.5 20 6.5 17.5C6.5 14 9 11.5 12 11.5Z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-white">Gerenciar Pets</h1>
            <p class="text-green-300">Controle todos os animais de estimação cadastrados no condomínio</p>
          </div>
        </div>
      </div>

      <!-- Actions Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center space-x-2">
          <NeonButton @click="router.push({ name: 'pets.create' })" size="md">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Novo Pet
          </NeonButton>
        </div>
        
        <!-- Filters -->
        <div class="flex items-center space-x-2">
          <input
            v-model="search"
            type="text"
            placeholder="Buscar por nome ou espécie..."
            class="px-4 py-2 bg-gray-800/50 border border-gray-600/50 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500/50 backdrop-blur-sm"
          />
          <select
            v-model="filterSpecies"
            class="px-4 py-2 bg-gray-800/50 border border-gray-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500/50 backdrop-blur-sm"
          >
            <option value="">Todas as espécies</option>
            <option v-for="species in availableSpecies" :key="species" :value="species">
              {{ species }}
            </option>
          </select>
          <NeonButton @click="clearFilters" size="sm" variant="secondary">
            Limpar
          </NeonButton>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <StatsCard
          title="Total de Pets"
          :value="stats.total"
          icon="pet"
          color="green"
        />
        <StatsCard
          title="Cachorros"
          :value="stats.dogs"
          icon="dog"
          color="blue"
        />
        <StatsCard
          title="Gatos"
          :value="stats.cats"
          icon="cat"
          color="purple"
        />
        <StatsCard
          title="Apartamentos"
          :value="stats.uniqueApartments"
          icon="building"
          color="orange"
        />
      </div>

      <!-- Pets Grid -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
      </div>

      <div v-else-if="filteredPets.length === 0" class="text-center py-12">
        <div class="bg-gray-800/50 backdrop-blur-md rounded-2xl p-8 border border-gray-700/50">
          <svg class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM16.5 6C17.6 6 18.5 6.9 18.5 8C18.5 9.1 17.6 10 16.5 10C15.4 10 14.5 9.1 14.5 8C14.5 6.9 15.4 6 16.5 6ZM7.5 6C8.6 6 9.5 6.9 9.5 8C9.5 9.1 8.6 10 7.5 10C6.4 10 5.5 9.1 5.5 8C5.5 6.9 6.4 6 7.5 6ZM12 11.5C15 11.5 17.5 14 17.5 17.5C17.5 20 15.5 22 12 22C8.5 22 6.5 20 6.5 17.5C6.5 14 9 11.5 12 11.5Z"/>
          </svg>
          <h3 class="text-xl font-medium text-white mb-2">Nenhum pet encontrado</h3>
          <p class="text-gray-400 mb-4">
            {{ search || filterSpecies ? 'Tente ajustar os filtros de busca' : 'Comece cadastrando o primeiro pet' }}
          </p>
          <NeonButton @click="router.push({ name: 'pets.create' })" size="md" v-if="!search && !filterSpecies">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Cadastrar Primeiro Pet
          </NeonButton>
        </div>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="pet in filteredPets"
          :key="pet.id"
          class="bg-gradient-to-br from-gray-900/90 via-gray-800/90 to-gray-900/90 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50 hover:border-green-500/50 transition-all duration-300 hover:shadow-xl hover:shadow-green-500/10"
        >
          <!-- Pet Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center space-x-3">
              <div class="h-10 w-10 rounded-full bg-green-500/20 flex items-center justify-center">
                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM16.5 6C17.6 6 18.5 6.9 18.5 8C18.5 9.1 17.6 10 16.5 10C15.4 10 14.5 9.1 14.5 8C14.5 6.9 15.4 6 16.5 6ZM7.5 6C8.6 6 9.5 6.9 9.5 8C9.5 9.1 8.6 10 7.5 10C6.4 10 5.5 9.1 5.5 8C5.5 6.9 6.4 6 7.5 6ZM12 11.5C15 11.5 17.5 14 17.5 17.5C17.5 20 15.5 22 12 22C8.5 22 6.5 20 6.5 17.5C6.5 14 9 11.5 12 11.5Z"/>
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-white">{{ pet.name }}</h3>
                <p class="text-sm text-gray-400">{{ pet.species }}</p>
              </div>
            </div>
            
            <!-- Actions Dropdown -->
            <div class="relative">
              <button
                @click="toggleDropdown(pet.id)"
                class="p-2 rounded-lg hover:bg-gray-700/50 transition-colors"
              >
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
              </button>
              
              <div
                v-if="openDropdowns[pet.id]"
                class="absolute right-0 mt-2 w-48 bg-gray-800/95 backdrop-blur-xl rounded-xl shadow-xl border border-gray-700/50 z-10"
              >
                <button
                  @click="editPet(pet)"
                  class="w-full text-left px-4 py-3 text-sm text-gray-300 hover:bg-gray-700/50 hover:text-white rounded-t-xl transition-colors flex items-center space-x-2"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  <span>Editar</span>
                </button>
                <button
                  @click="confirmDelete(pet)"
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

          <!-- Pet Photo -->
          <div v-if="pet.photo" class="mb-4">
            <img
              :src="pet.photo"
              :alt="pet.name"
              class="w-full h-40 object-cover rounded-xl border border-gray-700/50"
              @error="handleImageError"
            />
          </div>

          <!-- Pet Details -->
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-400">Raça:</span>
              <span class="text-sm font-medium text-white">{{ pet.breed || 'Não informado' }}</span>
            </div>
            
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-400">Apartamento:</span>
              <span class="text-sm font-medium text-white">
                {{ formatApartmentDescription(pet.apartment) }}
              </span>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="mt-4 pt-4 border-t border-gray-700/50">
            <div class="flex space-x-2">
              <NeonButton @click="editPet(pet)" size="sm" variant="secondary" class="flex-1">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
                </svg>
                Editar
              </NeonButton>
              <NeonButton @click="confirmDelete(pet)" size="sm" variant="danger" class="flex-1">
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
          <h4 class="font-medium text-red-300 mb-2">Pet a ser excluído:</h4>
          <div class="space-y-1 text-sm text-red-200">
            <p><strong>Nome:</strong> {{ deleteConfirmation.item.name }}</p>
            <p><strong>Espécie:</strong> {{ deleteConfirmation.item.species }}</p>
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
            @click="deletePet" 
            size="md" 
            variant="danger" 
            class="flex-1"
            :loading="deleteConfirmation.loading"
          >
            Excluir Pet
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
  name: 'PetsView',
  components: { 
    AuthenticatedLayout, 
    NeonButton, 
    Modal, 
    StatsCard 
  },
  setup() {
    const router = useRouter();
    const pets = ref([]);
    const loading = ref(true);
    const search = ref('');
    const filterSpecies = ref('');
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
        const petsRes = await fetch(`/api/pets?condominium_id=${condo}`, { headers });
        if (petsRes.ok) {
          pets.value = await petsRes.json();
        }
      } catch (e) {
        console.error('Erro ao carregar dados:', e);
      } finally {
        loading.value = false;
      }
    };

    const filteredPets = computed(() => {
      let filtered = pets.value;

      if (search.value) {
        const searchTerm = search.value.toLowerCase();
        filtered = filtered.filter(pet => 
          pet.name.toLowerCase().includes(searchTerm) ||
          pet.species.toLowerCase().includes(searchTerm) ||
          (pet.breed && pet.breed.toLowerCase().includes(searchTerm))
        );
      }

      if (filterSpecies.value) {
        filtered = filtered.filter(pet => pet.species === filterSpecies.value);
      }

      return filtered;
    });

    const availableSpecies = computed(() => {
      const species = [...new Set(pets.value.map(pet => pet.species))];
      return species.sort();
    });

    const stats = computed(() => {
      const total = pets.value.length;
      const dogs = pets.value.filter(p => p.species.toLowerCase().includes('cão') || p.species.toLowerCase().includes('cachorro')).length;
      const cats = pets.value.filter(p => p.species.toLowerCase().includes('gato')).length;
      const uniqueApartments = new Set(pets.value.map(p => p.apartment_id)).size;
      
      return {
        total,
        dogs,
        cats,
        uniqueApartments
      };
    });

    const clearFilters = () => {
      search.value = '';
      filterSpecies.value = '';
    };

    const toggleDropdown = (petId) => {
      // Close all other dropdowns
      Object.keys(openDropdowns.value).forEach(id => {
        if (id !== petId.toString()) {
          openDropdowns.value[id] = false;
        }
      });
      
      openDropdowns.value[petId] = !openDropdowns.value[petId];
    };

    const editPet = (pet) => {
      router.push({ name: 'pets.edit', params: { id: pet.id } });
      openDropdowns.value = {};
    };

    const confirmDelete = (pet) => {
      deleteConfirmation.value = {
        show: true,
        item: pet,
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

    const deletePet = async () => {
      if (!deleteConfirmation.value.item) return;
      
      deleteConfirmation.value.loading = true;
      const token = localStorage.getItem('token');
      const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

      try {
        const response = await fetch(`/api/pets/${deleteConfirmation.value.item.id}`, {
          method: 'DELETE',
          headers
        });

        if (response.ok) {
          pets.value = pets.value.filter(p => p.id !== deleteConfirmation.value.item.id);
          closeDeleteConfirmation();
        } else {
          console.error('Erro ao excluir pet');
        }
      } catch (e) {
        console.error('Erro ao excluir pet:', e);
      } finally {
        deleteConfirmation.value.loading = false;
      }
    };

    const handleImageError = (event) => {
      event.target.style.display = 'none';
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
      pets,
      loading,
      search,
      filterSpecies,
      filteredPets,
      availableSpecies,
      stats,
      openDropdowns,
      deleteConfirmation,
      clearFilters,
      toggleDropdown,
      editPet,
      confirmDelete,
      closeDeleteConfirmation,
      deletePet,
      handleImageError,
      formatApartmentDescription
    };
  }
};
</script>
