<template>
  <div class="bg-gray-900 rounded-lg p-6 border border-gray-700">
    <h3 class="text-lg font-semibold text-white mb-4">
      {{ isEditing ? 'Editar Morador' : 'Novo Morador' }}
    </h3>
    
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Nome -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
          Nome *
        </label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          required
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          placeholder="Nome completo do morador"
        />
        <p v-if="errors.name" class="mt-1 text-sm text-red-400">{{ errors.name }}</p>
      </div>

      <!-- Email -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
          Email *
        </label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          required
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          placeholder="email@exemplo.com"
        />
        <p v-if="errors.email" class="mt-1 text-sm text-red-400">{{ errors.email }}</p>
      </div>

      <!-- Telefone -->
      <div>
        <label for="phone" class="block text-sm font-medium text-gray-300 mb-2">
          Telefone
        </label>
        <input
          id="phone"
          v-model="form.phone"
          type="tel"
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          placeholder="(11) 99999-9999"
        />
        <p v-if="errors.phone" class="mt-1 text-sm text-red-400">{{ errors.phone }}</p>
      </div>

      <!-- Classificação -->
      <div>
        <label for="classification" class="block text-sm font-medium text-gray-300 mb-2">
          Classificação *
        </label>
        <select
          id="classification"
          v-model="form.classification"
          required
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
        >
          <option value="">Selecione uma classificação</option>
          <option value="proprietario">Proprietário</option>
          <option value="morador">Morador</option>
          <option value="inquilino">Inquilino</option>
        </select>
        <p class="mt-1 text-xs text-gray-500">
          <strong>Proprietário:</strong> Proprietário do imóvel (pode ser morador ou não)<br>
          <strong>Morador:</strong> Pessoa que reside no imóvel<br>
          <strong>Inquilino:</strong> Pessoa que aluga o imóvel
        </p>
        <p v-if="errors.classification" class="mt-1 text-sm text-red-400">{{ errors.classification }}</p>
      </div>

      <!-- Status Ativo -->
      <div class="flex items-center">
        <input
          id="active"
          v-model="form.active"
          type="checkbox"
          class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800"
        />
        <label for="active" class="ml-2 block text-sm text-gray-300">
          Morador ativo
        </label>
      </div>

      <!-- Condomínio -->
      <div v-if="condominiums.length > 0">
        <label for="condominium_id" class="block text-sm font-medium text-gray-300 mb-2">
          Condomínio *
        </label>
        <select
          id="condominium_id"
          v-model="form.condominium_id"
          required
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
        >
          <option value="">Selecione um condomínio</option>
          <option 
            v-for="condominium in condominiums" 
            :key="condominium.id" 
            :value="condominium.id"
          >
            {{ condominium.name }}
          </option>
        </select>
        <p v-if="errors.condominium_id" class="mt-1 text-sm text-red-400">{{ errors.condominium_id }}</p>
      </div>

      <!-- Botões -->
      <div class="flex space-x-3 pt-4">
        <NeonButton 
          type="submit" 
          :loading="loading"
          class="flex-1"
        >
          {{ isEditing ? 'Atualizar' : 'Criar' }} Morador
        </NeonButton>
        <NeonButton 
          type="button" 
          @click="$emit('cancel')"
          variant="secondary"
          class="flex-1"
        >
          Cancelar
        </NeonButton>
      </div>
    </form>
  </div>
</template>

<script>
import { ref, reactive, computed, watch } from 'vue';
import NeonButton from './NeonButton.vue';

export default {
  name: 'ResidentForm',
  components: { NeonButton },
  props: {
    resident: {
      type: Object,
      default: null
    },
    condominiums: {
      type: Array,
      default: () => []
    }
  },
  emits: ['submit', 'cancel'],
  setup(props, { emit }) {
    const loading = ref(false);
    const errors = ref({});

    const form = reactive({
      name: '',
      email: '',
      phone: '',
      classification: '',
      active: true,
      condominium_id: ''
    });

    const isEditing = computed(() => !!props.resident);

    // Preencher formulário quando editar
    watch(() => props.resident, (newResident) => {
      if (newResident) {
        form.name = newResident.name || '';
        form.email = newResident.email || '';
        form.phone = newResident.phone || '';
        form.classification = newResident.classification || '';
        form.active = newResident.active !== undefined ? newResident.active : true;
        form.condominium_id = newResident.condominium_id || '';
      }
    }, { immediate: true });

    const handleSubmit = async () => {
      loading.value = true;
      errors.value = {};

      try {
        const token = localStorage.getItem('token');
        const url = isEditing.value 
          ? `/api/residents/${props.resident.id}`
          : '/api/residents';
        
        const method = isEditing.value ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
          method,
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(form)
        });

        const data = await response.json();

        if (!response.ok) {
          if (data.errors) {
            errors.value = data.errors;
          } else {
            errors.value = { general: data.message || 'Erro ao salvar morador' };
          }
          return;
        }

        emit('submit', data);
      } catch (error) {
        console.error('Erro ao salvar morador:', error);
        errors.value = { general: 'Erro de conexão. Tente novamente.' };
      } finally {
        loading.value = false;
      }
    };

    return {
      form,
      loading,
      errors,
      isEditing,
      handleSubmit
    };
  }
};
</script> 