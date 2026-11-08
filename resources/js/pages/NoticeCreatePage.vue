<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Criar Novo Aviso</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
            <form @submit.prevent="submitForm">
              <!-- Título -->
              <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Título</label>
                <input type="text" v-model="form.title" id="title" name="title"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-600 dark:text-gray-200"
                       required>
                <p v-if="errors.title" class="text-sm text-red-600 mt-1">{{ errors.title[0] }}</p>
              </div>

              <!-- Corpo do Aviso (WYSIWYG) -->
              <div class="mb-4">
                <label for="body" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Conteúdo do Aviso</label>
                <!-- Placeholder para Editor WYSIWYG -->
                <textarea v-model="form.body" id="body" name="body" rows="10"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-600 dark:text-gray-200"
                          required></textarea>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Use formatação HTML básica se necessário. Um editor rico será implementado aqui.</p>
                <p v-if="errors.body" class="text-sm text-red-600 mt-1">{{ errors.body[0] }}</p>
              </div>

              <!-- Anexos -->
              <div class="mb-4">
                <label for="attachments" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Anexos (Imagens ou Documentos)</label>
                <input type="file" id="attachments" name="attachments" multiple @change="handleFileUpload"
                       class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 focus:outline-none">
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" id="file_input_help">PNG, JPG, GIF, PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT (MAX. 10MB por arquivo).</p>
                <div v-if="form.attachments.length > 0" class="mt-2">
                  <p class="text-sm font-medium">Arquivos selecionados:</p>
                  <ul>
                    <li v-for="(file, index) in form.attachments" :key="index" class="text-sm text-gray-600 dark:text-gray-400">
                      {{ file.name }} ({{ (file.size / 1024).toFixed(2) }} KB)
                    </li>
                  </ul>
                </div>
                <p v-if="errors.attachments" class="text-sm text-red-600 mt-1">{{ errors.attachments[0] }}</p>
              </div>

              <!-- Escopo do Aviso -->
              <div class="mb-6">
                <h3 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-2">Enviar Para</h3>
                <div class="flex items-center mb-3">
                  <input type="checkbox" v-model="form.send_to_all" id="send_to_all" name="send_to_all"
                         class="h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500">
                  <label for="send_to_all" class="ml-2 block text-sm text-gray-900 dark:text-gray-200">Todo o Condomínio</label>
                </div>

                <div v-if="!form.send_to_all">
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Seletor de Torres -->
                    <div>
                      <label for="towers" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Torres Específicas</label>
                      <!-- Placeholder para Vue-Multiselect ou similar -->
                      <select multiple v-model="form.targets.towers" id="towers" name="towers"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-600 dark:text-gray-200">
                        <option v-for="tower in availableTargets.towers" :key="tower.id" :value="tower.id">{{ tower.name }}</option>
                      </select>
                    </div>
                    <!-- Seletor de Blocos -->
                    <div>
                      <label for="blocks" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Blocos Específicos</label>
                      <select multiple v-model="form.targets.blocks" id="blocks" name="blocks"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-600 dark:text-gray-200">
                        <option v-for="block in availableTargets.blocks" :key="block.id" :value="block.id">{{ block.name }}</option>
                      </select>
                    </div>
                    <!-- Seletor de Apartamentos -->
                    <div>
                      <label for="apartments" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Apartamentos Específicos</label>
                      <select multiple v-model="form.targets.apartments" id="apartments" name="apartments"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-600 dark:text-gray-200">
                        <option v-for="apartment in availableTargets.apartments" :key="apartment.id" :value="apartment.number">{{ apartment.number }}</option>
                      </select>
                    </div>
                  </div>
                  <p v-if="errors.targets" class="text-sm text-red-600 mt-1">{{ errors.targets[0] }}</p>
                </div>
              </div>

              <!-- Botão de Envio -->
              <div class="flex justify-end">
                <button type="submit" :disabled="isSubmitting"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                  <span v-if="isSubmitting">Enviando...</span>
                  <span v-else>Criar Aviso</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
// Idealmente, usar um service para chamadas API
// import noticeService from '@/services/noticeService';
import api from '@/utils/api'; // Supondo que api.js configure o axios globalmente

const router = useRouter();

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
