<template>
  <div v-if="selectedCondominiumName" class="bg-indigo-500/10 border border-indigo-500/30 rounded-lg p-4">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <span class="text-indigo-300 font-medium">{{ label || 'Condomínio:' }}</span>
        <span class="text-white font-semibold">{{ selectedCondominiumName }}</span>
      </div>
      <NeonButton 
        v-if="showChangeButton" 
        @click="changeCondominium" 
        size="sm" 
        class="bg-gray-600/40 hover:bg-gray-600/60"
      >
        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Alterar
      </NeonButton>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import NeonButton from './NeonButton.vue';

export default {
  name: 'CondominiumSelector',
  components: { NeonButton },
  props: {
    label: {
      type: String,
      default: 'Condomínio:'
    },
    showChangeButton: {
      type: Boolean,
      default: false
    }
  },
  setup() {
    const router = useRouter();
    const selectedCondominiumName = ref(localStorage.getItem('selectedCondominiumName'));

    const changeCondominium = () => {
      localStorage.removeItem('selectedCondominium');
      localStorage.removeItem('selectedCondominiumName');
      router.push('/condominium-select');
    };

    return {
      selectedCondominiumName,
      changeCondominium
    };
  }
};
</script> 