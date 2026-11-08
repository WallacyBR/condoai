<template>
  <div class="space-y-4">
    <!-- Quick Actions -->
    <div class="flex flex-wrap gap-3 mb-4">
      <NeonButton
        @click="selectAllModules"
        type="button"
        size="sm"
        class="bg-green-600/40 hover:bg-green-600/60"
      >
        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Selecionar Todos
      </NeonButton>
      <NeonButton
        @click="clearAllModules"
        type="button"
        size="sm"
        class="bg-red-600/40 hover:bg-red-600/60"
      >
        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Limpar Todos
      </NeonButton>
    </div>

    <!-- Modules List -->
    <div class="space-y-4">
      <div
        v-for="module in modules"
        :key="module.id"
        class="border border-gray-600/50 rounded-lg p-4 transition-all duration-200"
        :class="{ 'bg-indigo-500/10 border-indigo-500/30': isModuleSelected(module.id) }"
      >
        <!-- Module Header -->
        <div class="flex items-center justify-between mb-3">
          <label class="flex items-center cursor-pointer">
            <input
              type="checkbox"
              :checked="isModuleSelected(module.id)"
              @change="toggleModule(module.id)"
              class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-600 bg-gray-800 rounded"
            />
            <div class="ml-3">
              <span class="font-medium text-white">{{ module.name }}</span>
              <p class="text-sm text-gray-400">{{ module.description || 'Módulo do sistema' }}</p>
            </div>
          </label>
        </div>

        <!-- Permissions (shown only when module is selected) -->
        <div v-if="isModuleSelected(module.id)" class="space-y-4 mt-4 pt-4 border-t border-gray-600/30">
          <!-- Permission Checkboxes -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <label class="flex items-center p-3 bg-gray-800/50 rounded-lg border border-gray-700 hover:border-green-500/50 transition-colors">
              <input
                type="checkbox"
                v-model="getModulePermissions(module.id).can_view"
                class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-600 bg-gray-800 rounded"
              />
              <span class="ml-3 text-green-300 cursor-pointer">
                <span class="font-medium">Visualizar</span>
                <span class="block text-xs text-gray-400">Ver registros</span>
              </span>
            </label>

            <label class="flex items-center p-3 bg-gray-800/50 rounded-lg border border-gray-700 hover:border-blue-500/50 transition-colors">
              <input
                type="checkbox"
                v-model="getModulePermissions(module.id).can_create"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded"
              />
              <span class="ml-3 text-blue-300 cursor-pointer">
                <span class="font-medium">Criar</span>
                <span class="block text-xs text-gray-400">Novos registros</span>
              </span>
            </label>

            <label class="flex items-center p-3 bg-gray-800/50 rounded-lg border border-gray-700 hover:border-yellow-500/50 transition-colors">
              <input
                type="checkbox"
                v-model="getModulePermissions(module.id).can_update"
                class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-600 bg-gray-800 rounded"
              />
              <span class="ml-3 text-yellow-300 cursor-pointer">
                <span class="font-medium">Editar</span>
                <span class="block text-xs text-gray-400">Modificar registros</span>
              </span>
            </label>

            <label class="flex items-center p-3 bg-gray-800/50 rounded-lg border border-gray-700 hover:border-red-500/50 transition-colors">
              <input
                type="checkbox"
                v-model="getModulePermissions(module.id).can_delete"
                class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-600 bg-gray-800 rounded"
              />
              <span class="ml-3 text-red-300 cursor-pointer">
                <span class="font-medium">Excluir</span>
                <span class="block text-xs text-gray-400">Remover registros</span>
              </span>
            </label>
          </div>

          <!-- Scope Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Escopo de Acesso</label>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <label class="flex items-center p-3 bg-gray-800/50 rounded-lg border border-gray-700 hover:border-indigo-500/50 transition-colors">
                <input
                  type="radio"
                  :name="`scope_${module.id}`"
                  value="own"
                  v-model="getModulePermissions(module.id).scope"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 bg-gray-800"
                />
                <span class="ml-3 text-gray-300 cursor-pointer">
                  <span class="font-medium">Próprios</span>
                  <span class="block text-xs text-gray-400">Apenas registros próprios</span>
                </span>
              </label>

              <label class="flex items-center p-3 bg-gray-800/50 rounded-lg border border-gray-700 hover:border-indigo-500/50 transition-colors">
                <input
                  type="radio"
                  :name="`scope_${module.id}`"
                  value="condominium"
                  v-model="getModulePermissions(module.id).scope"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 bg-gray-800"
                />
                <span class="ml-3 text-gray-300 cursor-pointer">
                  <span class="font-medium">Condomínio</span>
                  <span class="block text-xs text-gray-400">Registros do condomínio</span>
                </span>
              </label>

              <label class="flex items-center p-3 bg-gray-800/50 rounded-lg border border-gray-700 hover:border-indigo-500/50 transition-colors">
                <input
                  type="radio"
                  :name="`scope_${module.id}`"
                  value="all"
                  v-model="getModulePermissions(module.id).scope"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 bg-gray-800"
                />
                <span class="ml-3 text-gray-300 cursor-pointer">
                  <span class="font-medium">Todos</span>
                  <span class="block text-xs text-gray-400">Todos os registros</span>
                </span>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import NeonButton from '@/components/NeonButton.vue'

const props = defineProps({
  modules: {
    type: Array,
    default: () => []
  },
  modelValue: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue'])

// Computed para trabalhar com v-model
const selectedModules = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

// Utilitários para módulos
const isModuleSelected = (moduleId) => {
  return selectedModules.value.some(m => m.module_id === moduleId)
}

const getModulePermissions = (moduleId) => {
  const existing = selectedModules.value.find(m => m.module_id === moduleId)
  if (existing) return existing
  
  // Criar entrada padrão se não existir
  const defaultPerms = {
    module_id: moduleId,
    can_view: false,
    can_create: false,
    can_update: false,
    can_delete: false,
    scope: 'own'
  }
  
  const newValue = [...selectedModules.value, defaultPerms]
  selectedModules.value = newValue
  return defaultPerms
}

const toggleModule = (moduleId) => {
  const index = selectedModules.value.findIndex(m => m.module_id === moduleId)
  
  if (index >= 0) {
    // Remover módulo
    const newValue = selectedModules.value.filter((_, i) => i !== index)
    selectedModules.value = newValue
  } else {
    // Adicionar módulo com permissões padrão
    const newValue = [
      ...selectedModules.value,
      {
        module_id: moduleId,
        can_view: true,
        can_create: false,
        can_update: false,
        can_delete: false,
        scope: 'own'
      }
    ]
    selectedModules.value = newValue
  }
}

const selectAllModules = () => {
  const newModules = props.modules
    .filter(module => !isModuleSelected(module.id))
    .map(module => ({
      module_id: module.id,
      can_view: true,
      can_create: false,
      can_update: false,
      can_delete: false,
      scope: 'own'
    }))
  
  selectedModules.value = [...selectedModules.value, ...newModules]
}

const clearAllModules = () => {
  selectedModules.value = []
}
</script> 