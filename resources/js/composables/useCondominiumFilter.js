import { ref, onMounted, computed } from 'vue';

/**
 * Composable para gerenciar filtros por condomínio selecionado
 * Segue o princípio DRY para reutilização em diferentes componentes
 */
export function useCondominiumFilter() {
  const selectedCondominiumId = ref(localStorage.getItem('selectedCondominium'));
  const selectedCondominiumName = ref(localStorage.getItem('selectedCondominiumName'));

  /**
   * Verifica se o condomínio é determinado automaticamente
   */
  const isCondominiumAutoDetermined = computed(() => {
    return !!selectedCondominiumId.value;
  });

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
   * Determina se deve carregar a lista de condomínios
   * @returns {boolean} true se deve carregar, false se determinado automaticamente
   */
  const shouldLoadCondominiums = () => {
    return !isCondominiumAutoDetermined.value;
  };

  /**
   * Obtém o ID do condomínio atual para uso em formulários
   * @returns {string|null} ID do condomínio ou null se não disponível
   */
  const getCurrentCondominiumId = () => {
    return selectedCondominiumId.value;
  };

  /**
   * Obtém o nome do condomínio atual para exibição
   * @returns {string|null} Nome do condomínio ou null se não disponível
   */
  const getCurrentCondominiumName = () => {
    return selectedCondominiumName.value;
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
    isCondominiumAutoDetermined,
    updateSelectedCondominium,
    buildUrlWithCondominiumFilter,
    shouldLoadCondominiums,
    getCurrentCondominiumId,
    getCurrentCondominiumName,
    setupCondominiumListeners,
    cleanupCondominiumListeners
  };
} 