<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="close"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
              <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-white">
                {{ unit ? 'Editar Unidade' : 'Nova Unidade' }}
              </h3>
              <div class="mt-4">
                <form @submit.prevent="submit" class="space-y-4">
                  <!-- Número da Unidade -->
                  <div>
                    <label for="number" class="block text-sm font-medium text-gray-300">
                      Número da Unidade *
                    </label>
                    <input
                      id="number"
                      v-model="form.number"
                      type="text"
                      required
                      class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Ex: 101, 205, etc."
                    />
                  </div>

                  <!-- Tipo da Unidade -->
                  <div>
                    <label for="type" class="block text-sm font-medium text-gray-300">
                      Tipo *
                    </label>
                    <select
                      id="type"
                      v-model="form.type"
                      required
                      class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                      <option value="">Selecione o tipo</option>
                      <option value="apartment">Apartamento</option>
                      <option value="house">Casa</option>
                      <option value="commercial">Comercial</option>
                    </select>
                  </div>

                  <!-- Status -->
                  <div>
                    <label for="status" class="block text-sm font-medium text-gray-300">
                      Status *
                    </label>
                    <select
                      id="status"
                      v-model="form.status"
                      required
                      class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    >
                      <option value="">Selecione o status</option>
                      <option value="occupied">Ocupada</option>
                      <option value="vacant">Vazia</option>
                    </select>
                  </div>

                  <!-- Andar -->
                  <div>
                    <label for="floor" class="block text-sm font-medium text-gray-300">
                      Andar *
                    </label>
                    <input
                      id="floor"
                      v-model.number="form.floor"
                      type="number"
                      min="0"
                      required
                      class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Ex: 1, 2, 3..."
                    />
                  </div>

                  <!-- Área -->
                  <div>
                    <label for="area" class="block text-sm font-medium text-gray-300">
                      Área (m²) *
                    </label>
                    <input
                      id="area"
                      v-model.number="form.area"
                      type="number"
                      min="0"
                      step="0.01"
                      required
                      class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Ex: 85.5"
                    />
                  </div>

                  <!-- Nome do Proprietário -->
                  <div>
                    <label for="owner_name" class="block text-sm font-medium text-gray-300">
                      Nome do Proprietário
                    </label>
                    <input
                      id="owner_name"
                      v-model="form.owner_name"
                      type="text"
                      class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Nome do proprietário (opcional)"
                    />
                  </div>

                  <!-- Telefone do Proprietário -->
                  <div>
                    <label for="owner_phone" class="block text-sm font-medium text-gray-300">
                      Telefone do Proprietário
                    </label>
                    <input
                      id="owner_phone"
                      v-model="form.owner_phone"
                      type="tel"
                      class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="(11) 99999-9999"
                    />
                  </div>

                  <!-- Email do Proprietário -->
                  <div>
                    <label for="owner_email" class="block text-sm font-medium text-gray-300">
                      Email do Proprietário
                    </label>
                    <input
                      id="owner_email"
                      v-model="form.owner_email"
                      type="email"
                      class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="proprietario@email.com"
                    />
                  </div>

                  <!-- Observações -->
                  <div>
                    <label for="notes" class="block text-sm font-medium text-gray-300">
                      Observações
                    </label>
                    <textarea
                      id="notes"
                      v-model="form.notes"
                      rows="3"
                      class="mt-1 block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Observações adicionais..."
                    ></textarea>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button
            type="button"
            @click="submit"
            :disabled="loading"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ loading ? 'Salvando...' : (unit ? 'Atualizar' : 'Criar') }}
          </button>
          <button
            type="button"
            @click="close"
            :disabled="loading"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-600 shadow-sm px-4 py-2 bg-gray-700 text-base font-medium text-gray-300 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue';
import { apiRequest } from '../utils/api';

export default {
  name: 'UnitModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    unit: {
      type: Object,
      default: null
    },
    condominiums: {
      type: Array,
      default: () => []
    }
  },
  emits: ['close', 'submit'],
  setup(props, { emit }) {
    const loading = ref(false);
    
    const form = ref({
      number: '',
      type: '',
      status: '',
      floor: null,
      area: null,
      owner_name: '',
      owner_phone: '',
      owner_email: '',
      notes: ''
    });

    // Reset form when modal opens/closes
    watch(() => props.show, (newVal) => {
      if (newVal) {
        if (props.unit) {
          // Edit mode - populate form with existing data
          const { condominium_id: condominiumId, ...unitData } = props.unit;
          form.value = unitData;
        } else {
          // Create mode - reset form
          form.value = {
            number: '',
            type: '',
            status: '',
            floor: null,
            area: null,
            owner_name: '',
            owner_phone: '',
            owner_email: '',
            notes: ''
          };
        }
      }
    });

    const close = () => {
      if (!loading.value) {
        emit('close');
      }
    };

    const submit = async () => {
      try {
        loading.value = true;

        const url = props.unit ? `/units/${props.unit.id}` : '/units';
        const method = props.unit ? 'PUT' : 'POST';

        const data = await apiRequest(url, {
          method,
          body: JSON.stringify(form.value)
        });

        emit('submit', data);
        close();
      } catch (error) {
        // Notificacao visual deve ser adicionada pela tela chamadora.
      } finally {
        loading.value = false;
      }
    };

    return {
      form,
      loading,
      close,
      submit
    };
  }
};
</script>
