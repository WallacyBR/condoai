<template>
  <AuthenticatedLayout page-title="Visitantes Autorizados">
    <HeaderCard
      title="Visitantes Autorizados"
      description="Visualize todos os visitantes que foram autorizados a entrar no condomínio."
      icon-path="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
      icon-color="green"
    />

    <div class="mt-6 bg-gray-900/70 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-xl p-6">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold text-white">Visitantes com Entrada Autorizada</h3>
        <div class="flex gap-3">
          <input
            v-model="searchTerm"
            @input="debouncedSearch"
            type="text"
            placeholder="Buscar por nome ou documento..."
            class="form-input bg-gray-800 border-gray-600 text-white placeholder-gray-400"
          />
          <NeonButton @click="fetchAuthorizedVisitors" :loading="loading" size="sm" variant="secondary">
            <svg class="h-4 w-4" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            <span v-if="!loading" class="ml-2">Atualizar</span>
          </NeonButton>
        </div>
      </div>

      <div v-if="loading && authorizedVisitors.length === 0" class="text-center py-10">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-400 mx-auto"></div>
        <p class="text-gray-400 mt-3">Carregando visitantes autorizados...</p>
      </div>

      <div v-if="!loading && error" class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-center">
        <p class="text-red-300">{{ error }}</p>
      </div>

      <div v-if="!loading && authorizedVisitors.length === 0 && !error" class="text-center py-10">
        <svg class="h-12 w-12 mx-auto text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <p class="text-gray-400 mt-3">Nenhum visitante autorizado no momento.</p>
      </div>

      <div v-if="authorizedVisitors.length > 0" class="space-y-4">
        <AccessRequestCard
          v-for="visitor in authorizedVisitors"
          :key="visitor.id"
          :request="visitor"
          :show-actions="true"
        />
      </div>
    </div>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '../../components/layouts/AuthenticatedLayout.vue';
import HeaderCard from '../../components/layouts/HeaderCard.vue';
import NeonButton from '../../components/NeonButton.vue';
import AccessRequestCard from '../../components/access_control/AccessRequestCard.vue';
import api from '../../utils/api';
import { useToast } from '../../composables/useToast';
import { debounce } from '../../utils/helpers';

const { errorToast } = useToast();

const authorizedVisitors = ref([]);
const loading = ref(false);
const error = ref('');
const searchTerm = ref('');

const fetchAuthorizedVisitors = async () => {
  loading.value = true;
  error.value = '';
  
  try {
    const params = {
      per_page: 50,
      status: 'authorized',
      ...(searchTerm.value && { search: searchTerm.value })
    };
    
    const response = await api.get('/access-control/access-requests', { params });
    authorizedVisitors.value = response.data?.data || [];
  } catch (e) {
    console.error("Erro ao buscar visitantes autorizados:", e);
    error.value = 'Falha ao carregar visitantes autorizados.';
    errorToast(error.value);
  } finally {
    loading.value = false;
  }
};

const debouncedSearch = debounce(() => {
  fetchAuthorizedVisitors();
}, 500);

onMounted(() => fetchAuthorizedVisitors());
</script> 