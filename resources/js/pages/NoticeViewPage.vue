<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Detalhes do Aviso
        </h2>
        <router-link :to="{ name: 'notices.list' }"
                     class="px-4 py-2 bg-gray-200 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150">
          Voltar para Lista
        </router-link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div v-if="isLoading" class="text-center py-10">
          <p class="text-gray-500 dark:text-gray-400 text-lg">Carregando detalhes do aviso...</p>
          <!-- Adicionar um spinner/loader visual aqui -->
        </div>
        <div v-else-if="error" class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded relative" role="alert">
          <strong class="font-bold">Erro!</strong>
          <span class="block sm:inline">{{ error }}</span>
        </div>
        <div v-else-if="notice" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ notice.title }}</h3>
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-4">
              <p>Criado por: {{ notice.user?.name || 'Desconhecido' }}</p>
              <p>Data: {{ formatDate(notice.created_at) }}</p>
            </div>

            <div class="prose dark:prose-invert max-w-none mb-6" v-html="notice.body"></div>

            <!-- Seção de Destinatários -->
            <div class="mb-6">
              <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Enviado Para:</h4>
              <div v-if="notice.send_to_all" class="text-sm text-gray-600 dark:text-gray-300">
                Todo o Condomínio
              </div>
              <div v-else class="text-sm text-gray-600 dark:text-gray-300">
                <ul class="list-disc list-inside">
                  <li v-for="tower in notice.towers" :key="`tower-${tower.id}`">Torre: {{ tower.name }}</li>
                  <li v-for="block in notice.blocks" :key="`block-${block.id}`">Bloco: {{ block.name }}</li>
                  <li v-for="apartment in notice.apartments" :key="`apartment-${apartment.id}`">Apartamento: {{ apartment.number }}</li>
                </ul>
                <p v-if="!notice.towers?.length && !notice.blocks?.length && !notice.apartments?.length">
                  Nenhum alvo específico definido (pode ser um erro ou um aviso não direcionado).
                </p>
              </div>
            </div>

            <!-- Seção de Anexos -->
            <div v-if="notice.attachments && notice.attachments.length > 0" class="mb-6">
              <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Anexos:</h4>
              <ul class="space-y-2">
                <li v-for="attachment in notice.attachments" :key="attachment.id"
                    class="p-3 bg-gray-100 dark:bg-gray-700 rounded-md shadow-sm">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="font-medium text-gray-800 dark:text-gray-200">{{ attachment.original_name }}</p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">
                        Tipo: {{ attachment.file_type }} | Tamanho: {{ (attachment.file_size / 1024).toFixed(2) }} KB
                      </p>
                    </div>
                    <a :href="getAttachmentUrl(attachment.file_path)" target="_blank"
                       class="px-3 py-1 bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-semibold rounded-md transition-colors">
                      Ver / Baixar
                    </a>
                  </div>
                  <!-- Preview de Imagem -->
                  <div v-if="attachment.file_type === 'image'" class="mt-2">
                     <img :src="getAttachmentUrl(attachment.file_path)" :alt="attachment.original_name"
                          class="max-w-xs max-h-48 rounded-md object-cover">
                  </div>
                </li>
              </ul>
            </div>
            <div v-else class="mb-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum anexo para este aviso.</p>
            </div>

          </div>
        </div>
        <div v-else class="text-center py-10">
            <p class="text-gray-500 dark:text-gray-400 text-lg">Aviso não encontrado.</p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/utils/api'; // Supondo que api.js configure o axios globalmente
// import noticeService from '@/services/noticeService'; // Idealmente

const route = useRoute();
const notice = ref(null);
const isLoading = ref(true);
const error = ref(null);

async function fetchNoticeDetails() {
  isLoading.value = true;
  error.value = null;
  try {
    const noticeId = route.params.id;
    // const response = await noticeService.getNotice(noticeId);
    const response = await api.get(`/api/notices/${noticeId}`);
    notice.value = response.data;
  } catch (err) {
    console.error('Erro ao buscar detalhes do aviso:', err);
    if (err.response && err.response.status === 404) {
        error.value = 'Aviso não encontrado.';
    } else {
        error.value = 'Não foi possível carregar os detalhes do aviso. Tente novamente mais tarde.';
    }
  } finally {
    isLoading.value = false;
  }
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateString).toLocaleDateString('pt-BR', options);
}

function getAttachmentUrl(filePath) {
  // Assume que os arquivos estão em 'storage/public/' e o link simbólico está configurado.
  // O caminho retornado pela API (filePath) deve ser relativo a 'storage/app/public/'
  // Ex: 'notices/1/notice_1/arquivo.pdf' se torna '/storage/notices/1/notice_1/arquivo.pdf'
  if (!filePath) return '#';
  return `/storage/${filePath}`;
}

onMounted(() => {
  fetchNoticeDetails();
});
</script>

<style scoped>
/* Adicionar estilos para .prose se não estiverem globais via Tailwind Typography */
/* Exemplo básico se Tailwind Typography não estiver configurado: */
.prose :where(p):not(:where([class~="not-prose"] *)) {
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}
.prose :where(ul):not(:where([class~="not-prose"] *)) {
  list-style-type: disc;
  padding-left: 1.5em;
}
.prose :where(h4):not(:where([class~="not-prose"] *)) {
    font-size: 1.125rem; /* text-lg */
    font-weight: 600; /* semibold */
    margin-bottom: 0.5rem; /* mb-2 */
}
</style>
