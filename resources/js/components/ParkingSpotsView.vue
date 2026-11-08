<template>
  <AuthenticatedLayout page-title="Vagas">
    <div class="space-y-8">
      <form @submit.prevent="createSpot" class="flex space-x-2 mb-4">
        <input v-model="newSpot.identifier" class="border rounded px-2" placeholder="Identificador" />
        <select v-model="newSpot.apartment_id" class="border rounded px-2">
          <option :value="null">Selecione a unidade</option>
          <option v-for="a in apartments" :value="a.id" :key="a.id">{{ a.number }}</option>
        </select>
        <button type="submit" class="px-3 py-1 bg-cyan-600 hover:bg-cyan-500 transition-colors text-white rounded">Salvar</button>
      </form>
      <ul class="list-disc ml-5 text-cyan-100">
        <li v-for="spot in spots" :key="spot.id" class="space-x-2">
          <span>{{ spot.identifier }} - unidade {{ spot.apartment.number }}</span>
          <span v-if="spot.rented_to_apartment">alugada para {{ spot.rented_to_apartment.number }}</span>
          <select v-model="spot.rented_to_apartment_id" @change="rentSpot(spot)" class="border rounded px-2 ml-2">
            <option :value="null">Livre</option>
            <option v-for="a in apartments" :value="a.id" :key="a.id">{{ a.number }}</option>
          </select>
        </li>
      </ul>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';

export default {
  name: 'ParkingSpotsView',
  components: { AuthenticatedLayout },
  setup() {
    const token = localStorage.getItem('token');
    const condo = localStorage.getItem('selectedCondominium');
    const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

    const spots = ref([]);
    const apartments = ref([]);
    const newSpot = ref({ identifier: '', apartment_id: null, condominium_id: null });

    const loadData = async () => {
      const respApts = await fetch(`/api/apartments?condominium_id=${condo}`, { headers });
      apartments.value = respApts.ok ? await respApts.json() : [];
      const respSpots = await fetch(`/api/parking-spots?condominium_id=${condo}`, { headers });
      spots.value = respSpots.ok ? await respSpots.json() : [];
    };

    const createSpot = async () => {
      if (!newSpot.value.identifier || !newSpot.value.apartment_id) return;
      newSpot.value.condominium_id = condo;
      const resp = await fetch('/api/parking-spots', { method: 'POST', headers, body: JSON.stringify(newSpot.value) });
      if (resp.ok) {
        spots.value.push(await resp.json());
        newSpot.value.identifier = '';
        newSpot.value.apartment_id = null;
      }
    };

    const rentSpot = async (spot) => {
      const payload = {
        rented_to_apartment_id: spot.rented_to_apartment_id ?? null
      };
      const resp = await fetch(`/api/parking-spots/${spot.id}/rent`, {
        method: 'POST',
        headers,
        body: JSON.stringify(payload)
      });
      if (resp.ok) {
        const updated = await resp.json();
        Object.assign(spot, updated);
      }
    };

    onMounted(loadData);

    return { spots, apartments, newSpot, createSpot, rentSpot };
  }
};
</script>
