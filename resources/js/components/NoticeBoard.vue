<template>
  <AuthenticatedLayout page-title="Avisos">
    <div class="space-y-4">
      <form @submit.prevent="createNotice" class="space-y-2">
        <input v-model="form.title" placeholder="Título" class="border rounded px-2" />
        <quill-editor v-model:content="form.body" content-type="html" />
        <input v-model="form.image_path" placeholder="Imagem (opcional)" class="border rounded px-2" />
        <div class="flex space-x-2">
          <select v-model="filter.type" class="border rounded px-2">
            <option value="">Condomínio inteiro</option>
            <option value="tower">Torre</option>
            <option value="block">Bloco</option>
            <option value="apartment">Apartamento</option>
          </select>
          <input v-if="filter.type" v-model.number="filter.id" type="number" placeholder="ID" class="border rounded px-2" />
        </div>
        <NeonButton type="submit">Salvar</NeonButton>
      </form>
      <div>
        <h3 class="text-lg font-medium text-cyan-300">Avisos</h3>
        <ul class="list-disc ml-5 text-cyan-100">
          <li v-for="n in notices" :key="n.id">
            <strong>{{ n.title }}:</strong>
            <span v-html="n.body" />
          </li>
        </ul>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { QuillEditor } from 'vue3-quill';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import NeonButton from './NeonButton.vue';

const notices = ref([]);
const form = ref({ title: '', body: '', image_path: '' });
const filter = ref({ type: '', id: null });

const token = localStorage.getItem('token');
const condominiumId = localStorage.getItem('selectedCondominium');
const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

const loadNotices = async () => {
  const params = new URLSearchParams();
  params.append('condominium_id', condominiumId);
  if (filter.value.type && filter.value.id) {
    params.append('target_type', filter.value.type);
    params.append('target_id', filter.value.id);
  }
  const resp = await fetch(`/api/notices?${params.toString()}`, { headers });
  notices.value = resp.ok ? await resp.json() : [];
};

const createNotice = async () => {
  const payload = { ...form.value, condominium_id: condominiumId };
  if (filter.value.type && filter.value.id) {
    payload.targets = [{ type: filter.value.type, id: filter.value.id }];
  }
  const resp = await fetch('/api/notices', {
    method: 'POST',
    headers,
    body: JSON.stringify(payload)
  });
  if (resp.ok) {
    notices.value.push(await resp.json());
    form.value = { title: '', body: '', image_path: '' };
  }
};

onMounted(loadNotices);
</script>
