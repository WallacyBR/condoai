import { computed } from 'vue';

export function useUnitStats(units) {
  const stats = computed(() => {
    const totalUnidades = units.value.length;
    const ocupadas = units.value.filter(u => u.owner || u.tenant).length;
    const vazias = units.value.filter(u => !u.owner && !u.tenant).length;
    const taxaOcupacao = totalUnidades > 0 ? Math.round((ocupadas / totalUnidades) * 100) : 0;

    return {
      totalUnidades,
      ocupadas,
      vazias,
      taxaOcupacao: `${taxaOcupacao}%`
    };
  });

  const getStatusClass = (unit) => {
    if (unit.owner || unit.tenant) {
      return 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50';
    }
    return 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/50';
  };

  const getStatusLabel = (unit) => {
    if (unit.owner || unit.tenant) {
      return 'Ocupada';
    }
    return 'Vazia';
  };

  return {
    stats,
    getStatusClass,
    getStatusLabel
  };
} 