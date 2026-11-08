<template>
  <AuthenticatedLayout page-title="Dashboard de Análise">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-cyan-300">Dashboard de Análise</h1>
          <p class="text-gray-400">Análise detalhada de perfis e privilégios</p>
        </div>
        <div class="flex gap-3">
          <NeonButton @click="exportData" variant="info">
            <i class="fas fa-download mr-2"></i>Exportar
          </NeonButton>
          <NeonButton @click="loadData" :loading="loading">
            <i class="fas fa-sync-alt mr-2"></i>Atualizar
          </NeonButton>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-300"></div>
        <span class="ml-3 text-cyan-300">Carregando análises...</span>
      </div>

      <!-- Content -->
      <div v-else class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          <StatsCard
            title="Total de Perfis"
            :value="dashboardData.general_stats?.total_profiles || 0"
            icon="fas fa-users-cog"
            color="cyan"
          />
          <StatsCard
            title="Total de Usuários"
            :value="dashboardData.general_stats?.total_users || 0"
            icon="fas fa-users"
            color="blue"
          />
          <StatsCard
            title="Módulos"
            :value="dashboardData.general_stats?.total_modules || 0"
            icon="fas fa-puzzle-piece"
            color="green"
          />
          <StatsCard
            title="Perfis Ativos"
            :value="dashboardData.general_stats?.active_profiles || 0"
            icon="fas fa-user-check"
            color="purple"
          />
          <StatsCard
            title="Não Utilizados"
            :value="dashboardData.general_stats?.unused_profiles || 0"
            icon="fas fa-user-times"
            color="orange"
          />
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Users by Profile Chart -->
          <ChartCard
            title="Usuários por Perfil"
            chart-type="doughnut"
            :data="usersChartData"
            :loading="loading"
            :show-info="true"
            :chart-info="usersChartInfo"
            @chart-click="onUsersChartClick"
          />

          <!-- Modules Usage Chart -->
          <ChartCard
            title="Uso dos Módulos"
            chart-type="bar"
            :data="modulesChartData"
            :loading="loading"
            :options="modulesChartOptions"
            @chart-click="onModulesChartClick"
          />

          <!-- Privileges Chart -->
          <ChartCard
            title="Distribuição de Privilégios"
            chart-type="bar"
            :data="privilegesChartData"
            :loading="loading"
            :show-info="true"
            :chart-info="privilegesChartInfo"
          />

          <!-- Scope Chart -->
          <ChartCard
            title="Escopos de Acesso"
            chart-type="pie"
            :data="scopeChartData"
            :loading="loading"
            :show-info="true"
            :chart-info="scopeChartInfo"
          />
        </div>

        <!-- Security Analysis -->
        <SecurityAnalysisCard :dashboard-data="dashboardData" />

        <!-- Tables -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
          <!-- Users Table -->
          <div class="bg-gray-800/40 rounded-xl p-6 border border-gray-700/50">
            <h3 class="text-lg font-semibold text-cyan-300 mb-4">Detalhamento por Perfil</h3>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead>
                  <tr class="border-b border-gray-700">
                    <th class="text-left p-3 text-gray-300">Perfil</th>
                    <th class="text-center p-3 text-gray-300">Usuários</th>
                    <th class="text-center p-3 text-gray-300">%</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="profile in dashboardData.users_by_profile"
                    :key="profile.profile_name"
                    class="border-b border-gray-700/50 hover:bg-gray-700/30"
                  >
                    <td class="p-3 text-cyan-300">{{ profile.profile_name }}</td>
                    <td class="p-3 text-center text-white">{{ profile.users_count }}</td>
                    <td class="p-3 text-center">
                      <span class="px-2 py-1 bg-blue-500/20 text-blue-300 rounded-full text-xs">
                        {{ profile.percentage }}%
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Modules Table -->
          <div class="bg-gray-800/40 rounded-xl p-6 border border-gray-700/50">
            <h3 class="text-lg font-semibold text-cyan-300 mb-4">Uso dos Módulos</h3>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead>
                  <tr class="border-b border-gray-700">
                    <th class="text-left p-3 text-gray-300">Módulo</th>
                    <th class="text-center p-3 text-gray-300">Perfis</th>
                    <th class="text-center p-3 text-gray-300">% Uso</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="module in dashboardData.modules_usage"
                    :key="module.module_name"
                    class="border-b border-gray-700/50 hover:bg-gray-700/30"
                  >
                    <td class="p-3 text-cyan-300">{{ module.module_name }}</td>
                    <td class="p-3 text-center text-white">{{ module.profiles_with_access }}</td>
                    <td class="p-3 text-center">
                      <div class="flex items-center justify-center">
                        <div class="w-16 bg-gray-700 rounded-full h-2 mr-2">
                          <div
                            class="bg-green-500 h-2 rounded-full"
                            :style="{ width: `${module.usage_percentage}%` }"
                          ></div>
                        </div>
                        <span class="text-xs text-gray-300">{{ module.usage_percentage }}%</span>
                      </div>
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

<script setup>
import { ref, computed, onMounted } from 'vue'
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue'
import NeonButton from '../components/NeonButton.vue'
import StatsCard from '../components/ui/StatsCard.vue'
import ChartCard from '../components/charts/ChartCard.vue'
import SecurityAnalysisCard from '../components/profile_management/SecurityAnalysisCard.vue'
import api from '../utils/api'

const loading = ref(false)
const dashboardData = ref({})

// Chart Data Computed Properties
const usersChartData = computed(() => {
  if (!dashboardData.value.users_by_profile) return { labels: [], datasets: [] }
  
  const data = dashboardData.value.users_by_profile
  return {
    labels: data.map(item => item.profile_name),
    datasets: [{
      data: data.map(item => item.users_count),
      backgroundColor: [
        '#06b6d4', '#3b82f6', '#10b981', '#f59e0b',
        '#ef4444', '#8b5cf6', '#f97316', '#ec4899'
      ],
      borderWidth: 2,
      borderColor: '#374151'
    }]
  }
})

const usersChartInfo = computed(() => {
  if (!dashboardData.value.users_by_profile) return null
  
  const totalUsers = dashboardData.value.users_by_profile.reduce((sum, item) => sum + item.users_count, 0)
  const mostPopular = dashboardData.value.users_by_profile.reduce((max, item) => 
    item.users_count > max.users_count ? item : max, { users_count: 0 }
  )
  
  return {
    total_users: totalUsers,
    most_popular_profile: mostPopular.profile_name,
    highest_count: mostPopular.users_count
  }
})

const modulesChartData = computed(() => {
  if (!dashboardData.value.modules_usage) return { labels: [], datasets: [] }
  
  const data = dashboardData.value.modules_usage
  return {
    labels: data.map(item => item.module_name),
    datasets: [{
      label: 'Perfis com Acesso',
      data: data.map(item => item.profiles_with_access),
      backgroundColor: '#06b6d4',
      borderColor: '#0891b2',
      borderWidth: 1
    }]
  }
})

const modulesChartOptions = computed(() => ({
  scales: {
    x: {
      ticks: { 
        maxRotation: 45,
        minRotation: 45
      }
    }
  },
  plugins: {
    tooltip: {
      callbacks: {
        afterLabel: (context) => {
          const item = dashboardData.value.modules_usage[context.dataIndex]
          return `Uso: ${item.usage_percentage}%`
        }
      }
    }
  }
}))

const privilegesChartData = computed(() => {
  if (!dashboardData.value.privileges_distribution) return { labels: [], datasets: [] }
  
  const data = dashboardData.value.privileges_distribution
  return {
    labels: ['Visualizar', 'Criar', 'Editar', 'Excluir'],
    datasets: [{
      label: 'Quantidade de Permissões',
      data: [
        data.can_view?.count || 0,
        data.can_create?.count || 0,
        data.can_update?.count || 0,
        data.can_delete?.count || 0
      ],
      backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
      borderWidth: 1
    }]
  }
})

const privilegesChartInfo = computed(() => {
  if (!dashboardData.value.privileges_distribution) return null
  
  const data = dashboardData.value.privileges_distribution
  return {
    total_permissions: Object.values(data).reduce((sum, item) => sum + (item.count || 0), 0),
    most_granted: 'Visualizar',
    least_granted: 'Excluir'
  }
})

const scopeChartData = computed(() => {
  if (!dashboardData.value.scope_analysis) return { labels: [], datasets: [] }
  
  const data = dashboardData.value.scope_analysis
  return {
    labels: data.map(item => item.label),
    datasets: [{
      data: data.map(item => item.count),
      backgroundColor: ['#3b82f6', '#f59e0b', '#ef4444'],
      borderWidth: 2,
      borderColor: '#374151'
    }]
  }
})

const scopeChartInfo = computed(() => {
  if (!dashboardData.value.scope_analysis) return null
  
  const total = dashboardData.value.scope_analysis.reduce((sum, item) => sum + item.count, 0)
  const mostUsed = dashboardData.value.scope_analysis.reduce((max, item) => 
    item.count > max.count ? item : max, { count: 0 }
  )
  
  return {
    total_scope_assignments: total,
    most_used_scope: mostUsed.label,
    security_level: total > 50 ? 'Alto' : 'Médio'
  }
})

const loadData = async () => {
  loading.value = true
  try {
    const response = await api.get('/profile-analytics/dashboard')
    dashboardData.value = response.data
  } catch (error) {
    console.error('Error loading dashboard data:', error)
  } finally {
    loading.value = false
  }
}

const exportData = () => {
  const dataToExport = {
    generated_at: new Date().toISOString(),
    dashboard_data: dashboardData.value,
    chart_summaries: {
      users_distribution: usersChartInfo.value,
      privileges_analysis: privilegesChartInfo.value,
      scope_analysis: scopeChartInfo.value
    }
  }
  
  const blob = new Blob([JSON.stringify(dataToExport, null, 2)], {
    type: 'application/json'
  })
  
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `dashboard-perfis-${new Date().toISOString().split('T')[0]}.json`
  link.click()
  URL.revokeObjectURL(url)
}

// Chart Event Handlers
const onUsersChartClick = (element) => {
  const profileName = dashboardData.value.users_by_profile[element.index]?.profile_name
  if (profileName) {
    console.log(`Clicked on profile: ${profileName}`)
    // Here you could navigate to profile details or show a modal
  }
}

const onModulesChartClick = (element) => {
  const moduleName = dashboardData.value.modules_usage[element.index]?.module_name
  if (moduleName) {
    console.log(`Clicked on module: ${moduleName}`)
    // Here you could show module details
  }
}

onMounted(() => {
  loadData()
})
</script> 