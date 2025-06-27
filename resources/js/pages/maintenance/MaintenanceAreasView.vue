<template>
  <AuthenticatedLayout page-title="Áreas de Manutenção">
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="bg-gray-800/50 backdrop-blur-md shadow-xl rounded-2xl p-6 border border-cyan-500/30">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold text-white">Gerenciar Áreas de Manutenção</h2>
          <button @click="openFormModal()"
                  class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg shadow-md transition-colors">
            Nova Área
          </button>
        </div>

        <!-- Tabela de Áreas -->
        <div v-if="isLoadingAreas" class="text-center text-gray-400 py-8">Carregando áreas...</div>
        <div v-else-if="areas.length === 0" class="text-center text-gray-400 py-8">Nenhuma área encontrada.</div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-700/50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Nome</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Descrição</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-gray-800/30 divide-y divide-gray-700">
              <tr v-for="area in areas" :key="area.id" class="hover:bg-gray-700/60 transition-colors duration-150">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">{{ area.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ area.description || 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                  <button @click="openFormModal(area)" class="text-cyan-400 hover:text-cyan-300 transition-colors">Editar</button>
                  <button @click="confirmDeleteArea(area.id)" class="text-red-500 hover:text-red-400 transition-colors">Excluir</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginação -->
        <div v-if="!isLoadingAreas && pagination.total > pagination.per_page" class="mt-6 flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
            <p class="text-sm text-gray-400">
                Mostrando {{ pagination.from || 0 }} a {{ pagination.to || 0 }} de {{ pagination.total || 0 }} resultados.
            </p>
            <div class="flex space-x-1">
                <button @click="fetchAreas(1)" :disabled="pagination.current_page === 1"
                        class="px-3 py-1 text-sm bg-gray-600 hover:bg-gray-500 rounded disabled:opacity-50 transition-colors">
                    Primeira
                </button>
                <button @click="fetchAreas(pagination.current_page - 1)" :disabled="!pagination.prev_page_url"
                        class="px-3 py-1 text-sm bg-gray-600 hover:bg-gray-500 rounded disabled:opacity-50 transition-colors">
                    Anterior
                </button>
                 <span class="px-3 py-1 text-sm text-gray-300">Página {{ pagination.current_page }} de {{ pagination.last_page }}</span>
                <button @click="fetchAreas(pagination.current_page + 1)" :disabled="!pagination.next_page_url"
                        class="px-3 py-1 text-sm bg-gray-600 hover:bg-gray-500 rounded disabled:opacity-50 transition-colors">
                    Próxima
                </button>
                 <button @click="fetchAreas(pagination.last_page)" :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-1 text-sm bg-gray-600 hover:bg-gray-500 rounded disabled:opacity-50 transition-colors">
                    Última
                </button>
            </div>
        </div>
      </div>
    </div>

    <MaintenanceAreaForm
      :show="isFormModalOpen"
      :area-data="selectedArea"
      @close="closeFormModal"
      @saved="handleSave"
    />

    <Modal :show="isDeleteConfirmOpen" @close="isDeleteConfirmOpen = false" max-width="md">
        <template #title>Confirmar Exclusão</template>
        <div class="p-6">
            <p class="text-gray-300">Tem certeza que deseja excluir a área "{{ areaToDelete?.name || 'selecionada' }}"? Esta ação não pode ser desfeita.</p>
        </div>
        <template #footer>
            <button @click="isDeleteConfirmOpen = false" class="px-4 py-2 text-gray-300 bg-gray-600 hover:bg-gray-500 rounded-md shadow-sm transition-colors">Cancelar</button>
            <button @click="deleteArea" :disabled="isDeleting" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md ml-3 shadow-sm transition-colors disabled:opacity-50">
              {{ isDeleting ? 'Excluindo...' : 'Excluir' }}
            </button>
        </template>
    </Modal>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import MaintenanceAreaForm from '@/components/maintenance/MaintenanceAreaForm.vue';
import Modal from '@/components/Modal.vue';
import api from '@/utils/api';

const areas = ref([]);
const isLoadingAreas = ref(false);
const isFormModalOpen = ref(false);
const selectedArea = ref(null);

const pagination = ref({
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0,
    total: 0,
    per_page: 15,
    next_page_url: null,
    prev_page_url: null,
});

const isDeleteConfirmOpen = ref(false);
const areaToDeleteId = ref(null);
const isDeleting = ref(false);

const areaToDelete = computed(() => {
    return areas.value.find(a => a.id === areaToDeleteId.value);
});

async function fetchAreas(page = 1) {
  isLoadingAreas.value = true;
  try {
    const response = await api.get(`/maintenance/areas?page=${page}&per_page=${pagination.value.per_page}`);
    areas.value = response.data.data || [];

    const { current_page, last_page, from, to, total, per_page: respPerPage, next_page_url, prev_page_url } = response.data;
    pagination.value = { current_page, last_page, from, to, total, per_page: respPerPage || pagination.value.per_page, next_page_url, prev_page_url };

  } catch (error) {
    console.error('Erro ao buscar áreas:', error);
    areas.value = [];
  } finally {
    isLoadingAreas.value = false;
  }
}

function openFormModal(area = null) {
  selectedArea.value = area ? JSON.parse(JSON.stringify(area)) : null;
  isFormModalOpen.value = true;
}

function closeFormModal() {
  isFormModalOpen.value = false;
  selectedArea.value = null;
}

function handleSave() {
  fetchAreas(pagination.value.current_page);
  closeFormModal();
}

function confirmDeleteArea(id) {
    areaToDeleteId.value = id;
    isDeleteConfirmOpen.value = true;
}

async function deleteArea() {
    if (!areaToDeleteId.value) return;
    isDeleting.value = true;
    try {
        await api.delete(`/maintenance/areas/${areaToDeleteId.value}`);
        let currentPage = pagination.value.current_page;
        if (areas.value.length === 1 && currentPage > 1) {
            currentPage--;
        }
        fetchAreas(currentPage);
    } catch (error) {
        console.error('Erro ao excluir área:', error);
        if (error.response && error.response.data.message) {
            alert(`Erro ao excluir: ${error.response.data.message}`);
        } else {
            alert('Ocorreu um erro ao excluir a área.');
        }
    } finally {
        isDeleteConfirmOpen.value = false;
        areaToDeleteId.value = null;
        isDeleting.value = false;
    }
}

onMounted(() => {
  fetchAreas();
});
</script>
