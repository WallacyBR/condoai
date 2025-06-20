<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 p-4 rounded w-96">
      <h2 class="text-lg font-bold mb-2">Informa\u00e7\u00f5es</h2>
      <img v-if="pkg.photo" :src="pkg.photo" class="mb-2 w-full object-cover" />
      <p><strong>Data:</strong> {{ pkg.created_at?.slice(0,10) }}</p>
      <p><strong>Unidade:</strong> {{ formatApartmentDescription(pkg.apartment) }}</p>
      <p><strong>Usu\u00e1rio:</strong> {{ pkg.user?.name }}</p>
      <p><strong>Local:</strong> {{ pkg.pickup_location }}</p>
      <p><strong>Observa\u00e7\u00e3o:</strong> {{ pkg.observation }}</p>
      <div class="text-right mt-2">
        <button @click="$emit('close')" class="px-2 py-1 bg-cyan-600 text-white rounded">Fechar</button>
      </div>
    </div>
  </div>
</template>

<script>
import { formatApartmentDescription } from '../utils/formatters';

export default {
  name: 'PackageInfoModal',
  props: { pkg: Object },
  setup() {
    return { formatApartmentDescription };
  }
};
</script>
