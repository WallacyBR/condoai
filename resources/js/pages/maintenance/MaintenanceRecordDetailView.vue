<template>
  <AuthenticatedLayout page-title="Detalhes do Registro de Manutenção">
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="bg-gray-800/50 backdrop-blur-md shadow-xl rounded-2xl p-6 border border-cyan-500/30">
        <h2 class="text-xl font-semibold text-white mb-6">Detalhes do Registro #{{ recordId }}</h2>
        <div class="space-y-4 text-gray-300">
          <p><strong>Equipamento:</strong> Nome do Equipamento</p>
          <p><strong>Agendamento:</strong> Título do Agendamento</p>
          <p><strong>Realizado por:</strong> Nome do Usuário</p>
          <p><strong>Data da Realização:</strong> DD/MM/YYYY HH:MM</p>
          <p><strong>Status:</strong> <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-600 text-green-100">Completo</span></p>
          <div>
            <h3 class="text-md font-semibold text-gray-200 mt-4 mb-2">Notas:</h3>
            <p class="whitespace-pre-wrap p-3 bg-gray-700/50 rounded-md border border-gray-600">
              Conteúdo das notas da manutenção aqui...
            </p>
          </div>
          <!-- Fotos e Assinatura seriam exibidas aqui -->
          <div class="mt-6 flex space-x-3">
            <button class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg shadow-md">Reabrir</button>
            <button class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow-md">Para Revisão</button>
            <button class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg shadow-md">Arquivar</button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import { useRoute } from 'vue-router';
import { ref, onMounted } from 'vue';

const route = useRoute();
const recordId = ref(route.params.id);

onMounted(() => {
  // Carregar dados do registro aqui
  console.log(`Carregando detalhes para o registro ID: ${recordId.value}`);
});
</script>
