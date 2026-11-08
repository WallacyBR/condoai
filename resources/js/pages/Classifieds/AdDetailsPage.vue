<template>
  <AuthenticatedLayout page-title="Detalhes do Anúncio">
    <div class="max-w-4xl mx-auto space-y-6">
      <div v-if="isLoading" class="flex justify-center items-center py-12">
        <div class="relative">
          <div class="w-16 h-16 border-4 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin"></div>
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-8 h-8 bg-indigo-500 rounded-full animate-pulse"></div>
          </div>
        </div>
        <p class="text-gray-400 ml-4">Carregando anúncio...</p>
      </div>

      <div v-else-if="errors.general" class="bg-gradient-to-r from-red-900/50 to-red-800/50 border border-red-500/50 rounded-2xl p-6">
        <div class="flex items-center">
          <svg class="h-6 w-6 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-red-300">{{ errors.general }}</p>
        </div>
      </div>

      <div v-else-if="ad">
        <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2 border-indigo-500/20">
          <h1 class="text-3xl font-bold text-white mb-4">{{ ad.title }}</h1>
          
          <!-- Image Gallery -->
          <div v-if="ad.images && ad.images.length > 0" class="mb-6">
            <img :src="`/storage/${ad.images[0].image_path}`" alt="Ad Image" class="w-full h-96 object-cover rounded-lg" />
            <!-- TODO: Implement a proper image gallery/carousel -->
          </div>
          <div v-else class="w-full h-96 bg-gray-700 rounded-lg mb-6 flex items-center justify-center text-gray-400 text-xl">
            Sem Imagem
          </div>

          <p class="text-4xl font-extrabold text-indigo-400 mb-4">{{ formatCurrency(ad.price, ad.currency) }}</p>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-300 text-sm mb-6">
            <div><strong>Categoria:</strong> {{ ad.category }}</div>
            <div><strong>Condição:</strong> {{ ad.condition }}</div>
            <div v-if="ad.location"><strong>Localização:</strong> {{ ad.location }}</div>
            <div><strong>Anunciado por:</strong> {{ ad.user.name }}</div>
            <div><strong>Publicado em:</strong> {{ formatDate(ad.created_at) }}</div>
            <div v-if="ad.expires_at"><strong>Expira em:</strong> {{ formatDate(ad.expires_at) }}</div>
            <div><strong>Visualizações:</strong> {{ ad.views_count }}</div>
          </div>

          <h2 class="text-xl font-semibold text-white mb-2">Descrição</h2>
          <p class="text-gray-300 mb-6">{{ ad.description }}</p>

          <div class="flex flex-wrap gap-4">
            <NeonButton @click="contactSeller" size="md">
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.135a11.042 11.042 0 005.516 5.516l1.135-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              Entrar em Contato
            </NeonButton>
            
            <NeonButton @click="reportAd" variant="secondary" size="md">
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Denunciar Anúncio
            </NeonButton>

            <NeonButton v-if="isMyAd" @click="editAd" variant="info" size="md">
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Editar Anúncio
            </NeonButton>
            <NeonButton v-if="isMyAd && ad.status !== 'Vendido'" @click="markAsSold" variant="success" size="md">
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Marcar como Vendido
            </NeonButton>
            <NeonButton v-if="isMyAd && ad.status === 'Vendido'" @click="markAsUnsold" variant="secondary" size="md">
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Marcar como Não Vendido
            </NeonButton>
            <NeonButton v-if="isMyAd" @click="confirmDelete" variant="danger" size="md">
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Excluir Anúncio
            </NeonButton>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-black bg-opacity-75 transition-opacity" aria-hidden="true"></div>
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
          
          <div class="inline-block align-bottom bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-2 border-red-500/50">
            <div class="px-6 pt-6 pb-4">
              <div class="flex items-start">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-900/50 border-2 border-red-500">
                  <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                  <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">
                    Confirmar Exclusão
                  </h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-300">
                      Tem certeza que deseja excluir este anúncio? Esta ação não pode ser desfeita e o anúncio será removido permanentemente.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="bg-gray-800/50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-3">
              <NeonButton @click="deleteAd" variant="danger" size="md">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Excluir Anúncio
              </NeonButton>
              <NeonButton @click="cancelDelete" variant="secondary" size="md">
                Cancelar
              </NeonButton>
            </div>
          </div>
        </div>
      </div>

      <!-- Report Ad Modal -->
      <div v-if="showReportModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-black bg-opacity-75 transition-opacity" aria-hidden="true"></div>
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
          
          <div class="inline-block align-bottom bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-2 border-red-500/50">
            <div class="px-6 pt-6 pb-4">
              <div class="flex items-start">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-900/50 border-2 border-red-500">
                  <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                  <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">
                    Denunciar Anúncio
                  </h3>
                  <div class="mt-2">
                    <p class="text-sm text-gray-300">
                      Por favor, descreva o motivo da denúncia.
                    </p>
                    <textarea
                      v-model="reportReason"
                      rows="4"
                      class="mt-2 w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                      placeholder="Motivo da denúncia..."
                    ></textarea>
                    <p v-if="errors.reportReason" class="text-red-400 text-sm mt-1">{{ errors.reportReason }}</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="bg-gray-800/50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-3">
              <NeonButton @click="submitReport" variant="danger" size="md">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Enviar Denúncia
              </NeonButton>
              <NeonButton @click="cancelReport" variant="secondary" size="md">
                Cancelar
              </NeonButton>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import NeonButton from '@/components/NeonButton.vue';
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import adsService from '@/services/adsService';

const route = useRoute();
const router = useRouter();

const ad = ref(null);
const isLoading = ref(true);
const errors = ref({});
const showDeleteModal = ref(false);
const showReportModal = ref(false);
const reportReason = ref('');

const currentUserId = computed(() => {
  const user = localStorage.getItem('user');
  return user ? JSON.parse(user).id : null;
});

const isMyAd = computed(() => {
  return ad.value && ad.value.user_id === currentUserId.value;
});

async function fetchAd() {
  isLoading.value = true;
  errors.value = {};
  try {
    const response = await adsService.getAd(route.params.id);
    ad.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar anúncio:', error);
    errors.value.general = 'Não foi possível carregar o anúncio. Tente novamente mais tarde.';
  } finally {
    isLoading.value = false;
  }
}

function formatCurrency(value, currency) {
  if (value === null || value === undefined) return 'N/A';
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: currency || 'BRL',
  }).format(value);
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateString).toLocaleDateString('pt-BR', options);
}

async function contactSeller() {
  if (ad.value && ad.value.contact_info) {
    alert(`Informações de Contato: ${ad.value.contact_info}`);
    try {
      await adsService.recordContactClick(ad.value.id);
    } catch (error) {
      console.error('Erro ao registrar clique de contato:', error);
    }
  } else {
    alert('Nenhuma informação de contato disponível.');
  }
}

function reportAd() {
  showReportModal.value = true;
  reportReason.value = '';
  errors.value.reportReason = null;
}

function cancelReport() {
  showReportModal.value = false;
}

async function submitReport() {
  if (!reportReason.value) {
    errors.value.reportReason = 'O motivo da denúncia é obrigatório.';
    return;
  }
  try {
    await adsService.reportAd(ad.value.id, reportReason.value);
    alert('Anúncio denunciado com sucesso!');
    showReportModal.value = false;
  } catch (error) {
    console.error('Erro ao denunciar anúncio:', error);
    errors.value.reportReason = 'Não foi possível enviar a denúncia. Tente novamente.';
  }
}

function editAd() {
  router.push({ name: 'classifieds.edit', params: { id: ad.value.id } });
}

async function markAsSold() {
  try {
    await adsService.markAdAsSold(ad.value.id);
    ad.value.status = 'Vendido';
    alert('Anúncio marcado como vendido!');
  } catch (error) {
    console.error('Erro ao marcar como vendido:', error);
    alert('Não foi possível marcar como vendido. Tente novamente.');
  }
}

async function markAsUnsold() {
  try {
    await adsService.markAdAsUnsold(ad.value.id);
    ad.value.status = 'Ativo';
    alert('Anúncio marcado como não vendido!');
  } catch (error) {
    console.error('Erro ao marcar como não vendido:', error);
    alert('Não foi possível marcar como não vendido. Tente novamente.');
  }
}

function confirmDelete() {
  showDeleteModal.value = true;
}

function cancelDelete() {
  showDeleteModal.value = false;
}

async function deleteAd() {
  try {
    await adsService.deleteAd(ad.value.id);
    alert('Anúncio excluído com sucesso!');
    router.push({ name: 'classifieds.index' });
  } catch (error) {
    console.error('Erro ao excluir anúncio:', error);
    alert('Não foi possível excluir o anúncio. Tente novamente.');
  } finally {
    showDeleteModal.value = false;
  }
}

onMounted(() => {
  fetchAd();
});
</script>
