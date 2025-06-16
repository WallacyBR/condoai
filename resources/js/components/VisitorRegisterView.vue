<template>
  <AuthenticatedLayout page-title="Visitantes">
    <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow">
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Nome</label>
          <input v-model="form.name" class="mt-1 block w-full border-gray-300 rounded-md" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Documento</label>
          <input v-model="form.document" class="mt-1 block w-full border-gray-300 rounded-md" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Apartamento</label>
          <select v-model="form.apartment_id" class="mt-1 block w-full border-gray-300 rounded-md" required>
            <option value="" disabled>Selecione</option>
            <option v-for="ap in apartments" :key="ap.id" :value="ap.id">
              {{ ap.number }} - {{ ap.block.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Data da visita</label>
          <input type="datetime-local" v-model="form.visit_date" class="mt-1 block w-full border-gray-300 rounded-md" required />
        </div>
        <NeonButton type="submit">Registrar</NeonButton>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import NeonButton from './NeonButton.vue';

const form = ref({
  name: '',
  document: '',
  apartment_id: '',
  visit_date: new Date().toISOString().slice(0,16)
});

const apartments = ref([]);

const loadApartments = async () => {
  const token = localStorage.getItem('token');
  const response = await fetch('/api/apartments', {
    headers: { Authorization: `Bearer ${token}` }
  });
  if (response.ok) {
    apartments.value = await response.json();
  }
};

const submit = async () => {
  const token = localStorage.getItem('token');
  const response = await fetch('/api/visitors', {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ ...form.value, condominium_id: JSON.parse(localStorage.getItem('selectedCondominium')).id })
  });
  if (response.ok) {
    form.value.name = '';
    form.value.document = '';
  }
};

onMounted(loadApartments);
</script>
