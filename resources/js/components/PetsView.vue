<template>
  <AuthenticatedLayout page-title="Pets">
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <div class="flex space-x-4">
          <div class="relative">
            <input
              type="text"
              v-model="search"
              placeholder="Buscar pets..."
              class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
        </div>
        <button
          @click="openNewPetModal"
          class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200 flex items-center"
        >
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Novo Pet
        </button>
      </div>

      <div v-if="loading" class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      </div>

      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-600">
        {{ error }}
      </div>

      <div v-else-if="filteredPets.length === 0" class="text-center py-8">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a1 1 0 00-1-1h-2a1 1 0 00-1 1v6M9 11V7a1 1 0 00-1-1H6a1 1 0 00-1 1v4M5 19h14M5 15h14" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum pet encontrado</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ search ? 'Tente ajustar sua busca' : 'Comece adicionando um novo pet' }}
        </p>
        <div class="mt-6">
          <button
            @click="openNewPetModal"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Novo Pet
          </button>
        </div>
      </div>

      <div v-else class="bg-white shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Espécie</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Raça</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unidade</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="pet in filteredPets" :key="pet.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">{{ pet.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ pet.species }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ pet.breed }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ pet.apartment.number }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <PetForm :show="showModal" :pet="selected" :apartment-id="apartmentId" @close="closeModal" @saved="refresh" />
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, computed } from 'vue';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import PetForm from './PetForm.vue';

export default {
  name: 'PetsView',
  components: { AuthenticatedLayout, PetForm },
  props: { apartmentId: { type: Number, required: true } },
  setup(props) {
    const pets = ref([]);
    const loading = ref(true);
    const error = ref('');
    const search = ref('');
    const showModal = ref(false);
    const selected = ref(null);

    const loadData = async () => {
      try {
        const token = localStorage.getItem('token');
        const response = await fetch(`/api/pets?apartment_id=${props.apartmentId}`, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });

        if (response.ok) {
          pets.value = await response.json();
        } else {
          error.value = 'Falha ao carregar dados';
        }
      } catch (e) {
        error.value = 'Erro de conexão';
      } finally {
        loading.value = false;
      }
    };

    const filteredPets = computed(() => {
      return pets.value.filter(pet => pet.name.toLowerCase().includes(search.value.toLowerCase()));
    });

    const openNewPetModal = () => {
      selected.value = null;
      showModal.value = true;
    };

    const closeModal = () => {
      showModal.value = false;
    };

    const refresh = async () => {
      showModal.value = false;
      await loadData();
    };

    loadData();

    return {
      pets,
      loading,
      error,
      search,
      filteredPets,
      openNewPetModal,
      closeModal,
      showModal,
      selected,
      refresh
    };
  }
};
</script>
