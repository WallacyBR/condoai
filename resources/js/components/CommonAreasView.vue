<template>
  <AuthenticatedLayout page-title="Áreas Comuns">
    <div class="space-y-6">
      <TopCard title="Áreas Comuns" :icon="AreaIcon" :subtitle="selectedCondominiumName">
        <template #actions>
          <AppButton @click="createNewArea()" size="sm" variant="primary">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nova Área Comum
          </AppButton>
        </template>
      </TopCard>

      <!-- Aviso quando nenhum condomínio está selecionado -->
      <div v-if="!selectedCondominiumName && !loading" class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-xl p-4">
        <div class="flex items-center space-x-2">
          <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
          <span class="text-amber-900 dark:text-amber-200 font-medium">Nenhum condomínio selecionado</span>
        </div>
        <p class="text-amber-700 dark:text-amber-300 text-sm mt-1">Selecione um condomínio para visualizar as áreas comuns.</p>
      </div>

      <!-- Estatísticas Modernas -->
      <div v-if="selectedCondominiumName && areas.length > 0" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <StatsCard
          label="Total de Áreas"
          :value="areas.length"
          variant="default"
          :default-icon-path="'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'"
        />
        <StatsCard
          label="Áreas Ativas"
          :value="activeAreas"
          variant="green"
          :default-icon-path="'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'"
        />
        <StatsCard
          label="Com Preço"
          :value="areasWithPrice"
          variant="orange"
          :default-icon-path="'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1'"
        />
        <StatsCard
          label="Capacidade Total"
          :value="totalCapacity"
          variant="primary"
          :default-icon-path="'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'"
        />
      </div>

      <!-- Filtros e Busca -->
      <div v-if="selectedCondominiumName" class="flex flex-wrap gap-3 items-center">
        <select
          v-model="filter"
          class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
        >
          <option value="all">Todas as áreas</option>
          <option value="active">Ativas</option>
          <option value="inactive">Inativas</option>
        </select>
        
        <div class="relative flex-1 sm:w-64">
          <input
            type="text"
            v-model="search"
            placeholder="Buscar áreas..."
            class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm"
          />
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="flex items-center space-x-3">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <span class="text-slate-500 dark:text-slate-400 text-sm">Carregando áreas comuns...</span>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/50 rounded-xl p-4 text-rose-700 dark:text-rose-300 text-sm">
        <div class="flex items-center space-x-2">
          <svg class="h-5 w-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>{{ error }}</span>
        </div>
      </div>

      <!-- Empty State -->
      <EmptyState
        v-else-if="filteredAreas.length === 0 && selectedCondominiumName"
        :title="search ? 'Nenhuma área encontrada' : 'Nenhuma área comum cadastrada'"
        :description="search ? 'Tente ajustar sua busca ou filtro.' : 'Use o botão \'Nova Área Comum\' no topo da página para começar.'"
      >
        <template #actions v-if="!search">
          <AppButton @click="createNewArea()" variant="primary">
            Nova Área Comum
          </AppButton>
        </template>
      </EmptyState>

      <!-- Grid de Áreas Comuns -->
      <div v-else-if="selectedCondominiumName" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="area in filteredAreas" 
          :key="area.id" 
          class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-colors overflow-hidden flex flex-col justify-between"
        >
          <div>
            <!-- Imagem da área -->
            <div class="relative h-44 bg-slate-100 dark:bg-slate-800">
              <img 
                v-if="area.main_photo" 
                :src="getImageUrl(area.main_photo)" 
                class="w-full h-full object-cover" 
                :alt="area.name"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <svg class="h-12 w-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              
              <!-- Status badge -->
              <div class="absolute top-3 right-3">
                <span class="px-2.5 py-0.5 text-xs font-medium rounded-full"
                      :class="area.active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/80 dark:text-emerald-300 dark:border-emerald-800/60' : 'bg-slate-100 text-slate-700 border border-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'">
                  {{ area.active ? 'Ativa' : 'Inativa' }}
                </span>
              </div>

              <!-- Preço badge -->
              <div v-if="area.price_per_hour" class="absolute top-3 left-3">
                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-white/90 dark:bg-slate-900/90 text-slate-900 dark:text-slate-100 border border-slate-200 dark:border-slate-700 shadow-sm backdrop-blur-sm">
                  R$ {{ formatPrice(area.price_per_hour) }}/h
                </span>
              </div>
            </div>

            <!-- Conteúdo do card -->
            <div class="p-5">
              <!-- Header -->
              <div class="mb-3">
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ area.name }}</h3>
                <p v-if="area.description" class="text-slate-500 dark:text-slate-400 text-xs mt-1 line-clamp-2">{{ area.description }}</p>
              </div>

              <!-- Informações -->
              <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/40 rounded-lg p-3 border border-slate-100 dark:border-slate-800">
                <!-- Capacidade -->
                <div class="flex items-center justify-between">
                  <span class="text-slate-500">Capacidade:</span>
                  <span class="font-medium text-slate-800 dark:text-slate-200">{{ area.capacity }} pessoas</span>
                </div>

                <!-- Tempo de reserva -->
                <div class="flex items-center justify-between">
                  <span class="text-slate-500">Duração:</span>
                  <span class="font-medium text-slate-800 dark:text-slate-200">{{ area.min_booking_hours }}h a {{ area.max_booking_hours }}h</span>
                </div>

                <!-- Aprovação necessária -->
                <div v-if="area.requires_approval" class="flex items-center justify-between text-amber-700 dark:text-amber-300">
                  <span>Aprovação:</span>
                  <span class="font-medium">Obrigatória</span>
                </div>

                <!-- Número de reservas -->
                <div class="flex items-center justify-between">
                  <span class="text-slate-500">Reservas:</span>
                  <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ area.bookings ? area.bookings.length : 0 }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Botões de ação -->
          <div class="p-5 pt-0 flex space-x-2">
            <AppButton 
              @click="editArea(area)"
              variant="secondary"
              size="sm"
              class="flex-1"
            >
              <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Editar
            </AppButton>
            
            <AppButton 
              @click="viewAreaCalendar(area)"
              variant="secondary"
              size="sm"
              class="flex-1"
            >
              <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Reservas
            </AppButton>

            <button 
              @click="deleteArea(area)"
              class="p-2 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg border border-rose-200 dark:border-rose-800/40 transition-colors"
              title="Excluir Área"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v1M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Modal do calendário -->
      <div v-if="showCalendar && selectedArea" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeCalendar"></div>
          </div>

          <div class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-xl text-left overflow-hidden shadow-2xl border border-slate-200 dark:border-slate-800 transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="relative p-6">
              <button
                @click="closeCalendar"
                class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>

              <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-4">
                Reservas - {{ selectedArea.name }}
              </h3>
              
              <CommonAreaCalendar :area="selectedArea" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, computed, onMounted, h } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import NeonButton from './NeonButton.vue';
import AppButton from './ui/AppButton.vue';
import CommonAreaCalendar from './CommonAreaCalendar.vue';
import TopCard from './ui/TopCard.vue';
import StatsCard from './ui/StatsCard.vue';
import EmptyState from './ui/EmptyState.vue';
import { apiRequest } from '../utils/api';

export default {
  name: 'CommonAreasView',
  components: {
    AuthenticatedLayout,
    NeonButton,
    AppButton,
    CommonAreaCalendar,
    TopCard,
    StatsCard,
    EmptyState,
  },
  setup() {
    const router = useRouter();
    const areas = ref([]);
    const loading = ref(true);
    const error = ref('');
    const search = ref('');
    const filter = ref('all');
    const showCalendar = ref(false);
    const selectedArea = ref(null);

    const AreaIcon = {
      render() {
        return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 7h18M3 12h18M3 17h18' })
        ]);
      }
    };

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

    const createNewArea = () => {
      router.push({ name: 'common-areas.new' });
    };

    const editArea = (area) => {
      router.push({ name: 'common-areas.edit', params: { id: area.id } });
    };

    const viewAreaCalendar = (area) => {
      selectedArea.value = area;
      showCalendar.value = true;
    };

    const closeCalendar = () => {
      showCalendar.value = false;
      selectedArea.value = null;
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
      showCalendar,
      selectedArea,
      selectedCondominiumName,
      filteredAreas,
      activeAreas,
      areasWithPrice,
      totalCapacity,
      createNewArea,
      editArea,
      viewAreaCalendar,
      closeCalendar,
      deleteArea,
      getImageUrl,
      formatPrice,
      AreaIcon
    };
  }
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
