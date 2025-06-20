<template>
  <AuthenticatedLayout :page-title="title">
    <form @submit.prevent="submit" class="space-y-4">
      <select v-model="form.apartment_id" class="border rounded px-2 w-full">
        <option :value="null">Unidade</option>
        <option v-for="a in apartments" :key="a.id" :value="a.id">{{ formatApartmentDescription(a) }}</option>
      </select>
      <input v-model="form.pickup_location" class="border rounded px-2 w-full" placeholder="Local" />
      <textarea v-model="form.observation" class="border rounded px-2 w-full" placeholder="Observa\u00e7\u00e3o"></textarea>
      <WebcamCapture v-model="form.photo" />
      <button class="px-3 py-1 bg-cyan-600 text-white rounded">Salvar</button>
    </form>
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import { apiRequest } from '../utils/api';
import { formatApartmentDescription } from '../utils/formatters';
import WebcamCapture from '../components/WebcamCapture.vue';
import { useRoute, useRouter } from 'vue-router';

export default {
  name: 'PackageFormPage',
  components: { AuthenticatedLayout, WebcamCapture },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const id = route.params.id;
    const title = id ? 'Editar Encomenda' : 'Nova Encomenda';
    const form = ref({
      apartment_id: null,
      pickup_location: '',
      observation: '',
      photo: ''
    });
    const apartments = ref([]);

    const load = async () => {
      const condo = localStorage.getItem('selectedCondominium');
      apartments.value = await apiRequest(`/api/apartments?condominium_id=${condo}`) || [];
      if (id) {
        const pkg = await apiRequest(`/api/packages/${id}`);
        if (pkg) {
          form.value.apartment_id = pkg.apartment_id;
          form.value.pickup_location = pkg.pickup_location;
          form.value.observation = pkg.observation;
          form.value.photo = pkg.photo;
        }
      }
    };

    const submit = async () => {
      const url = id ? `/api/packages/${id}` : '/api/packages';
      const method = id ? 'PUT' : 'POST';
      const condo = localStorage.getItem('selectedCondominium');
      const body = { ...form.value, condominium_id: condo };
      const resp = await apiRequest(url, { method, body: JSON.stringify(body) });
      if (resp) router.push('/packages');
    };

    onMounted(load);

    return { form, apartments, title, submit, formatApartmentDescription };
  }
};
</script>
