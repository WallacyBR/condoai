<template>
  <AuthenticatedLayout page-title="Editar Unidade">
    <div class="space-y-6">
      <!-- Debug Info -->
      <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4">
        <h3 class="text-blue-300 font-medium mb-2">Debug Info</h3>
        <div class="text-sm text-blue-200 space-y-1">
          <div>ID da Rota: {{ route.params.id }}</div>
          <div>Token: {{ token ? 'Presente' : 'Ausente' }}</div>
          <div>Condomínio: {{ condominiumId || 'Não selecionado' }}</div>
          <div>Loading: {{ loading }}</div>
          <div>Error: {{ error || 'Nenhum' }}</div>
        </div>
      </div>

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

export default {
  name: 'EditUnitPage',
  components: { AuthenticatedLayout, NeonButton },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const loading = ref(false);
    const error = ref('');
    const token = ref(localStorage.getItem('token'));
    const condominiumId = ref(localStorage.getItem('selectedCondominium'));
    const form = ref({
      number: '',
      floor: null,
      active: true,
      condominium_id: condominiumId.value
    });

    // Carregar dados da unidade
    const loadUnit = async () => {
      try {
        console.log('Carregando unidade com ID:', route.params.id);
        loading.value = true;
        error.value = '';
        const response = await fetch(`/api/apartments/${route.params.id}`, {
          headers: {
            'Authorization': `Bearer ${token.value}`,
            'Content-Type': 'application/json'
          }
        });

        console.log('Status da resposta:', response.status);
        console.log('Headers da resposta:', response.headers);

        if (response.ok) {
          const data = await response.json();
          console.log('Dados recebidos da API:', data);
          form.value = {
            number: data.number || '',
            floor: data.floor || null,
            active: data.active !== undefined ? data.active : true,
            condominium_id: data.condominium_id || condominiumId.value
          };
          console.log('Formulário preenchido:', form.value);
        } else {
          console.error('Erro ao carregar unidade - Status:', response.status);
          const errorText = await response.text();
          console.error('Erro detalhado:', errorText);
          error.value = 'Erro ao carregar dados da unidade';
        }
      } catch (error) {
        console.error('Erro ao carregar unidade:', error);
        error.value = 'Erro de conexão ao carregar dados';
      } finally {
        loading.value = false;
      }
    };

    const submit = async () => {
      try {
        console.log('Enviando formulário:', form.value);
        loading.value = true;
        error.value = '';
        const response = await fetch(`/api/apartments/${route.params.id}`, {
          method: 'PUT',
          headers: {
            'Authorization': `Bearer ${token.value}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(form.value)
        });

        console.log('Status da resposta do submit:', response.status);

        if (response.ok) {
          console.log('Unidade atualizada com sucesso');
          router.push('/units-list');
        } else {
          const errorData = await response.json();
          console.error('Erro ao salvar unidade:', errorData);
          error.value = 'Erro ao salvar alterações';
        }
      } catch (error) {
        console.error('Erro ao salvar unidade:', error);
        error.value = 'Erro de conexão ao salvar';
      } finally {
        loading.value = false;
      }
    };

    const goBack = () => {
      console.log('Voltando para a lista de unidades');
      router.push('/units-list');
    };

    onMounted(() => {
      console.log('EditUnitPage montado, ID da rota:', route.params.id);
      
      // Verificar autenticação
      if (!token.value) {
        console.log('Token não encontrado, redirecionando para login');
        router.push('/login');
        return;
      }
      
      // Verificar condomínio selecionado
      if (!condominiumId.value) {
        console.log('Condomínio não selecionado, redirecionando para seleção');
        router.push('/condominium-select');
        return;
      }
      
      if (route.params.id) {
        console.log('Iniciando carregamento da unidade');
        loadUnit();
      } else {
        console.log('Nenhum ID fornecido, redirecionando');
        router.push('/units-list');
      }
    });

    return {
      form,
      loading,
      error,
      token,
      condominiumId,
      route,
      submit,
      goBack
    };
  }
};
</script> 