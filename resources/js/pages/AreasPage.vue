<template>
  <AuthenticatedLayout page-title="Áreas">
    <div class="space-y-6">
      <TopCard title="Áreas" :icon="AreaIcon" :subtitle="selectedCondominiumName">
        <template #actions>
          <div class="flex gap-2">
            <NeonButton 
              v-if="activeTab === 'areas'"
              @click="createNewArea()" 
              size="md"
            >
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Nova Área
            </NeonButton>
            
            <NeonButton 
              v-if="activeTab === 'bookings'"
              @click="createNewBooking()" 
              size="md"
            >
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Nova Reserva
            </NeonButton>
          </div>
        </template>
      </TopCard>

      <!-- Aviso quando nenhum condomínio está selecionado -->
      <div v-if="!selectedCondominiumName && !loading" class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4">
        <div class="flex items-center space-x-2">
          <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
          <span class="text-yellow-300 font-medium">Nenhum condomínio selecionado</span>
        </div>
        <p class="text-yellow-200 text-sm mt-1">Selecione um condomínio para visualizar áreas e reservas.</p>
      </div>

      <!-- Tabs Navigation -->
      <div v-if="selectedCondominiumName" class="border-b border-gray-700">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'areas'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === 'areas'
                ? 'border-indigo-500 text-indigo-400'
                : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300'
            ]"
          >
            <svg class="h-4 w-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            Áreas Comuns
          </button>
          <button
            @click="activeTab = 'bookings'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === 'bookings'
                ? 'border-indigo-500 text-indigo-400'
                : 'border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300'
            ]"
          >
            <svg class="h-4 w-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Reservas
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div v-if="selectedCondominiumName">
        <!-- Áreas Comuns Tab -->
        <div v-if="activeTab === 'areas'">
          <CommonAreasSection />
        </div>

              <!-- Reservas Tab -->
      <div v-if="activeTab === 'bookings'">
        <div class="space-y-6">
          <!-- Botão para ir para página de reservas -->
          <div class="text-center py-8">
            <h3 class="text-xl font-medium text-gray-300 mb-4">Gerenciar Reservas</h3>
            <p class="text-gray-400 mb-6">Visualize e gerencie todas as reservas de áreas comuns</p>
            <NeonButton @click="goToBookings">
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Ver Todas as Reservas
            </NeonButton>
          </div>
        </div>
      </div>
    </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, h } from 'vue';
import { useRouter } from 'vue-router';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import TopCard from '../components/ui/TopCard.vue';
import NeonButton from '../components/NeonButton.vue';
import CommonAreasSection from '../components/CommonAreasSection.vue';

export default {
  name: 'AreasPage',
  components: {
    AuthenticatedLayout,
    TopCard,
    NeonButton,
    CommonAreasSection
  },
  setup() {
    const router = useRouter();
    const activeTab = ref('areas');
    const loading = ref(false);

    const AreaIcon = {
      render() {
        return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 7h18M3 12h18M3 17h18' })
        ]);
      }
    };

    // Estado do condomínio selecionado
    const selectedCondominiumName = ref(localStorage.getItem('selectedCondominiumName'));

    const createNewArea = () => {
      router.push({ name: 'common-areas.new' });
    };

    const createNewBooking = () => {
      router.push({ name: 'bookings.new' });
    };

    const goToBookings = () => {
      router.push({ name: 'bookings' });
    };

    return {
      activeTab,
      loading,
      selectedCondominiumName,
      AreaIcon,
      createNewArea,
      createNewBooking,
      goToBookings
    };
  }
};
</script> 