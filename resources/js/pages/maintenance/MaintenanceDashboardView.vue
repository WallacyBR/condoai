<template>
  <AuthenticatedLayout page-title="Dashboard de Manutenção">
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="bg-gray-800/50 backdrop-blur-md shadow-xl rounded-2xl p-6 border border-cyan-500/30">
        <h2 class="text-xl font-semibold text-white mb-4">Visão Geral da Manutenção</h2>
        <p class="text-gray-300">
          Conteúdo do Dashboard de Manutenção aqui... Cards com estatísticas, etc.
        </p>
        <!-- Exemplo de Card de Estatística (a ser componentizado) -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="stats-blue p-5 rounded-xl shadow-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-blue-300">Manutenções Vencidas</p>
                <p class="text-3xl font-bold text-blue-100">0</p>
              </div>
              <div class="h-12 w-12 rounded-full bg-blue-500/20 flex items-center justify-center">
                <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              </div>
            </div>
          </div>
          <!-- Outros cards -->
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
// Lógica do dashboard será adicionada aqui (chamadas API, etc.)
</script>

<style scoped>
/* Estilos do DESIGN_MANUAL.md para stats cards */
.stats-blue {
  background: linear-gradient(to bottom right, rgba(29, 78, 216, 0.5), rgba(30, 64, 175, 0.5), rgba(29, 78, 216, 0.5)); /* from-blue-900/50 via-blue-800/50 to-blue-900/50 */
  border: 1px solid rgba(59, 130, 246, 0.3); /* border-blue-500/30 */
}
.stats-blue .text-blue-300 { color: #93c5fd; }
.stats-blue .text-blue-100 { color: #dbeafe; }
.stats-blue .bg-blue-500\/20 { background-color: rgba(59, 130, 246, 0.2); }
.stats-blue .text-blue-400 { color: #60a5fa; }
</style>
