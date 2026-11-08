<template>
  <div>
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-foreground">Moradores</h1>
        <p class="text-muted-foreground mt-1">Gerencie os moradores do condomínio</p>
      </div>
      <router-link
        :to="{ name: 'residents.create' }"
        class="bg-primary text-primary-foreground px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors shadow-lg"
      >
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Novo Morador
      </router-link>
    </div>

    <!-- Filters -->
    <div class="bg-card rounded-lg border border-border p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <FormField
          v-model="filters.condominium_id"
          type="select"
          name="condominium_filter"
          label="Condomínio"
          placeholder="Todos os condomínios"
          :options="condominiumOptions"
          @update:model-value="applyFilters"
        />
        
        <FormField
          v-model="filters.status"
          type="select"
          name="status_filter"
          label="Status"
          placeholder="Todos os status"
          :options="statusOptions"
          @update:model-value="applyFilters"
        />
        
        <div class="flex items-end space-x-2">
          <ActionButton
            @click="applyFilters"
            variant="primary"
            text="Filtrar"
            size="md"
          />
          <ActionButton
            @click="clearFilters"
            variant="outline"
            text="Limpar"
            size="md"
          />
        </div>
      </div>
    </div>

    <!-- Resource List -->
    <ResourceList
      ref="residentsTable"
      endpoint="/api/residents"
      :columns="columns"
      :searchable="true"
      :show-actions="true"
      create-route="residents.create"
      edit-route="residents.edit"
      show-route="residents.show"
      @edit="handleEdit"
      @delete="handleDelete"
      @view="handleView"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const residentsTable = ref(null)

// Filtros
const filters = ref({
  condominium_id: '',
  status: ''
})

// Opções para filtros
const condominiumOptions = ref([])
const statusOptions = ref([
  { value: '1', label: 'Ativo' },
  { value: '0', label: 'Inativo' }
])

// Configuração das colunas
const columns = ref([
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'condominium_name', label: 'Condomínio', sortable: true },
  { key: 'phone', label: 'Telefone', sortable: false },
  { key: 'email', label: 'E-mail', sortable: true },
  { key: 'status_label', label: 'Status', sortable: true }
])

// Métodos
const loadCondominiums = async () => {
  try {
    const response = await axios.get('/api/condominiums')
    condominiumOptions.value = response.data.map(item => ({
      value: item.id,
      label: item.name
    }))
  } catch (error) {
    console.error('Erro ao carregar condomínios:', error)
  }
}

const applyFilters = () => {
  // Construir query string com filtros
  const params = new URLSearchParams()
  
  if (filters.value.condominium_id) {
    params.append('condominium_id', filters.value.condominium_id)
  }
  
  if (filters.value.status) {
    params.append('status', filters.value.status)
  }
  
  // Atualizar endpoint da tabela
  const endpoint = `/api/residents${params.toString() ? '?' + params.toString() : ''}`
  
  // Recarregar dados da tabela
  if (residentsTable.value) {
    residentsTable.value.reload()
  }
}

const clearFilters = () => {
  filters.value = {
    condominium_id: '',
    status: ''
  }
  applyFilters()
}

const handleEdit = (resident) => {
  router.push({ name: 'residents.edit', params: { id: resident.id } })
}

const handleDelete = async (resident) => {
  try {
    await axios.delete(`/api/residents/${resident.id}`)
    
    // Mostrar mensagem de sucesso
    if (window.showFlash) {
      window.showFlash('success', 'Morador excluído com sucesso!')
    }
    
    // Recarregar tabela
    if (residentsTable.value) {
      residentsTable.value.reload()
    }
  } catch (error) {
    console.error('Erro ao excluir morador:', error)
    
    if (window.showFlash) {
      window.showFlash('error', 'Erro ao excluir morador. Tente novamente.')
    }
  }
}

const handleView = (resident) => {
  router.push({ name: 'residents.show', params: { id: resident.id } })
}

onMounted(() => {
  loadCondominiums()
})
</script>
