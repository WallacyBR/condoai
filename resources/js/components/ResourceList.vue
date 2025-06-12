<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  endpoint: { 
    type: String, 
    required: true 
  },
  columns: {
    type: Array,
    default: () => []
  },
  showActions: {
    type: Boolean,
    default: true
  },
  searchable: {
    type: Boolean,
    default: true
  },
  createRoute: {
    type: String,
    default: ''
  },
  editRoute: {
    type: String,
    default: ''
  },
  showRoute: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['edit', 'delete', 'view'])

const items = ref([])
const loading = ref(true)
const error = ref('')
const searchTerm = ref('')

const filteredItems = computed(() => {
  if (!searchTerm.value) return items.value
  
  return items.value.filter(item => {
    return Object.values(item).some(value => 
      String(value).toLowerCase().includes(searchTerm.value.toLowerCase())
    )
  })
})

const displayColumns = computed(() => {
  if (props.columns.length > 0) return props.columns
  
  if (items.value.length === 0) return []
  
  return Object.keys(items.value[0]).map(key => ({
    key,
    label: key.charAt(0).toUpperCase() + key.slice(1),
    sortable: true
  }))
})

const loadData = async () => {
  try {
    loading.value = true
    error.value = ''
    const response = await axios.get(props.endpoint)
    items.value = response.data.data || response.data
  } catch (err) {
    error.value = 'Erro ao carregar dados'
    console.error('Erro ao carregar dados:', err)
  } finally {
    loading.value = false
  }
}

const handleEdit = (item) => {
  emit('edit', item)
}

const handleDelete = (item) => {
  if (confirm('Tem certeza que deseja excluir este item?')) {
    emit('delete', item)
  }
}

const handleView = (item) => {
  emit('view', item)
}

const formatValue = (value) => {
  if (value === null || value === undefined) return '-'
  if (typeof value === 'boolean') return value ? 'Sim' : 'Não'
  if (typeof value === 'object') return JSON.stringify(value)
  return String(value)
}

onMounted(() => {
  loadData()
})

// Expor método para recarregar dados
defineExpose({
  reload: loadData
})
</script>

<template>
  <div class="space-y-4">
    <!-- Header com busca e botão criar -->
    <div class="flex justify-between items-center">
      <div v-if="searchable" class="max-w-md">
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <input
            v-model="searchTerm"
            type="search"
            placeholder="Buscar..."
            class="w-full pl-10 pr-4 py-2 bg-background border border-border rounded-lg focus:ring-2 focus:ring-ring focus:border-transparent"
          />
        </div>
      </div>
      
      <router-link
        v-if="createRoute"
        :to="{ name: createRoute }"
        class="bg-primary text-primary-foreground px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors shadow-lg"
      >
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Novo
      </router-link>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-8">
      <svg class="animate-spin h-8 w-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
      </svg>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-destructive/10 border border-destructive/20 rounded-lg p-4">
      <p class="text-destructive">{{ error }}</p>
    </div>

    <!-- Table -->
    <div v-else-if="filteredItems.length > 0" class="bg-card rounded-lg border border-border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-muted/50">
            <tr>
              <th
                v-for="column in displayColumns"
                :key="column.key"
                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider"
              >
                {{ column.label }}
              </th>
              <th v-if="showActions" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">
                Ações
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border">
            <tr
              v-for="item in filteredItems"
              :key="item.id"
              class="hover:bg-muted/50 transition-colors"
            >
              <td
                v-for="column in displayColumns"
                :key="column.key"
                class="px-6 py-4 text-sm text-foreground"
              >
                {{ formatValue(item[column.key]) }}
              </td>
              <td v-if="showActions" class="px-6 py-4 text-sm">
                <div class="flex space-x-2">
                  <button
                    v-if="showRoute"
                    @click="handleView(item)"
                    class="text-primary hover:text-primary/80 transition-colors"
                    title="Visualizar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                  <button
                    v-if="editRoute"
                    @click="handleEdit(item)"
                    class="text-yellow-600 hover:text-yellow-500 transition-colors"
                    title="Editar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button
                    @click="handleDelete(item)"
                    class="text-destructive hover:text-destructive/80 transition-colors"
                    title="Excluir"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-8">
      <svg class="w-12 h-12 text-muted-foreground mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
      </svg>
      <p class="text-muted-foreground">Nenhum registro encontrado</p>
    </div>
  </div>
</template>
