<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-gray-900 rounded-2xl p-6 w-full max-w-md mx-4 border-2 border-indigo-500/30 shadow-2xl">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-white">
          {{ visit ? 'Editar Visita' : 'Nova Visita' }}
        </h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-white">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Nome do Visitante -->
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">
            Nome do Visitante *
          </label>
          <input
            v-model="form.visitor_name"
            type="text"
            required
            class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
            placeholder="Nome completo do visitante"
          />
        </div>

        <!-- Telefone do Visitante -->
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">
            Telefone do Visitante *
          </label>
          <input
            v-model="form.visitor_phone"
            type="tel"
            required
            class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
            placeholder="(11) 99999-9999"
          />
        </div>

        <!-- Número da Unidade -->
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">
            Número da Unidade *
          </label>
          <input
            v-model="form.unit_number"
            type="text"
            required
            class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
            placeholder="Ex: 101, 205, 310"
          />
        </div>

        <!-- Data e Hora da Visita -->
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">
            Data e Hora da Visita *
          </label>
          <input
            v-model="form.visit_date"
            type="datetime-local"
            required
            class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          />
        </div>

        <!-- Status da Visita -->
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">
            Status da Visita
          </label>
          <select
            v-model="form.status"
            class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          >
            <option value="pending">Pendente</option>
            <option value="approved">Aprovada</option>
            <option value="rejected">Rejeitada</option>
          </select>
        </div>

        <!-- Observações -->
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-2">
            Observações
          </label>
          <textarea
            v-model="form.observations"
            rows="3"
            class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
            placeholder="Observações sobre a visita..."
          ></textarea>
        </div>

        <!-- Condomínio (se não estiver selecionado) -->
        <div v-if="!selectedCondominiumId">
          <label class="block text-sm font-medium text-gray-300 mb-2">
            Condomínio *
          </label>
          <select
            v-model="form.condominium_id"
            required
            class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          >
            <option value="">Selecione um condomínio</option>
            <option v-for="condo in condominiums" :key="condo.id" :value="condo.id">
              {{ condo.name }}
            </option>
          </select>
        </div>

        <!-- Botões -->
        <div class="flex space-x-3 pt-4">
          <button
            type="button"
            @click="$emit('close')"
            class="flex-1 px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors"
          >
            Cancelar
          </button>
          <button
            type="submit"
            class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-500 transition-colors"
            :disabled="loading"
          >
            <span v-if="loading" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Salvando...
            </span>
            <span v-else>{{ visit ? 'Atualizar' : 'Criar' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, watch, computed } from 'vue';

export default {
  name: 'VisitModal',
  props: {
    show: { type: Boolean, required: true },
    visit: { type: Object, default: null },
    condominiums: { type: Array, default: () => [] }
  },
  emits: ['close', 'submit'],
  setup(props, { emit }) {
    const loading = ref(false);
    
    const selectedCondominiumId = ref(localStorage.getItem('selectedCondominium'));

    const form = ref({
      visitor_name: '',
      visitor_phone: '',
      unit_number: '',
      visit_date: '',
      status: 'pending',
      observations: '',
      condominium_id: selectedCondominiumId.value || ''
    });

    // Resetar formulário quando modal abrir/fechar
    watch(() => props.show, (newShow) => {
      if (newShow) {
        if (props.visit) {
          // Modo edição
          form.value = {
            visitor_name: props.visit.visitor_name || '',
            visitor_phone: props.visit.visitor_phone || '',
            unit_number: props.visit.unit_number || '',
            visit_date: props.visit.visit_date ? props.visit.visit_date.slice(0, 16) : '',
            status: props.visit.status || 'pending',
            observations: props.visit.observations || '',
            condominium_id: props.visit.condominium_id || selectedCondominiumId.value || ''
          };
        } else {
          // Modo criação
          form.value = {
            visitor_name: '',
            visitor_phone: '',
            unit_number: '',
            visit_date: new Date().toISOString().slice(0, 16),
            status: 'pending',
            observations: '',
            condominium_id: selectedCondominiumId.value || ''
          };
        }
      }
    });

    const handleSubmit = async () => {
      loading.value = true;
      
      try {
        const token = localStorage.getItem('token');
        const url = props.visit 
          ? `/api/visitor-units/${props.visit.id}` 
          : '/api/visitor-units';
        
        const method = props.visit ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
          method,
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(form.value)
        });

        if (response.ok) {
          const data = await response.json();
          emit('submit', data);
          emit('close');
        } else {
          const errorData = await response.json();
          console.error('Erro ao salvar visita:', errorData);
          // Aqui você pode adicionar uma notificação de erro
        }
      } catch (error) {
        console.error('Erro ao salvar visita:', error);
        // Aqui você pode adicionar uma notificação de erro
      } finally {
        loading.value = false;
      }
    };

    return {
      form,
      loading,
      selectedCondominiumId,
      handleSubmit
    };
  }
};
</script> 