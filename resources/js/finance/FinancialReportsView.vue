<template>
  <AuthenticatedLayout page-title="Relatórios Financeiros">
    <div class="space-y-6">
      <TopCard title="Relatórios Financeiros" :icon="ReportIcon">
        <template #actions>
          <NeonButton @click="generateDre" size="md" class="mr-2">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Gerar DRE
          </NeonButton>
          <NeonButton @click="generateBalancete" size="md">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Gerar Balancete
          </NeonButton>
        </template>
      </TopCard>

      <div class="flex flex-wrap gap-4 p-4 bg-gray-800 rounded-lg shadow-lg border border-gray-700">
        <input type="date" v-model="startDate" 
               class="p-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500" />
        <input type="date" v-model="endDate" 
               class="p-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500" />
      </div>

      <div v-if="reportData.dre" class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2 border-green-500/20">
        <h2 class="text-xl font-semibold mb-4 text-white">Demonstrativo de Resultado do Exercício (DRE)</h2>
        <p class="text-gray-300">Receitas: <span class="font-bold text-green-400">R$ {{ parseFloat(reportData.dre.receitas).toFixed(2) }}</span></p>
        <p class="text-gray-300">Despesas: <span class="font-bold text-red-400">R$ {{ parseFloat(reportData.dre.despesas).toFixed(2) }}</span></p>
        <p class="font-bold text-lg mt-2" :class="{'text-green-400': reportData.dre.resultado >= 0, 'text-red-400': reportData.dre.resultado < 0}">Resultado: R$ {{ parseFloat(reportData.dre.resultado).toFixed(2) }}</p>
      </div>

      <div v-if="reportData.balancete" class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2 border-purple-500/20">
        <h2 class="text-xl font-semibold mb-4 text-white">Balancete</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full bg-gray-800 rounded-lg overflow-hidden">
            <thead>
              <tr>
                <th class="py-3 px-4 bg-gray-700 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Conta</th>
                <th class="py-3 px-4 bg-gray-700 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Tipo</th>
                <th class="py-3 px-4 bg-gray-700 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Débito</th>
                <th class="py-3 px-4 bg-gray-700 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Crédito</th>
                <th class="py-3 px-4 bg-gray-700 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Saldo</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
              <tr v-for="account in reportData.balancete" :key="account.account">
                <td class="py-3 px-4 whitespace-nowrap text-sm font-medium text-gray-200">{{ account.account }}</td>
                <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-300">{{ account.type }}</td>
                <td class="py-3 px-4 whitespace-nowrap text-sm text-red-400">{{ parseFloat(account.debit).toFixed(2) }}</td>
                <td class="py-3 px-4 whitespace-nowrap text-sm text-green-400">{{ parseFloat(account.credit).toFixed(2) }}</td>
                <td class="py-3 px-4 whitespace-nowrap text-sm" :class="{'text-green-400': account.balance >= 0, 'text-red-400': account.balance < 0}">{{ parseFloat(account.balance).toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, h } from 'vue';
import api from '@/utils/api';
import { useUser } from '@/composables/useUser';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../components/NeonButton.vue';
import TopCard from '../components/ui/TopCard.vue';

const startDate = ref('');
const endDate = ref('');
const reportData = ref({
  dre: null,
  balancete: null,
});

const { user } = useUser();
const condominiumId = user.value?.condominium_id;

const ReportIcon = {
  render() {
    return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' })
    ]);
  }
};

async function generateDre() {
  try {
    const response = await api.get('/finance/reports/dre', {
      params: {
        condominium_id: condominiumId,
        start_date: startDate.value,
        end_date: endDate.value,
      },
      responseType: 'blob', // Important for downloading PDF
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'dre.pdf');
    document.body.appendChild(link);
    link.click();
    link.remove();

    // For displaying data directly, if API returns JSON
    // const { data } = await api.get('/finance/reports/dre', { params: { condominium_id: condominiumId, start_date: startDate.value, end_date: endDate.value } });
    // reportData.value.dre = data;
  } catch (error) {
    console.error('Erro ao gerar DRE:', error);
    alert('Erro ao gerar DRE. Verifique o console para mais detalhes.');
  }
}

async function generateBalancete() {
  try {
    const response = await api.get('/finance/reports/balancete', {
      params: {
        condominium_id: condominiumId,
        start_date: startDate.value,
        end_date: endDate.value,
      },
      responseType: 'blob', // Important for downloading PDF
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'balancete.pdf');
    document.body.appendChild(link);
    link.click();
    link.remove();

    // For displaying data directly, if API returns JSON
    // const { data } = await api.get('/finance/reports/balancete', { params: { condominium_id: condominiumId, start_date: startDate.value, end_date: endDate.value } });
    // reportData.value.balancete = data;
  } catch (error) {
    console.error('Erro ao gerar Balancete:', error);
    alert('Erro ao gerar Balancete. Verifique o console para mais detalhes.');
  }
}
</script>