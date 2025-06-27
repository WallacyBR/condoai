<template>
  <AuthenticatedLayout page-title="Agendamentos de Manutenção">
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="bg-gray-800/50 backdrop-blur-md shadow-xl rounded-2xl p-6 border border-cyan-500/30">
         <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold text-white">Gerenciar Agendamentos</h2>
            <!-- Botão Novo - Usar NeonButton -->
          <button class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg shadow-md transition-colors">
            Novo Agendamento
          </button>
        </div>
        <p class="text-gray-300">
          Listagem e formulários para Agendamentos de Manutenção.
          Idealmente, esta tela seria filtrada por equipamento ou teria um seletor.
        </p>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
// Lógica para CRUD de Agendamentos será adicionada aqui
</script>
