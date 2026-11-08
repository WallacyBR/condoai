<template>
  <Modal :show="show" @close="$emit('close')">
    <template #title>{{ isEditMode ? 'Editar Área' : 'Nova Área de Manutenção' }}</template>

    <form @submit.prevent="submitForm" class="p-6 space-y-6">
      <div>
        <label for="name" class="block text-sm font-medium text-gray-300">Nome</label>
        <input type="text" id="name" v-model="form.name" required
               class="mt-1 block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm">
        <p v-if="errors.name" class="text-sm text-red-400 mt-1">{{ errors.name.join(', ') }}</p>
      </div>

      <div>
        <label for="description" class="block text-sm font-medium text-gray-300">Descrição</label>
        <textarea id="description" v-model="form.description" rows="3"
                  class="mt-1 block w-full bg-gray-700 border-gray-600 text-white rounded-md shadow-sm focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm"></textarea>
        <p v-if="errors.description" class="text-sm text-red-400 mt-1">{{ errors.description.join(', ') }}</p>
      </div>

      <div class="pt-6 flex justify-end space-x-3">
        <button type="button" @click="$emit('close')"
                class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-600 hover:bg-gray-500 rounded-md shadow-sm transition-colors">
          Cancelar
        </button>
        <button type="submit" :disabled="isLoading"
                class="px-4 py-2 text-sm font-medium text-white bg-cyan-600 hover:bg-cyan-700 rounded-md shadow-sm transition-colors disabled:opacity-50">
          {{ isLoading ? 'Salvando...' : (isEditMode ? 'Atualizar' : 'Criar Área') }}
        </button>
      </div>
    </form>
  </Modal>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import Modal from '@/components/Modal.vue';
import api from '@/utils/api';

const props = defineProps({
  show: Boolean,
  areaData: Object, // Dados para edição
});

const emit = defineEmits(['close', 'saved']);

const isEditMode = computed(() => !!props.areaData?.id);
const isLoading = ref(false);
const errors = ref({});

const form = ref({
  name: '',
  description: '',
});

// Popula o formulário quando areaData muda (para edição)
watch(() => props.areaData, (newData) => {
  if (newData && newData.id) {
    form.value = { ...newData };
  } else {
    resetForm();
  }
}, { immediate: true, deep: true });

function resetForm() {
  form.value = {
    name: '',
    description: '',
  };
  errors.value = {};
}

async function submitForm() {
  isLoading.value = true;
  errors.value = {};
  try {
    let response;
    if (isEditMode.value) {
      response = await api.put(`/maintenance/areas/${props.areaData.id}`, form.value);
    } else {
      response = await api.post('/maintenance/areas', form.value);
    }
    emit('saved', response.data);
    emit('close');
  } catch (error) {
    console.error('Erro ao salvar área:', error);
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
  } else if (newVal && isEditMode.value && props.areaData) {
     Object.assign(form.value, props.areaData);
  } else if (newVal && !isEditMode.value) {
      resetForm();
  }
});
</script>
