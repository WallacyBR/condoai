<template>
  <AuthenticatedLayout :page-title="pageTitle">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header Card - OBRIGATÓRIO -->
      <div :class="headerCardClasses">
        <div class="flex items-center space-x-4">
          <div :class="headerIconClasses">
            <svg class="h-8 w-8" :class="headerIconColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="isEditing" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-white">{{ pageTitle }}</h1>
            <p :class="headerDescriptionColor">{{ pageDescription }}</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <div class="flex items-center space-x-2">
        <NeonButton 
          type="button" 
          @click="router.push('/maintenance/areas')" 
          size="sm" 
          class="bg-gray-600/40 hover:bg-gray-600/60"
        >
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Voltar para Áreas
        </NeonButton>
      </div>

      <!-- Form Card -->
      <div class="bg-gradient-to-br from-gray-900/90 via-gray-800/90 to-gray-900/90 backdrop-blur-xl rounded-2xl p-6 border-2 border-gray-700/50 shadow-xl">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Basic Information -->
          <div class="space-y-4">
            <div class="flex items-center space-x-3 mb-6">
              <div class="h-10 w-10 rounded-full bg-cyan-500/20 flex items-center justify-center">
                <svg class="h-5 w-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h2 class="text-xl font-semibold text-white">Informações Básicas</h2>
            </div>
            
            <div class="grid grid-cols-1 gap-6">
              <!-- Name -->
              <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                  Nome da Área *
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  id="name"
                  name="name"
                  placeholder="Ex: Elevadores, Portaria, Piscina, Jardim..."
                  required
                  :class="inputClasses"
                />
                <p class="mt-1 text-xs text-gray-500">Nome identificador da área de manutenção</p>
              </div>

              <!-- Description -->
              <div>
                <label for="description" class="block text-sm font-medium text-gray-300 mb-2">
                  Descrição
                </label>
                <textarea
                  v-model="form.description"
                  id="description"
                  name="description"
                  rows="4"
                  placeholder="Descrição detalhada da área, localização, equipamentos inclusos..."
                  :class="textareaClasses"
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">Descrição opcional para melhor identificação da área</p>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-700/50">
            <NeonButton
              type="button"
              @click="router.push('/maintenance/areas')"
              size="md"
              class="bg-gray-600/40 hover:bg-gray-600/60 flex-1 sm:flex-none"
            >
              Cancelar
            </NeonButton>
            <NeonButton
              type="submit"
              :disabled="isSubmitting"
              :loading="isSubmitting"
              size="md"
              class="flex-1 bg-gradient-to-r from-indigo-600/60 to-purple-600/60 hover:from-indigo-600/80 hover:to-purple-600/80"
            >
              <svg v-if="!isSubmitting" class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              {{ isEditing ? 'Atualizar Área' : 'Cadastrar Área' }}
            </NeonButton>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import AuthenticatedLayout from '../../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../../components/NeonButton.vue';
import api from '../../utils/api.js';

export default {
  name: 'MaintenanceAreaFormPage',
  components: {
    AuthenticatedLayout,
    NeonButton
  },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const isEditing = computed(() => !!route.params.id);
    
    const form = ref({
      name: '',
      description: ''
    });

    const isSubmitting = ref(false);
    const loading = ref(false);

    const pageTitle = computed(() => {
      return isEditing.value ? 'Editar Área' : 'Nova Área de Manutenção';
    });

    const pageDescription = computed(() => {
      return isEditing.value 
        ? 'Atualize as informações da área de manutenção' 
        : 'Cadastre uma nova área para organizar os equipamentos';
    });

    // Header card classes based on context
    const headerCardClasses = computed(() => {
      return isEditing.value
        ? 'bg-gradient-to-r from-orange-600/20 to-amber-600/20 rounded-2xl p-6 border-2 border-orange-500/30'
        : 'bg-gradient-to-r from-green-600/20 to-emerald-600/20 rounded-2xl p-6 border-2 border-green-500/30';
    });

    const headerIconClasses = computed(() => {
      return isEditing.value
        ? 'h-16 w-16 rounded-full bg-orange-100 flex items-center justify-center border-2 border-orange-500/30'
        : 'h-16 w-16 rounded-full bg-green-100 flex items-center justify-center border-2 border-green-500/30';
    });

    const headerIconColor = computed(() => {
      return isEditing.value ? 'text-orange-600' : 'text-green-600';
    });

    const headerDescriptionColor = computed(() => {
      return isEditing.value ? 'text-orange-300' : 'text-green-300';
    });

    const inputClasses = 'w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors';
    
    const textareaClasses = 'w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors resize-none';

    const loadData = async () => {
      if (!isEditing.value) return;

      loading.value = true;
      try {
        const response = await api.get(`/maintenance/areas/${route.params.id}`);
        const area = response.data;
        form.value = {
          name: area.name || '',
          description: area.description || ''
        };
      } catch (error) {
        console.error('Erro ao carregar área:', error);
        // TODO: Mostrar notificação de erro
      } finally {
        loading.value = false;
      }
    };

    const handleSubmit = async () => {
      isSubmitting.value = true;
      
      try {
        if (isEditing.value) {
          await api.put(`/maintenance/areas/${route.params.id}`, form.value);
        } else {
          await api.post('/maintenance/areas', form.value);
        }
        
        router.push('/maintenance/areas');
      } catch (error) {
        console.error('Erro ao salvar área:', error);
        // TODO: Mostrar notificação de erro
      } finally {
        isSubmitting.value = false;
      }
    };

    onMounted(() => {
      loadData();
    });

    return {
      router,
      form,
      isSubmitting,
      loading,
      isEditing,
      pageTitle,
      pageDescription,
      headerCardClasses,
      headerIconClasses,
      headerIconColor,
      headerDescriptionColor,
      inputClasses,
      textareaClasses,
      handleSubmit
    };
  }
};
</script> 