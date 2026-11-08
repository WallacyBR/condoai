import { ref, onMounted } from 'vue';

/**
 * Composable para gerenciar filtros por condomínio selecionado
 * Segue o princípio DRY para reutilização em diferentes componentes
 */
export function useCondominiumFilter() {
  const selectedCondominiumId = ref(localStorage.getItem('selectedCondominium'));
  const selectedCondominiumName = ref(localStorage.getItem('selectedCondominiumName'));

  /**
   * Atualiza as informações do condomínio selecionado
   */
  const updateSelectedCondominium = () => {
    selectedCondominiumId.value = localStorage.getItem('selectedCondominium');
    selectedCondominiumName.value = localStorage.getItem('selectedCondominiumName');
  };

  /**
   * Constrói URL com filtro por condomínio
   * @param {string} baseUrl - URL base da API
   * @returns {string} URL com parâmetro de condomínio se disponível
   */
  const buildUrlWithCondominiumFilter = (baseUrl) => {
    if (selectedCondominiumId.value) {
      const separator = baseUrl.includes('?') ? '&' : '?';
      return `${baseUrl}${separator}condominium_id=${selectedCondominiumId.value}`;
    }
    return baseUrl;
  };

  /**
   * Configura listeners para mudanças de condomínio
   * @param {Function} callback - Função a ser chamada quando o condomínio for alterado
   */
  const setupCondominiumListeners = (callback) => {
    // Listener para evento customizado
    window.addEventListener('condominiumSelected', () => {
      console.log('Condomínio alterado via evento customizado');
      updateSelectedCondominium();
      if (callback) callback();
    });

    // Listener para mudanças no localStorage
    window.addEventListener('storage', (e) => {
      if (e.key === 'selectedCondominium') {
        console.log('Condomínio alterado via localStorage');
        updateSelectedCondominium();
        if (callback) callback();
      }
    });
  };

  /**
   * Remove listeners de condomínio
   */
  const cleanupCondominiumListeners = () => {
    window.removeEventListener('condominiumSelected', updateSelectedCondominium);
    window.removeEventListener('storage', updateSelectedCondominium);
  };

  return {
    selectedCondominiumId,
    selectedCondominiumName,
    updateSelectedCondominium,
    buildUrlWithCondominiumFilter,
    setupCondominiumListeners,
    cleanupCondominiumListeners
  };
} 