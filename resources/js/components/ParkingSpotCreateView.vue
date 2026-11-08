<template>
  <AuthenticatedLayout page-title="Criar Nova Vaga">
    <div class="max-w-2xl mx-auto bg-gray-900/50 rounded-2xl p-6 border-2 border-indigo-500/30 shadow-xl">
      <form @submit.prevent="createSpot" class="space-y-6">
        <div>
          <label for="identifier" class="block text-sm font-medium text-gray-300">Identificador</label>
          <input
            id="identifier"
            v-model="newSpot.identifier"
            type="text"
            class="mt-1 block w-full bg-gray-800 border-gray-700 rounded-lg px-3 py-2 text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 transition-colors"
            placeholder="Ex: G1-01 ou 101A"
            required
          />
          <p class="mt-2 text-sm text-gray-400">
            A vaga pode ter designação numérica ou textual. Pode inclusive herdar a designação da unidade (ex: Bloco A, Ap 101).
          </p>
        </div>
        <div>
          <label for="apartment_id" class="block text-sm font-medium text-gray-300">Unidade Proprietária</label>
          <select
            id="apartment_id"
            v-model="newSpot.apartment_id"
            class="mt-1 block w-full bg-gray-800 border-gray-700 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-indigo-500 transition-colors"
            required
          >
            <option :value="null" disabled>Selecione a unidade</option>
            <option v-for="a in apartments" :value="a.id" :key="a.id">{{ formatApartmentDescription(a) }}</option>
          </select>
        </div>
        <div class="flex justify-end space-x-4 pt-4">
          <NeonButton type="button" @click="cancel" class="bg-gray-600/40 hover:bg-gray-600/60">Cancelar</NeonButton>
          <NeonButton type="submit">Salvar Vaga</NeonButton>
        </div>
      </form>
    </div>
    <div v-if="error" class="mt-4 max-w-2xl mx-auto bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-red-300">
      {{ error }}
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import NeonButton from './NeonButton.vue';
import { formatApartmentDescription } from '../utils/formatters.js';
import { apiRequest } from '../utils/api';

export default {
  name: 'ParkingSpotCreateView',
  components: { AuthenticatedLayout, NeonButton },
  setup() {
    const router = useRouter();
    const condo = localStorage.getItem('selectedCondominium');
    
    const newSpot = ref({ identifier: '', apartment_id: null, condominium_id: condo });
    const apartments = ref([]);
    const error = ref('');

    const loadApartments = async () => {
      try {
        apartments.value = await apiRequest(`/api/apartments?condominium_id=${condo}`);
      } catch (e) {
        error.value = e.message;
      }
    };

    const createSpot = async () => {
      error.value = '';
      
      if (!newSpot.value.identifier || !newSpot.value.apartment_id) {
        error.value = 'Por favor, preencha todos os campos.';
        return;
      }
      
      try {
        await apiRequest('/api/parking-spots', {
          method: 'POST',
          body: JSON.stringify(newSpot.value)
        });
        
        router.push({ name: 'parking-spots.index' });
      } catch (e) {
        error.value = e.message;
      }
    };

    const cancel = () => {
      router.back();
    };

    onMounted(loadApartments);

    return {
      newSpot,
      apartments,
      createSpot,
      cancel,
      error,
      formatApartmentDescription
    };
  }
};
</script> 