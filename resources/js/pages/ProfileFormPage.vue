<template>
  <AuthenticatedLayout page-title="Formulário de Perfil">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header Card -->
      <div class="bg-gradient-to-r from-indigo-600/20 to-purple-600/20 rounded-2xl p-6 border-2 border-indigo-500/30">
        <div class="flex items-center space-x-4">
          <div class="h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center border-2 border-indigo-500/30">
            <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-white">{{ isEditing ? 'Editar Perfil' : 'Novo Perfil' }}</h1>
            <p class="text-indigo-300">{{ isEditing ? 'Modifique as informações e privilégios do perfil' : 'Configure um novo perfil de acesso ao sistema' }}</p>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-cyan-300"></div>
      </div>

      <!-- Form -->
      <form v-else @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Basic Information -->
        <div class="bg-gradient-to-br from-gray-800/80 via-gray-700/80 to-gray-800/80 backdrop-blur-xl rounded-2xl p-6 border-2 border-gray-700/50 shadow-xl">
          <h2 class="text-xl font-semibold text-cyan-300 mb-6">Informações Básicas</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                Nome do Perfil *
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                :disabled="form.name === 'SysOp' && isEditing"
                class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all"
                placeholder="Ex: Administrador, Morador, etc."
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-400">{{ errors.name[0] }}</p>
            </div>

            <!-- Description -->
            <div>
              <label for="description" class="block text-sm font-medium text-gray-300 mb-2">
                Descrição
              </label>
              <input
                id="description"
                v-model="form.description"
                type="text"
                class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all"
                placeholder="Descrição breve do perfil"
              />
              <p v-if="errors.description" class="mt-1 text-sm text-red-400">{{ errors.description[0] }}</p>
            </div>
          </div>
        </div>

        <!-- Privileges Section -->
        <PrivilegeSelector
          v-model="form.modules"
          :modules="modules"
        />

        <!-- Form Actions -->
        <div class="flex justify-between">
          <NeonButton
            @click="$router.push({ name: 'profiles' })"
            type="button"
            size="md"
            class="bg-gray-600/40 hover:bg-gray-600/60"
          >
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar
          </NeonButton>

          <NeonButton
            type="submit"
            :loading="saving"
            size="md"
            class="bg-gradient-to-r from-indigo-600/60 to-purple-600/60 hover:from-indigo-600/80 hover:to-purple-600/80"
          >
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ isEditing ? 'Atualizar Perfil' : 'Criar Perfil' }}
          </NeonButton>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue'
import NeonButton from '@/components/NeonButton.vue'
import PrivilegeSelector from '@/components/PrivilegeSelector.vue'
import api from '@/utils/api'

const router = useRouter()
const route = useRoute()

// Estados
const modules = ref([])
const loading = ref(true)
const saving = ref(false)
const errors = ref({})

// Formulário
const form = reactive({
  name: '',
  description: '',
  modules: []
})

// Computed
const isEditing = computed(() => !!route.params.id)

// Carregar dados iniciais
const loadData = async () => {
  loading.value = true
  
  try {
    const response = await api.get('/profile-management/data')
    modules.value = response.data.modules
    
    // Se estiver editando, carregar dados do perfil
    if (isEditing.value) {
      const profileResponse = await api.get(`/profile-management/profiles/${route.params.id}`)
      const profile = profileResponse.data.profile
      
      form.name = profile.name
      form.description = profile.description || ''
      
      // Configurar módulos e permissões
      if (profile.modules) {
        form.modules = profile.modules.map(module => ({
          module_id: module.id,
          can_view: module.pivot.can_view,
          can_create: module.pivot.can_create,
          can_update: module.pivot.can_update,
          can_delete: module.pivot.can_delete,
          scope: module.pivot.scope
        }))
      }
    }
  } catch (err) {
    console.error('Erro ao carregar dados:', err)
  } finally {
    loading.value = false
  }
}

// Submit
const handleSubmit = async () => {
  saving.value = true
  errors.value = {}
  
  try {
    const payload = {
      name: form.name,
      description: form.description,
      modules: form.modules
    }
    
    if (isEditing.value) {
      await api.put(`/profile-management/profiles/${route.params.id}`, payload)
    } else {
      await api.post('/profile-management/profiles', payload)
    }
    
    router.push({ name: 'profiles' })
  } catch (err) {
    console.error('Erro ao salvar perfil:', err)
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
    }
  } finally {
    saving.value = false
  }
}

// Carregar dados ao montar
onMounted(() => {
  loadData()
})
</script> 