<template>
  <AuthenticatedLayout :page-title="pageTitle">
    <div class="space-y-6">
      <!-- Header Card - OBRIGATÓRIO conforme DESIGN_MANUAL.md -->
      <div :class="headerCardClasses">
        <div class="flex items-center space-x-4">
          <div :class="headerIconClasses">
            <svg class="h-8 w-8" :class="headerIconColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="isEditing" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4M8 7a5 5 0 105 5v1H8V7z" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-white">{{ pageTitle }}</h1>
            <p :class="headerDescriptionColor">{{ pageDescription }}</p>
          </div>
        </div>
      </div>

      <!-- Form Card -->
      <div class="bg-gradient-to-br from-gray-900/90 via-gray-800/90 to-gray-900/90 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Título e Equipamento -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Título *</label>
              <input v-model="form.title" type="text" id="title" required :class="inputClasses" placeholder="Manutenção Preventiva..." />
              <p v-if="errors.title" class="text-sm text-red-400 mt-1">{{ errors.title[0] }}</p>
            </div>
            <div>
              <label for="maintenance_equipment_id" class="block text-sm font-medium text-gray-300 mb-2">Equipamento *</label>
              <select v-model="form.maintenance_equipment_id" id="maintenance_equipment_id" required :class="selectClasses">
                <option value="">Selecione o equipamento</option>
                <option v-for="equipment in availableEquipments" :key="equipment.id" :value="equipment.id">
                  {{ equipment.name }}
                </option>
              </select>
              <p v-if="errors.maintenance_equipment_id" class="text-sm text-red-400 mt-1">{{ errors.maintenance_equipment_id[0] }}</p>
            </div>
          </div>

          <!-- Tipo e Data de Início -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="type" class="block text-sm font-medium text-gray-300 mb-2">Tipo *</label>
              <select v-model="form.type" id="type" required :class="selectClasses">
                <option value="">Selecione o tipo</option>
                <option value="daily">Diário</option>
                <option value="weekly">Semanal</option>
                <option value="biweekly">Quinzenal</option>
                <option value="monthly">Mensal</option>
                <option value="semiannual">Semestral</option>
                <option value="annual">Anual</option>
                <option value="specific_days">Dias Específicos</option>
              </select>
              <p v-if="errors.type" class="text-sm text-red-400 mt-1">{{ errors.type[0] }}</p>
            </div>
            <div>
              <label for="start_date" class="block text-sm font-medium text-gray-300 mb-2">Data de Início *</label>
              <input v-model="form.start_date" type="date" id="start_date" required :class="inputClasses" />
              <p v-if="errors.start_date" class="text-sm text-red-400 mt-1">{{ errors.start_date[0] }}</p>
            </div>
          </div>

          <!-- Data de Término e Horário -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="end_date" class="block text-sm font-medium text-gray-300 mb-2">Data de Término (Opcional)</label>
              <input v-model="form.end_date" type="date" id="end_date" :class="inputClasses" />
              <p v-if="errors.end_date" class="text-sm text-red-400 mt-1">{{ errors.end_date[0] }}</p>
            </div>
            <div>
              <label for="due_time" class="block text-sm font-medium text-gray-300 mb-2">Horário Previsto (Opcional)</label>
              <input v-model="form.due_time" type="time" id="due_time" :class="inputClasses" />
              <p v-if="errors.due_time" class="text-sm text-red-400 mt-1">{{ errors.due_time[0] }}</p>
            </div>
          </div>

          <!-- Campos condicionais baseados no tipo -->
          <div v-if="form.type === 'weekly' || form.type === 'specific_days'" class="space-y-3">
            <label class="block text-sm font-medium text-gray-300">Dias da Semana *</label>
            <div class="grid grid-cols-7 gap-2">
              <label v-for="(day, index) in daysOfWeek" :key="index" class="flex items-center space-x-2 text-sm text-gray-300">
                <input 
                  type="checkbox" 
                  :value="index" 
                  v-model="form.specific_days_of_week"
                  class="rounded border-gray-600 bg-gray-700 text-cyan-500 focus:ring-cyan-500 focus:ring-offset-gray-800"
                />
                <span>{{ day }}</span>
              </label>
            </div>
            <p v-if="errors.specific_days_of_week" class="text-sm text-red-400 mt-1">{{ errors.specific_days_of_week[0] }}</p>
          </div>

          <div v-if="form.type === 'monthly'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="day_of_month" class="block text-sm font-medium text-gray-300 mb-2">Dia do Mês *</label>
              <input v-model.number="form.day_of_month" type="number" id="day_of_month" min="1" max="31" required :class="inputClasses" placeholder="Ex: 15" />
              <p v-if="errors.day_of_month" class="text-sm text-red-400 mt-1">{{ errors.day_of_month[0] }}</p>
            </div>
          </div>

          <div v-if="form.type === 'semiannual' || form.type === 'annual'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="month_of_year" class="block text-sm font-medium text-gray-300 mb-2">Mês do Ano *</label>
              <select v-model.number="form.month_of_year" id="month_of_year" required :class="selectClasses">
                <option value="">Selecione o mês</option>
                <option v-for="(month, index) in months" :key="index + 1" :value="index + 1">{{ month }}</option>
              </select>
              <p v-if="errors.month_of_year" class="text-sm text-red-400 mt-1">{{ errors.month_of_year[0] }}</p>
            </div>
            <div v-if="form.type === 'annual'">
              <label for="day_of_month_annual" class="block text-sm font-medium text-gray-300 mb-2">Dia do Mês *</label>
              <input v-model.number="form.day_of_month" type="number" id="day_of_month_annual" min="1" max="31" required :class="inputClasses" placeholder="Ex: 15" />
              <p v-if="errors.day_of_month" class="text-sm text-red-400 mt-1">{{ errors.day_of_month[0] }}</p>
            </div>
          </div>

          <!-- Descrição -->
          <div>
            <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Descrição</label>
            <textarea v-model="form.description" id="description" rows="4" :class="inputClasses" placeholder="Descreva os serviços a serem realizados..."></textarea>
            <p v-if="errors.description" class="text-sm text-red-400 mt-1">{{ errors.description[0] }}</p>
          </div>

          <!-- Usuários Responsáveis -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Usuários Responsáveis (Opcional)</label>
            <div class="max-h-40 overflow-y-auto bg-gray-800/50 border border-gray-600/50 rounded-xl p-3 space-y-2">
              <label v-for="user in availableUsers" :key="user.id" class="flex items-center space-x-2 text-sm text-gray-300">
                <input 
                  type="checkbox" 
                  :value="user.id" 
                  v-model="form.responsible_user_ids"
                  class="rounded border-gray-600 bg-gray-700 text-cyan-500 focus:ring-cyan-500 focus:ring-offset-gray-800"
                />
                <span>{{ user.name }}</span>
              </label>
            </div>
            <p v-if="errors.responsible_user_ids" class="text-sm text-red-400 mt-1">{{ errors.responsible_user_ids[0] }}</p>
          </div>

          <!-- Recorrente -->
          <div class="flex items-center space-x-3">
            <input 
              type="checkbox" 
              id="is_recurring" 
              v-model="form.is_recurring"
              class="rounded border-gray-600 bg-gray-700 text-cyan-500 focus:ring-cyan-500 focus:ring-offset-gray-800"
            />
            <label for="is_recurring" class="text-sm font-medium text-gray-300">É um agendamento recorrente</label>
          </div>

          <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-700/50">
            <NeonButton type="button" @click="router.push('/maintenance/schedules')" size="md" variant="secondary">Cancelar</NeonButton>
            <NeonButton type="submit" size="md" :loading="isSubmitting">{{ isEditing ? 'Atualizar' : 'Criar' }}</NeonButton>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import AuthenticatedLayout from '../../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../../components/NeonButton.vue';
import { useToast } from '../../composables/useToast.js';
import api from '../../utils/api.js';

export default {
  name: 'MaintenanceScheduleFormPage',
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
      title: '',
      maintenance_equipment_id: '',
      type: '',
      start_date: new Date().toISOString().split('T')[0],
      end_date: '',
      due_time: '',
      description: '',
      is_recurring: false,
      specific_days_of_week: [],
      day_of_month: null,
      month_of_year: null,
      responsible_user_ids: []
    });

    const availableEquipments = ref([]);
    const availableUsers = ref([]);
    const isSubmitting = ref(false);
    const errors = ref({});

    const daysOfWeek = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
    const months = [
      'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
      'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ];

    const pageTitle = computed(() => {
      return isEditing.value ? 'Editar Agendamento' : 'Novo Agendamento';
    });

    const pageDescription = computed(() => {
      return isEditing.value ? 'Modifique os dados do agendamento de manutenção' : 'Crie um novo agendamento de manutenção';
    });

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

    // Buscar dados iniciais
    const fetchEquipments = async () => {
      try {
        const response = await api.get('/maintenance/equipments?per_page=1000');
        availableEquipments.value = response.data.data || response.data;
      } catch (err) {
        console.error('Erro ao buscar equipamentos:', err);
        error('Erro ao carregar equipamentos');
      }
    };

    const fetchUsers = async () => {
      try {
        const response = await api.get('/users?per_page=1000');
        availableUsers.value = response.data.data || response.data;
      } catch (err) {
        console.error('Erro ao buscar usuários:', err);
        error('Erro ao carregar usuários');
      }
    };

    const fetchSchedule = async () => {
      if (!isEditing.value) return;

      try {
        const response = await api.get(`/maintenance/schedules/${route.params.id}`);
        const schedule = response.data;
        
        form.value = {
          title: schedule.title || '',
          maintenance_equipment_id: schedule.maintenance_equipment_id || '',
          type: schedule.type || '',
          start_date: schedule.start_date || '',
          end_date: schedule.end_date || '',
          due_time: schedule.due_time || '',
          description: schedule.description || '',
          is_recurring: schedule.is_recurring || false,
          specific_days_of_week: schedule.specific_days_of_week || [],
          day_of_month: schedule.day_of_month || null,
          month_of_year: schedule.month_of_year || null,
          responsible_user_ids: schedule.responsible_users ? schedule.responsible_users.map(u => u.id) : []
        };
      } catch (err) {
        console.error('Erro ao buscar agendamento:', err);
        error('Erro ao carregar agendamento');
        router.push('/maintenance/schedules');
      }
    };

    const handleSubmit = async () => {
      isSubmitting.value = true;
      errors.value = {};

      try {
        const payload = { ...form.value };
        
        // Converter números para inteiros
        if (payload.day_of_month) payload.day_of_month = parseInt(payload.day_of_month);
        if (payload.month_of_year) payload.month_of_year = parseInt(payload.month_of_year);
        if (payload.maintenance_equipment_id) payload.maintenance_equipment_id = parseInt(payload.maintenance_equipment_id);
        
        // Remover campos vazios
        Object.keys(payload).forEach(key => {
          if (
            payload[key] === '' ||
            payload[key] === null ||
            (Array.isArray(payload[key]) && payload[key].length === 0)
          ) {
            delete payload[key];
          }
        });

        if (isEditing.value) {
          await api.put(`/maintenance/schedules/${route.params.id}`, payload);
          success('Agendamento atualizado com sucesso!');
        } else {
          await api.post('/maintenance/schedules', payload);
          success('Agendamento criado com sucesso!');
        }

        router.push('/maintenance/schedules');
      } catch (err) {
        console.error('Erro ao salvar agendamento:', err);
        
        if (err.response?.data?.errors) {
          errors.value = err.response.data.errors;
        } else if (err.response?.data?.message) {
          error(err.response.data.message);
        } else {
          error('Erro ao salvar agendamento. Tente novamente.');
        }
      } finally {
        isSubmitting.value = false;
      }
    };

    onMounted(() => {
      fetchEquipments();
      fetchUsers();
      fetchSchedule();
    });

    return {
      router,
      form,
      availableEquipments,
      availableUsers,
      isSubmitting,
      errors,
      isEditing,
      pageTitle,
      pageDescription,
      headerCardClasses,
      headerIconClasses,
      headerIconColor,
      headerDescriptionColor,
      inputClasses,
      selectClasses,
      daysOfWeek,
      months,
      handleSubmit
    };
  }
};
</script>
