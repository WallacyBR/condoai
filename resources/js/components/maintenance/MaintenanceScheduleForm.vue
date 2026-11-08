<template>
  <Modal :show="show" @close="$emit('close')">
    <template #title>{{ isEditMode ? 'Editar Agendamento' : 'Novo Agendamento de Manutenção' }}</template>

    <form @submit.prevent="submitForm" class="p-6 space-y-6">
      <div>
        <label for="title" class="block text-sm font-medium text-gray-300">Título</label>
        <input type="text" id="title" v-model="form.title" required
               class="mt-1 block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm">
        <p v-if="errors.title" class="text-sm text-red-400 mt-1">{{ errors.title.join(', ') }}</p>
      </div>

      <div>
        <label for="maintenance_equipment_id" class="block text-sm font-medium text-gray-300">Equipamento</label>
        <select id="maintenance_equipment_id" v-model="form.maintenance_equipment_id" required
                class="mt-1 block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm">
          <option disabled value="">Selecione um equipamento</option>
          <option v-for="equipment in availableEquipment" :key="equipment.id" :value="equipment.id">
            {{ equipment.name }}
          </option>
        </select>
        <p v-if="errors.maintenance_equipment_id" class="text-sm text-red-400 mt-1">{{ errors.maintenance_equipment_id.join(', ') }}</p>
      </div>

      <div>
        <label for="description" class="block text-sm font-medium text-gray-300">Descrição</label>
        <textarea id="description" v-model="form.description" rows="3"
                  class="mt-1 block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm"></textarea>
        <p v-if="errors.description" class="text-sm text-red-400 mt-1">{{ errors.description.join(', ') }}</p>
      </div>

      <div>
        <label for="type" class="block text-sm font-medium text-gray-300">Tipo de Recorrência</label>
        <select id="type" v-model="form.type" required
                class="mt-1 block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm">
          <option value="daily">Diário</option>
          <option value="weekly">Semanal</option>
          <option value="biweekly">Quinzenal</option>
          <option value="monthly">Mensal</option>
          <option value="semiannual">Semestral</option>
          <option value="annual">Anual</option>
          <option value="specific_days">Dias Específicos na Semana</option>
          <!-- Adicionar opção para 'não recorrente' se necessário, ou controlar via 'is_recurring' -->
        </select>
         <p v-if="errors.type" class="text-sm text-red-400 mt-1">{{ errors.type.join(', ') }}</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label for="start_date" class="block text-sm font-medium text-gray-300">Data de Início</label>
          <input type="date" id="start_date" v-model="form.start_date" required
                 class="mt-1 block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm">
          <p v-if="errors.start_date" class="text-sm text-red-400 mt-1">{{ errors.start_date.join(', ') }}</p>
        </div>
        <div>
          <label for="due_time" class="block text-sm font-medium text-gray-300">Hora Prevista</label>
          <input type="time" id="due_time" v-model="form.due_time"
                 class="mt-1 block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm">
          <p v-if="errors.due_time" class="text-sm text-red-400 mt-1">{{ errors.due_time.join(', ') }}</p>
        </div>
      </div>

       <div>
        <label for="responsible_user_ids" class="block text-sm font-medium text-gray-300">Responsáveis</label>
        <!-- Idealmente, usar um componente MultiSelectField robusto aqui -->
        <select multiple id="responsible_user_ids" v-model="form.responsible_user_ids"
                class="mt-1 block w-full h-32 bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm">
          <option v-for="user in availableUsers" :key="user.id" :value="user.id">
            {{ user.name }} ({{ user.profile?.name || 'N/A' }})
          </option>
        </select>
        <p class="text-xs text-gray-400 mt-1">Segure Ctrl/Cmd para selecionar múltiplos.</p>
        <p v-if="errors.responsible_user_ids" class="text-sm text-red-400 mt-1">{{ errors.responsible_user_ids.join(', ') }}</p>
      </div>

      <!-- Outros campos como end_date, specific_days_of_week, etc. podem ser adicionados aqui condicionalmente baseados no 'type' -->
       <div v-if="form.type === 'weekly' || form.type === 'specific_days' || form.type === 'biweekly'">
        <label class="block text-sm font-medium text-gray-300">Dias da Semana Específicos</label>
        <div class="mt-2 grid grid-cols-3 sm:grid-cols-4 gap-2">
          <label v-for="(day, index) in daysOfWeek" :key="index" class="flex items-center space-x-2 p-2 bg-gray-700 rounded-md hover:bg-gray-600">
            <input type="checkbox" :value="index" v-model="form.specific_days_of_week"
                   class="rounded text-cyan-600 focus:ring-cyan-500 bg-gray-800 border-gray-600">
            <span class="text-white">{{ day }}</span>
          </label>
        </div>
        <p v-if="errors.specific_days_of_week" class="text-sm text-red-400 mt-1">{{ errors.specific_days_of_week.join(', ') }}</p>
      </div>


      <div class="flex items-center">
        <input id="is_recurring" type="checkbox" v-model="form.is_recurring"
               class="h-4 w-4 text-cyan-600 focus:ring-cyan-500 border-gray-600 bg-gray-700 rounded">
        <label for="is_recurring" class="ml-2 block text-sm text-gray-300">Agendamento Recorrente</label>
      </div>
       <p v-if="errors.is_recurring" class="text-sm text-red-400 mt-1">{{ errors.is_recurring.join(', ') }}</p>

      <div class="pt-6 flex justify-end space-x-3">
        <button type="button" @click="$emit('close')"
                class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-600 hover:bg-gray-500 rounded-md shadow-sm transition-colors">
          Cancelar
        </button>
        <button type="submit" :disabled="isLoading"
                class="px-4 py-2 text-sm font-medium text-white bg-cyan-600 hover:bg-cyan-700 rounded-md shadow-sm transition-colors disabled:opacity-50">
          {{ isLoading ? 'Salvando...' : (isEditMode ? 'Atualizar' : 'Criar Agendamento') }}
        </button>
      </div>
    </form>
  </Modal>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import Modal from '@/components/Modal.vue'; // Ajuste o caminho se necessário
import api from '@/utils/api'; // Assume que você tem um helper de API

const props = defineProps({
  show: Boolean,
  scheduleData: Object, // Dados para edição
});

const emit = defineEmits(['close', 'saved']);

const isEditMode = computed(() => !!props.scheduleData?.id);
const isLoading = ref(false);
const errors = ref({});

const form = ref({
  title: '',
  maintenance_equipment_id: '',
  description: '',
  type: 'weekly',
  start_date: new Date().toISOString().split('T')[0],
  end_date: '',
  due_time: '',
  is_recurring: true,
  specific_days_of_week: [], // 0 para Domingo, 6 para Sábado
  day_of_month: null,
  month_of_year: null,
  responsible_user_ids: [],
});

const daysOfWeek = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
const availableEquipment = ref([]);
const availableUsers = ref([]); // Zeladores, etc.

// Popula o formulário quando scheduleData muda (para edição)
watch(() => props.scheduleData, (newData) => {
  if (newData && newData.id) {
    form.value = {
      ...newData,
      start_date: newData.start_date ? new Date(newData.start_date).toISOString().split('T')[0] : '',
      end_date: newData.end_date ? new Date(newData.end_date).toISOString().split('T')[0] : '',
      due_time: newData.due_time || '', // Formato HH:mm
      responsible_user_ids: newData.responsible_users ? newData.responsible_users.map(u => u.id) : [],
      specific_days_of_week: newData.specific_days_of_week || [],
    };
  } else {
    resetForm();
  }
}, { immediate: true, deep: true });


function resetForm() {
  form.value = {
    title: '',
    maintenance_equipment_id: '',
    description: '',
    type: 'weekly',
    start_date: new Date().toISOString().split('T')[0],
    end_date: '',
    due_time: '',
    is_recurring: true,
    specific_days_of_week: [],
    day_of_month: null,
    month_of_year: null,
    responsible_user_ids: [],
  };
  errors.value = {};
}

async function fetchEquipments() {
  try {
    // TODO: Ajustar endpoint e paginação se necessário
    const response = await api.get('/maintenance/equipments?per_page=1000');
    availableEquipment.value = response.data.data || response.data; // Ajustar conforme a estrutura da API
  } catch (error) {
    console.error('Erro ao buscar equipamentos:', error);
    // Tratar erro (ex: notificação para o usuário)
  }
}

async function fetchUsers() {
  try {
    // TODO: Ajustar endpoint, paginação e filtros (ex: por profile_id para zeladores)
    // Exemplo: /users?profile_id=ID_DO_PERFIL_ZELADOR&per_page=1000
    const response = await api.get('/users?per_page=1000');
    availableUsers.value = response.data.data || response.data; // Ajustar conforme a estrutura da API
  } catch (error) {
    console.error('Erro ao buscar usuários:', error);
    // Tratar erro
  }
}

onMounted(() => {
  fetchEquipments();
  fetchUsers();
  if (props.scheduleData && props.scheduleData.id) {
    // Se em modo de edição, popula o formulário
     Object.assign(form.value, {
        ...props.scheduleData,
        start_date: props.scheduleData.start_date ? new Date(props.scheduleData.start_date).toISOString().split('T')[0] : '',
        end_date: props.scheduleData.end_date ? new Date(props.scheduleData.end_date).toISOString().split('T')[0] : '',
        due_time: props.scheduleData.due_time || '',
        responsible_user_ids: props.scheduleData.responsible_users ? props.scheduleData.responsible_users.map(u => u.id) : [],
        specific_days_of_week: props.scheduleData.specific_days_of_week || [],
     });
  }
});

async function submitForm() {
  isLoading.value = true;
  errors.value = {};
  try {
    let response;
    const payload = { ...form.value };
    // Certifique-se de que os campos numéricos/booleanos estão corretos
    payload.is_recurring = Boolean(payload.is_recurring);
    if (payload.type !== 'weekly' && payload.type !== 'specific_days' && payload.type !== 'biweekly') {
        delete payload.specific_days_of_week;
    }
     if (payload.type !== 'monthly') {
        delete payload.day_of_month;
    }
    // etc para outros campos condicionais

    if (isEditMode.value) {
      response = await api.put(`/maintenance/schedules/${props.scheduleData.id}`, payload);
    } else {
      response = await api.post('/maintenance/schedules', payload);
    }
    emit('saved', response.data);
    emit('close');
  } catch (error) {
    console.error('Erro ao salvar agendamento:', error);
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors || {};
    } else {
      // Tratar outros erros (ex: notificação genérica)
      errors.value = { general: ['Ocorreu um erro ao salvar. Tente novamente.'] };
    }
  } finally {
    isLoading.value = false;
  }
}

// Quando o modal é fechado, reseta o formulário se não estiver em modo de edição
watch(() => props.show, (newVal) => {
  if (!newVal && !isEditMode.value) {
    resetForm();
  } else if (newVal && isEditMode.value && props.scheduleData) {
     // Repopular em caso de reabertura para edição, se necessário
     Object.assign(form.value, {
        ...props.scheduleData,
        start_date: props.scheduleData.start_date ? new Date(props.scheduleData.start_date).toISOString().split('T')[0] : '',
        end_date: props.scheduleData.end_date ? new Date(props.scheduleData.end_date).toISOString().split('T')[0] : '',
        due_time: props.scheduleData.due_time || '',
        responsible_user_ids: props.scheduleData.responsible_users ? props.scheduleData.responsible_users.map(u => u.id) : [],
        specific_days_of_week: props.scheduleData.specific_days_of_week || [],
     });
  } else if (newVal && !isEditMode.value) {
      resetForm(); // Garante que está limpo ao abrir para novo
  }
});

</script>

<style scoped>
/* Estilos adicionais se necessário, mas tentando usar classes Tailwind */
</style>
