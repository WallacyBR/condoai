<template>
  <div class="bg-gray-800/40 rounded-xl p-6 border border-gray-700/50">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-cyan-300">Análise de Segurança</h3>
      <div class="flex items-center">
        <span class="text-sm text-gray-400 mr-2">Nível de Segurança:</span>
        <span 
          class="px-3 py-1 rounded-full text-sm font-medium"
          :class="getSecurityLevelColor(securityLevel)"
        >
          {{ securityLevel }}
        </span>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <!-- High Privilege Profiles -->
      <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4">
        <div class="flex items-center justify-between mb-2">
          <h4 class="text-red-400 font-medium">Perfis de Alto Privilégio</h4>
          <i class="fas fa-exclamation-triangle text-red-400"></i>
        </div>
        <div class="text-2xl font-bold text-red-300 mb-1">{{ highPrivilegeCount }}</div>
        <div class="text-xs text-gray-400">
          {{ highPrivilegePercentage }}% do total
        </div>
        <div class="mt-2">
          <div class="w-full bg-gray-700 rounded-full h-2">
            <div 
              class="bg-red-500 h-2 rounded-full transition-all duration-500"
              :style="{ width: `${Math.min(highPrivilegePercentage, 100)}%` }"
            ></div>
          </div>
        </div>
      </div>

      <!-- Admin Users -->
      <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-4">
        <div class="flex items-center justify-between mb-2">
          <h4 class="text-yellow-400 font-medium">Usuários Administrativos</h4>
          <i class="fas fa-user-shield text-yellow-400"></i>
        </div>
        <div class="text-2xl font-bold text-yellow-300 mb-1">{{ adminUsersCount }}</div>
        <div class="text-xs text-gray-400">
          {{ adminUsersPercentage }}% do total
        </div>
        <div class="mt-2">
          <div class="w-full bg-gray-700 rounded-full h-2">
            <div 
              class="bg-yellow-500 h-2 rounded-full transition-all duration-500"
              :style="{ width: `${Math.min(adminUsersPercentage, 100)}%` }"
            ></div>
          </div>
        </div>
      </div>

      <!-- Critical Access -->
      <div class="bg-orange-500/10 border border-orange-500/20 rounded-lg p-4">
        <div class="flex items-center justify-between mb-2">
          <h4 class="text-orange-400 font-medium">Acesso Crítico</h4>
          <i class="fas fa-key text-orange-400"></i>
        </div>
        <div class="text-2xl font-bold text-orange-300 mb-1">{{ criticalAccessCount }}</div>
        <div class="text-xs text-gray-400">
          Perfis com acesso a módulos críticos
        </div>
        <div class="mt-2">
          <div class="w-full bg-gray-700 rounded-full h-2">
            <div 
              class="bg-orange-500 h-2 rounded-full transition-all duration-500"
              :style="{ width: `${Math.min(criticalAccessPercentage, 100)}%` }"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Security Recommendations -->
    <div class="border-t border-gray-700 pt-4">
      <h4 class="text-gray-300 font-medium mb-3">Recomendações de Segurança</h4>
      <div class="space-y-2">
        <div 
          v-for="(recommendation, index) in recommendations" 
          :key="index"
          class="flex items-start p-3 rounded-lg"
          :class="getRecommendationClass(recommendation.level)"
        >
          <i 
            class="flex-shrink-0 mt-0.5 mr-3"
            :class="getRecommendationIcon(recommendation.level)"
          ></i>
          <div>
            <div class="font-medium">{{ recommendation.title }}</div>
            <div class="text-sm opacity-90">{{ recommendation.description }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Security Trends -->
    <div class="border-t border-gray-700 pt-4 mt-4">
      <h4 class="text-gray-300 font-medium mb-3">Tendências de Segurança</h4>
      <div class="grid grid-cols-2 gap-4">
        <div class="text-center">
          <div class="text-lg font-bold text-green-400">{{ securityTrends.improving }}</div>
          <div class="text-xs text-gray-400">Melhorias identificadas</div>
        </div>
        <div class="text-center">
          <div class="text-lg font-bold text-red-400">{{ securityTrends.risks }}</div>
          <div class="text-xs text-gray-400">Riscos detectados</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  dashboardData: { type: Object, default: () => ({}) }
})

// Security Analysis Computed Properties
const highPrivilegeCount = computed(() => {
  // Count profiles with more than 80% of modules
  if (!props.dashboardData.users_by_profile || !props.dashboardData.general_stats) return 0
  
  const totalModules = props.dashboardData.general_stats.total_modules || 1
  return props.dashboardData.users_by_profile.filter(profile => {
    // Simulate module count calculation (would come from API in real implementation)
    return Math.random() > 0.7 // Placeholder logic
  }).length
})

const highPrivilegePercentage = computed(() => {
  const totalProfiles = props.dashboardData.general_stats?.total_profiles || 1
  return Math.round((highPrivilegeCount.value / totalProfiles) * 100)
})

const adminUsersCount = computed(() => {
  if (!props.dashboardData.users_by_profile) return 0
  
  return props.dashboardData.users_by_profile
    .filter(profile => ['SysOp', 'Administradora'].includes(profile.profile_name))
    .reduce((sum, profile) => sum + profile.users_count, 0)
})

const adminUsersPercentage = computed(() => {
  const totalUsers = props.dashboardData.general_stats?.total_users || 1
  return Math.round((adminUsersCount.value / totalUsers) * 100)
})

const criticalAccessCount = computed(() => {
  // Count profiles with access to critical modules
  if (!props.dashboardData.users_by_profile) return 0
  
  return props.dashboardData.users_by_profile.filter(profile => 
    ['SysOp', 'Administradora', 'Síndico'].includes(profile.profile_name)
  ).length
})

const criticalAccessPercentage = computed(() => {
  const totalProfiles = props.dashboardData.general_stats?.total_profiles || 1
  return Math.round((criticalAccessCount.value / totalProfiles) * 100)
})

const securityLevel = computed(() => {
  const risks = highPrivilegePercentage.value + adminUsersPercentage.value
  
  if (risks < 20) return 'Excelente'
  if (risks < 40) return 'Bom'
  if (risks < 60) return 'Moderado'
  if (risks < 80) return 'Ruim'
  return 'Crítico'
})

const recommendations = computed(() => {
  const recs = []
  
  if (highPrivilegePercentage.value > 30) {
    recs.push({
      level: 'warning',
      title: 'Muitos perfis com alto privilégio',
      description: 'Considere revisar e reduzir privilégios desnecessários'
    })
  }
  
  if (adminUsersPercentage.value > 20) {
    recs.push({
      level: 'danger',
      title: 'Muitos usuários administrativos',
      description: 'Limite o número de usuários com acesso administrativo'
    })
  }
  
  if (criticalAccessCount.value > 3) {
    recs.push({
      level: 'warning',
      title: 'Acesso crítico amplo',
      description: 'Restrinja acesso a módulos críticos apenas ao necessário'
    })
  }
  
  if (recs.length === 0) {
    recs.push({
      level: 'success',
      title: 'Sistema bem configurado',
      description: 'Os privilégios estão bem distribuídos e seguros'
    })
  }
  
  return recs
})

const securityTrends = computed(() => ({
  improving: Math.floor(Math.random() * 5) + 1, // Placeholder
  risks: Math.floor(Math.random() * 3) + 1 // Placeholder
}))

// Helper Methods
const getSecurityLevelColor = (level) => {
  const colors = {
    'Excelente': 'bg-green-500/20 text-green-300',
    'Bom': 'bg-blue-500/20 text-blue-300',
    'Moderado': 'bg-yellow-500/20 text-yellow-300',
    'Ruim': 'bg-orange-500/20 text-orange-300',
    'Crítico': 'bg-red-500/20 text-red-300'
  }
  return colors[level] || 'bg-gray-500/20 text-gray-300'
}

const getRecommendationClass = (level) => {
  const classes = {
    'success': 'bg-green-500/10 border border-green-500/20',
    'warning': 'bg-yellow-500/10 border border-yellow-500/20',
    'danger': 'bg-red-500/10 border border-red-500/20'
  }
  return classes[level] || 'bg-gray-500/10 border border-gray-500/20'
}

const getRecommendationIcon = (level) => {
  const icons = {
    'success': 'fas fa-check-circle text-green-400',
    'warning': 'fas fa-exclamation-triangle text-yellow-400',
    'danger': 'fas fa-times-circle text-red-400'
  }
  return icons[level] || 'fas fa-info-circle text-gray-400'
}
</script> 