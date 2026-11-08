<template>
  <div>
    <h3 class="text-lg text-cyan-300 mb-2">Módulos</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
      <label v-for="module in modules" :key="module.id" class="flex items-center space-x-2">
        <input type="checkbox" :value="module.id" v-model="localModules" :disabled="isSysOp" />
        <span class="text-cyan-300">{{ module.name }}</span>
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
