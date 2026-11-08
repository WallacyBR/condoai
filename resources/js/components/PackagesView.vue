<template>
  <AuthenticatedLayout page-title="Encomendas">
    <div class="space-y-8">
      <form @submit.prevent="createPackage" class="space-x-2 mb-4 flex flex-wrap">
        <select v-model="newPackage.apartment_id" class="border rounded px-2">
          <option :value="null">Unidade</option>
          <option v-for="a in apartments" :key="a.id" :value="a.id">{{ formatApartmentDescription(a) }}</option>
        </select>
        <input v-model="newPackage.photo" class="border rounded px-2" placeholder="URL da foto" />
        <button type="submit" class="px-3 py-1 bg-cyan-600 text-white rounded">Registrar</button>
      </form>
      <ul class="list-disc ml-5 text-cyan-100">
        <li v-for="p in packages" :key="p.id" class="space-x-2">
          <span>{{ formatApartmentDescription(p.apartment) }}</span>
          <span v-if="p.retrieved_at" class="text-green-400">Retirada</span>
          <button v-else class="px-2 py-1 bg-green-600 rounded" @click="withdraw(p)">Retirar</button>
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
  name: 'PackagesView',
  components: { AuthenticatedLayout },
  setup() {
    const packages = ref([]);
    const apartments = ref([]);
    const newPackage = ref({
      apartment_id: null,
      photo: ''
    });

    const loadData = async () => {
      const token = localStorage.getItem('token');
      const condo = localStorage.getItem('selectedCondominium');
      const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

      try {
        const [aptsRes, packagesRes] = await Promise.all([
          fetch(`/api/apartments?condominium_id=${condo}`, { headers }),
          fetch(`/api/packages?condominium_id=${condo}`, { headers })
        ]);

        if (aptsRes.ok) apartments.value = await aptsRes.json();
        if (packagesRes.ok) packages.value = await packagesRes.json();
      } catch (e) {
        console.error('Erro ao carregar dados:', e);
      }
    };

    const createPackage = async () => {
      const token = localStorage.getItem('token');
      const condo = localStorage.getItem('selectedCondominium');
      const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

      try {
        const resp = await fetch('/api/packages', {
          method: 'POST',
          headers,
          body: JSON.stringify({ ...newPackage.value, condominium_id: condo })
        });

        if (resp.ok) {
          const pkg = await resp.json();
          packages.value.push(pkg);
          newPackage.value = { apartment_id: null, photo: '' };
        }
      } catch (e) {
        console.error('Erro ao criar encomenda:', e);
      }
    };

    const withdraw = async (pkg) => {
      const token = localStorage.getItem('token');
      const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

      try {
        const resp = await fetch(`/api/packages/${pkg.id}/withdraw`, {
          method: 'PUT',
          headers
        });

        if (resp.ok) {
          const updated = await resp.json();
          const index = packages.value.findIndex(p => p.id === pkg.id);
          if (index !== -1) {
            packages.value[index] = updated;
          }
        }
      } catch (e) {
        console.error('Erro ao retirar encomenda:', e);
      }
    };

    onMounted(loadData);

    return {
      packages,
      apartments,
      newPackage,
      createPackage,
      withdraw,
      formatApartmentDescription
    };
  }
};
</script>
