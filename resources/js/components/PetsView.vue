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
          <option v-for="a in apartments" :key="a.id" :value="a.id">{{ formatApartmentDescription(a) }}</option>
        </select>
        <button type="submit" class="px-3 py-1 bg-cyan-600 text-white rounded">Salvar</button>
      </form>
      <ul class="list-disc ml-5 text-cyan-100">
        <li v-for="p in pets" :key="p.id" class="space-x-2">
          <span>{{ p.name }} - {{ p.species }} - {{ p.breed }}</span>
          <span>{{ formatApartmentDescription(p.apartment) }}</span>
        </li>
      </ul>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import { formatApartmentDescription } from '../utils/formatters.js';

export default {
  name: 'PetsView',
  components: { AuthenticatedLayout },
  setup() {
    const pets = ref([]);
    const apartments = ref([]);
    const newPet = ref({
      name: '',
      species: '',
      breed: '',
      photo: '',
      apartment_id: null
    });

    const loadData = async () => {
      const token = localStorage.getItem('token');
      const condo = localStorage.getItem('selectedCondominium');
      const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

      try {
        const respApts = await fetch(`/api/apartments?condominium_id=${condo}`, { headers });
        if (respApts.ok) apartments.value = await respApts.json();

        const respPets = await fetch(`/api/pets?condominium_id=${condo}`, { headers });
        if (respPets.ok) pets.value = await respPets.json();
      } catch (e) {
        console.error('Erro ao carregar dados:', e);
      }
    };

    const createPet = async () => {
      const token = localStorage.getItem('token');
      const condo = localStorage.getItem('selectedCondominium');
      const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

      try {
        const resp = await fetch('/api/pets', {
          method: 'POST',
          headers,
          body: JSON.stringify({ ...newPet.value, condominium_id: condo })
        });

        if (resp.ok) {
          const pet = await resp.json();
          pets.value.push(pet);
          newPet.value = { name: '', species: '', breed: '', photo: '', apartment_id: null };
        }
      } catch (e) {
        console.error('Erro ao criar pet:', e);
      }
    };

    onMounted(loadData);

    return {
      pets,
      apartments,
      newPet,
      createPet,
      formatApartmentDescription
    };
  }
};
</script>
