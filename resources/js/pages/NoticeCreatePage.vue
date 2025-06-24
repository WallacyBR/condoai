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
            <div>
              <label for="attachments" class="block text-sm font-medium text-gray-300 mb-2">Adicionar Arquivos (Imagens ou Documentos)</label>
              <input type="file" id="attachments" name="attachments" multiple @change="handleFileUpload"
                     class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer"
                     :class="{ 'border-red-500': errors.attachments }">
              <p class="mt-1 text-xs text-gray-500">Tipos permitidos: PNG, JPG, GIF, PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT (MAX. 10MB por arquivo).</p>
              <div v-if="form.attachments.length > 0" class="mt-3 space-y-2">
                <p class="text-sm font-medium text-gray-300">Arquivos selecionados:</p>
                <ul class="list-disc list-inside pl-1">
                  <li v-for="(file, index) in form.attachments" :key="index" class="text-sm text-gray-400">
                    {{ file.name }} ({{ (file.size / 1024).toFixed(2) }} KB)
                  </li>
                </ul>
              </div>
              <p v-if="errors.attachments" class="mt-1 text-sm text-red-400">{{ errors.attachments[0] }}</p>
              <p v-if="errors['attachments.0']" class="mt-1 text-sm text-red-400">{{ errors['attachments.0'][0] }}</p>
            </div>
          </div>

          <!-- Seção: Escopo do Aviso -->
          <div class="space-y-4">
            <h3 class="text-xl font-semibold text-indigo-300 border-b border-indigo-500/30 pb-2">
              Destinatários
            </h3>
            <div class="space-y-3">
              <div class="flex items-center">
                <input type="checkbox" v-model="form.send_to_all" id="send_to_all" name="send_to_all"
                       class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800">
                <label for="send_to_all" class="ml-2 text-gray-300">Enviar para Todo o Condomínio</label>
              </div>

              <div v-if="!form.send_to_all" class="space-y-4 pt-2">
                <p class="text-sm text-gray-400">Ou selecione alvos específicos:</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label for="towers" class="block text-sm font-medium text-gray-300 mb-2">Torres</label>
                    <select multiple v-model="form.targets.towers" id="towers" name="towers"
                            class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors h-32">
                      <option v-for="tower in availableTargets.towers" :key="tower.id" :value="tower.id">{{ tower.name }}</option>
                    </select>
                  </div>
                  <div>
                    <label for="blocks" class="block text-sm font-medium text-gray-300 mb-2">Blocos</label>
                    <select multiple v-model="form.targets.blocks" id="blocks" name="blocks"
                            class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors h-32">
                      <option v-for="block in availableTargets.blocks" :key="block.id" :value="block.id">{{ block.name }}</option>
                    </select>
                  </div>
                  <div>
                    <label for="apartments" class="block text-sm font-medium text-gray-300 mb-2">Apartamentos</label>
                    <select multiple v-model="form.targets.apartments" id="apartments" name="apartments"
                            class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors h-32">
                      <option v-for="apartment in availableTargets.apartments" :key="apartment.id" :value="apartment.id">{{ apartment.number }}</option>
                    </select>
                  </div>
                </div>
                <p v-if="errors.targets" class="mt-1 text-sm text-red-400">{{ errors.targets[0] }}</p>
              </div>
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
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/utils/api';

const router = useRouter();

const goBack = () => router.back();

const form = reactive({
  title: '',
  body: '',
  attachments: [], // Array de File objects
  send_to_all: false,
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

function handleFileUpload(event) {
  form.attachments = Array.from(event.target.files);
}

async function fetchTargetData() {
  try {
    // Estas chamadas podem precisar de tratamento de erro e loading state
    // Adapte os endpoints conforme a sua API de listagem de torres, blocos e apartamentos
    const towersRes = await api.get('/api/towers'); // Ajustar endpoint se necessário
    availableTargets.towers = towersRes.data.data || towersRes.data; // Ajustar conforme a estrutura da resposta

    const blocksRes = await api.get('/api/blocks'); // Ajustar endpoint se necessário
    availableTargets.blocks = blocksRes.data.data || blocksRes.data;

    const apartmentsRes = await api.get('/api/apartments'); // Ajustar endpoint se necessário
    availableTargets.apartments = apartmentsRes.data.data || apartmentsRes.data;
  } catch (error) {
    console.error('Erro ao buscar dados para seleção de alvos:', error);
    // Tratar erro, talvez mostrar uma notificação para o usuário
  }
}

async function submitForm() {
  isSubmitting.value = true;
  errors.value = {};

  const formData = new FormData();
  formData.append('title', form.title);
  formData.append('body', form.body);
  formData.append('send_to_all', form.send_to_all ? '1' : '0');

  form.attachments.forEach(file => {
    formData.append('attachments[]', file);
  });

  if (!form.send_to_all) {
    const targetsPayload = [];
    form.targets.towers.forEach(id => targetsPayload.push({ type: 'tower', id }));
    form.targets.blocks.forEach(id => targetsPayload.push({ type: 'block', id }));
    form.targets.apartments.forEach(id => targetsPayload.push({ type: 'apartment', id }));

    if (targetsPayload.length > 0) {
      targetsPayload.forEach((target, index) => {
        formData.append(`targets[${index}][type]`, target.type);
        formData.append(`targets[${index}][id]`, target.id);
      });
    } else if (targetsPayload.length === 0 && !form.send_to_all) {
        // Se não for para todos e nenhum alvo específico for selecionado, pode ser um erro.
        // Ou pode ser permitido, dependendo da regra de negócio.
        // Adicionando um erro de exemplo:
        errors.value.targets = ['Se não for para "Todo o Condomínio", ao menos um alvo específico deve ser selecionado.'];
        isSubmitting.value = false;
        return;
    }
  }

  try {
    // await noticeService.createNotice(formData); // Usando o service
    await api.post('/api/notices', formData, {
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

onMounted(() => {
  fetchTargetData();
});

</script>

<style scoped>
/* Estilos específicos do componente, se necessário */
/* Considere usar vue-multiselect ou similar para melhores seletores */
</style>
