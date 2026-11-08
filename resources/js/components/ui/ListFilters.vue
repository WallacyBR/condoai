<template>
  <div class="space-y-4">
    <!-- Filtros -->
    <div class="flex flex-wrap items-center gap-3">
      <div class="relative flex-1 min-w-[220px] max-w-xs">
        <input
          type="text"
          :value="modelValue.search"
          @input="updateFilter('search', $event.target.value)"
          :placeholder="searchPlaceholder"
          class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors text-sm shadow-xs"
        />
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>
      
      <select
        :value="modelValue.statusFilter"
        @change="updateFilter('statusFilter', $event.target.value)"
        class="px-3.5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors text-sm shadow-xs"
      >
        <option value="all">Todos os Status</option>
        <option v-for="status in statusOptions" :key="status.value" :value="status.value">
          {{ status.label }}
        </option>
      </select>
      
      <select
        v-if="towerOptions.length > 0"
        :value="modelValue.towerFilter"
        @change="updateFilter('towerFilter', $event.target.value)"
        class="px-3.5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors text-sm shadow-xs"
      >
        <option value="all">Todas as Torres</option>
        <option v-for="tower in towerOptions" :key="tower.id" :value="tower.id">
          {{ tower.name }}
        </option>
      </select>
      
      <select
        v-if="blockOptions.length > 0"
        :value="modelValue.blockFilter"
        @change="updateFilter('blockFilter', $event.target.value)"
        class="px-3.5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors text-sm shadow-xs"
      >
        <option value="all">Todos os Blocos</option>
        <option v-for="block in blockOptions" :key="block.id" :value="block.id">
          {{ block.name }}
        </option>
      </select>
    </div>

    <!-- Status dos Filtros -->
    <div v-if="isFilterActive" class="bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200/80 dark:border-indigo-800/50 rounded-xl p-3 sm:p-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
          <div class="flex items-center space-x-1.5 text-indigo-700 dark:text-indigo-300 font-semibold text-xs uppercase tracking-wider">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
            </svg>
            <span>Filtros Ativos:</span>
          </div>
          <div class="flex flex-wrap gap-1.5">
            <span v-if="modelValue.search" class="px-2.5 py-0.5 bg-white dark:bg-slate-900 text-indigo-700 dark:text-indigo-300 text-xs font-medium rounded-full border border-indigo-200 dark:border-indigo-800 shadow-2xs">
              Busca: "{{ modelValue.search }}"
            </span>
            <span v-if="modelValue.statusFilter !== 'all'" class="px-2.5 py-0.5 bg-white dark:bg-slate-900 text-indigo-700 dark:text-indigo-300 text-xs font-medium rounded-full border border-indigo-200 dark:border-indigo-800 shadow-2xs">
              Status: {{ getStatusLabel(modelValue.statusFilter) }}
            </span>
            <span v-if="modelValue.towerFilter !== 'all'" class="px-2.5 py-0.5 bg-white dark:bg-slate-900 text-indigo-700 dark:text-indigo-300 text-xs font-medium rounded-full border border-indigo-200 dark:border-indigo-800 shadow-2xs">
              Torre: {{ getTowerLabel(modelValue.towerFilter) }}
            </span>
            <span v-if="modelValue.blockFilter !== 'all'" class="px-2.5 py-0.5 bg-white dark:bg-slate-900 text-indigo-700 dark:text-indigo-300 text-xs font-medium rounded-full border border-indigo-200 dark:border-indigo-800 shadow-2xs">
              Bloco: {{ getBlockLabel(modelValue.blockFilter) }}
            </span>
          </div>
        </div>
        <div class="flex items-center space-x-3 self-end sm:self-auto">
          <div class="text-xs text-slate-500 dark:text-slate-400">
            <slot name="summary"></slot>
          </div>
          <AppButton 
            @click="clearFilters" 
            size="sm" 
            variant="secondary"
          >
            <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Limpar
          </AppButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import AppButton from './AppButton.vue';

export default {
  name: 'ListFilters',
  components: { AppButton },
  props: {
    modelValue: {
      type: Object,
      required: true
    },
    searchPlaceholder: {
      type: String,
      default: 'Buscar...'
    },
    statusOptions: {
      type: Array,
      default: () => [
        { value: 'occupied', label: 'Ocupadas' },
        { value: 'vacant', label: 'Vazias' }
      ]
    },
    towerOptions: {
      type: Array,
      default: () => []
    },
    blockOptions: {
      type: Array,
      default: () => []
    }
  },
  emits: ['update:modelValue', 'clear-filters'],
  computed: {
    isFilterActive() {
      return this.modelValue.search || 
             this.modelValue.statusFilter !== 'all' || 
             this.modelValue.towerFilter !== 'all' || 
             this.modelValue.blockFilter !== 'all';
    }
  },
  methods: {
    updateFilter(key, value) {
      this.$emit('update:modelValue', {
        ...this.modelValue,
        [key]: value
      });
    },
    clearFilters() {
      this.$emit('clear-filters');
    },
    getStatusLabel(value) {
      const status = this.statusOptions.find(s => s.value === value);
      return status ? status.label : value;
    },
    getTowerLabel(value) {
      const tower = this.towerOptions.find(t => t.id === value);
      return tower ? tower.name : 'Todas as Torres';
    },
    getBlockLabel(value) {
      const block = this.blockOptions.find(b => b.id === value);
      return block ? block.name : 'Todos os Blocos';
    }
  }
};
</script> 