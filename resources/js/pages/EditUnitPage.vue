<template>
  <AuthenticatedLayout page-title="Editar Unidade">
    <div class="space-y-6">
      <!-- Botão Voltar -->
      <div class="flex items-center space-x-4">
        <NeonButton @click="goBack" size="sm" variant="secondary">
          <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Voltar
        </NeonButton>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        <span class="ml-2 text-white">Carregando...</span>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-red-300">
        {{ error }}
      </div>

      <!-- Formulário -->
      <div v-else class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl p-6 border-2 border-indigo-500/20">
        <form @submit.prevent="submit" class="space-y-4">
          <!-- Número da Unidade -->
          <div>
            <label for="number" class="block text-sm font-medium text-gray-300">
              Número da Unidade *
            </label>
            <input
              id="number"
              v-model="form.number"
              type="text"
              required
              class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              placeholder="Ex: 101, 205, etc."
            />
          </div>

          <!-- Andar -->
          <div>
            <label for="floor" class="block text-sm font-medium text-gray-300">
              Andar *
            </label>
            <input
              id="floor"
              v-model.number="form.floor"
              type="number"
              min="0"
              required
              class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              placeholder="Ex: 1, 2, 3..."
            />
          </div>

          <!-- Status -->
          <div>
            <label for="active" class="block text-sm font-medium text-gray-300">
              Status
            </label>
            <select
              id="active"
              v-model="form.active"
              class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option :value="true">Ativo</option>
              <option :value="false">Inativo</option>
            </select>
          </div>

          <!-- Botões de Ação -->
          <div class="flex justify-end space-x-3 pt-4">
            <NeonButton
              type="button"
              @click="goBack"
              size="md"
              variant="secondary"
            >
              Cancelar
            </NeonButton>
            <NeonButton
              type="submit"
              size="md"
              :disabled="loading"
              variant="success"
            >
              <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ loading ? 'Salvando...' : 'Salvar' }}
            </NeonButton>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../components/NeonButton.vue';
import { apiRequest } from '../utils/api';

export default {
  name: 'EditUnitPage',
  components: { AuthenticatedLayout, NeonButton },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const loading = ref(false);
    const error = ref('');
    const form = ref({
      number: '',
      floor: null,
      active: true
    });

    // Carregar dados da unidade
    const loadUnit = async () => {
      try {
        loading.value = true;
        error.value = '';
        const data = await apiRequest(`/apartments/${route.params.id}`);
        form.value = {
          number: data.number || '',
          floor: data.floor || null,
          active: data.active !== undefined ? data.active : true
        };
      } catch (err) {
        error.value = err.message || 'Erro ao carregar dados da unidade';
      } finally {
        loading.value = false;
      }
    };

    const submit = async () => {
      try {
        loading.value = true;
        error.value = '';
        await apiRequest(`/apartments/${route.params.id}`, {
          method: 'PUT',
          body: JSON.stringify(form.value)
        });

        router.push('/units-list');
      } catch (err) {
        error.value = err.message || 'Erro ao salvar alterações';
      } finally {
        loading.value = false;
      }
    };

    const goBack = () => {
      router.push('/units-list');
    };

    onMounted(() => {
      if (route.params.id) {
        loadUnit();
      } else {
        router.push('/units-list');
      }
    });

    return {
      form,
      loading,
      error,
      route,
      submit,
      goBack
    };
  }
};
</script>
