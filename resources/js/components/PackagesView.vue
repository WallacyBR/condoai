<template>
  <AuthenticatedLayout page-title="Encomendas">
    <div class="space-y-4">
      <div class="flex flex-wrap gap-2 mb-4">
        <input v-model="filters.unit" class="border rounded px-2" placeholder="Unidade" />
        <input type="date" v-model="filters.date" class="border rounded px-2" />
        <input v-model="filters.pickup_location" class="border rounded px-2" placeholder="Local de retirada" />
        <input v-model="filters.observation" class="border rounded px-2" placeholder="Observa\u00e7\u00e3o" />
        <select v-model="filters.status" class="border rounded px-2">
          <option value="">Todos</option>
          <option value="pending">Pendente</option>
          <option value="retrieved">Retirada</option>
        </select>
        <button @click="loadPackages" class="px-3 py-1 bg-cyan-600 text-white rounded">Filtrar</button>
        <router-link to="/packages/new" class="px-3 py-1 bg-green-600 text-white rounded">Novo</router-link>
      </div>
      <table class="min-w-full text-left text-sm">
        <thead>
          <tr>
            <th>Unidade</th>
            <th>Data</th>
            <th>Local</th>
            <th>Observa\u00e7\u00e3o</th>
            <th>Status</th>
            <th>A\u00e7\u00f5es</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="pkg in packages" :key="pkg.id" class="border-t">
            <td>{{ formatApartmentDescription(pkg.apartment) }}</td>
            <td>{{ pkg.created_at?.slice(0,10) }}</td>
            <td>{{ pkg.pickup_location }}</td>
            <td>{{ pkg.observation }}</td>
            <td>
              <span v-if="pkg.retrieved_at" class="text-green-500">Retirada</span>
              <span v-else class="text-yellow-500">Pendente</span>
            </td>
            <td class="space-x-1">
              <button @click="openInfo(pkg)" class="px-2 py-1 bg-blue-500 text-white rounded">Info</button>
              <router-link :to="`/packages/${pkg.id}/edit`" class="px-2 py-1 bg-indigo-500 text-white rounded">Editar</router-link>
              <button @click="remove(pkg)" class="px-2 py-1 bg-red-500 text-white rounded">Excluir</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <PackageInfoModal v-if="showInfo" :pkg="selected" @close="showInfo = false" />
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import { apiRequest } from '../utils/api';
import { formatApartmentDescription } from '../utils/formatters';
import PackageInfoModal from './PackageInfoModal.vue';

export default {
  name: 'PackagesView',
  components: { AuthenticatedLayout, PackageInfoModal },
  setup() {
    const packages = ref([]);
    const filters = ref({
      unit: '',
      date: '',
      pickup_location: '',
      observation: '',
      status: ''
    });
    const selected = ref(null);
    const showInfo = ref(false);

    const buildUrl = () => {
      let url = '/api/packages';
      const params = [];
      const condo = localStorage.getItem('selectedCondominium');
      if (condo) params.push('condominium_id=' + condo);
      if (filters.value.unit) params.push('unit=' + filters.value.unit);
      if (filters.value.date) params.push('date=' + filters.value.date);
      if (filters.value.pickup_location) params.push('pickup_location=' + filters.value.pickup_location);
      if (filters.value.observation) params.push('observation=' + filters.value.observation);
      if (filters.value.status) params.push('status=' + filters.value.status);
      if (params.length) url += '?' + params.join('&');
      return url;
    };

    const loadPackages = async () => {
      const data = await apiRequest(buildUrl());
      if (data) packages.value = data;
    };

    const remove = async (pkg) => {
      if (!confirm('Confirmar exclus\u00e7\u00e3o?')) return;
      const resp = await apiRequest(`/api/packages/${pkg.id}`, { method: 'DELETE' });
      if (resp === null) return;
      packages.value = packages.value.filter(p => p.id !== pkg.id);
    };

    const openInfo = (pkg) => {
      selected.value = pkg;
      showInfo.value = true;
    };

    onMounted(loadPackages);

    return { packages, filters, loadPackages, remove, openInfo, selected, showInfo, formatApartmentDescription };
  }
};
</script>
