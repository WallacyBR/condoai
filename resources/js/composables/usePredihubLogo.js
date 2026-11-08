import { computed } from 'vue';
import { useTheme } from './useTheme';
import logoLight from '../../images/predihub_logo_light.png';
import logoDark from '../../images/predihub_logo_dark.png';

export { logoLight, logoDark };

/**
 * Retorna o logo PrediHub adequado ao tema atual (claro ou escuro).
 */
export function usePredihubLogo() {
  const { isDark } = useTheme();

  const logo = computed(() => (isDark.value ? logoDark : logoLight));

  return { logo, logoLight, logoDark };
}
