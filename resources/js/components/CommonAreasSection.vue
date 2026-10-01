<template>
  <div class="space-y-6">
    <!-- Header com filtros -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h2 class="text-xl font-bold text-white">Áreas Comuns</h2>
        <p class="text-gray-400">{{ selectedCondominiumName }}</p>
      </div>
      
      <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
        <!-- Filtros -->
        <div class="flex gap-2">
          <select
            v-model="filter"
            class="px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          >
            <option value="all">Todas</option>
            <option value="active">Ativas</option>
            <option value="inactive">Inativas</option>
          </select>
          
          <div class="relative">
            <input
              type="text"
              v-model="search"
              placeholder="Buscar áreas..."
              class="w-48 pl-10 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
            />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Estatísticas -->
    <div v-if="areas.length > 0" class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-4 border border-gray-700/50">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm">Total de Áreas</p>
            <p class="text-2xl font-bold text-white">{{ areas.length }}</p>
          </div>
          <div class="h-10 w-10 rounded-full bg-blue-500/20 flex items-center justify-center">
            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-4 border border-gray-700/50">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm">Áreas Ativas</p>
            <p class="text-2xl font-bold text-green-400">{{ activeAreas }}</p>
          </div>
          <div class="h-10 w-10 rounded-full bg-green-500/20 flex items-center justify-center">
            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-4 border border-gray-700/50">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm">Com Preço</p>
            <p class="text-2xl font-bold text-yellow-400">{{ areasWithPrice }}</p>
          </div>
          <div class="h-10 w-10 rounded-full bg-yellow-500/20 flex items-center justify-center">
            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl p-4 border border-gray-700/50">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm">Capacidade Total</p>
            <p class="text-2xl font-bold text-purple-400">{{ totalCapacity }}</p>
          </div>
          <div class="h-10 w-10 rounded-full bg-purple-500/20 flex items-center justify-center">
            <svg class="h-5 w-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="flex items-center space-x-3">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        <span class="text-gray-400">Carregando áreas comuns...</span>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-red-300">
      <div class="flex items-center space-x-2">
        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ error }}</span>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredAreas.length === 0" class="text-center py-12">
      <div class="max-w-md mx-auto">
        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <h3 class="text-xl font-medium text-gray-300 mb-2">
          {{ search ? 'Nenhuma área encontrada' : 'Nenhuma área comum cadastrada' }}
        </h3>
        <p class="text-gray-500">
          {{ search ? 'Tente ajustar sua busca' : 'Use o botão "Nova Área" no topo da página para começar' }}
        </p>
      </div>
    </div>

    <!-- Grid de Áreas Comuns -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="area in filteredAreas" 
        :key="area.id" 
        class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all overflow-hidden"
      >
        <!-- Imagem da área -->
        <div class="relative h-48 bg-gray-800">
          <img 
            v-if="area.main_photo" 
            :src="getImageUrl(area.main_photo)" 
            class="w-full h-full object-cover" 
            :alt="area.name"
          />
          <div v-else class="w-full h-full flex items-center justify-center">
            <svg class="h-16 w-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          
          <!-- Status badge -->
          <div class="absolute top-3 right-3">
            <span class="px-2 py-1 text-xs font-semibold rounded-full"
                  :class="area.active ? 'bg-green-500/20 text-green-300 border border-green-500/30' : 'bg-red-500/20 text-red-300 border border-red-500/30'">
              {{ area.active ? 'Ativa' : 'Inativa' }}
            </span>
          </div>

          <!-- Preço badge -->
          <div v-if="area.price_per_hour" class="absolute top-3 left-3">
            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-500/20 text-yellow-300 border border-yellow-500/30">
              R$ {{ formatPrice(area.price_per_hour) }}/h
            </span>
          </div>
        </div>

        <!-- Conteúdo do card -->
        <div class="p-6 space-y-4">
          <!-- Título e descrição -->
          <div>
            <h3 class="text-xl font-bold text-white mb-2 line-clamp-1">{{ area.name }}</h3>
            <p class="text-gray-400 text-sm line-clamp-2">{{ area.description || 'Sem descrição' }}</p>
          </div>

          <!-- Informações adicionais -->
          <div class="space-y-2">
            <!-- Capacidade -->
            <div class="flex items-center text-gray-300">
              <svg class="h-4 w-4 mr-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span class="text-sm">Capacidade: {{ area.capacity || 0 }} pessoas</span>
            </div>

            <!-- Requer aprovação -->
            <div v-if="area.requires_approval" class="flex items-center text-yellow-300">
              <svg class="h-4 w-4 mr-2 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              <span class="text-sm">Requer aprovação</span>
            </div>

            <!-- Número de reservas -->
            <div class="flex items-center text-gray-300">
              <svg class="h-4 w-4 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span class="text-sm">{{ area.bookings ? area.bookings.length : 0 }} reservas</span>
            </div>
          </div>

          <!-- Botões de ação -->
          <div class="flex space-x-2">
            <NeonButton 
              @click="editArea(area)"
              variant="secondary"
              size="sm"
              class="flex-1"
            >
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Editar
            </NeonButton>
            
            <NeonButton 
              @click="viewAreaCalendar(area)"
              size="sm"
              class="flex-1"
            >
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Reservas
            </NeonButton>

            <button 
              @click="deleteArea(area)"
              class="px-3 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-300 rounded-lg border border-red-500/30 hover:border-red-500/50 transition-colors text-sm"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H9a1 1 0 00-1 1v1M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import NeonButton from './NeonButton.vue';
import { apiRequest } from '../utils/api';

export default {
  name: 'CommonAreasSection',
  components: {
    NeonButton
  },
  setup() {
    const router = useRouter();
    const areas = ref([]);
    const loading = ref(true);
    const error = ref('');
    const search = ref('');
    const filter = ref('all');

    const selectedCondominiumName = ref(localStorage.getItem('selectedCondominiumName') || 'Condomínio atual');

    // Computed properties
    const filteredAreas = computed(() => {
      return areas.value.filter(area => {
        const matchesSearch = area.name.toLowerCase().includes(search.value.toLowerCase()) ||
                            (area.description && area.description.toLowerCase().includes(search.value.toLowerCase()));
        
        const matchesFilter = filter.value === 'all' || 
                            (filter.value === 'active' && area.active) ||
                            (filter.value === 'inactive' && !area.active);
        
        return matchesSearch && matchesFilter;
      });
    });

    const activeAreas = computed(() => {
      return areas.value.filter(area => area.active).length;
    });

    const areasWithPrice = computed(() => {
      return areas.value.filter(area => area.price_per_hour && area.price_per_hour > 0).length;
    });

    const totalCapacity = computed(() => {
      return areas.value.reduce((total, area) => total + (area.capacity || 0), 0);
    });

    // Funções
    const loadAreas = async () => {
      try {
        loading.value = true;
        error.value = '';
        
        const data = await apiRequest('/common-areas');
        areas.value = Array.isArray(data) ? data : (data?.data || []);
      } catch (e) {
        error.value = e.message || 'Erro ao carregar áreas comuns';
      } finally {
        loading.value = false;
      }
    };

    const editArea = (area) => {
      router.push({ name: 'common-areas.edit', params: { id: area.id } });
    };

    const viewAreaCalendar = (area) => {
      router.push({ name: 'common-areas.bookings', params: { id: area.id } });
    };

    const deleteArea = async (area) => {
      if (!confirm(`Tem certeza que deseja excluir a área "${area.name}"?`)) {
        return;
      }

      try {
        await apiRequest(`/common-areas/${area.id}`, { method: 'DELETE' });
        await loadAreas();
      } catch (e) {
        error.value = e.message || 'Erro ao excluir área comum';
      }
    };

    const getImageUrl = (image) => {
      if (!image) return '';
      if (image.startsWith('data:')) return image;
      if (image.startsWith('common-areas/')) return `/storage/${image}`;
      return image;
    };

    const formatPrice = (price) => {
      return Number(price).toFixed(2).replace('.', ',');
    };

    // Carregar dados ao montar o componente
    onMounted(() => {
      loadAreas();
    });

    return {
      areas,
      loading,
      error,
      search,
      filter,
      selectedCondominiumName,
      filteredAreas,
      activeAreas,
      areasWithPrice,
      totalCapacity,
      editArea,
      viewAreaCalendar,
      deleteArea,
      getImageUrl,
      formatPrice
    };
  }
};
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
