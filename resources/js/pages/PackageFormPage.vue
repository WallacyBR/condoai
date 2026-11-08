<template>
  <AuthenticatedLayout :page-title="title">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="bg-gradient-to-r from-indigo-600/20 to-purple-600/20 rounded-2xl p-6 border-2 border-indigo-500/30">
        <div class="flex items-center space-x-4">
          <div class="h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center border-2 border-indigo-500/30">
            <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-white">{{ title }}</h1>
            <p class="text-indigo-300">{{ id ? 'Atualize as informações da correspondência' : 'Registre uma nova correspondência no sistema' }}</p>
          </div>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Informações Básicas -->
        <div class="bg-gradient-to-br from-gray-900/90 via-gray-800/90 to-gray-900/90 rounded-2xl p-6 border-2 border-gray-700/50 shadow-xl">
          <div class="flex items-center space-x-3 mb-6">
            <div class="h-10 w-10 rounded-full bg-blue-500/20 flex items-center justify-center">
              <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h2 class="text-xl font-semibold text-white">Informações Básicas</h2>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Unidade -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-300">
                <span class="flex items-center space-x-2">
                  <svg class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  <span>Unidade *</span>
                  <span v-if="apartments.length > 0" class="text-xs text-green-400">({{ apartments.length }} unidades)</span>
                  <span v-else class="text-xs text-orange-400">(carregando...)</span>
                </span>
              </label>
              <select 
                v-model="form.apartment_id" 
                required
                class="w-full bg-gray-800/50 border border-gray-600/50 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              >
                <option :value="null" disabled>
                  {{ apartments.length > 0 ? 'Selecione a unidade' : 'Carregando unidades...' }}
                </option>
                <option v-for="a in apartments" :key="a.id" :value="a.id" class="bg-gray-800">
                  {{ formatApartmentDescription(a) }}
                </option>
              </select>
              <!-- Debug info -->
              <div v-if="apartments.length === 0 && !loading" class="text-xs text-orange-400">
                ⚠️ Nenhuma unidade encontrada. Verifique se o condomínio possui unidades cadastradas.
              </div>
            </div>

            <!-- Local de Retirada -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-300">
                <span class="flex items-center space-x-2">
                  <svg class="h-4 w-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span>Local de Retirada *</span>
                </span>
              </label>
              <input 
                v-model="form.pickup_location" 
                type="text"
                required
                placeholder="Ex: Portaria Principal, Recepção..."
                class="w-full bg-gray-800/50 border border-gray-600/50 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
              />
            </div>
          </div>

          <!-- Observação -->
          <div class="space-y-2 mt-6">
            <label class="block text-sm font-medium text-gray-300">
              <span class="flex items-center space-x-2">
                <svg class="h-4 w-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4M7 16h10M3 4h18a2 2 0 012 2v12a2 2 0 01-2 2H3a2 2 0 01-2-2V6a2 2 0 012-2z" />
                </svg>
                <span>Observações</span>
              </span>
            </label>
            <textarea 
              v-model="form.observation" 
              rows="3"
              placeholder="Informações adicionais sobre a correspondência..."
              class="w-full bg-gray-800/50 border border-gray-600/50 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-colors resize-none"
            ></textarea>
          </div>
        </div>

        <!-- Foto da Correspondência -->
        <div class="bg-gradient-to-br from-gray-900/90 via-gray-800/90 to-gray-900/90 rounded-2xl p-6 border-2 border-gray-700/50 shadow-xl">
          <div class="flex items-center space-x-3 mb-6">
            <div class="h-10 w-10 rounded-full bg-purple-500/20 flex items-center justify-center">
              <svg class="h-5 w-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <div>
              <h2 class="text-xl font-semibold text-white">Foto da Correspondência</h2>
              <p class="text-sm text-gray-400">Capture uma foto da correspondência para facilitar a identificação</p>
            </div>
          </div>

          <div class="bg-gray-800/30 rounded-lg p-4 border border-gray-600/30">
            <WebcamCapture v-model="form.photo" />
          </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex justify-between items-center pt-6">
          <!-- Botão de teste da API (apenas em desenvolvimento) -->
          <NeonButton 
            type="button" 
            @click="testApiConnection" 
            size="sm" 
            class="bg-yellow-600/40 hover:bg-yellow-600/60 text-xs"
            v-if="isDevelopment"
          >
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Testar API
          </NeonButton>
          
          <div class="flex space-x-4">
            <NeonButton 
              type="button" 
              @click="goBack" 
              size="md" 
              class="bg-gray-600/40 hover:bg-gray-600/60"
            >
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Cancelar
            </NeonButton>
            
            <NeonButton 
              type="submit" 
              size="md" 
              :loading="saving"
              :disabled="!isFormValid"
              class="bg-gradient-to-r from-indigo-600/60 to-purple-600/60 hover:from-indigo-600/80 hover:to-purple-600/80"
            >
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              {{ id ? 'Atualizar' : 'Salvar' }} Correspondência
            </NeonButton>
          </div>
        </div>
      </form>

      <!-- Loading Overlay -->
      <div v-if="loading" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-gradient-to-br from-gray-900/90 via-gray-800/90 to-gray-900/90 rounded-2xl p-8 border-2 border-indigo-500/30 shadow-2xl">
          <div class="flex items-center space-x-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <span class="text-white font-medium">{{ id ? 'Carregando dados...' : 'Carregando...' }}</span>
          </div>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="fixed bottom-4 right-4 bg-red-500/90 backdrop-blur-sm text-white px-6 py-4 rounded-lg shadow-lg border border-red-400/50 z-50">
        <div class="flex items-center space-x-2">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>{{ error }}</span>
        </div>
      </div>

      <!-- Success Message -->
      <div v-if="success" class="fixed bottom-4 right-4 bg-green-500/90 backdrop-blur-sm text-white px-6 py-4 rounded-lg shadow-lg border border-green-400/50 z-50">
        <div class="flex items-center space-x-2">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span>{{ success }}</span>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../components/NeonButton.vue';
import { apiRequest } from '../utils/api';
import { formatApartmentDescription } from '../utils/formatters';
import WebcamCapture from '../components/WebcamCapture.vue';
import { useRoute, useRouter } from 'vue-router';

export default {
  name: 'PackageFormPage',
  components: { AuthenticatedLayout, NeonButton, WebcamCapture },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const id = route.params.id;
    const title = id ? 'Editar Correspondência' : 'Nova Correspondência';
    
    const form = ref({
      apartment_id: null,
      pickup_location: '',
      observation: '',
      photo: ''
    });
    
    const apartments = ref([]);
    const loading = ref(true);
    const saving = ref(false);
    const error = ref('');
    const success = ref('');

    // Computed para validação do formulário
    const isFormValid = computed(() => {
      return form.value.apartment_id && form.value.pickup_location.trim();
    });

    // Computed para verificar se estamos em modo de desenvolvimento
    const isDevelopment = computed(() => {
      return import.meta.env.DEV;
    });

    const showMessage = (message, type = 'error') => {
      if (type === 'error') {
        error.value = message;
        setTimeout(() => error.value = '', 5000);
      } else {
        success.value = message;
        setTimeout(() => success.value = '', 3000);
      }
    };

    const load = async () => {
      try {
        loading.value = true;
        error.value = '';
        
        const condo = localStorage.getItem('selectedCondominium');
        console.log('Condomínio selecionado:', condo); // Debug
        
        if (!condo) {
          showMessage('Nenhum condomínio selecionado');
          return;
        }

        // Carregar apartamentos
        console.log('Carregando apartamentos...'); // Debug
        
        try {
          const apartmentsData = await apiRequest(`/api/apartments?condominium_id=${condo}`);
          console.log('Dados dos apartamentos recebidos:', apartmentsData); // Debug
          
          // Verificar se recebemos dados válidos
          if (apartmentsData) {
            apartments.value = Array.isArray(apartmentsData) ? apartmentsData : (apartmentsData?.data || []);
            console.log('Apartamentos processados:', apartments.value); // Debug
            console.log('Total de apartamentos:', apartments.value.length); // Debug
          } else {
            console.warn('Nenhum dado de apartamento recebido');
            apartments.value = [];
          }
        } catch (apiError) {
          console.error('Erro específico na API de apartamentos:', apiError);
          showMessage(`Erro ao carregar apartamentos: ${apiError.message}`);
          apartments.value = [];
        }

        // Se for edição, carregar dados do pacote
        if (id) {
          console.log('Carregando dados do pacote para edição, ID:', id); // Debug
          try {
            const pkg = await apiRequest(`/api/packages/${id}`);
            console.log('Dados do pacote carregados:', pkg); // Debug
            
            if (pkg) {
              form.value.apartment_id = pkg.apartment_id;
              form.value.pickup_location = pkg.pickup_location;
              form.value.observation = pkg.observation || '';
              form.value.photo = pkg.photo || '';
            } else {
              showMessage('Correspondência não encontrada');
            }
          } catch (pkgError) {
            console.error('Erro ao carregar pacote:', pkgError);
            showMessage(`Erro ao carregar dados da correspondência: ${pkgError.message}`);
          }
        }
      } catch (e) {
        console.error('Erro geral ao carregar dados:', e);
        showMessage('Erro ao carregar dados do formulário');
      } finally {
        loading.value = false;
      }
    };

    // Função para testar a conectividade da API
    const testApiConnection = async () => {
      try {
        console.log('Testando conexão com a API...');
        const testResponse = await apiRequest('/api/packages');
        console.log('Teste de API bem-sucedido:', testResponse);
        showMessage('Conexão com a API funcionando corretamente!', 'success');
      } catch (e) {
        console.error('Erro no teste da API:', e);
        showMessage(`Erro na conexão com a API: ${e.message}`);
      }
    };

    const submit = async () => {
      if (!isFormValid.value) {
        showMessage('Preencha todos os campos obrigatórios');
        return;
      }

      try {
        saving.value = true;
        error.value = '';

        const url = id ? `/api/packages/${id}` : '/api/packages';
        const method = id ? 'PUT' : 'POST';
        const condo = localStorage.getItem('selectedCondominium');
        
        // Validação adicional
        if (!condo) {
          showMessage('Nenhum condomínio selecionado. Selecione um condomínio antes de continuar.');
          return;
        }

        // Construir dados do formulário
        const body = {
          apartment_id: parseInt(form.value.apartment_id),
          pickup_location: form.value.pickup_location.trim(),
          observation: form.value.observation ? form.value.observation.trim() : null,
          photo: form.value.photo || null
        };

        // Log para depuração
        console.log('Dados sendo enviados:', body);
        console.log('URL:', url);
        console.log('Method:', method);

        const resp = await apiRequest(url, { 
          method, 
          body: JSON.stringify(body),
          headers: {
            'Content-Type': 'application/json'
          }
        });

        console.log('Resposta da API:', resp);

        if (resp) {
          showMessage(
            id ? 'Correspondência atualizada com sucesso!' : 'Correspondência registrada com sucesso!', 
            'success'
          );
          
          // Redirecionar após um breve delay
          setTimeout(() => {
            router.push('/packages');
          }, 1500);
        }
      } catch (e) {
        console.error('Erro detalhado ao salvar:', e);
        console.error('Stack trace:', e.stack);
        
        // Melhor tratamento de mensagens de erro
        let errorMessage = 'Erro ao salvar correspondência. Tente novamente.';
        
        if (e.message) {
          if (e.message.includes('apartamento')) {
            errorMessage = 'Erro: Apartamento inválido ou não encontrado.';
          } else if (e.message.includes('validação') || e.message.includes('validation')) {
            errorMessage = 'Erro de validação: Verifique se todos os campos estão preenchidos corretamente.';
          } else if (e.message.includes('conexão') || e.message.includes('network')) {
            errorMessage = 'Erro de conexão: Verifique sua conexão com a internet.';
          } else if (e.message.includes('401') || e.message.includes('unauthorized')) {
            errorMessage = 'Erro de autenticação: Faça login novamente.';
          } else {
            errorMessage = `Erro: ${e.message}`;
          }
        }
        
        showMessage(errorMessage);
      } finally {
        saving.value = false;
      }
    };

    const goBack = () => {
      router.push('/packages');
    };

    onMounted(load);

    return { 
      form, 
      apartments, 
      title, 
      id,
      loading,
      saving,
      error,
      success,
      isFormValid,
      isDevelopment,
      submit, 
      goBack,
      formatApartmentDescription,
      testApiConnection
    };
  }
};
</script>
