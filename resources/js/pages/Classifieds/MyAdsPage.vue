<template>
  <AuthenticatedLayout page-title="Meus Anúncios">
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header Section -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-white">Meus Anúncios</h1>
          <p class="text-gray-400 mt-1">Gerencie seus anúncios e veja suas estatísticas</p>
        </div>
        <NeonButton @click="router.push({ name: 'classifieds.create' })" size="md">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Novo Anúncio
        </NeonButton>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex justify-center items-center py-12">
        <div class="relative">
          <div class="w-16 h-16 border-4 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin"></div>
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-8 h-8 bg-indigo-500 rounded-full animate-pulse"></div>
          </div>
        </div>
        <p class="text-gray-400 ml-4">Carregando seus anúncios...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="errors.general" class="bg-gradient-to-r from-red-900/50 to-red-800/50 border border-red-500/50 rounded-2xl p-6">
        <div class="flex items-center">
          <svg class="h-6 w-6 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-red-300">{{ errors.general }}</p>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="ads.length === 0" class="text-center py-16">
        <div class="mx-auto h-24 w-24 text-gray-600 mb-4">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-300 mb-2">Nenhum anúncio seu encontrado</h3>
        <p class="text-gray-500 mb-6">Você ainda não publicou nenhum anúncio.</p>
        <NeonButton @click="router.push({ name: 'classifieds.create' })">
          Criar Primeiro Anúncio
        </NeonButton>
      </div>

      <!-- Ads Grid -->
      <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="ad in ads"
          :key="ad.id"
          class="group bg-white dark:bg-slate-900 rounded-xl shadow-sm p-5 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all"
        >
          <img v-if="ad.images.length > 0" :src="`/storage/${ad.images[0].image_path}`" alt="Ad Image" class="w-full h-48 object-cover rounded-lg mb-4" />
          <div v-else class="w-full h-48 bg-slate-100 dark:bg-slate-800 rounded-lg mb-4 flex items-center justify-center text-slate-400 text-sm">
            Sem Imagem
          </div>
          <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-2 line-clamp-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
            {{ ad.title }}
          </h3>
          <p class="text-slate-600 dark:text-slate-400 text-sm mb-4 line-clamp-3">
            {{ ad.description }}
          </p>
          <div class="flex items-center justify-between text-sm text-slate-500 dark:text-slate-400 mb-4">
            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ formatCurrency(ad.price, ad.currency) }}</span>
            <span :class="statusClass(ad.status)">{{ ad.status }}</span>
          </div>

          <!-- Statistics -->
          <div class="grid grid-cols-2 gap-2 text-xs text-gray-400 mb-4">
            <div class="flex items-center"><svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg> {{ ad.views_count }} Visualizações</div>
            <div class="flex items-center"><svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.135a11.042 11.042 0 005.516 5.516l1.135-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg> {{ ad.contact_clicks_count }} Contatos</div>
            <div class="flex items-center"><svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> {{ ad.reported_count }} Denúncias</div>
            <div class="flex items-center"><svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> {{ ad.status === 'Vendido' ? 'Vendido' : 'Não Vendido' }}</div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-700">
            <NeonButton @click.stop="editAd(ad.id)" variant="info" size="sm">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </NeonButton>
            <NeonButton v-if="ad.status !== 'Vendido'" @click.stop="markAsSold(ad.id)" variant="success" size="sm">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </NeonButton>
            <NeonButton v-else @click.stop="markAsUnsold(ad.id)" variant="secondary" size="sm">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </NeonButton>
            <NeonButton @click.stop="confirmDelete(ad.id)" variant="danger" size="sm">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
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
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import NeonButton from '@/components/NeonButton.vue';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import adsService from '@/services/adsService';

const router = useRouter();

const ads = ref([]);
const isLoading = ref(true);
const errors = ref({});
const showDeleteModal = ref(false);
const adToDeleteId = ref(null);

async function fetchMyAds() {
  isLoading.value = true;
  errors.value = {};
  try {
    const response = await adsService.getMyAds();
    ads.value = response.data.data.map(ad => ({
      ...ad,
      contact_clicks_count: 0, // Default, will be updated by fetchAdStatistics
    }));
    // Fetch statistics for each ad
    for (const ad of ads.value) {
      try {
        const statsResponse = await adsService.getAdStatistics(ad.id);
        ad.views_count = statsResponse.data.total_views;
        ad.reported_count = statsResponse.data.total_reports;
        ad.contact_clicks_count = statsResponse.data.contact_clicks;
        ad.status = statsResponse.data.status;
      } catch (statError) {
        console.error(`Erro ao buscar estatísticas para o anúncio ${ad.id}:`, statError);
      }
    }
  } catch (error) {
    console.error('Erro ao buscar meus anúncios:', error);
    errors.value.general = 'Não foi possível carregar seus anúncios. Tente novamente mais tarde.';
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

function statusClass(status) {
  switch (status) {
    case 'Ativo': return 'text-green-400';
    case 'Pausado': return 'text-yellow-400';
    case 'Vendido': return 'text-red-400';
    case 'Excluído': return 'text-gray-400';
    default: return '';
  }
}

function editAd(id) {
  router.push({ name: 'classifieds.edit', params: { id } });
}

async function markAsSold(id) {
  try {
    await adsService.markAdAsSold(id);
    alert('Anúncio marcado como vendido!');
    fetchMyAds(); // Refresh list
  } catch (error) {
    console.error('Erro ao marcar como vendido:', error);
    alert('Não foi possível marcar como vendido. Tente novamente.');
  }
}

async function markAsUnsold(id) {
  try {
    await adsService.markAdAsUnsold(id);
    alert('Anúncio marcado como não vendido!');
    fetchMyAds(); // Refresh list
  } catch (error) {
    console.error('Erro ao marcar como não vendido:', error);
    alert('Não foi possível marcar como não vendido. Tente novamente.');
  }
}

function confirmDelete(id) {
  adToDeleteId.value = id;
  showDeleteModal.value = true;
}

function cancelDelete() {
  showDeleteModal.value = false;
  adToDeleteId.value = null;
}

async function deleteAd() {
  if (!adToDeleteId.value) return;
  try {
    await adsService.deleteAd(adToDeleteId.value);
    alert('Anúncio excluído com sucesso!');
    showDeleteModal.value = false;
    fetchMyAds(); // Refresh list
  } catch (error) {
    console.error('Erro ao excluir anúncio:', error);
    alert('Não foi possível excluir o anúncio. Tente novamente.');
  } finally {
    adToDeleteId.value = null;
  }
}

onMounted(() => {
  fetchMyAds();
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
