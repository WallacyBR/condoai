<template>
  <AuthenticatedLayout page-title="Veículos">
    <div class="space-y-8">
      <form @submit.prevent="createVehicle" class="space-x-2 mb-4 flex flex-wrap">
        <input v-model="newVehicle.license_plate" class="border rounded px-2" placeholder="Placa" />
        <input v-model="newVehicle.model" class="border rounded px-2" placeholder="Modelo" />
        <input v-model="newVehicle.color" class="border rounded px-2" placeholder="Cor" />
        <select v-model="newVehicle.apartment_id" class="border rounded px-2">
          <option :value="null">Unidade</option>
          <option v-for="a in apartments" :value="a.id" :key="a.id">{{ a.number }}</option>
        </select>
        <select v-model="newVehicle.parking_spot_id" class="border rounded px-2">
          <option :value="null">Vaga</option>
          <option v-for="s in spots" :value="s.id" :key="s.id">{{ s.identifier }}</option>
        </select>
        <button type="submit" class="px-3 py-1 bg-cyan-600 text-white rounded">Salvar</button>
      </form>
      <ul class="list-disc ml-5 text-cyan-100">
        <li v-for="v in vehicles" :key="v.id" class="space-x-2">
          <span>{{ v.license_plate }} - {{ v.model }} - {{ v.color }}</span>
          <span>Unidade {{ v.apartment.number }}</span>
          <span v-if="v.parking_spot">- Vaga {{ v.parking_spot.identifier }}</span>
        </li>
      </ul>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';

export default {
  name: 'VehiclesView',
  components: { AuthenticatedLayout },
  setup() {
    const token = localStorage.getItem('token');
    const condo = localStorage.getItem('selectedCondominium');
    const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

    const vehicles = ref([]);
    const apartments = ref([]);
    const spots = ref([]);
    const newVehicle = ref({ license_plate: '', model: '', color: '', apartment_id: null, parking_spot_id: null });

    const loadData = async () => {
      const [aRes, sRes, vRes] = await Promise.all([
        fetch(`/api/apartments?condominium_id=${condo}`, { headers }),
        fetch(`/api/parking-spots?condominium_id=${condo}`, { headers }),
        fetch('/api/vehicles', { headers })
      ]);
      apartments.value = aRes.ok ? await aRes.json() : [];
      spots.value = sRes.ok ? await sRes.json() : [];
      vehicles.value = vRes.ok ? await vRes.json() : [];
    };

    const createVehicle = async () => {
      if (!newVehicle.value.license_plate || !newVehicle.value.apartment_id || !newVehicle.value.model || !newVehicle.value.color) return;
      const resp = await fetch('/api/vehicles', { method: 'POST', headers, body: JSON.stringify(newVehicle.value) });
      if (resp.ok) {
        vehicles.value.push(await resp.json());
        newVehicle.value.license_plate = '';
        newVehicle.value.model = '';
        newVehicle.value.color = '';
        newVehicle.value.apartment_id = null;
        newVehicle.value.parking_spot_id = null;
      }
    };

    onMounted(loadData);

    return { vehicles, apartments, spots, newVehicle, createVehicle };
  }
};
</script>
