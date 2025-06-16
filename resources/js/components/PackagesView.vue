<template>
  <AuthenticatedLayout page-title="Encomendas">
    <div class="space-y-8">
      <form @submit.prevent="createPackage" class="space-x-2 mb-4 flex flex-wrap">
        <select v-model="newPackage.apartment_id" class="border rounded px-2">
          <option :value="null">Unidade</option>
          <option v-for="a in apartments" :key="a.id" :value="a.id">{{ a.number }}</option>
        </select>
        <input v-model="newPackage.photo" class="border rounded px-2" placeholder="URL da foto" />
        <button type="submit" class="px-3 py-1 bg-cyan-600 text-white rounded">Registrar</button>
      </form>
      <ul class="list-disc ml-5 text-cyan-100">
        <li v-for="p in packages" :key="p.id" class="space-x-2">
          <span>Unidade {{ p.apartment.number }}</span>
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

export default {
  name: 'PackagesView',
  components: { AuthenticatedLayout },
  setup() {
    const token = localStorage.getItem('token');
    const condo = localStorage.getItem('selectedCondominium');
    const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

    const packages = ref([]);
    const apartments = ref([]);
    const newPackage = ref({ apartment_id: null, photo: '' });

    const loadData = async () => {
      const [aRes, pRes] = await Promise.all([
        fetch(`/api/apartments?condominium_id=${condo}`, { headers }),
        fetch('/api/packages', { headers })
      ]);
      apartments.value = aRes.ok ? await aRes.json() : [];
      packages.value = pRes.ok ? await pRes.json() : [];
    };

    const createPackage = async () => {
      if (!newPackage.value.apartment_id) return;
      const resp = await fetch('/api/packages', { method: 'POST', headers, body: JSON.stringify(newPackage.value) });
      if (resp.ok) {
        packages.value.push(await resp.json());
        newPackage.value.apartment_id = null;
        newPackage.value.photo = '';
      }
    };

    const withdraw = async (pack) => {
      const code = prompt('Código de retirada');
      if (!code) return;
      const resp = await fetch(`/api/packages/${pack.id}/withdraw`, { method: 'POST', headers, body: JSON.stringify({ code }) });
      if (resp.ok) {
        const updated = await resp.json();
        const idx = packages.value.findIndex(p => p.id === updated.id);
        packages.value[idx] = updated;
      }
    };

    onMounted(loadData);

    return { packages, apartments, newPackage, createPackage, withdraw };
  }
};
</script>
