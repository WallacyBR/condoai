<template>
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6">{{ isEditing ? 'Editar' : 'Nova' }} Ocorrência</h1>
    <form @submit.prevent="handleSubmit" class="space-y-6 bg-white shadow-md rounded-lg p-6">
      <div>
        <label for="title" class="block text-sm font-medium text-gray-700">Título</label>
        <input type="text" id="title" v-model="form.title" required
               class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
               :class="{'border-red-500': errors.title}">
        <p v-if="errors.title" class="mt-1 text-xs text-red-500">{{ errors.title.join(', ') }}</p>
      </div>

      <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
        <textarea id="description" v-model="form.description" rows="4" required
                  class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  :class="{'border-red-500': errors.description}"></textarea>
        <p v-if="errors.description" class="mt-1 text-xs text-red-500">{{ errors.description.join(', ') }}</p>
      </div>

      <div>
        <label for="priority" class="block text-sm font-medium text-gray-700">Prioridade</label>
        <select id="priority" v-model="form.priority" required
                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :class="{'border-red-500': errors.priority}">
          <option disabled value="">Selecione a prioridade</option>
          <option v-for="(label, value) in priorityOptions" :key="value" :value="value">{{ label }}</option>
        </select>
        <p v-if="errors.priority" class="mt-1 text-xs text-red-500">{{ errors.priority.join(', ') }}</p>
      </div>

      <div v-if="isEditing">
        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
        <select id="status" v-model="form.status" required
                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :class="{'border-red-500': errors.status}">
          <option v-for="(label, value) in statusOptions" :key="value" :value="value">{{ label }}</option>
        </select>
        <p v-if="errors.status" class="mt-1 text-xs text-red-500">{{ errors.status.join(', ') }}</p>
      </div>

      <!-- Upload de Fotos -->
      <div v-if="!isEditing" class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">Fotos (opcional)</label>
        <FileUpload
          v-model="form.photos"
          :multiple="true"
          accept="image/*"
          :maxSize="5"
        />
        <p v-if="errors.photos" class="mt-1 text-xs text-red-500">{{ errors.photos.join(', ') }}</p>
        <!-- A pré-visualização e remoção de fotos agora são tratadas dentro do FileUpload.vue -->
        <!-- A entrada de descrição individual por foto no upload inicial foi removida para simplificação -->
      </div>

      <div class="flex justify-end space-x-3 pt-4">
        <router-link :to="{ name: 'OccurrenceIndex' }"
                     class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          Cancelar
        </router-link>
        <button type="submit" :disabled="submitting"
                class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
          {{ submitting ? 'Salvando...' : (isEditing ? 'Atualizar Ocorrência' : 'Criar Ocorrência') }}
        </button>
      </div>
      <p v-if="submitError" class="text-sm text-red-600 mt-2">{{ submitError }}</p>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from '../../utils/api';
import { useToast } from '../../composables/useToast';
import FileUpload from '../FileUpload.vue'; // Importar o componente FileUpload

const router = useRouter();
const route = useRoute();
const toast = useToast();

const isEditing = computed(() => !!route.params.id);
const occurrenceId = computed(() => route.params.id);

const form = reactive({
  title: '',
  description: '',
  priority: '',
  status: 'open', // Default para criação, será atualizado se editando
  photos: [], // Array de File objects // Este será agora o v-model para FileUpload
  // photo_descriptions: [], // Removido conforme decisão de simplificação
});

// photoPreviews será gerenciado internamente pelo FileUpload ou não será mais necessário da mesma forma
// const photoPreviews = ref([]);

const errors = ref({});
const submitError = ref('');
const submitting = ref(false);

const priorityOptions = {
  low: 'Baixa',
  medium: 'Média',
  high: 'Alta',
  urgent: 'Urgente',
};

const statusOptions = {
  open: 'Aberta',
  in_progress: 'Em Progresso',
  resolved: 'Resolvida',
  closed: 'Fechada',
  reopened: 'Reaberta',
};

const fetchOccurrence = async () => {
  if (!isEditing.value) return;
  try {
    const response = await api.get(`/occurrences/${occurrenceId.value}`);
    const data = response.data;
    form.title = data.title;
    form.description = data.description;
    form.priority = data.priority;
    form.status = data.status;
    // Fotos são gerenciadas separadamente em edição por enquanto
  } catch (err) {
    console.error("Erro ao buscar ocorrência:", err);
    submitError.value = "Não foi possível carregar os dados da ocorrência para edição.";
    toast.error("Erro ao carregar dados da ocorrência.");
  }
};

// handleFileUpload e removePhoto não são mais necessários aqui,
// pois FileUpload.vue gerencia seus próprios arquivos internamente via v-model.

const handleSubmit = async () => {
  submitting.value = true;
  errors.value = {};
  submitError.value = '';

  const formData = new FormData();
  formData.append('title', form.title);
  formData.append('description', form.description);
  formData.append('priority', form.priority);

  if (isEditing.value) {
    formData.append('status', form.status);
    formData.append('_method', 'PUT');
    // Nota: A lógica de upload de fotos para edição não está neste formulário.
    // As fotos são gerenciadas na OccurrenceView.vue ou precisarão ser adicionadas aqui se o escopo mudar.
  } else {
    // Apenas adicionar fotos se não estiver editando e houver fotos
    if (form.photos.length > 0) {
      form.photos.forEach((file, index) => {
        formData.append(`photos[${index}]`, file);
        // Descrições individuais no upload inicial foram removidas para simplificação.
        // Se necessário, o FileUpload.vue precisaria ser estendido ou as descrições gerenciadas de outra forma.
      });
    }
  }

  try {
    let response;
    if (isEditing.value) {
      response = await api.post(`/occurrences/${occurrenceId.value}`, formData, {
         headers: { 'Content-Type': 'multipart/form-data' } // Necessário para _method PUT com FormData
      });
      toast.success('Ocorrência atualizada com sucesso!');
    } else {
      response = await api.post('/occurrences', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      toast.success('Ocorrência criada com sucesso!');
    }
    router.push({ name: 'OccurrenceShow', params: { id: response.data.id } });
  } catch (err) {
    console.error("Erro ao salvar ocorrência:", err);
    if (err.response && err.response.status === 422) {
      errors.value = err.response.data.errors;
      submitError.value = "Por favor, corrija os erros no formulário.";
    } else {
      submitError.value = err.response?.data?.message || 'Ocorreu um erro ao salvar a ocorrência.';
    }
    toast.error(submitError.value);
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  if (isEditing.value) {
    fetchOccurrence();
  } else {
    // Define um valor padrão para prioridade ao criar
    form.priority = 'medium';
  }
});

</script>

<style scoped>
/* Adicionar estilos específicos se necessário */
</style>
