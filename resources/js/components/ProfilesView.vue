<template>
  <AuthenticatedLayout page-title="Perfis">
    <div class="space-y-6">
      <div class="flex justify-end">
        <NeonButton @click="fetchProfiles">Recarregar</NeonButton>
      </div>
      <div v-if="loading" class="text-center">Carregando...</div>
      <div v-else class="space-y-4">
        <div v-for="profile in profiles" :key="profile.id" class="border p-4 rounded-xl bg-gray-800/40">
          <h2 class="text-cyan-300 text-xl mb-2">{{ profile.name }}</h2>
          <ProfileModuleManager
            v-model="profile.modulesIds"
            :modules="modules"
            :profileName="profile.name"
            @update:modelValue="updateModules(profile)"
          />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import NeonButton from './NeonButton.vue';
import ProfileModuleManager from './ProfileModuleManager.vue';
import axios from 'axios';

const profiles = ref([]);
const modules = ref([]);
const loading = ref(false);

const fetchProfiles = async () => {
  loading.value = true;
  const [pRes, mRes] = await Promise.all([
    axios.get('/api/profiles', authHeader()),
    axios.get('/api/modules', authHeader()),
  ]);
  profiles.value = pRes.data.map(p => ({ ...p, modulesIds: p.modules.map(m => m.id) }));
  modules.value = mRes.data;
  loading.value = false;
};

const updateModules = async (profile) => {
  await axios.put(`/api/profiles/${profile.id}`, { modules: profile.modulesIds }, authHeader());
};

const authHeader = () => ({ headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } });

onMounted(fetchProfiles);
</script>
