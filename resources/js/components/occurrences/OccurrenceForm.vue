<template>
  <div class="space-y-6">
    <TopCard
      :title="pageTitle"
      subtitle="Preencha os dados do chamado do condomínio"
      :icon="OccurrenceIcon"
    >
      <template #actions>
        <NeonButton @click="goBack" size="sm" variant="secondary">
          Voltar
        </NeonButton>
      </template>
    </TopCard>

    <form
      @submit.prevent="handleSubmit"
      class="space-y-6 bg-white dark:bg-slate-900 shadow-sm rounded-xl border border-slate-200 dark:border-slate-800 p-6"
    >
      <div>
        <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
          Título
        </label>
        <input
          id="title"
          v-model="form.title"
          type="text"
          required
          class="mt-1.5 block w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
          :class="{ '!border-rose-500': errors.title }"
        />
        <p v-if="errors.title" class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ errors.title.join(', ') }}</p>
      </div>

      <div>
        <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
          Descrição
        </label>
        <textarea
          id="description"
          v-model="form.description"
          rows="5"
          required
          class="mt-1.5 block w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
          :class="{ '!border-rose-500': errors.description }"
        ></textarea>
        <p v-if="errors.description" class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ errors.description.join(', ') }}</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="priority" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
            Prioridade
          </label>
          <select
            id="priority"
            v-model="form.priority"
            required
            class="mt-1.5 block w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
            :class="{ '!border-rose-500': errors.priority }"
          >
            <option disabled value="">Selecione a prioridade</option>
            <option v-for="(label, value) in priorityOptions" :key="value" :value="value">{{ label }}</option>
          </select>
          <p v-if="errors.priority" class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ errors.priority.join(', ') }}</p>
        </div>

        <div v-if="isEditing">
          <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
            Status
          </label>
          <select
            id="status"
            v-model="form.status"
            required
            class="mt-1.5 block w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
            :class="{ '!border-rose-500': errors.status }"
          >
            <option v-for="(label, value) in statusOptions" :key="value" :value="value">{{ label }}</option>
          </select>
          <p v-if="errors.status" class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ errors.status.join(', ') }}</p>
        </div>
      </div>

      <div v-if="!isEditing" class="space-y-2">
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">
          Fotos (opcional)
        </label>
        <FileUpload
          v-model="form.photos"
          :multiple="true"
          accept="image/*"
          :maxSize="5"
        />
        <p v-if="errors.photos" class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ errors.photos.join(', ') }}</p>
      </div>

      <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
        <NeonButton @click="goBack" size="md" variant="secondary">
          Cancelar
        </NeonButton>
        <NeonButton type="submit" :disabled="submitting" :loading="submitting" size="md" variant="primary">
          {{ submitting ? 'Salvando...' : (isEditing ? 'Atualizar Ocorrência' : 'Criar Ocorrência') }}
        </NeonButton>
      </div>
      <p v-if="submitError" class="text-xs text-rose-600 dark:text-rose-400 mt-2">{{ submitError }}</p>
    </form>
  </div>
</template>

<script setup>
import { h, ref, reactive, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from '../../utils/api';
import { useToast } from '../../composables/useToast';
import FileUpload from '../FileUpload.vue';
import TopCard from '../ui/TopCard.vue';
import NeonButton from '../NeonButton.vue';

const router = useRouter();
const route = useRoute();
const toast = useToast();

const isEditing = computed(() => !!route.params.id);
const occurrenceId = computed(() => route.params.id);
const pageTitle = computed(() => `${isEditing.value ? 'Editar' : 'Nova'} Ocorrência`);

const OccurrenceIcon = {
  render() {
    return h('svg', {
      ...this.$attrs,
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': 2,
        d: 'M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z',
      }),
    ]);
  },
};

const form = reactive({
  title: '',
  description: '',
  priority: '',
  status: 'open',
  photos: [],
});

const errors = ref({});
const submitting = ref(false);
const submitError = ref('');

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

const fetchOccurrence = async (id) => {
  try {
    const response = await api.get(`/occurrences/${id}`);
    form.title = response.data.title;
    form.description = response.data.description;
    form.priority = response.data.priority;
    form.status = response.data.status;
  } catch (err) {
    toast.error('Erro ao carregar os dados da ocorrência.');
    console.error(err);
    router.push({ name: 'OccurrenceIndex' });
  }
};

const handleSubmit = async () => {
  submitting.value = true;
  errors.value = {};
  submitError.value = '';

  try {
    if (isEditing.value) {
      await api.put(`/occurrences/${occurrenceId.value}`, {
        title: form.title,
        description: form.description,
        priority: form.priority,
        status: form.status,
      });
      toast.success('Ocorrência atualizada com sucesso!');
    } else {
      const formData = new FormData();
      formData.append('title', form.title);
      formData.append('description', form.description);
      formData.append('priority', form.priority);
      formData.append('status', form.status);

      if (form.photos && form.photos.length > 0) {
        form.photos.forEach((photo) => {
          formData.append('photos[]', photo);
        });
      }

      await api.post('/occurrences', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      toast.success('Ocorrência criada com sucesso!');
    }
    router.push({ name: 'OccurrenceIndex' });
  } catch (err) {
    if (err.response && err.response.data && err.response.data.errors) {
      errors.value = err.response.data.errors;
    } else {
      submitError.value = 'Ocorreu um erro ao salvar a ocorrência.';
    }
    toast.error('Erro ao salvar a ocorrência.');
    console.error(err);
  } finally {
    submitting.value = false;
  }
};

const goBack = () => {
  router.push({ name: 'OccurrenceIndex' });
};

onMounted(() => {
  if (isEditing.value) {
    fetchOccurrence(occurrenceId.value);
  }
});
</script>
