<template>
  <Modal :show="show" @close="$emit('close')">
    <template #title>{{ isEditMode ? 'Editar Equipamento' : 'Novo Equipamento de Manutenção' }}</template>

    <form @submit.prevent="submitForm" class="p-6 space-y-6">
      <div>
        <label for="name" class="block text-sm font-medium text-gray-300">Nome</label>
        <input type="text" id="name" v-model="form.name" required
               class="mt-1 block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm">
        <p v-if="errors.name" class="text-sm text-red-400 mt-1">{{ errors.name.join(', ') }}</p>
      </div>

      <div>
        <label for="maintenance_area_id" class="block text-sm font-medium text-gray-300">Área de Manutenção</label>
        <select id="maintenance_area_id" v-model="form.maintenance_area_id" required
                class="mt-1 block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm">
          <option disabled value="">Selecione uma área</option>
          <option v-for="area in availableAreas" :key="area.id" :value="area.id">
            {{ area.name }}
          </option>
        </select>
        <p v-if="errors.maintenance_area_id" class="text-sm text-red-400 mt-1">{{ errors.maintenance_area_id.join(', ') }}</p>
      </div>

      <div>
        <label for="description" class="block text-sm font-medium text-gray-300">Descrição</label>
        <textarea id="description" v-model="form.description" rows="3"
                  class="mt-1 block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm"></textarea>
        <p v-if="errors.description" class="text-sm text-red-400 mt-1">{{ errors.description.join(', ') }}</p>
      </div>

      <div>
        <label for="location_details" class="block text-sm font-medium text-gray-300">Detalhes da Localização</label>
        <input type="text" id="location_details" v-model="form.location_details"
               class="mt-1 block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm">
        <p v-if="errors.location_details" class="text-sm text-red-400 mt-1">{{ errors.location_details.join(', ') }}</p>
      </div>

      <div>
        <label for="qr_code_access_level" class="block text-sm font-medium text-gray-300">Nível de Acesso QR Code</label>
        <select id="qr_code_access_level" v-model="form.qr_code_access_level" required
                class="mt-1 block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm">
          <option value="public">Público</option>
          <option value="internal_users">Usuários Internos</option>
          <option value="specific_users">Usuários Específicos</option>
        </select>
        <p v-if="errors.qr_code_access_level" class="text-sm text-red-400 mt-1">{{ errors.qr_code_access_level.join(', ') }}</p>
      </div>

      <div class="pt-6 flex justify-end space-x-3">
        <button type="button" @click="$emit('close')"
                class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-600 hover:bg-gray-500 rounded-md shadow-sm transition-colors">
          Cancelar
        </button>
        <button type="submit" :disabled="isLoading"
                class="px-4 py-2 text-sm font-medium text-white bg-cyan-600 hover:bg-cyan-700 rounded-md shadow-sm transition-colors disabled:opacity-50">
          {{ isLoading ? 'Salvando...' : (isEditMode ? 'Atualizar' : 'Criar Equipamento') }}
        </button>
      </div>
    </form>
  </Modal>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import Modal from '@/components/Modal.vue';
import api from '@/utils/api';

const props = defineProps({
  show: Boolean,
  equipmentData: Object, // Dados para edição
});

const emit = defineEmits(['close', 'saved']);

const isEditMode = computed(() => !!props.equipmentData?.id);
const isLoading = ref(false);
const errors = ref({});

const form = ref({
  name: '',
  maintenance_area_id: '',
  description: '',
  location_details: '',
  qr_code_access_level: 'internal_users',
});

const availableAreas = ref([]);

// Popula o formulário quando equipmentData muda (para edição)
watch(() => props.equipmentData, (newData) => {
  if (newData && newData.id) {
    form.value = { ...newData };
  } else {
    resetForm();
  }
}, { immediate: true, deep: true });

function resetForm() {
  form.value = {
    name: '',
    maintenance_area_id: '',
    description: '',
    location_details: '',
    qr_code_access_level: 'internal_users',
  };
  errors.value = {};
}

async function fetchAreas() {
  try {
    const response = await api.get('/maintenance/areas?per_page=1000');
    availableAreas.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erro ao buscar áreas:', error);
  }
}

onMounted(() => {
  fetchAreas();
});

async function submitForm() {
  isLoading.value = true;
  errors.value = {};
  try {
    let response;
    if (isEditMode.value) {
      response = await api.put(`/maintenance/equipments/${props.equipmentData.id}`, form.value);
    } else {
      response = await api.post('/maintenance/equipments', form.value);
    }
    emit('saved', response.data);
    emit('close');
  } catch (error) {
    console.error('Erro ao salvar equipamento:', error);
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors || {};
    } else {
      errors.value = { general: ['Ocorreu um erro ao salvar. Tente novamente.'] };
    }
  } finally {
    isLoading.value = false;
  }
}

watch(() => props.show, (newVal) => {
  if (!newVal && !isEditMode.value) {
    resetForm();
  } else if (newVal && isEditMode.value && props.equipmentData) {
     Object.assign(form.value, props.equipmentData);
  } else if (newVal && !isEditMode.value) {
      resetForm();
  }
});
</script>
