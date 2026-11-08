<template>
  <AuthenticatedLayout page-title="Relatórios">
    <div class="space-y-6">
      <TopCard title="Relatórios" :icon="ReportIcon" />
      <!-- Filters -->
      <div class="bg-white shadow-sm rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Condomínio</label>
            <select
              v-model="filters.condominium"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">Todos</option>
              <option v-for="condo in condominiums" :key="condo.id" :value="condo.id">
                {{ condo.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Relatório</label>
            <select
              v-model="filters.type"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="financial">Financeiro</option>
              <option value="maintenance">Manutenção</option>
              <option value="residents">Moradores</option>
              <option value="incidents">Incidentes</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data Inicial</label>
            <input
              type="date"
              v-model="filters.startDate"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data Final</label>
            <input
              type="date"
              v-model="filters.endDate"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
        </div>
        <div class="mt-6 flex justify-end">
          <button
            @click="generateReport"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200 flex items-center"
          >
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Gerar Relatório
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-600">
        {{ error }}
      </div>

      <!-- Report Content -->
      <div v-else-if="report" class="space-y-6">
        <!-- Report Header -->
        <div class="bg-white shadow-sm rounded-lg p-6">
          <div class="flex justify-between items-center">
            <div>
              <h3 class="text-lg font-medium text-gray-900">{{ report.title }}</h3>
              <p class="mt-1 text-sm text-gray-500">
                Período: {{ formatDate(report.startDate) }} - {{ formatDate(report.endDate) }}
              </p>
            </div>
            <div class="flex space-x-4">
              <button
                @click="downloadPDF"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center"
              >
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                PDF
              </button>
              <button
                @click="downloadExcel"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center"
              >
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Excel
              </button>
            </div>
          </div>
        </div>

        <!-- Report Content -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
          <div class="p-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
              <div v-for="(stat, index) in report.summary" :key="index" 
                   class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-500">{{ stat.label }}</h4>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ stat.value }}</p>
                <p class="mt-1 text-sm" :class="stat.trend > 0 ? 'text-green-600' : 'text-red-600'">
                  {{ stat.trend > 0 ? '+' : '' }}{{ stat.trend }}% em relação ao período anterior
                </p>
              </div>
            </div>

            <!-- Chart -->
            <div class="h-96 bg-gray-50 rounded-lg flex items-center justify-center">
              <p class="text-gray-500">Gráfico será renderizado aqui</p>
            </div>

            <!-- Data Table -->
            <div class="mt-8">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th v-for="header in report.tableHeaders" :key="header"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      {{ header }}
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(row, index) in report.tableData" :key="index" class="hover:bg-gray-50">
                    <td v-for="(cell, cellIndex) in row" :key="cellIndex"
                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ cell }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, reactive, h } from 'vue';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import TopCard from './ui/TopCard.vue';

export default {
  name: 'ReportsView',
  components: { AuthenticatedLayout, TopCard },
  setup() {
    const condominiums = ref([]);
    const loading = ref(false);
    const error = ref('');
    const report = ref(null);

    const ReportIcon = {
      render() {
        return h('svg', { class: 'h-5 w-5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' })
        ]);
      }
    };

    const filters = reactive({
      condominium: '',
      type: 'financial',
      startDate: '',
      endDate: ''
    });

    const loadCondominiums = async () => {
      try {
        const token = localStorage.getItem('token');
        const response = await fetch('/api/condominiums', {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });
        
        if (response.ok) {
          condominiums.value = await response.json();
        }
      } catch (e) {
        console.error('Erro ao carregar condomínios:', e);
      }
    };

    const generateReport = async () => {
      loading.value = true;
      error.value = '';
      
      try {
        const token = localStorage.getItem('token');
        const response = await fetch('/api/reports/generate', {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(filters)
        });
        
        if (response.ok) {
          report.value = await response.json();
        } else {
          error.value = 'Falha ao gerar relatório';
        }
      } catch (e) {
        error.value = 'Erro de conexão';
      } finally {
        loading.value = false;
      }
    };

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('pt-BR');
    };

    const downloadPDF = () => {
      // Implementar download do PDF
      console.log('Download PDF');
    };

    const downloadExcel = () => {
      // Implementar download do Excel
      console.log('Download Excel');
    };

    // Carregar condomínios ao montar o componente
    loadCondominiums();

    return {
      condominiums,
      loading,
      error,
      report,
      filters,
      generateReport,
      formatDate,
      downloadPDF,
      downloadExcel,
      ReportIcon
    };
  }
};
</script> 