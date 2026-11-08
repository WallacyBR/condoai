<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-gray-800 rounded-xl p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-cyan-300">
          {{ profile ? 'Editar Perfil' : 'Novo Perfil' }}
        </h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-white">
          <i class="fas fa-times text-xl"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Basic Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              Nome do Perfil *
            </label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:border-cyan-500 focus:outline-none"
              placeholder="Ex: Administrador"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              Descrição
            </label>
            <input
              v-model="form.description"
              type="text"
              class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white focus:border-cyan-500 focus:outline-none"
              placeholder="Descrição do perfil"
            />
          </div>
        </div>

        <!-- Modules Selection -->
        <div>
          <h3 class="text-lg font-semibold text-cyan-300 mb-4">Módulos e Privilégios</h3>
          
          <div class="space-y-4">
            <div
              v-for="module in modules"
              :key="module.id"
              class="border border-gray-600 rounded-lg p-4"
            >
              <div class="flex items-center justify-between mb-3">
                <label class="flex items-center">
                  <input
                    type="checkbox"
                    :checked="isModuleSelected(module.id)"
                    @change="toggleModule(module.id)"
                    class="mr-3 h-4 w-4 text-cyan-600"
                  />
                  <div>
                    <span class="text-lg font-medium text-cyan-300">{{ module.name }}</span>
                    <p class="text-sm text-gray-400">{{ module.description }}</p>
                  </div>
                </label>
              </div>

              <div v-if="isModuleSelected(module.id)" class="space-y-3">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                  <label class="flex items-center">
                    <input
                      type="checkbox"
                      v-model="getModulePermissions(module.id).can_view"
                      class="mr-2 h-4 w-4"
                    />
                    <span class="text-green-300 text-sm">Visualizar</span>
                  </label>
                  <label class="flex items-center">
                    <input
                      type="checkbox"
                      v-model="getModulePermissions(module.id).can_create"
                      class="mr-2 h-4 w-4"
                    />
                    <span class="text-blue-300 text-sm">Criar</span>
                  </label>
                  <label class="flex items-center">
                    <input
                      type="checkbox"
                      v-model="getModulePermissions(module.id).can_update"
                      class="mr-2 h-4 w-4"
                    />
                    <span class="text-yellow-300 text-sm">Editar</span>
                  </label>
                  <label class="flex items-center">
                    <input
                      type="checkbox"
                      v-model="getModulePermissions(module.id).can_delete"
                      class="mr-2 h-4 w-4"
                    />
                    <span class="text-red-300 text-sm">Excluir</span>
                  </label>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-300 mb-2">Escopo</label>
                  <select
                    v-model="getModulePermissions(module.id).scope"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white"
                  >
                    <option value="own">Próprios registros</option>
                    <option value="condominium">Registros do condomínio</option>
                    <option value="all">Todos os registros</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-4 pt-6 border-t border-gray-700">
          <button
            type="button"
            @click="$emit('close')"
            class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-500"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="!form.name || loading"
            class="px-6 py-3 bg-cyan-600 text-white rounded-lg hover:bg-cyan-500 disabled:opacity-50"
          >
            {{ profile ? 'Atualizar' : 'Criar' }} Perfil
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'

const props = defineProps({
  show: { type: Boolean, default: false },
  profile: { type: Object, default: null },
  modules: { type: Array, default: () => [] },
  scopeOptions: { type: Object, default: () => ({}) }
})

const emit = defineEmits(['close', 'save'])

const loading = ref(false)
const form = reactive({
  name: '',
  description: '',
  modules: []
})

watch(() => props.profile, (newProfile) => {
  if (newProfile) {
    form.name = newProfile.name
    form.description = newProfile.description || ''
    form.modules = newProfile.modules?.map(module => ({
      module_id: module.id,
      can_view: module.pivot?.can_view || false,
      can_create: module.pivot?.can_create || false,
      can_update: module.pivot?.can_update || false,
      can_delete: module.pivot?.can_delete || false,
      scope: module.pivot?.scope || 'own'
    })) || []
  } else {
    form.name = ''
    form.description = ''
    form.modules = []
  }
}, { immediate: true })

const isModuleSelected = (moduleId) => {
  return form.modules.some(m => m.module_id === moduleId)
}

const toggleModule = (moduleId) => {
  const index = form.modules.findIndex(m => m.module_id === moduleId)
  if (index >= 0) {
    form.modules.splice(index, 1)
  } else {
    form.modules.push({
      module_id: moduleId,
      can_view: true,
      can_create: false,
      can_update: false,
      can_delete: false,
      scope: 'own'
    })
  }
}

const getModulePermissions = (moduleId) => {
  let modulePermission = form.modules.find(m => m.module_id === moduleId)
  if (!modulePermission) {
    modulePermission = {
      module_id: moduleId,
      can_view: true,
      can_create: false,
      can_update: false,
      can_delete: false,
      scope: 'own'
    }
    form.modules.push(modulePermission)
  }
  return modulePermission
}

const handleSubmit = async () => {
  loading.value = true
  try {
    const profileData = {
      name: form.name,
      description: form.description,
      modules: form.modules
    }
    
    if (props.profile) {
      profileData.id = props.profile.id
    }
    
    emit('save', profileData)
  } finally {
    loading.value = false
  }
}
</script> 