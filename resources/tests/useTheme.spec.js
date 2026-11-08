import { useTheme } from '../js/composables/useTheme.js';

describe('useTheme composable', () => {
  beforeEach(() => {
    localStorage.clear();
    document.documentElement.className = '';
  });

  test('inicializa com sistema ou valor padrão', () => {
    const { theme } = useTheme();
    expect(['light', 'dark', 'system']).toContain(theme.value);
  });

  test('alterna para modo dark e aplica classe no documentElement', () => {
    const { setTheme, isDark, theme } = useTheme();
    setTheme('dark');
    expect(theme.value).toBe('dark');
    expect(isDark.value).toBe(true);
    expect(document.documentElement.classList.contains('dark')).toBe(true);
    expect(localStorage.getItem('theme')).toBe('dark');
  });

  test('alterna para modo light e remove classe dark do documentElement', () => {
    const { setTheme, isDark, theme } = useTheme();
    setTheme('light');
    expect(theme.value).toBe('light');
    expect(isDark.value).toBe(false);
    expect(document.documentElement.classList.contains('dark')).toBe(false);
    expect(localStorage.getItem('theme')).toBe('light');
  });

  test('ignora temas inválidos', () => {
    const { setTheme, theme } = useTheme();
    setTheme('light');
    setTheme('invalid-theme');
    expect(theme.value).toBe('light');
  });
});
