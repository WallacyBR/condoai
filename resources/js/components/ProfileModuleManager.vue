<template>
  <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Módulos de Acesso</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
      <label 
        v-for="module in modules" 
        :key="module.id" 
        class="flex items-center space-x-2.5 p-2 rounded-lg border border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 hover:bg-slate-100/60 dark:hover:bg-slate-800/70 transition-colors cursor-pointer select-none"
        :class="{ 'opacity-60 cursor-not-allowed': isSysOp }"
      >
        <input 
          type="checkbox" 
          :value="module.id" 
          v-model="localModules" 
          :disabled="isSysOp" 
          class="rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500 h-4 w-4 bg-white dark:bg-slate-800"
        />
        <span class="text-xs font-medium text-slate-700 dark:text-slate-200">{{ module.name }}</span>
      </label>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  modules: { type: Array, default: () => [] },
  profileName: { type: String, default: '' },
});
const emit = defineEmits(['update:modelValue']);

const localModules = ref([...props.modelValue]);
const isSysOp = computed(() => props.profileName === 'SysOp');

watch(localModules, (val) => emit('update:modelValue', val));
watch(() => props.modelValue, (val) => { localModules.value = [...val]; });
</script>
