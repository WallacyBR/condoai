<template>
  <AuthenticatedLayout page-title="Gerenciamento de Perfis">
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header Card -->
      <div class="bg-gradient-to-r from-indigo-600/20 to-purple-600/20 rounded-2xl p-6 border-2 border-indigo-500/30">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
          <div class="flex items-center space-x-4">
            <div class="h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center border-2 border-indigo-500/30">
              <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-2xl font-bold text-white">Perfis e Privilégios</h1>
              <p class="text-indigo-300">Gerencie perfis de usuários e controle de acesso ao sistema</p>
            </div>
          </div>
          
          <div class="flex flex-wrap gap-3">
            <NeonButton 
              @click="$router.push({ name: 'profiles.dashboard' })" 
              size="md" 
              class="bg-purple-600/40 hover:bg-purple-600/60"
            >
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
              Dashboard
            </NeonButton>
            <NeonButton 
              @click="$router.push({ name: 'profiles.create' })" 
              size="md" 
              class="bg-gradient-to-r from-indigo-600/60 to-purple-600/60 hover:from-indigo-600/80 hover:to-purple-600/80"
            >
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
              Novo Perfil
            </NeonButton>
            <NeonButton @click="loadData" :loading="loading" size="md">
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              Recarregar
            </NeonButton>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-900/50 via-blue-800/50 to-blue-900/50 backdrop-blur-xl rounded-2xl p-6 border-2 border-blue-500/30 shadow-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-300 text-sm font-medium">Total de Perfis</p>
              <p class="text-blue-100 text-3xl font-bold">{{ totalProfiles }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-blue-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.5a8.25 8.25 0 0116.5 0H4z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-gradient-to-br from-green-900/50 via-green-800/50 to-green-900/50 backdrop-blur-xl rounded-2xl p-6 border-2 border-green-500/30 shadow-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-green-300 text-sm font-medium">Perfis Ativos</p>
              <p class="text-green-100 text-3xl font-bold">{{ activeProfiles }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-green-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-gradient-to-br from-orange-900/50 via-orange-800/50 to-orange-900/50 backdrop-blur-xl rounded-2xl p-6 border-2 border-orange-500/30 shadow-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-orange-300 text-sm font-medium">Total de Usuários</p>
              <p class="text-orange-100 text-3xl font-bold">{{ totalUsers }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-orange-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-gradient-to-br from-purple-900/50 via-purple-800/50 to-purple-900/50 backdrop-blur-xl rounded-2xl p-6 border-2 border-purple-500/30 shadow-xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-purple-300 text-sm font-medium">Módulos do Sistema</p>
              <p class="text-purple-100 text-3xl font-bold">{{ totalModules }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-purple-500/20 flex items-center justify-center">
              <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-cyan-300"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="bg-red-500/20 border border-red-500/50 rounded-lg p-6 text-center">
        <svg class="h-16 w-16 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="text-lg font-semibold text-red-300 mb-2">Erro ao Carregar Dados</h3>
        <p class="text-red-200 mb-4">{{ error }}</p>
        <NeonButton @click="loadData" size="md">
          Tentar Novamente
        </NeonButton>
      </div>

      <!-- Profiles Grid -->
      <div v-else class="grid gap-6">
        <div
          v-for="profile in profiles"
          :key="profile.id"
          class="bg-gradient-to-br from-gray-800/80 via-gray-700/80 to-gray-800/80 backdrop-blur-xl rounded-2xl p-6 border-2 border-gray-700/50 shadow-xl hover:border-cyan-500/50 transition-all duration-300"
        >
          <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between space-y-4 lg:space-y-0">
            <!-- Profile Info -->
            <div class="flex-1">
              <div class="flex items-center space-x-3 mb-3">
                <div class="h-12 w-12 rounded-full bg-indigo-500/20 flex items-center justify-center">
                  <svg class="h-6 w-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                </div>
                <div>
                  <h3 class="text-xl font-semibold text-cyan-300">{{ profile.name }}</h3>
                  <p class="text-gray-400 text-sm">{{ profile.description || 'Sem descrição' }}</p>
                </div>
              </div>

              <!-- Stats -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div class="text-center p-3 bg-blue-500/10 rounded-lg border border-blue-500/30">
                  <p class="text-blue-100 text-lg font-bold">{{ profile.users_count || 0 }}</p>
                  <p class="text-blue-300 text-xs">Usuários</p>
                </div>
                <div class="text-center p-3 bg-green-500/10 rounded-lg border border-green-500/30">
                  <p class="text-green-100 text-lg font-bold">{{ profile.modules?.length || 0 }}</p>
                  <p class="text-green-300 text-xs">Módulos</p>
                </div>
                <div class="text-center p-3 bg-purple-500/10 rounded-lg border border-purple-500/30">
                  <p class="text-purple-100 text-lg font-bold">{{ getPrivilegeCount(profile) }}</p>
                  <p class="text-purple-300 text-xs">Privilégios</p>
                </div>
                <div class="text-center p-3 bg-cyan-500/10 rounded-lg border border-cyan-500/30">
                  <p class="text-cyan-100 text-lg font-bold">{{ getSecurityLevel(profile) }}</p>
                  <p class="text-cyan-300 text-xs">Segurança</p>
                </div>
              </div>

              <!-- Module Preview -->
              <div v-if="profile.modules && profile.modules.length > 0" class="mb-4">
                <p class="text-sm text-gray-400 mb-2">Módulos com acesso:</p>
                <div class="flex flex-wrap gap-2">
                  <span 
                    v-for="module in profile.modules.slice(0, 4)" 
                    :key="module.id"
                    class="inline-flex items-center px-2 py-1 bg-indigo-600/20 text-indigo-300 text-xs rounded-full border border-indigo-500/30"
                  >
                    {{ module.name }}
                  </span>
                  <span 
                    v-if="profile.modules.length > 4" 
                    class="inline-flex items-center px-2 py-1 bg-gray-600/20 text-gray-300 text-xs rounded-full border border-gray-500/30"
                  >
                    +{{ profile.modules.length - 4 }} mais
                  </span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-2">
              <NeonButton
                @click="$router.push({ name: 'profiles.edit', params: { id: profile.id } })"
                size="sm"
                class="bg-orange-600/40 hover:bg-orange-600/60"
              >
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Editar
              </NeonButton>
              <NeonButton
                v-if="profile.name !== 'SysOp'"
                @click="confirmDelete(profile)"
                size="sm"
                class="bg-red-600/40 hover:bg-red-600/60"
              >
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Excluir
              </NeonButton>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue'
import NeonButton from '@/components/NeonButton.vue'
import api from '@/utils/api'

const router = useRouter()

// Estados
const profiles = ref([])
const modules = ref([])
const loading = ref(true)
const error = ref(null)

// Confirmação de exclusão
const deleteConfirmation = ref({
  show: false,
  profile: null,
  loading: false
})

// Computed para estatísticas
const totalProfiles = computed(() => profiles.value.length)
const activeProfiles = computed(() => profiles.value.filter(p => p.users_count > 0).length)
const totalUsers = computed(() => profiles.value.reduce((sum, p) => sum + (p.users_count || 0), 0))
const totalModules = computed(() => modules.value.length)

// Carregar dados
const loadData = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await api.get('/profile-management/data')
    profiles.value = response.data.profiles
    modules.value = response.data.modules
  } catch (err) {
    console.error('Erro ao carregar perfis:', err)
    error.value = err.response?.data?.message || 'Erro ao carregar dados'
  } finally {
    loading.value = false
  }
}

// Utilitários
const getPrivilegeCount = (profile) => {
  if (!profile.modules) return 0
  return profile.modules.reduce((sum, module) => {
    const pivot = module.pivot || {}
    return sum + Object.values(pivot).filter(val => val === true).length
  }, 0)
}

const getSecurityLevel = (profile) => {
  const privilegeCount = getPrivilegeCount(profile)
  
  if (privilegeCount === 0) return 'Baixo'
  if (privilegeCount < 10) return 'Médio'
  if (privilegeCount < 20) return 'Alto'
  return 'Crítico'
}

const confirmDelete = (profile) => {
  deleteConfirmation.value = {
    show: true,
    profile,
    loading: false
  }
}

// Carregar dados ao montar
onMounted(() => {
  loadData()
})
</script> 