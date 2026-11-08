import { ref, computed } from 'vue';

const theme = ref(
  (typeof window !== 'undefined' && localStorage.getItem('theme')) || 'system'
);

const isSystemDark = ref(
  typeof window !== 'undefined' &&
  typeof window.matchMedia === 'function' &&
  window.matchMedia('(prefers-color-scheme: dark)').matches
);

const isDark = computed(() => {
  if (theme.value === 'dark') return true;
  if (theme.value === 'light') return false;
  return isSystemDark.value;
});

function applyTheme() {
  if (typeof document === 'undefined') return;

  if (isDark.value) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
}

function setTheme(newTheme) {
  if (!['light', 'dark', 'system'].includes(newTheme)) return;
  theme.value = newTheme;
  if (typeof window !== 'undefined') {
    localStorage.setItem('theme', newTheme);
  }
  applyTheme();
}

function toggleTheme() {
  if (theme.value === 'light') {
    setTheme('dark');
  } else if (theme.value === 'dark') {
    setTheme('system');
  } else {
    setTheme('light');
  }
}

if (typeof window !== 'undefined' && typeof window.matchMedia === 'function') {
  const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
  const updateSystem = (e) => {
    isSystemDark.value = e.matches;
    if (theme.value === 'system') {
      applyTheme();
    }
  };

  if (typeof mediaQuery.addEventListener === 'function') {
    mediaQuery.addEventListener('change', updateSystem);
  } else if (typeof mediaQuery.addListener === 'function') {
    mediaQuery.addListener(updateSystem);
  }

  // Aplicação inicial
  applyTheme();
}

export function useTheme() {
  return {
    theme: computed(() => theme.value),
    isDark,
    setTheme,
    toggleTheme,
    applyTheme
  };
}
