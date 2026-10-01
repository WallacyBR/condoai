<template>
  <AuthenticatedLayout :page-title="pageTitle">
    <HeaderCard
      :title="pageTitle"
      :description="isEditing ? 'Altere os dados da medida protetiva abaixo.' : 'Preencha os dados para registrar uma nova medida protetiva.'"
      icon-path="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
      :icon-color="isEditing ? 'orange' : 'green'"
    />

    <div v-if="loadingInitialData" class="text-center py-12">
      <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-orange-400 mx-auto"></div>
      <p class="text-gray-300 mt-4 text-lg">Carregando dados...</p>
    </div>

    <div v-if="initialError && !loadingInitialData" class="bg-red-700/30 border border-red-500/50 rounded-2xl p-8 text-center mt-6 shadow-xl">
        <h3 class="text-2xl font-semibold text-red-300 mb-3">Erro ao Carregar</h3>
        <p class="text-red-200">{{ initialError }}</p>
        <NeonButton @click="goBack" variant="danger" class="mt-6">
            Voltar à Lista
        </NeonButton>
    </div>

    <form @submit.prevent="submitProtectiveMeasure" v-if="!loadingInitialData && !initialError" class="mt-6 space-y-8">
      <div class="bg-gray-800/50 border border-gray-700/50 rounded-2xl p-6 shadow-lg">
        <h3 class="text-lg font-semibold text-white mb-6">Detalhes da Medida Protetiva</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
          <div>
            <label for="protected_resident_id" class="form-label">Morador Protegido *</label>
            <select id="protected_resident_id" v-model="form.protected_resident_id" required class="form-input">
              <option disabled value="">Selecione o morador</option>
              <option v-for="resident in users" :key="resident.id" :value="resident.id">
                {{ resident.name }} ({{ resident.email }})
              </option>
            </select>
            <p v-if="loadingUsers" class="text-xs text-cyan-400 mt-1">Carregando moradores...</p>
          </div>
          <div>
            <label for="aggressor_visitor_id" class="form-label">Visitante Agressor *</label>
            <select id="aggressor_visitor_id" v-model="form.aggressor_visitor_id" required class="form-input">
              <option disabled value="">Selecione o visitante</option>
              <option v-for="visitor in visitors" :key="visitor.id" :value="visitor.id">
                {{ visitor.name }} ({{ visitor.document }})
              </option>
            </select>
             <p v-if="loadingVisitors" class="text-xs text-cyan-400 mt-1">Carregando visitantes...</p>
          </div>
          <div>
            <label for="process_number" class="form-label">Número do Processo (Opcional)</label>
            <input type="text" id="process_number" v-model="form.process_number" class="form-input"/>
          </div>
           <div>
            <label for="validity_start_date" class="form-label">Data de Início da Validade *</label>
            <input type="date" id="validity_start_date" v-model="form.validity_start_date" required class="form-input"/>
          </div>
          <div>
            <label for="validity_end_date" class="form-label">Data de Fim da Validade (Opcional)</label>
            <input type="date" id="validity_end_date" v-model="form.validity_end_date" class="form-input"/>
          </div>
          <div class="md:col-span-2">
            <label for="measure_description" class="form-label">Descrição da Medida *</label>
            <textarea id="measure_description" v-model="form.measure_description" rows="4" required class="form-input"></textarea>
          </div>
          <div class="md:col-span-2">
            <label class="form-label">Documento Comprobatório (PDF, JPG, PNG - Máx 5MB)</label>
            <FileUpload
                v-model="documentFile"
                accept=".pdf,.jpg,.jpeg,.png"
                :multiple="false"
                :maxSize="5"
                :showCamera="false"
                class="upload-card-style"
            />
            <a
                v-if="isEditing && form.document_url"
                :href="form.document_url"
                target="_blank"
                class="mt-2 inline-flex items-center text-sm text-cyan-400 hover:text-cyan-300 hover:underline"
            >
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                Ver Documento Atual
            </a>
             <NeonButton
                v-if="isEditing && form.document_url && !documentFile.length"
                @click="markDocumentForRemoval"
                size="xs"
                variant="danger_outline"
                class="mt-2 ml-3"
                type="button"
            >
                Remover Documento Existente
            </NeonButton>
             <p v-if="documentMarkedForRemoval" class="text-xs text-yellow-400 mt-1">O documento existente será removido ao salvar.</p>
          </div>
           <div class="flex items-center mt-2">
            <input type="checkbox" id="is_active" v-model="form.is_active" class="h-4 w-4 text-cyan-600 border-gray-500 rounded focus:ring-cyan-500">
            <label for="is_active" class="ml-2 block text-sm text-gray-300">Medida Ativa?</label>
          </div>
        </div>
      </div>

      <div class="flex justify-end space-x-4 pt-4 border-t border-gray-700/50">
        <NeonButton type="button" @click="goBack" variant="secondary">
          Cancelar
        </NeonButton>
        <NeonButton type="submit" :loading="submitting">
          {{ isEditing ? 'Salvar Alterações' : 'Registrar Medida' }}
        </NeonButton>
      </div>
    </form>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AuthenticatedLayout from '../../components/layouts/AuthenticatedLayout.vue';
import HeaderCard from '../../components/layouts/HeaderCard.vue';
import NeonButton from '../../components/NeonButton.vue';
import FileUpload from '../../components/FileUpload.vue';
import api from '../../utils/api';
import { useToast } from '../../composables/useToast';
import { format, parseISO } from 'date-fns';

const route = useRoute();
const router = useRouter();
const { success, error: errorToast } = useToast();

const measureId = route.params.id;
const isEditing = computed(() => !!measureId);
const pageTitle = computed(() => isEditing.value ? 'Editar Medida Protetiva' : 'Nova Medida Protetiva');

const form = reactive({
  protected_resident_id: '',
  aggressor_visitor_id: '',
  process_number: '',
  measure_description: '',
  document_url: '', // Apenas para exibir o link do documento existente
  validity_start_date: format(new Date(), 'yyyy-MM-dd'),
  validity_end_date: '',
  is_active: true,
});

const documentFile = ref([]); // Para o FileUpload.vue
const documentMarkedForRemoval = ref(false);

const users = ref([]); // Para moradores
const visitors = ref([]);
const loadingUsers = ref(false);
const loadingVisitors = ref(false);
const loadingInitialData = ref(false);
const initialError = ref('');
const submitting = ref(false);

const fetchUsers = async () => {
  loadingUsers.value = true;
  try {
    const response = await api.get('/residents?per_page=1000');
    users.value = response.data.data || response.data;
  } catch (e) {
    errorToast('Erro ao carregar lista de moradores.');
  } finally {
    loadingUsers.value = false;
  }
};

const fetchVisitors = async () => {
  loadingVisitors.value = true;
  try {
    const response = await api.get('/access-control/visitors?per_page=1000'); // Ajustar conforme API
    visitors.value = response.data.data || response.data;
  } catch (e) {
    errorToast('Erro ao carregar lista de visitantes.');
  } finally {
    loadingVisitors.value = false;
  }
};

const fetchMeasureDetails = async () => {
  if (!isEditing.value) return;
  loadingInitialData.value = true;
  initialError.value = '';
  try {
    const response = await api.get(`/access-control/protective-measures/${measureId}`);
    const data = response.data;
    form.protected_resident_id = data.protected_resident_id;
    form.aggressor_visitor_id = data.aggressor_visitor_id;
    form.process_number = data.process_number || '';
    form.measure_description = data.measure_description;
    form.document_url = data.document_url || '';
    form.validity_start_date = data.validity_start_date ? format(parseISO(data.validity_start_date), 'yyyy-MM-dd') : '';
    form.validity_end_date = data.validity_end_date ? format(parseISO(data.validity_end_date), 'yyyy-MM-dd') : '';
    form.is_active = data.is_active;
  } catch (e) {
    console.error("Erro ao buscar detalhes da medida:", e);
    initialError.value = "Falha ao carregar dados da medida protetiva para edição.";
    errorToast(initialError.value);
  } finally {
    loadingInitialData.value = false;
  }
};

const markDocumentForRemoval = () => {
    documentMarkedForRemoval.value = true;
    form.document_url = ''; // Remove a URL para indicar que será removido
    documentFile.value = []; // Limpa qualquer novo arquivo selecionado
    success("Documento existente será removido ao salvar.");
}

const submitProtectiveMeasure = async () => {
  submitting.value = true;

  const formData = new FormData();
  formData.append('protected_resident_id', form.protected_resident_id);
  formData.append('aggressor_visitor_id', form.aggressor_visitor_id);
  formData.append('measure_description', form.measure_description);
  formData.append('validity_start_date', form.validity_start_date);
  formData.append('is_active', form.is_active ? '1' : '0');

  if (form.process_number) formData.append('process_number', form.process_number);
  if (form.validity_end_date) formData.append('validity_end_date', form.validity_end_date);

  if (documentFile.value.length > 0) {
    formData.append('document', documentFile.value[0]);
  } else if (isEditing.value && documentMarkedForRemoval.value) {
    formData.append('document_url', ''); // Enviar vazio para indicar remoção no backend se necessário, ou null.
                                        // O controller já trata 'document_url' === null
  }


  const url = isEditing.value
    ? `/access-control/protective-measures/${measureId}`
    : '/access-control/protective-measures';

  // Para PUT com FormData, precisa enviar como POST e adicionar _method
  if (isEditing.value) {
      formData.append('_method', 'PUT');
  }

  try {
    await api.post(url, formData, { // Sempre POST para FormData com _method
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    success(`Medida protetiva ${isEditing.value ? 'atualizada' : 'registrada'} com sucesso!`);
    router.push({ name: 'ProtectiveMeasuresList' });
  } catch (e) {
    console.error("Erro ao salvar medida:", e);
    if (e.response && e.response.data && e.response.data.errors) {
        const errors = e.response.data.errors;
        const firstErrorKey = Object.keys(errors)[0];
        errorToast(errors[firstErrorKey][0] || 'Erro de validação.');
    } else {
        errorToast(e.response?.data?.message || 'Falha ao salvar medida protetiva.');
    }
  } finally {
    submitting.value = false;
  }
};

const goBack = () => {
  router.push({ name: 'ProtectiveMeasuresList' });
};

onMounted(async () => {
  loadingInitialData.value = true;
  await Promise.all([fetchUsers(), fetchVisitors()]);
  if (isEditing.value) {
    await fetchMeasureDetails();
  }
  loadingInitialData.value = false;
});

</script>

<style scoped>
.form-input {
  @apply w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors;
}
.form-label {
  @apply block text-sm font-medium text-gray-300 mb-1;
}
.upload-card-style :deep(.border-gray-600\/50) {
  @apply border-gray-700 bg-gray-800/30 hover:border-cyan-500/50;
}
</style>
