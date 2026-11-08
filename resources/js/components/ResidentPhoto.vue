<template>
  <div class="relative">
    <!-- Foto do morador -->
    <img 
      v-if="photo && !imageLoadFailed"
      :src="photo" 
      :alt="name"
      class="w-full h-full"
      style="object-fit: cover; object-position: 50% 50%; transform: scale(1.8);"
      @error="handleImageError"
    />
    
    <!-- Avatar padrão quando não há foto -->
    <div 
      v-else
      class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold text-lg"
    >
      {{ getInitials(name) }}
    </div>

    <!-- Indicador de status ativo/inativo -->
    <div 
      v-if="showStatus"
      class="absolute -top-1 -right-1 w-3 h-3 rounded-full border-2 border-white"
      :class="active ? 'bg-green-500' : 'bg-red-500'"
      :title="active ? 'Ativo' : 'Inativo'"
    ></div>
  </div>
</template>

<script>
import { ref, watch } from 'vue';

export default {
  name: 'ResidentPhoto',
  props: {
    photo: {
      type: String,
      default: ''
    },
    name: {
      type: String,
      required: true
    },
    active: {
      type: Boolean,
      default: true
    },
    showStatus: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    const imageLoadFailed = ref(false);

    watch(() => props.photo, () => {
        imageLoadFailed.value = false;
    });

    // Gerar iniciais do nome
    const getInitials = (name) => {
      if (!name) return '?';
      
      const names = name.trim().split(' ');
      if (names.length === 1) {
        return names[0].charAt(0).toUpperCase();
      }
      
      return (names[0].charAt(0) + names[names.length - 1].charAt(0)).toUpperCase();
    };

    // Tratar erro de carregamento da imagem
    const handleImageError = () => {
      imageLoadFailed.value = true;
    };

    return {
      getInitials,
      handleImageError,
      imageLoadFailed
    };
  }
};
</script> 