<template>
  <AuthenticatedLayout page-title="Pets">
    <div class="space-y-6">
      <!-- Header unificado -->
      <PageHeader
        title="Pets"
        subtitle="Controle todos os animais de estimação cadastrados no condomínio"
      >
        <template #actions>
          <AppButton @click="router.push({ name: 'pets.create' })" variant="primary">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Novo Pet
          </AppButton>
        </template>
      </PageHeader>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <StatsCard
          title="Total de Pets"
          :value="stats.total"
          icon="pet"
          variant="green"
        />
        <StatsCard
          title="Cachorros"
          :value="stats.dogs"
          icon="dog"
          variant="primary"
        />
        <StatsCard
          title="Gatos"
          :value="stats.cats"
          icon="cat"
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
        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
          <div class="relative flex-1 sm:w-72">
            <input
              v-model="search"
              type="text"
              placeholder="Buscar por nome ou espécie..."
              class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
            />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
          <select
            v-model="filterSpecies"
            class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm"
          >
            <option value="">Todas as espécies</option>
            <option v-for="species in availableSpecies" :key="species" :value="species">
              {{ species }}
            </option>
          </select>
          <AppButton v-if="search || filterSpecies" @click="clearFilters" size="sm" variant="ghost">
            Limpar
          </AppButton>
        </div>
      </div>

      <!-- Pets Grid -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      </div>

      <EmptyState
        v-else-if="filteredPets.length === 0"
        title="Nenhum pet encontrado"
        :description="search || filterSpecies ? 'Tente ajustar os filtros de busca.' : 'Comece cadastrando o primeiro pet.'"
      >
        <template #actions v-if="!search && !filterSpecies">
          <AppButton @click="router.push({ name: 'pets.create' })" variant="primary">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Cadastrar Primeiro Pet
          </AppButton>
        </template>
      </EmptyState>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="pet in filteredPets"
          :key="pet.id"
          class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-colors shadow-sm flex flex-col justify-between"
        >
          <div>
            <!-- Pet Header -->
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center space-x-3">
                <div class="h-10 w-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                  <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM16.5 6C17.6 6 18.5 6.9 18.5 8C18.5 9.1 17.6 10 16.5 10C15.4 10 14.5 9.1 14.5 8C14.5 6.9 15.4 6 16.5 6ZM7.5 6C8.6 6 9.5 6.9 9.5 8C9.5 9.1 8.6 10 7.5 10C6.4 10 5.5 9.1 5.5 8C5.5 6.9 6.4 6 7.5 6ZM12 11.5C15 11.5 17.5 14 17.5 17.5C17.5 20 15.5 22 12 22C8.5 22 6.5 20 6.5 17.5C6.5 14 9 11.5 12 11.5Z"/>
                  </svg>
                </div>
                <div>
                  <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ pet.name }}</h3>
                  <p class="text-xs text-slate-500 dark:text-slate-400">{{ pet.species }}</p>
                </div>
              </div>
              
              <!-- Actions Dropdown -->
              <div class="relative">
                <button
                  @click="toggleDropdown(pet.id)"
                  class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                  </svg>
                </button>
                
                <div
                  v-if="openDropdowns[pet.id]"
                  class="absolute right-0 mt-1 w-44 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-10"
                >
                  <button
                    @click="editPet(pet)"
                    class="w-full text-left px-4 py-2 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors flex items-center space-x-2"
                  >
                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Editar</span>
                  </button>
                  <button
                    @click="confirmDelete(pet)"
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

            <!-- Pet Photo -->
            <div v-if="pet.photo" class="mb-4">
              <img
                :src="pet.photo"
                :alt="pet.name"
                class="w-full h-36 object-cover rounded-lg border border-slate-200 dark:border-slate-800"
                @error="handleImageError"
              />
            </div>

            <!-- Pet Details -->
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-3.5 space-y-2 border border-slate-100 dark:border-slate-800 text-xs">
              <div class="flex items-center justify-between">
                <span class="text-slate-500 dark:text-slate-400">Raça:</span>
                <span class="font-medium text-slate-800 dark:text-slate-200">{{ pet.breed || 'Não informado' }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-slate-500 dark:text-slate-400">Apartamento:</span>
                <span class="font-medium text-slate-800 dark:text-slate-200">
                  {{ formatApartmentDescription(pet.apartment) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex space-x-2">
            <AppButton @click="editPet(pet)" size="sm" variant="secondary" class="flex-1">
              <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
              </svg>
              Editar
            </AppButton>
            <AppButton @click="confirmDelete(pet)" size="sm" variant="danger" class="flex-1">
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
          <h4 class="text-xs font-semibold text-rose-900 dark:text-rose-200 mb-1">Pet a ser excluído:</h4>
          <div class="space-y-0.5 text-xs text-rose-700 dark:text-rose-300">
            <p><strong>Nome:</strong> {{ deleteConfirmation.item.name }}</p>
            <p><strong>Espécie:</strong> {{ deleteConfirmation.item.species }}</p>
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
            @click="deletePet" 
            size="md" 
            variant="danger" 
            class="flex-1"
            :loading="deleteConfirmation.loading"
          >
            Excluir Pet
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
  name: 'PetsView',
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

      try {
        const data = await apiRequest('/pets');
        pets.value = Array.isArray(data) ? data : (data?.data || []);
      } catch (e) {
        pets.value = [];
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

      try {
        await apiRequest(`/pets/${deleteConfirmation.value.item.id}`, { method: 'DELETE' });
        pets.value = pets.value.filter(p => p.id !== deleteConfirmation.value.item.id);
        closeDeleteConfirmation();
      } catch (e) {
        deleteConfirmation.value.loading = false;
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
