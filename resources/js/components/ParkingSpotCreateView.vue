<template>
  <AuthenticatedLayout page-title="Criar Nova Vaga">
    <div class="max-w-2xl mx-auto space-y-6">
      <PageHeader
        title="Criar Nova Vaga"
        subtitle="Cadastre uma vaga de garagem vinculada a uma unidade"
        show-back
        @back="cancel"
      />

      <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
        <form @submit.prevent="createSpot" class="space-y-5">
          <div>
            <label for="identifier" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Identificador *</label>
            <input
              id="identifier"
              v-model="newSpot.identifier"
              type="text"
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
              placeholder="Ex: G1-01 ou 101A"
              required
            />
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              A vaga pode ter designação numérica ou textual (ex: Bloco A, Ap 101).
            </p>
          </div>
          <div>
            <label for="apartment_id" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Unidade Proprietária *</label>
            <select
              id="apartment_id"
              v-model="newSpot.apartment_id"
              class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
              required
            >
              <option :value="null" disabled>Selecione a unidade</option>
              <option v-for="a in apartments" :value="a.id" :key="a.id">{{ formatApartmentDescription(a) }}</option>
            </select>
          </div>
          <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <AppButton type="button" @click="cancel" variant="secondary">Cancelar</AppButton>
            <AppButton type="submit" variant="primary">Salvar Vaga</AppButton>
          </div>
        </form>
      </div>

      <div v-if="error" class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 rounded-xl p-4 text-rose-700 dark:text-rose-300 text-sm">
        {{ error }}
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import NeonButton from './NeonButton.vue';
import AppButton from './ui/AppButton.vue';
import PageHeader from './ui/PageHeader.vue';
import { formatApartmentDescription } from '../utils/formatters.js';
import { apiRequest } from '../utils/api';

export default {
  name: 'ParkingSpotCreateView',
  components: { AuthenticatedLayout, NeonButton, AppButton, PageHeader },
  setup() {
    const router = useRouter();

    const newSpot = ref({ identifier: '', apartment_id: null });
    const apartments = ref([]);
    const error = ref('');

    const loadApartments = async () => {
      try {
        const data = await apiRequest('/apartments');
        apartments.value = Array.isArray(data) ? data : (data?.data || []);
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
        await apiRequest('/parking-spots', {
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
