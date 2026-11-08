<template>
  <AuthenticatedLayout :page-title="pageTitle">
    <div class="space-y-6">
      <!-- Header Card - OBRIGATÓRIO conforme DESIGN_MANUAL.md -->
      <div :class="headerCardClasses">
        <div class="flex items-center space-x-4">
          <div :class="headerIconClasses">
            <svg class="h-8 w-8" :class="headerIconColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="isEditing" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path v-if="!isEditing" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
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
        <NeonButton @click="router.push('/maintenance/equipment')" size="md" variant="secondary">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Voltar para Equipamentos
        </NeonButton>
      </div>

      <!-- Form Card -->
      <div class="bg-gradient-to-br from-gray-900/90 via-gray-800/90 to-gray-900/90 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Basic Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                Nome do Equipamento *
              </label>
              <input
                v-model="form.name"
                type="text"
                id="name"
                name="name"
                placeholder="Ex: Motor da Piscina, Elevador 1, Gerador..."
                required
                :class="inputClasses"
              />
            </div>

            <!-- Maintenance Area -->
            <div>
              <label for="maintenance_area_id" class="block text-sm font-medium text-gray-300 mb-2">
                Área de Manutenção *
              </label>
              <select
                v-model="form.maintenance_area_id"
                id="maintenance_area_id"
                name="maintenance_area_id"
                required
                :class="selectClasses"
              >
                <option value="">Selecione a área</option>
                <option v-for="area in maintenanceAreas" :key="area.id" :value="area.id">
                  {{ area.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Location Details -->
          <div>
            <label for="location_details" class="block text-sm font-medium text-gray-300 mb-2">
              Detalhes da Localização
            </label>
            <input
              v-model="form.location_details"
              type="text"
              id="location_details"
              name="location_details"
              placeholder="Ex: Casa de máquinas, Subsolo 2, Cobertura..."
              :class="inputClasses"
            />
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
              rows="3"
              placeholder="Descrição detalhada do equipamento, função, características..."
              :class="inputClasses"
            ></textarea>
          </div>

          <!-- QR Code Access Level -->
          <div>
            <label for="qr_code_access_level" class="block text-sm font-medium text-gray-300 mb-2">
              Nível de Acesso QR Code
            </label>
            <select
              v-model="form.qr_code_access_level"
              id="qr_code_access_level"
              name="qr_code_access_level"
              :class="selectClasses"
            >
              <option value="public">Público</option>
              <option value="internal_users">Usuários Internos</option>
              <option value="specific_users">Usuários Específicos</option>
            </select>
            <p class="mt-1 text-xs text-gray-500">Define quem pode acessar o relatório público deste equipamento via QR Code</p>
          </div>

          <!-- Actions -->
          <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-700/50">
            <NeonButton
              type="button"
              @click="router.push('/maintenance/equipment')"
              size="md"
              variant="cancel"
              class="flex-1 sm:flex-none"
            >
              Cancelar
            </NeonButton>
            <NeonButton
              type="submit"
              :disabled="isSubmitting"
              :loading="isSubmitting"
              size="md"
              variant="confirm"
              class="flex-1"
            >
              <svg v-if="!isSubmitting" class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span v-if="isSubmitting">Salvando...</span>
              <span v-else>{{ isEditing ? 'Atualizar Equipamento' : 'Cadastrar Equipamento' }}</span>
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
import { useToast } from '../../composables/useToast.js';
import api from '../../utils/api.js';

export default {
  name: 'MaintenanceEquipmentFormPage',
  components: {
    AuthenticatedLayout,
    NeonButton
  },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const { success, error } = useToast();
    const isEditing = computed(() => !!route.params.id);
    
    const form = ref({
      name: '',
      maintenance_area_id: '',
      description: '',
      location_details: '',
      qr_code_access_level: 'internal_users'
    });

    const maintenanceAreas = ref([]);
    const isSubmitting = ref(false);
    const loading = ref(false);

    const pageTitle = computed(() => {
      return isEditing.value ? 'Editar Equipamento' : 'Novo Equipamento';
    });

    const pageDescription = computed(() => {
      return isEditing.value 
        ? 'Atualize as informações do equipamento' 
        : 'Cadastre um novo equipamento para controle de manutenção';
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

    const inputClasses = 'w-full px-4 py-3 bg-gray-800/50 border border-gray-600/50 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500/50 backdrop-blur-sm transition-all duration-200';
    
    const selectClasses = 'w-full px-4 py-3 bg-gray-800/50 border border-gray-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500/50 backdrop-blur-sm transition-all duration-200';

    const loadMaintenanceAreas = async () => {
      try {
        const response = await api.get('/maintenance/areas?per_page=1000');
        maintenanceAreas.value = response.data.data || response.data;
      } catch (err) {
        console.error('Erro ao carregar áreas:', err);
        error('Erro ao carregar as áreas de manutenção. Tente recarregar a página.');
      }
    };

    const loadData = async () => {
      if (!isEditing.value) return;

      loading.value = true;
      try {
        const response = await api.get(`/maintenance/equipments/${route.params.id}`);
        const equipment = response.data;
        form.value = {
          name: equipment.name || '',
          maintenance_area_id: equipment.maintenance_area_id || '',
          description: equipment.description || '',
          location_details: equipment.location_details || '',
          qr_code_access_level: equipment.qr_code_access_level || 'internal_users'
        };
      } catch (err) {
        console.error('Erro ao carregar equipamento:', err);
        error('Erro ao carregar os dados do equipamento. Verifique se o equipamento existe.');
      } finally {
        loading.value = false;
      }
    };

    const handleSubmit = async () => {
      // Validação básica no frontend
      if (!form.value.name.trim()) {
        error('O nome do equipamento é obrigatório.');
        return;
      }
      
      if (!form.value.maintenance_area_id) {
        error('A área de manutenção é obrigatória.');
        return;
      }

      isSubmitting.value = true;
      
      try {
        if (isEditing.value) {
          await api.put(`/maintenance/equipments/${route.params.id}`, form.value);
          success('Equipamento atualizado com sucesso!');
        } else {
          await api.post('/maintenance/equipments', form.value);
          success('Equipamento cadastrado com sucesso!');
        }
        
        // Aguardar um pouco para mostrar a mensagem de sucesso antes de redirecionar
        setTimeout(() => {
          router.push('/maintenance/equipment');
        }, 1500);
        
      } catch (err) {
        console.error('Erro ao salvar equipamento:', err);
        
        if (err.response && err.response.status === 400) {
          // Erro de validação do backend
          const errors = err.response.data;
          if (typeof errors === 'object') {
            const firstError = Object.values(errors)[0];
            error(Array.isArray(firstError) ? firstError[0] : firstError);
          } else {
            error(errors.message || 'Dados inválidos. Verifique os campos preenchidos.');
          }
        } else if (err.response && err.response.status === 403) {
          error('Você não tem permissão para realizar esta ação.');
        } else if (err.response && err.response.status === 404) {
          error('Equipamento não encontrado.');
        } else {
          error('Erro interno do servidor. Tente novamente mais tarde.');
        }
      } finally {
        isSubmitting.value = false;
      }
    };

    onMounted(() => {
      loadMaintenanceAreas();
      loadData();
    });

    return {
      router,
      form,
      maintenanceAreas,
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
      selectClasses,
      handleSubmit
    };
  }
};
</script> 