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
      return 'bg-blue-500/20 text-blue-300 border border-blue-500/30';
    }
    return 'bg-orange-500/20 text-orange-300 border border-orange-500/30';
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