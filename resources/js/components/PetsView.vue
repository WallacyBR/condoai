<template>
  <AuthenticatedLayout page-title="Pets">
    <div class="space-y-8">
      <form @submit.prevent="createPet" class="space-x-2 mb-4 flex flex-wrap">
        <input v-model="newPet.name" class="border rounded px-2" placeholder="Nome" />
        <input v-model="newPet.species" class="border rounded px-2" placeholder="Espécie" />
        <input v-model="newPet.breed" class="border rounded px-2" placeholder="Raça" />
        <input v-model="newPet.photo" class="border rounded px-2" placeholder="URL da foto" />
        <select v-model="newPet.apartment_id" class="border rounded px-2">
          <option :value="null">Unidade</option>
          <option v-for="a in apartments" :key="a.id" :value="a.id">{{ a.number }}</option>
        </select>
        <button type="submit" class="px-3 py-1 bg-cyan-600 text-white rounded">Salvar</button>
      </form>
      <ul class="list-disc ml-5 text-cyan-100">
        <li v-for="p in pets" :key="p.id" class="space-x-2">
          <span>{{ p.name }} - {{ p.species }} - {{ p.breed }}</span>
          <span>Unidade {{ p.apartment.number }}</span>
        </li>
      </ul>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';

export default {
  name: 'PetsView',
  components: { AuthenticatedLayout },
  setup() {
    const token = localStorage.getItem('token');
    const condo = localStorage.getItem('selectedCondominium');
    const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

    const pets = ref([]);
    const apartments = ref([]);
    const newPet = ref({ name: '', species: '', breed: '', photo: '', apartment_id: null });

    const loadData = async () => {
      const [aRes, pRes] = await Promise.all([
        fetch(`/api/apartments?condominium_id=${condo}`, { headers }),
        fetch('/api/pets', { headers })
      ]);
      apartments.value = aRes.ok ? await aRes.json() : [];
      pets.value = pRes.ok ? await pRes.json() : [];
    };

    const createPet = async () => {
      if (!newPet.value.name || !newPet.value.species || !newPet.value.breed || !newPet.value.apartment_id) return;
      const resp = await fetch('/api/pets', { method: 'POST', headers, body: JSON.stringify(newPet.value) });
      if (resp.ok) {
        pets.value.push(await resp.json());
        newPet.value.name = '';
        newPet.value.species = '';
        newPet.value.breed = '';
        newPet.value.photo = '';
        newPet.value.apartment_id = null;
      }
    };

    onMounted(loadData);

    return { pets, apartments, newPet, createPet };
  }
};
</script>
