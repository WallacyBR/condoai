<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Overlay -->
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 transition-opacity" aria-hidden="true">
        <div class="absolute inset-0 bg-gray-900 opacity-75" @click="closeModal"></div>
      </div>

      <!-- Modal -->
      <div class="inline-block align-bottom bg-gray-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="relative">
          <!-- Botão fechar -->
          <button
            @click="closeModal"
            class="absolute top-4 right-4 text-gray-400 hover:text-white transition-colors"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Conteúdo -->
          <ResidentForm
            :resident="resident"
            :condominiums="condominiums"
            @submit="handleSubmit"
            @cancel="closeModal"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue';
import ResidentForm from './ResidentForm.vue';

export default {
  name: 'ResidentModal',
  components: { ResidentForm },
  props: {
    show: {
      type: Boolean,
      default: false
    },
    resident: {
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
    const closeModal = () => {
      emit('close');
    };

    const handleSubmit = (data) => {
      emit('submit', data);
      closeModal();
    };

    return {
      closeModal,
      handleSubmit
    };
  }
};
</script> 