<template>
  <AuthenticatedLayout page-title="Áreas Comuns">
    <div class="space-y-8">
      <form @submit.prevent="createArea" class="flex space-x-2 mb-4">
        <input v-model="newArea.name" class="border rounded px-2" placeholder="Nome" />
        <input v-model.number="newArea.capacity" type="number" class="border rounded px-2" placeholder="Capacidade" />
        <button type="submit" class="px-3 py-1 bg-cyan-600 text-white rounded">Salvar</button>
      </form>
      <ul class="list-disc ml-5 text-cyan-100">
        <li v-for="area in areas" :key="area.id" class="space-x-2">
          <span>{{ area.name }} ({{ area.capacity }})</span>
          <CommonAreaCalendar :area="area" />
        </li>
      </ul>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import CommonAreaCalendar from './CommonAreaCalendar.vue';

export default {
  name: 'CommonAreasView',
  components: { AuthenticatedLayout, CommonAreaCalendar },
  setup() {
    const token = localStorage.getItem('token');
    const condo = localStorage.getItem('selectedCondominium');
    const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

    const areas = ref([]);
    const newArea = ref({ name: '', capacity: 0, condominium_id: condo });

    const loadAreas = async () => {
      const resp = await fetch(`/api/common-areas?condominium_id=${condo}`, { headers });
      areas.value = resp.ok ? await resp.json() : [];
    };

    const createArea = async () => {
      if (!newArea.value.name) return;
      const resp = await fetch('/api/common-areas', { method: 'POST', headers, body: JSON.stringify(newArea.value) });
      if (resp.ok) {
        areas.value.push(await resp.json());
        newArea.value.name = '';
        newArea.value.capacity = 0;
      }
    };

    onMounted(loadAreas);

    return { areas, newArea, createArea };
  }
};
</script>
