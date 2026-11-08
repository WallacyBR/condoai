<template>
  <div class="p-6">
    <div v-if="loading" class="flex justify-center py-4">Carregando...</div>
    <div v-else>
      <ul class="space-y-2">
        <li v-for="area in areas" :key="area.id" class="border p-2 rounded">
          <span class="font-semibold">{{ area.name }}</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
export default {
  name: 'CommonAreaList',
  setup() {
    const areas = ref([]);
    const loading = ref(true);
    const token = localStorage.getItem('token');

    const loadData = async () => {
      const resp = await fetch('/api/common-areas', {
        headers: { Authorization: `Bearer ${token}` }
      });
      if (resp.ok) {
        areas.value = await resp.json();
      }
      loading.value = false;
    };

    onMounted(loadData);
    return { areas, loading };
  }
};
</script>
