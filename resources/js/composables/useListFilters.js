import { ref, computed } from 'vue';

export function useListFilters() {
  const search = ref('');
  const statusFilter = ref('all');
  const towerFilter = ref('all');
  const blockFilter = ref('all');

  const clearFilters = () => {
    search.value = '';
    statusFilter.value = 'all';
    towerFilter.value = 'all';
    blockFilter.value = 'all';
  };

  const isFilterActive = computed(() => {
    return search.value || 
           statusFilter.value !== 'all' || 
           towerFilter.value !== 'all' || 
           blockFilter.value !== 'all';
  });

  const getFilterSummary = (filteredCount, totalCount) => {
    return `${filteredCount} de ${totalCount} itens`;
  };

  return {
    search,
    statusFilter,
    towerFilter,
    blockFilter,
    clearFilters,
    isFilterActive,
    getFilterSummary
  };
} 