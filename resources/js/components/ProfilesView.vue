<template>
  <AuthenticatedLayout page-title="Perfis de Acesso">
    <div class="space-y-6">
      <div class="flex justify-end">
        <NeonButton @click="fetchProfiles" size="sm" variant="secondary">
          <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Recarregar
        </NeonButton>
      </div>

      <div v-if="loading" class="flex justify-center items-center py-16">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-slate-300 border-t-indigo-600 dark:border-slate-700 dark:border-t-indigo-400"></div>
      </div>

      <div v-else class="space-y-4">
        <div 
          v-for="profile in profiles" 
          :key="profile.id" 
          class="border border-slate-200 dark:border-slate-800 p-6 rounded-xl bg-white dark:bg-slate-900 shadow-sm"
        >
          <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ profile.name }}</h2>
            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
              {{ profile.modulesIds.length }} {{ profile.modulesIds.length === 1 ? 'módulo' : 'módulos' }}
            </span>
          </div>

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
import api from '../utils/api';

const profiles = ref([]);
const modules = ref([]);
const loading = ref(false);

const fetchProfiles = async () => {
  loading.value = true;
  try {
    const [pRes, mRes] = await Promise.all([
      api.get('/profiles'),
      api.get('/modules'),
    ]);
    profiles.value = pRes.data.map(p => ({ ...p, modulesIds: p.modules.map(m => m.id) }));
    modules.value = mRes.data;
  } finally {
    loading.value = false;
  }
};

const updateModules = async (profile) => {
  await api.put(`/profiles/${profile.id}`, { modules: profile.modulesIds });
};

onMounted(fetchProfiles);
</script>
