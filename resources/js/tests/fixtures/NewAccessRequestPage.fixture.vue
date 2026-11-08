<template>
  <AuthenticatedLayout page-title="Nova Solicitação de Acesso">
    <HeaderCard
      title="Registrar Nova Solicitação de Acesso"
      description="Preencha os dados para criar uma nova solicitação de acesso para um visitante."
      icon-path="M12 6v6m0 0v6m0-6h6m-6 0H6"
      icon-color="green"
    />

    <form @submit.prevent="submitNewAccessRequest" class="mt-6 space-y-8">
      <!-- Seção de Busca de Visitante -->
      <div class="bg-gray-800/50 border border-gray-700/50 rounded-2xl p-6 shadow-lg">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
          <svg class="h-5 w-5 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
          Buscar Visitante Existente
        </h3>
        <div class="flex items-end space-x-3">
          <div class="flex-grow">
            <label for="visitor_document_search" class="block text-sm font-medium text-gray-300 mb-1">Documento do Visitante</label>
            <input
              type="text"
              id="visitor_document_search"
              v-model="searchDocument"
              @keyup.enter="searchVisitor"
              @blur="searchVisitor"
              placeholder="Digite o número do documento"
              class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors"
            />
          </div>
          <NeonButton @click="searchVisitor" :loading="searchingVisitor" size="md" type="button">
            <svg class="h-5 w-5 mr-2" v-if="!searchingVisitor" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            Buscar
          </NeonButton>
        </div>
        <p v-if="searchError" class="text-sm text-red-400 mt-2">{{ searchError }}</p>
      </div>

      <!-- Alerta de Medida Protetiva -->
      <div v-if="protectiveMeasureAlert" class="bg-red-500/20 border-2 border-red-500/50 rounded-2xl p-6 shadow-xl text-center">
        <div class="flex flex-col items-center">
          <svg class="h-12 w-12 text-red-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
          <h3 class="text-xl font-semibold text-red-300">ALERTA DE MEDIDA PROTETIVA ATIVA!</h3>
          <p class="text-red-200 mt-1">Este visitante possui uma medida protetiva ativa contra o morador selecionado.</p>
          <p class="text-red-200">A criação da solicitação de acesso está bloqueada.</p>
        </div>
      </div>

      <!-- Formulário de Dados do Visitante -->
      <div class="bg-gray-800/50 border border-gray-700/50 rounded-2xl p-6 shadow-lg">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
          <svg class="h-5 w-5 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
          Dados do Visitante
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
          <div>
            <label for="visitor_name" class="block text-sm font-medium text-gray-300 mb-1">Nome Completo *</label>
            <input type="text" id="visitor_name" v-model="form.visitor_name" required class="form-input"/>
          </div>
          <div>
            <label for="visitor_document" class="block text-sm font-medium text-gray-300 mb-1">Documento (CPF/RG/Passaporte) *</label>
            <input type="text" id="visitor_document" v-model="form.visitor_document" @blur="checkProtectiveMeasure" :disabled="!!form.visitor_id" required class="form-input"/>
          </div>
          <div>
            <label for="visitor_phone" class="block text-sm font-medium text-gray-300 mb-1">Telefone</label>
            <input type="tel" id="visitor_phone" v-model="form.visitor_phone" class="form-input"/>
          </div>
           <div>
            <label for="visitor_observations" class="block text-sm font-medium text-gray-300 mb-1">Observações do Visitante</label>
            <textarea id="visitor_observations" v-model="form.visitor_observations" rows="2" class="form-input"></textarea>
          </div>
        </div>
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-300 mb-2">Foto do Visitante</label>
            <FileUpload
                v-model="visitorPhotoFile"
                accept="image/*"
                :multiple="false"
                :maxSize="2"
                :showCamera="true"
                class="upload-card-style"
            />
            <!-- TODO: Aplicar estilo UploadCard do DESIGN_MANUAL.md no FileUpload -->
        </div>
      </div>

      <!-- Dados do Veículo -->
       <div class="bg-gray-800/50 border border-gray-700/50 rounded-2xl p-6 shadow-lg">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
          <svg class="h-5 w-5 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM21 17a2 2 0 11-4 0 2 2 0 014 0zM13 6h5l3 4v6a1 1 0 01-1 1h-2m-10 0H3a1 1 0 01-1-1v-6l3-4h5m0 0V3a1 1 0 011-1h2a1 1 0 011 1v3"></path></svg>
          Dados do Veículo (Opcional)
        </h3>
        <div class="flex items-center mb-4">
          <input type="checkbox" id="has_vehicle" v-model="form.has_vehicle" class="h-4 w-4 text-cyan-600 border-gray-500 rounded focus:ring-cyan-500">
          <label for="has_vehicle" class="ml-2 block text-sm text-gray-300">Visitante possui veículo?</label>
        </div>
        <div v-if="form.has_vehicle" class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4">
          <div>
            <label for="vehicle_plate" class="block text-sm font-medium text-gray-300 mb-1">Placa *</label>
            <input type="text" id="vehicle_plate" v-model="form.vehicle_plate" @blur="checkProtectiveMeasure" :required="form.has_vehicle" class="form-input"/>
          </div>
          <div>
            <label for="vehicle_model" class="block text-sm font-medium text-gray-300 mb-1">Modelo *</label>
            <input type="text" id="vehicle_model" v-model="form.vehicle_model" :required="form.has_vehicle" class="form-input"/>
          </div>
          <div>
            <label for="vehicle_color" class="block text-sm font-medium text-gray-300 mb-1">Cor *</label>
            <input type="text" id="vehicle_color" v-model="form.vehicle_color" :required="form.has_vehicle" class="form-input"/>
          </div>
        </div>
      </div>

      <!-- Destino e Observações da Portaria -->
      <div class="bg-gray-800/50 border border-gray-700/50 rounded-2xl p-6 shadow-lg">
        <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
          <svg class="h-5 w-5 mr-2 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
          Destino e Observações
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
          <div>
            <label for="resident_id" class="block text-sm font-medium text-gray-300 mb-1">Morador de Destino *</label>
            <!-- TODO: Usar um componente de select/busca para moradores -->
            <select id="resident_id" v-model="form.resident_id" @change="checkProtectiveMeasure" required class="form-input">
              <option disabled value="">Selecione o morador</option>
              <option v-for="resident in residents" :key="resident.id" :value="resident.id">
                {{ resident.name }} ({{ resident.email }})
              </option>
            </select>
             <p v-if="loadingResidents" class="text-xs text-cyan-400 mt-1">Carregando moradores...</p>
          </div>
          <div class="md:col-span-2">
            <label for="gatehouse_observation" class="block text-sm font-medium text-gray-300 mb-1">Observações da Portaria</label>
            <textarea id="gatehouse_observation" v-model="form.gatehouse_observation" rows="3" class="form-input"></textarea>
          </div>
        </div>
      </div>

      <!-- Botões de Ação -->
      <div class="flex justify-end space-x-4 pt-4 border-t border-gray-700/50">
        <NeonButton type="button" @click="goBack" variant="secondary">
          Cancelar
        </NeonButton>
        <NeonButton type="submit" :loading="submitting" :disabled="protectiveMeasureAlert">
          Enviar Solicitação
        </NeonButton>
      </div>
    </form>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from '../../components/layouts/AuthenticatedLayout.vue';
import HeaderCard from '../../components/layouts/HeaderCard.vue';
import NeonButton from '../../components/NeonButton.vue';
import FileUpload from '../../components/FileUpload.vue'; // Usando o FileUpload existente
import api from '../../utils/api';
import { useToast } from '../../composables/useToast';

const router = useRouter();
const { successToast, errorToast } = useToast();

const form = reactive({
  visitor_id: null,
  visitor_name: '',
  visitor_document: '',
  visitor_phone: '',
  visitor_observations: '',

  has_vehicle: false,
  visitor_vehicle_id: null, // Se veículo já existir para o visitante
  vehicle_plate: '',
  vehicle_model: '',
  vehicle_color: '',

  resident_id: '',
  gatehouse_observation: '',
});

const visitorPhotoFile = ref([]); // Para o FileUpload.vue que espera um array

const searchDocument = ref('');
const searchingVisitor = ref(false);
const searchError = ref('');
const submitting = ref(false);
const protectiveMeasureAlert = ref(false);

const residents = ref([]);
const loadingResidents = ref(false);

const fetchResidents = async () => {
  loadingResidents.value = true;
  try {
    // Idealmente, esta rota teria paginação ou um limite, ou um select com busca
    const response = await api.get('/users?profile_type=resident&per_page=1000'); // Exemplo, ajustar endpoint
    residents.value = response.data.data || response.data; // Ajustar conforme estrutura da API de users
  } catch (e) {
    console.error("Erro ao buscar moradores:", e);
    errorToast('Não foi possível carregar a lista de moradores.');
  } finally {
    loadingResidents.value = false;
  }
};

const searchVisitor = async () => {
  if (!searchDocument.value.trim()) {
    searchError.value = 'Digite um documento para buscar.';
    return;
  }
  searchingVisitor.value = true;
  searchError.value = '';
  protectiveMeasureAlert.value = false; // Resetar alerta ao buscar novo visitante
  try {
    const response = await api.get(`/api/access-control/visitors/search-by-document?document=${searchDocument.value.trim()}`);
    const visitor = response.data;
    form.visitor_id = visitor.id;
    form.visitor_name = visitor.name;
    form.visitor_document = visitor.document;
    form.visitor_phone = visitor.phone || '';
    form.visitor_observations = visitor.observations || '';
    // TODO: Lidar com foto do visitante e veículos existentes (selecionar ou adicionar novo)
    successToast(`Visitante "${visitor.name}" encontrado.`);
    await checkProtectiveMeasure(); // Verificar medida protetiva se morador já estiver selecionado
  } catch (e) {
    if (e.response && e.response.status === 404) {
      searchError.value = 'Visitante não encontrado. Preencha os dados para cadastrá-lo.';
      // Limpar campos do visitante para novo cadastro, exceto o documento buscado
      form.visitor_id = null;
      form.visitor_name = '';
      // form.visitor_document = searchDocument.value; // Manter o documento buscado se for um novo cadastro
      form.visitor_phone = '';
      form.visitor_observations = '';
    } else {
      searchError.value = 'Erro ao buscar visitante.';
      console.error("Erro na busca:", e);
    }
  } finally {
    searchingVisitor.value = false;
  }
};

const checkProtectiveMeasure = async () => {
  protectiveMeasureAlert.value = false; // Resetar alerta
  if (!form.visitor_document && !form.vehicle_plate) {
    return; // Não há nada para verificar
  }

  try {
    const response = await api.get('/access-control/check-protective-measure', {
      params: {
        visitor_document: form.visitor_document || null,
        vehicle_plate: form.vehicle_plate || null,
      },
    });

    if (response.data.has_protective_measure) {
      protectiveMeasureAlert.value = true;
      errorToast("ALERTA: Medida Protetiva Ativa! " + (response.data.measure.measure_description || ''));
    } else {
      protectiveMeasureAlert.value = false;
    }
  } catch (e) {
    console.error("Erro ao verificar medida protetiva:", e);
    // Se o erro for 422 (validação), pode ser que nenhum documento/placa foi fornecido
    if (e.response && e.response.status === 422) {
        // Não fazer nada, apenas não ativar o alerta
    } else {
        errorToast('Erro ao verificar medidas protetivas.');
    }
    protectiveMeasureAlert.value = false; // Garantir que o alerta seja desativado em caso de erro inesperado
  }
};

watch(() => form.resident_id, checkProtectiveMeasure);
// Se o visitor_id mudar (após busca), também verificar
watch(() => form.visitor_id, checkProtectiveMeasure);


const submitNewAccessRequest = async () => {
  if (protectiveMeasureAlert.value) {
    errorToast("Não é possível criar a solicitação devido a uma medida protetiva ativa.");
    return;
  }
  submitting.value = true;

  const formData = new FormData();
  formData.append('visitor_name', form.visitor_name);
  formData.append('visitor_document', form.visitor_document);
  if (form.visitor_id) formData.append('visitor_id', form.visitor_id);
  if (form.visitor_phone) formData.append('visitor_phone', form.visitor_phone);
  if (form.visitor_observations) formData.append('visitor_observations', form.visitor_observations);

  if (visitorPhotoFile.value.length > 0) {
    formData.append('visitor_photo', visitorPhotoFile.value[0]);
  }

  formData.append('has_vehicle', form.has_vehicle ? '1' : '0');
  if (form.has_vehicle) {
    formData.append('vehicle_plate', form.vehicle_plate);
    formData.append('vehicle_model', form.vehicle_model);
    formData.append('vehicle_color', form.vehicle_color);
    if (form.visitor_vehicle_id) formData.append('visitor_vehicle_id', form.visitor_vehicle_id);
  }

  formData.append('resident_id', form.resident_id);
  if (form.gatehouse_observation) formData.append('gatehouse_observation', form.gatehouse_observation);

  try {
    await api.post('/access-control/access-requests', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    successToast('Solicitação de acesso criada com sucesso!');
    router.push({ name: 'AccessControlDashboard' });
  } catch (e) {
    console.error("Erro ao criar solicitação:", e);
    if (e.response?.data?.medida_protetiva_ativa) {
        protectiveMeasureAlert.value = true;
        errorToast(e.response.data.message || "ALERTA: Medida Protetiva Ativa!");
    } else if (e.response?.data?.visitor_blocked) {
        errorToast(e.response.data.message || "Visitante bloqueado.");
    } else if (e.response && e.response.data && e.response.data.errors) {
        const errors = e.response.data.errors;
        const firstErrorKey = Object.keys(errors)[0];
        errorToast(errors[firstErrorKey][0] || 'Erro de validação.');
    } else {
        errorToast(e.response?.data?.message || 'Falha ao criar solicitação.');
    }
  } finally {
    submitting.value = false;
  }
};

const goBack = () => {
  router.back();
};

onMounted(() => {
  fetchResidents();
});

</script>

<style scoped>
.form-input {
  @apply w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors;
}
/* Estilo para o FileUpload.vue se assemelhar ao UploadCard */
.upload-card-style :deep(.border-gray-600\/50) { /* Acessando classe interna do FileUpload */
  @apply border-gray-700 bg-gray-800/30 hover:border-cyan-500/50;
}
.upload-card-style :deep(.text-gray-400) {
  @apply text-gray-300;
}
.upload-card-style :deep(.text-gray-500) {
  @apply text-gray-400;
}
.upload-card-style :deep(.text-gray-600) {
  @apply text-gray-500;
}
</style>
