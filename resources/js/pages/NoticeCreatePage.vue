<template>
  <AuthenticatedLayout page-title="Criar Novo Aviso">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-white">Criar Novo Aviso</h1>
          <p class="text-gray-400 mt-1">Preencha os detalhes abaixo para criar um novo aviso.</p>
        </div>
        <NeonButton @click="goBack" variant="secondary" size="md">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Voltar
        </NeonButton>
      </div>

      <!-- Formulário -->
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-8 border-2 border-indigo-500/20">
        <form @submit.prevent="submitForm" class="space-y-6">

          <!-- Seção: Detalhes do Aviso -->
          <div class="space-y-4">
            <h3 class="text-xl font-semibold text-indigo-300 border-b border-indigo-500/30 pb-2">
              Detalhes do Aviso
            </h3>
            <div class="space-y-4">
              <!-- Título -->
              <div>
                <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Título *</label>
                <input type="text" v-model="form.title" id="title" name="title"
                       class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                       :class="{ 'border-red-500': errors.title }"
                       placeholder="Assunto principal do aviso"
                       required>
                <p v-if="errors.title" class="mt-1 text-sm text-red-400">{{ errors.title[0] }}</p>
              </div>

              <!-- Corpo do Aviso (WYSIWYG) -->
              <div>
                <label for="body" class="block text-sm font-medium text-gray-300 mb-2">Conteúdo do Aviso *</label>
                <textarea v-model="form.body" id="body" name="body" rows="10"
                          class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                          :class="{ 'border-red-500': errors.body }"
                          placeholder="Digite a mensagem completa do aviso..."
                          required></textarea>
                <p class="mt-1 text-xs text-gray-400">Um editor de texto rico (WYSIWYG) será integrado aqui para formatação.</p>
                <p v-if="errors.body" class="mt-1 text-sm text-red-400">{{ errors.body[0] }}</p>
              </div>
            </div>
          </div>

          <!-- Seção: Anexos -->
          <div class="space-y-4">
            <h3 class="text-xl font-semibold text-indigo-300 border-b border-indigo-500/30 pb-2">
              Anexos
            </h3>
            <div class="bg-gradient-to-br from-gray-900/90 via-gray-800/90 to-gray-900/90 rounded-2xl p-6 border-2 border-gray-700/50 shadow-xl">
              <div class="flex items-center space-x-3 mb-6">
                <div class="h-10 w-10 rounded-full bg-blue-500/20 flex items-center justify-center">
                  <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                  </svg>
                </div>
                <div>
                  <h2 class="text-xl font-semibold text-white">Upload de Documentos</h2>
                  <p class="text-sm text-gray-400">Envie arquivos arrastando aqui ou clicando para selecionar</p>
                </div>
              </div>
              <div class="bg-gray-800/30 rounded-lg p-4 border border-gray-600/30">
                <FileUpload
                  v-model="form.attachments"
                  :accept="'.png,.jpg,.jpeg,.gif,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt'"
                  :multiple="true"
                  :max-size="10"
                />
              </div>
              <p v-if="errors.attachments" class="mt-3 text-sm text-red-400">{{ errors.attachments[0] }}</p>
              <p v-if="errors['attachments.0']" class="mt-1 text-sm text-red-400">{{ errors['attachments.0'][0] }}</p>
            </div>
          </div>

          <!-- Seção: Escopo do Aviso -->
          <div class="space-y-4">
            <h3 class="text-xl font-semibold text-indigo-300 border-b border-indigo-500/30 pb-2">
              Destinatários
            </h3>
            
            <!-- Seleção inicial do tipo de destinatário -->
            <div class="space-y-3">
              <p class="text-sm font-medium text-gray-300 mb-4">Selecione o tipo de destinatário:</p>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="flex items-center p-3 bg-gray-800/50 rounded-lg border border-gray-700 hover:border-indigo-500/50 transition-colors">
                  <input type="radio" v-model="form.target_type" value="all" id="target_all" name="target_type"
                         class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 bg-gray-800">
                  <label for="target_all" class="ml-3 text-gray-300 cursor-pointer">
                    <span class="font-medium">Todo o Condomínio</span>
                    <span class="block text-xs text-gray-400">Enviar para todos os moradores</span>
                  </label>
                </div>
                
                <div class="flex items-center p-3 bg-gray-800/50 rounded-lg border border-gray-700 hover:border-indigo-500/50 transition-colors">
                  <input type="radio" v-model="form.target_type" value="towers" id="target_towers" name="target_type"
                         class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 bg-gray-800">
                  <label for="target_towers" class="ml-3 text-gray-300 cursor-pointer">
                    <span class="font-medium">Torres Específicas</span>
                    <span class="block text-xs text-gray-400">Selecionar torres</span>
                  </label>
                </div>
                
                <div class="flex items-center p-3 bg-gray-800/50 rounded-lg border border-gray-700 hover:border-indigo-500/50 transition-colors">
                  <input type="radio" v-model="form.target_type" value="blocks" id="target_blocks" name="target_type"
                         class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 bg-gray-800">
                  <label for="target_blocks" class="ml-3 text-gray-300 cursor-pointer">
                    <span class="font-medium">Blocos Específicos</span>
                    <span class="block text-xs text-gray-400">Selecionar blocos</span>
                  </label>
                </div>
                
                <div class="flex items-center p-3 bg-gray-800/50 rounded-lg border border-gray-700 hover:border-indigo-500/50 transition-colors">
                  <input type="radio" v-model="form.target_type" value="apartments" id="target_apartments" name="target_type"
                         class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 bg-gray-800">
                  <label for="target_apartments" class="ml-3 text-gray-300 cursor-pointer">
                    <span class="font-medium">Unidades Específicas</span>
                    <span class="block text-xs text-gray-400">Selecionar apartamentos</span>
                  </label>
                </div>
              </div>
              
              <p v-if="errors.target_type" class="mt-1 text-sm text-red-400">{{ errors.target_type[0] }}</p>
            </div>

            <!-- Multi-selects condicionais -->
            <div v-if="form.target_type && form.target_type !== 'all'" class="space-y-4 pt-4">
              <!-- Torres -->
              <MultiSelectField
                v-if="form.target_type === 'towers'"
                v-model="form.targets.towers"
                :options="availableTargets.towers"
                label="Selecione as Torres"
                name="towers"
                count-label="torre(s)"
                :error-message="errors.targets?.[0] || ''"
              />

              <!-- Blocos -->
              <MultiSelectField
                v-if="form.target_type === 'blocks'"
                v-model="form.targets.blocks"
                :options="availableTargets.blocks"
                label="Selecione os Blocos"
                name="blocks"
                count-label="bloco(s)"
                :error-message="errors.targets?.[0] || ''"
              />

              <!-- Apartamentos -->
              <MultiSelectField
                v-if="form.target_type === 'apartments'"
                v-model="form.targets.apartments"
                :options="apartmentOptions"
                label="Selecione as Unidades"
                name="apartments"
                count-label="unidade(s)"
                :error-message="errors.targets?.[0] || ''"
              />
            </div>
          </div>

          <!-- Botões de Ação -->
          <div class="flex justify-end space-x-4 pt-6">
            <NeonButton @click="goBack" type="button" variant="secondary">
              Cancelar
            </NeonButton>
            <NeonButton type="submit" :loading="isSubmitting">
               <svg v-if="!isSubmitting" class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              {{ isSubmitting ? 'Enviando...' : 'Criar Aviso' }}
            </NeonButton>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import NeonButton from '@/components/NeonButton.vue'; // Assumindo que NeonButton está em @/components/
import MultiSelectField from '@/components/MultiSelectField.vue';
import FileUpload from '@/components/FileUpload.vue';
import { ref, reactive, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/utils/api';
import { formatApartmentDescription } from '@/utils/formatters';

const router = useRouter();

const goBack = () => router.back();

const form = reactive({
  title: '',
  body: '',
  attachments: [], // Array de File objects
  target_type: 'all', // 'all', 'towers', 'blocks', 'apartments'
  targets: {
    towers: [],
    blocks: [],
    apartments: []
  }
});

const errors = ref({});
const isSubmitting = ref(false);

const availableTargets = reactive({
  towers: [],
  blocks: [],
  apartments: []
});

// Computed property para formatar apartamentos com informações completas
// Formato esperado: "Unidade 101 - 1º andar - Bloco A - Torre Norte"
const apartmentOptions = computed(() => 
  availableTargets.apartments.map(apartment => ({
    id: apartment.id,
    name: formatApartmentDescription(apartment)
  }))
);


async function fetchTargetData() {
  try {
    // Buscar dados para seleção de destinatários
    const [towersRes, blocksRes, apartmentsRes] = await Promise.all([
      api.get('/towers'),
      api.get('/blocks'), 
      api.get('/apartments')
    ]);

    availableTargets.towers = towersRes.data.data || towersRes.data;
    availableTargets.blocks = blocksRes.data.data || blocksRes.data;
    
    // Os apartamentos já vêm com relacionamentos tower e block carregados
    availableTargets.apartments = apartmentsRes.data.data || apartmentsRes.data;
    
  } catch (error) {
    console.error('Erro ao buscar dados para seleção de alvos:', error);
    // Mostrar erro para o usuário
    errors.value.general = ['Erro ao carregar dados para seleção. Tente recarregar a página.'];
  }
}

async function submitForm() {
  isSubmitting.value = true;
  errors.value = {};

  // Validar seleção de tipo de destinatário
  if (!form.target_type) {
    errors.value.target_type = ['Selecione o tipo de destinatário.'];
    isSubmitting.value = false;
    return;
  }

  const formData = new FormData();
  formData.append('title', form.title);
  formData.append('body', form.body);
  formData.append('target_type', form.target_type);
  formData.append('send_to_all', form.target_type === 'all' ? '1' : '0');

  form.attachments.forEach(file => {
    formData.append('attachments[]', file);
  });

  // Processar targets específicos baseado no tipo selecionado
  if (form.target_type !== 'all') {
    const targetsPayload = [];
    
    switch (form.target_type) {
      case 'towers':
        form.targets.towers.forEach(id => targetsPayload.push({ type: 'tower', id }));
        break;
      case 'blocks':
        form.targets.blocks.forEach(id => targetsPayload.push({ type: 'block', id }));
        break;
      case 'apartments':
        form.targets.apartments.forEach(id => targetsPayload.push({ type: 'apartment', id }));
        break;
    }

    if (targetsPayload.length === 0) {
      const targetTypeNames = {
        towers: 'torres',
        blocks: 'blocos',
        apartments: 'unidades'
      };
      errors.value.targets = [`Selecione pelo menos uma ${targetTypeNames[form.target_type]} para enviar o aviso.`];
      isSubmitting.value = false;
      return;
    }

    targetsPayload.forEach((target, index) => {
      formData.append(`targets[${index}][type]`, target.type);
      formData.append(`targets[${index}][id]`, target.id);
    });
  }

  try {
    // await noticeService.createNotice(formData); // Usando o service
    await api.post('/notices', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    // Idealmente, mostrar uma notificação de sucesso
    router.push({ name: 'notices.list' }); // Redirecionar para a lista de avisos (a ser criada)
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      console.error('Erro ao criar aviso:', error);
      // Mostrar erro genérico para o usuário
      errors.value.general = ['Ocorreu um erro ao tentar criar o aviso. Tente novamente.'];
    }
  } finally {
    isSubmitting.value = false;
  }
}

// Limpar seleções quando o tipo de destinatário mudar
watch(() => form.target_type, (newType, oldType) => {
  if (newType !== oldType) {
    // Limpar todas as seleções quando mudar o tipo
    form.targets.towers = [];
    form.targets.blocks = [];
    form.targets.apartments = [];
    
    // Limpar possíveis erros relacionados aos targets
    if (errors.value.targets) {
      delete errors.value.targets;
    }
  }
});

onMounted(() => {
  fetchTargetData();
});

</script>

<style scoped>
/* Estilos específicos do componente, se necessário */
/* Considere usar vue-multiselect ou similar para melhores seletores */
</style>
