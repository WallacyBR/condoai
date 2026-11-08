<template>
  <AuthenticatedLayout page-title="Dashboard">
    <!-- Header sutil e métricas operacionais -->
    <div class="mb-6">
      <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Visão Geral</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Indicadores operacionais e resumo em tempo real do condomínio.</p>
    </div>

    <!-- Stats Cards Sóbrios e Consistentes -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
      <StatsCard
        label="Total de Unidades"
        :value="condoStats.apartments_count"
        variant="primary"
      />
      <StatsCard
        label="Total de Moradores"
        :value="condoStats.residents_count"
        variant="green"
      />
      <StatsCard
        label="Vagas de Garagem"
        :value="condoStats.parking_spots_count"
        variant="blue"
      />
      <StatsCard
        label="Chamados Abertos"
        value="0"
        variant="orange"
      />
    </div>
  </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import StatsCard from './ui/StatsCard.vue';
import { ref, onMounted, onUnmounted, h } from 'vue';
import { useRouter } from 'vue-router';
import api from '../utils/api';
import { useTenantStore } from '../stores/tenant';
import { CONDOMINIUM_SELECTED_EVENT } from '../utils/tenant';

export default {
  name: 'DashboardView',
  components: {
    AuthenticatedLayout,
    StatsCard
  },
  setup() {
    const router = useRouter();
    const tenantStore = useTenantStore();
    const selectedCondoName = ref(tenantStore.selectedName);
    const BuildingIcon = {
      render() {
        return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' })
        ]);
      }
    };
    const condoStats = ref({
      apartments_count: 0,
      residents_count: 0,
      parking_spots_count: 0,
    });

    const fetchCondoStats = async () => {
      const condoId = tenantStore.selectedId;
      if (condoId) {
        try {
          const response = await api.get(`/condominiums/${condoId}`);
          condoStats.value = response.data;
        } catch (error) {
          console.error('Erro ao buscar estatísticas do condomínio:', error);
        }
      }
    };

    const updateSelectedCondo = () => {
      tenantStore.syncFromStorage();
      selectedCondoName.value = tenantStore.selectedName;
      fetchCondoStats();
    };

    onMounted(fetchCondoStats);
    window.addEventListener(CONDOMINIUM_SELECTED_EVENT, updateSelectedCondo);
    onUnmounted(() => {
      window.removeEventListener(CONDOMINIUM_SELECTED_EVENT, updateSelectedCondo);
    });

    const changeCondominium = () => {
      tenantStore.clear();
      router.push('/condominium-select');
    };

    return {
      selectedCondoName,
      condoStats,
      changeCondominium,
      BuildingIcon
    };
  }
};
</script>
