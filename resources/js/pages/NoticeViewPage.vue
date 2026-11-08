<template>
  <AuthenticatedLayout page-title="Detalhes do Aviso">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header Section -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-white">Detalhes do Aviso</h1>
          <p class="text-gray-400 mt-1">Visualize todas as informações do aviso selecionado</p>
        </div>
        <NeonButton @click="goBack" variant="secondary" size="md">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Voltar
        </NeonButton>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex justify-center items-center py-16">
        <div class="relative">
          <div class="w-16 h-16 border-4 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin"></div>
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-8 h-8 bg-indigo-500 rounded-full animate-pulse"></div>
          </div>
        </div>
        <p class="text-gray-400 ml-4">Carregando detalhes do aviso...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-gradient-to-r from-red-900/50 to-red-800/50 border border-red-500/50 rounded-2xl p-6">
        <div class="flex items-center">
          <svg class="h-6 w-6 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <h3 class="text-red-300 font-semibold">Erro!</h3>
            <p class="text-red-200">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Notice Content -->
      <div v-else-if="notice" class="space-y-6">
        <!-- Main Notice Card -->
        <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-8 border-2 border-indigo-500/20">
          <!-- Notice Header -->
          <div class="flex items-start justify-between mb-6">
            <div class="flex-1">
              <h2 class="text-2xl font-bold text-white mb-4">{{ notice.title }}</h2>
              
              <!-- Notice Meta -->
              <div class="flex flex-wrap items-center gap-4 text-sm text-gray-400">
                <span class="flex items-center">
                  <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  Criado por: {{ notice.user?.name || 'Desconhecido' }}
                </span>
                <span class="flex items-center">
                  <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ formatDate(notice.created_at) }}
                </span>
              </div>
            </div>
            
            <!-- Notice Type Badge -->
            <div>
              <span v-if="notice.send_to_all" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-900 text-green-300 border border-green-700">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Todo o Condomínio
              </span>
              <span v-else class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-900 text-blue-300 border border-blue-700">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Aviso Específico
              </span>
            </div>
          </div>

          <!-- Notice Body -->
          <div class="bg-gray-800/50 rounded-xl p-6 mb-6">
            <div class="prose prose-invert max-w-none text-gray-300 leading-relaxed">
              <div v-html="formatNoticeBody(notice.body)"></div>
            </div>
          </div>

          <!-- Recipients Section -->
          <div class="mb-6">
            <h3 class="text-lg font-semibold text-indigo-300 mb-4 flex items-center">
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
              </svg>
              Destinatários
            </h3>
            
            <div v-if="notice.send_to_all" class="bg-green-900/20 border border-green-700/50 rounded-xl p-4">
              <div class="flex items-center text-green-300">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="font-medium">Enviado para todo o condomínio</span>
              </div>
            </div>
            
            <div v-else class="bg-blue-900/20 border border-blue-700/50 rounded-xl p-4">
              <div class="space-y-3">
                <div v-if="notice.towers?.length" class="flex items-start">
                  <svg class="h-5 w-5 mr-2 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  <div>
                    <span class="text-blue-300 font-medium">Torres:</span>
                    <div class="flex flex-wrap gap-2 mt-1">
                      <span v-for="tower in notice.towers" :key="`tower-${tower.id}`" 
                            class="px-2 py-1 bg-blue-800 text-blue-200 rounded-md text-sm">
                        {{ tower.name }}
                      </span>
                    </div>
                  </div>
                </div>
                
                <div v-if="notice.blocks?.length" class="flex items-start">
                  <svg class="h-5 w-5 mr-2 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  <div>
                    <span class="text-blue-300 font-medium">Blocos:</span>
                    <div class="flex flex-wrap gap-2 mt-1">
                      <span v-for="block in notice.blocks" :key="`block-${block.id}`" 
                            class="px-2 py-1 bg-blue-800 text-blue-200 rounded-md text-sm">
                        {{ block.name }}
                      </span>
                    </div>
                  </div>
                </div>
                
                <div v-if="notice.apartments?.length" class="flex items-start">
                  <svg class="h-5 w-5 mr-2 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                  </svg>
                  <div>
                    <span class="text-blue-300 font-medium">Apartamentos:</span>
                    <div class="flex flex-wrap gap-2 mt-1">
                      <span v-for="apartment in notice.apartments" :key="`apartment-${apartment.id}`" 
                            class="px-2 py-1 bg-blue-800 text-blue-200 rounded-md text-sm">
                        {{ apartment.number }}
                      </span>
                    </div>
                  </div>
                </div>
                
                <div v-if="!notice.towers?.length && !notice.blocks?.length && !notice.apartments?.length" 
                     class="text-yellow-300 text-sm">
                  <svg class="h-4 w-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                  Nenhum destinatário específico definido
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Attachments Section -->
        <div v-if="notice.attachments && notice.attachments.length > 0" 
             class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2 border-indigo-500/20">
          <h3 class="text-lg font-semibold text-indigo-300 mb-4 flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
            </svg>
            Anexos ({{ notice.attachments.length }})
          </h3>
          
          <div class="grid gap-4 md:grid-cols-2">
            <div v-for="attachment in notice.attachments" :key="attachment.id"
                 class="bg-gray-800/50 rounded-xl p-4 border border-gray-700 hover:border-indigo-500/50 transition-colors">
              <!-- Image Preview -->
              <div v-if="attachment.file_type === 'image'" class="mb-4">
                <img :src="getAttachmentUrl(attachment.file_path)" 
                     :alt="attachment.original_name"
                     class="w-full h-32 object-cover rounded-lg border border-gray-700">
              </div>
              
              <!-- File Icon -->
              <div v-else class="mb-4 flex justify-center">
                <div class="w-16 h-16 bg-indigo-900/50 rounded-lg flex items-center justify-center border border-indigo-700">
                  <svg class="h-8 w-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
              </div>
              
              <!-- File Info -->
              <div class="text-center">
                <h4 class="text-white font-medium mb-1 truncate">{{ attachment.original_name }}</h4>
                <p class="text-xs text-gray-400 mb-3">
                  {{ attachment.file_type.toUpperCase() }} • {{ formatFileSize(attachment.file_size) }}
                </p>
                
                <NeonButton 
                  @click="downloadAttachment(attachment)"
                  variant="secondary" 
                  size="sm"
                  class="w-full"
                >
                  <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Baixar
                </NeonButton>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions Section -->
        <div class="flex justify-center">
          <NeonButton @click="goBack" size="lg">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Voltar para Lista
          </NeonButton>
        </div>
      </div>

      <!-- Not Found State -->
      <div v-else class="text-center py-16">
        <div class="mx-auto h-24 w-24 text-gray-600 mb-4">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-300 mb-2">Aviso não encontrado</h3>
        <p class="text-gray-500 mb-6">O aviso que você está procurando não existe ou foi removido.</p>
        <NeonButton @click="goBack">
          Voltar para Lista
        </NeonButton>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import NeonButton from '@/components/NeonButton.vue';
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/utils/api';

const route = useRoute();
const router = useRouter();
const notice = ref(null);
const isLoading = ref(true);
const error = ref(null);

async function fetchNoticeDetails() {
  isLoading.value = true;
  error.value = null;
  try {
    const noticeId = route.params.id;
    const response = await api.get(`/notices/${noticeId}`);
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
  const options = { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric', 
    hour: '2-digit', 
    minute: '2-digit',
    weekday: 'long'
  };
  return new Date(dateString).toLocaleDateString('pt-BR', options);
}

function formatNoticeBody(body) {
  if (!body) return '';
  // Converter quebras de linha em parágrafos HTML
  return body.replace(/\n\n/g, '</p><p>').replace(/\n/g, '<br>');
}

function formatFileSize(bytes) {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function getAttachmentUrl(filePath) {
  if (!filePath) return '#';
  return `/storage/${filePath}`;
}

function downloadAttachment(attachment) {
  const url = getAttachmentUrl(attachment.file_path);
  window.open(url, '_blank');
}

function goBack() {
  router.push({ name: 'notices.list' });
}

onMounted(() => {
  fetchNoticeDetails();
});
</script>

<style scoped>
.prose p {
  margin-top: 0.5em;
  margin-bottom: 0.5em;
}

.prose ul {
  list-style-type: disc;
  padding-left: 1.5em;
}

.prose h4 {
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}
</style>
