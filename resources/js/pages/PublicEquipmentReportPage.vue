<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-gray-200 p-4 sm:p-6 lg:p-8 flex items-center justify-center">
    <div class="w-full max-w-2xl">
      <div v-if="loading" class="text-center">
        <svg class="animate-spin h-10 w-10 text-cyan-400 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-lg">Carregando relatório do equipamento...</p>
      </div>

      <div v-if="error" class="bg-red-800/50 border border-red-500/30 p-6 rounded-2xl shadow-xl text-center">
        <h2 class="text-2xl font-semibold text-red-300 mb-3">Erro ao Carregar Relatório</h2>
        <p class="text-red-200">{{ error }}</p>
        <button @click="goHome" class="mt-6 px-6 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg shadow-md transition-colors">
          Voltar
        </button>
      </div>

      <div v-if="!loading && !error && reportData" class="bg-gray-800/60 backdrop-blur-xl shadow-2xl rounded-3xl p-6 sm:p-8 border-2 border-cyan-500/30">
        <div class="text-center mb-8">
          <img :src="logoImage" alt="Logo Condomínio" class="h-16 mx-auto mb-4" @error="handleLogoError"/> <!-- Idealmente o logo do condomínio específico -->
          <h1 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-pink-500">
            Relatório do Equipamento
          </h1>
          <p class="text-gray-400 text-sm mt-1">Condomínio: {{ reportData.equipment?.condominium_name }}</p>
        </div>

        <div class="space-y-6">
          <!-- Detalhes do Equipamento -->
          <section class="bg-gray-700/30 p-4 rounded-xl border border-gray-600/50">
            <h2 class="text-xl font-semibold text-cyan-400 mb-3">Detalhes do Equipamento</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
              <p><strong>Nome:</strong> {{ reportData.equipment?.name }}</p>
              <p><strong>Área:</strong> {{ reportData.equipment?.maintenance_area_name }}</p>
              <p class="sm:col-span-2"><strong>Localização:</strong> {{ reportData.equipment?.location_details || 'N/A' }}</p>
              <p class="sm:col-span-2"><strong>Descrição:</strong> {{ reportData.equipment?.description || 'N/A' }}</p>
            </div>
          </section>

          <!-- Próxima Manutenção Agendada -->
          <section v-if="reportData.next_scheduled_maintenance" class="bg-gray-700/30 p-4 rounded-xl border border-gray-600/50">
            <h2 class="text-xl font-semibold text-yellow-400 mb-3">Próxima Manutenção Agendada</h2>
            <p><strong>Título:</strong> {{ reportData.next_scheduled_maintenance.title }}</p>
            <p><strong>Data Prevista:</strong> {{ reportData.next_scheduled_maintenance.scheduled_date }}
               <span v-if="reportData.next_scheduled_maintenance.scheduled_time">às {{ reportData.next_scheduled_maintenance.scheduled_time }}</span>
            </p>
            <p><strong>Status Atual:</strong> <span class="font-semibold text-yellow-300">{{ reportData.next_scheduled_maintenance.status }}</span></p>
          </section>
           <section v-else class="bg-gray-700/30 p-4 rounded-xl border border-gray-600/50">
            <h2 class="text-xl font-semibold text-gray-400 mb-3">Próxima Manutenção Agendada</h2>
            <p class="text-gray-300">Nenhuma manutenção pendente encontrada.</p>
          </section>

          <!-- Últimas Manutenções Realizadas -->
          <section class="bg-gray-700/30 p-4 rounded-xl border border-gray-600/50">
            <h2 class="text-xl font-semibold text-green-400 mb-3">Últimas Manutenções Realizadas (Máx. 5)</h2>
            <div v-if="reportData.last_maintenances && reportData.last_maintenances.length > 0" class="space-y-3">
              <div v-for="record in reportData.last_maintenances" :key="record.id" class="text-sm p-3 bg-gray-600/30 rounded-lg border border-gray-500/50">
                <p><strong>Data:</strong> {{ formatDate(record.performed_at) }} (Por: {{ record.user?.name || 'N/A' }})</p>
                <p class="mt-1"><strong>Notas:</strong> {{ record.notes || 'Sem notas.' }}</p>
              </div>
            </div>
            <p v-else class="text-gray-300">Nenhum registro de manutenção concluída encontrado.</p>
          </section>
        </div>
         <p class="text-center text-xs text-gray-500 mt-8">QR Code gerado por PrediHub &copy; {{ new Date().getFullYear() }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/utils/api'; // Supondo que api.js lida com base URL e token se necessário
import logoImage from '../../images/predihub_quadrado.png';

const route = useRoute();
const router = useRouter();
const qrCodeHash = ref(route.params.hash);
const reportData = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchReportData = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await api.get(`/qr/${qrCodeHash.value}`);
    reportData.value = response.data;
  } catch (err) {
    console.error("Erro ao buscar relatório do QR Code:", err);
    if (err.response) {
      error.value = err.response.data.message || 'Falha ao carregar os dados.';
      if (err.response.status === 401) { // Não autenticado para relatório restrito
         // Idealmente, redirecionar para login com redirect para esta página após login
         // router.push({ name: 'login', query: { redirect: route.fullPath } });
         error.value = 'Acesso restrito. Por favor, faça login para visualizar este relatório.';
      }
    } else {
      error.value = 'Não foi possível conectar ao servidor.';
    }
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' };
  return new Date(dateString).toLocaleDateString('pt-BR', options);
};

const goHome = () => {
  router.push('/'); // Ou para a página de login, dependendo do contexto
};

const handleLogoError = (event) => {
  console.warn('Erro ao carregar logo:', event.target.src);
  // Fallback: esconder imagem ou usar texto
  event.target.style.display = 'none';
};

onMounted(() => {
  if (qrCodeHash.value) {
    fetchReportData();
  } else {
    error.value = "Hash do QR Code não fornecido.";
    loading.value = false;
  }
});
</script>

<style scoped>
/* Estilos adicionais podem ser colocados aqui, se necessário */
</style>
