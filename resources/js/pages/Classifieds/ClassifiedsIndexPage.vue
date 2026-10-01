<template>
  <AuthenticatedLayout page-title="Classificados">
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header Section -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-white">Classificados</h1>
          <p class="text-gray-400 mt-1">Encontre e anuncie itens para comércio com outros moradores</p>
        </div>
        <NeonButton @click="router.push({ name: 'classifieds.create' })" size="md">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Novo Anúncio
        </NeonButton>
      </div>

      <!-- Filter Section -->
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2 border-indigo-500/20">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="flex-1">
            <label for="filter-search" class="block text-sm font-medium text-gray-300 mb-2">
              Buscar anúncios
            </label>
            <input
              type="text"
              id="filter-search"
              v-model="filterSearch"
              placeholder="Título, descrição..."
              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
            />
          </div>
          <div class="flex-1">
            <label for="filter-category" class="block text-sm font-medium text-gray-300 mb-2">
              Categoria
            </label>
            <select
              id="filter-category"
              v-model="filterCategory"
              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
            >
              <option value="">Todas</option>
              <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
            </select>
          </div>
          <div class="flex-1">
            <label for="filter-condition" class="block text-sm font-medium text-gray-300 mb-2">
              Condição
            </label>
            <select
              id="filter-condition"
              v-model="filterCondition"
              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
            >
              <option value="">Todas</option>
              <option v-for="condition in conditions" :key="condition" :value="condition">{{ condition }}</option>
            </select>
          </div>
          <div class="flex items-end">
            <NeonButton @click="clearFilters" variant="secondary" size="md">
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Limpar
            </NeonButton>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex justify-center items-center py-12">
        <div class="relative">
          <div class="w-16 h-16 border-4 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin"></div>
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-8 h-8 bg-indigo-500 rounded-full animate-pulse"></div>
          </div>
        </div>
        <p class="text-gray-400 ml-4">Carregando anúncios...</p>
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
        <h3 class="text-lg font-medium text-gray-300 mb-2">Nenhum anúncio encontrado</h3>
        <p class="text-gray-500 mb-6">Não há anúncios para exibir no momento.</p>
        <NeonButton @click="router.push({ name: 'classifieds.create' })">
          Criar Primeiro Anúncio
        </NeonButton>
      </div>

      <!-- Ads Grid -->
      <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="ad in ads"
          :key="ad.id"
          class="group bg-white dark:bg-slate-900 rounded-xl shadow-sm p-5 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all cursor-pointer"
          @click="viewAd(ad.id)"
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
          <div class="flex items-center justify-between text-sm text-slate-500 dark:text-slate-400 pt-2 border-t border-slate-100 dark:border-slate-800">
            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ formatCurrency(ad.price, ad.currency) }}</span>
            <span>{{ ad.category }}</span>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > pagination.per_page && !isLoading" class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6">
        <div class="text-sm text-gray-400">
          Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} resultados
        </div>
        
        <div class="flex items-center space-x-2">
          <NeonButton
            @click="fetchAds(pagination.current_page - 1)"
            :disabled="!pagination.prev_page_url"
            variant="secondary"
            size="sm"
          >
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Anterior
          </NeonButton>
          
          <div class="flex space-x-1">
            <button
              v-for="pageNumber in simplePageRange"
              :key="pageNumber"
              @click="fetchAds(pageNumber)"
              :class="[
                'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                pageNumber === pagination.current_page
                  ? 'bg-indigo-600 text-white border-2 border-indigo-500'
                  : 'text-gray-300 hover:text-white hover:bg-gray-700 border-2 border-transparent'
              ]"
            >
              {{ pageNumber }}
            </button>
          </div>
          
          <NeonButton
            @click="fetchAds(pagination.current_page + 1)"
            :disabled="!pagination.next_page_url"
            variant="secondary"
            size="sm"
          >
            Próximo
            <svg class="h-4 w-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </NeonButton>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import NeonButton from '@/components/NeonButton.vue';
import { ref, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import adsService from '@/services/adsService';

const router = useRouter();

const ads = ref([]);
const isLoading = ref(true);
const errors = ref({});
const filterSearch = ref('');
const filterCategory = ref('');
const filterCondition = ref('');

const categories = ref([
  'Eletrônicos', 'Móveis', 'Serviços', 'Veículos', 'Imóveis', 'Outros'
]);
const conditions = ref([
  'Novo', 'Usado - Como Novo', 'Usado - Bom Estado', 'Usado - Com Marcas'
]);

const pagination = ref({
    current_page: 1,
    data: [],
    first_page_url: '',
    from: 0,
    last_page: 1,
    last_page_url: '',
    links: [],
    next_page_url: null,
    path: '',
    per_page: 15,
    prev_page_url: null,
    to: 0,
    total: 0,
});

async function fetchAds(page = 1) {
  isLoading.value = true;
  errors.value = {};
  try {
    const params = { page, per_page: pagination.value.per_page };
    if (filterSearch.value) {
      params.search = filterSearch.value;
    }
    if (filterCategory.value) {
      params.category = filterCategory.value;
    }
    if (filterCondition.value) {
      params.condition = filterCondition.value;
    }
    const response = await adsService.getAllAds(params);
    ads.value = response.data.data;
    pagination.value = response.data;
  } catch (error) {
    console.error('Erro ao buscar anúncios:', error);
    errors.value.general = 'Não foi possível carregar os anúncios. Tente novamente mais tarde.';
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

function viewAd(id) {
  router.push({ name: 'classifieds.show', params: { id } });
}

function clearFilters() {
    filterSearch.value = '';
    filterCategory.value = '';
    filterCondition.value = '';
    fetchAds(1);
}

const simplePageRange = computed(() => {
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;
    const delta = 2;
    const left = current - delta;
    const right = current + delta + 1;
    const range = [];
    const rangeWithDots = [];
    let l;

    for (let i = 1; i <= last; i++) {
        if (i === 1 || i === last || (i >= left && i < right)) {
            range.push(i);
        }
    }

    for (let i of range) {
        if (l) {
            if (i - l === 2) {
                rangeWithDots.push(l + 1);
            } else if (i - l !== 1) {
                rangeWithDots.push('...');
            }
        }
        rangeWithDots.push(i);
        l = i;
    }
    return rangeWithDots.filter(p => p !== '...');
});

watch([filterSearch, filterCategory, filterCondition], () => {
  // TODO: Adicionar debounce
  fetchAds(1);
});

onMounted(() => {
  fetchAds();
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
