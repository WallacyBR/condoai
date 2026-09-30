import { ref, onMounted, computed } from 'vue';
import { useTenantStore } from '../stores/tenant';
import { CONDOMINIUM_SELECTED_EVENT } from '../utils/tenant';

/**
 * Composable para gerenciar o condomínio selecionado.
 * O isolamento de tenant é feito via header X-Condominium-Id (interceptor api.js).
 */
export function useCondominiumFilter() {
  const tenantStore = useTenantStore();
  const selectedCondominiumId = ref(tenantStore.selectedId);
  const selectedCondominiumName = ref(tenantStore.selectedName);

  const isCondominiumAutoDetermined = computed(() => {
    return !!selectedCondominiumId.value;
  });

  const updateSelectedCondominium = () => {
    tenantStore.syncFromStorage();
    selectedCondominiumId.value = tenantStore.selectedId;
    selectedCondominiumName.value = tenantStore.selectedName;
  };

  /**
   * @deprecated O tenant é enviado via header X-Condominium-Id; não use query string como SoT.
   */
  const buildUrlWithCondominiumFilter = (baseUrl) => baseUrl;

  const shouldLoadCondominiums = () => {
    return !isCondominiumAutoDetermined.value;
  };

  const getCurrentCondominiumId = () => {
    return selectedCondominiumId.value;
  };

  const getCurrentCondominiumName = () => {
    return selectedCondominiumName.value;
  };

  const setupCondominiumListeners = (callback) => {
    const onCondominiumChanged = () => {
      updateSelectedCondominium();
      if (callback) {
        callback();
      }
    };

    window.addEventListener(CONDOMINIUM_SELECTED_EVENT, onCondominiumChanged);

    window.addEventListener('storage', (event) => {
      if (event.key === 'selectedCondominium' || event.key === 'selectedCondominiumName') {
        onCondominiumChanged();
      }
    });
  };

  const cleanupCondominiumListeners = () => {
    window.removeEventListener(CONDOMINIUM_SELECTED_EVENT, updateSelectedCondominium);
    window.removeEventListener('storage', updateSelectedCondominium);
  };

  onMounted(updateSelectedCondominium);

  return {
    selectedCondominiumId,
    selectedCondominiumName,
    isCondominiumAutoDetermined,
    updateSelectedCondominium,
    buildUrlWithCondominiumFilter,
    shouldLoadCondominiums,
    getCurrentCondominiumId,
    getCurrentCondominiumName,
    setupCondominiumListeners,
    cleanupCondominiumListeners,
  };
}
