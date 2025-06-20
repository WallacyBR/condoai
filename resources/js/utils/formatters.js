/**
 * Utilitários para formatação de dados
 */

/**
 * Formata a descrição completa de uma unidade incluindo torre, bloco e número
 * @param {Object} apartment - Objeto da unidade com relacionamentos tower e block
 * @returns {string} Descrição formatada da unidade
 */
export const formatApartmentDescription = (apartment) => {
  if (!apartment) return '';
  
  const descriptionParts = [];
  
  if (apartment.number) {
    descriptionParts.push(`Unidade ${apartment.number}`);
  }
  
  if (apartment.tower && apartment.tower.name) {
    descriptionParts.push(apartment.tower.name);
  }
  
  if (apartment.block && apartment.block.name) {
    descriptionParts.push(apartment.block.name);
  }
  
  return descriptionParts.join(' - ');
};

/**
 * Formata a descrição resumida de uma unidade (apenas número)
 * @param {Object} apartment - Objeto da unidade
 * @returns {string} Descrição resumida da unidade
 */
export const formatApartmentNumber = (apartment) => {
  if (!apartment) return '';
  return `Unidade ${apartment.number}`;
};

/**
 * Formata a descrição da localização da unidade (torre e bloco)
 * @param {Object} apartment - Objeto da unidade com relacionamentos tower e block
 * @returns {string} Descrição da localização
 */
export const formatApartmentLocation = (apartment) => {
  if (!apartment) return '';
  
  const parts = [];
  
  if (apartment.tower && apartment.tower.name) {
    parts.push(apartment.tower.name);
  }
  
  if (apartment.block && apartment.block.name) {
    parts.push(apartment.block.name);
  }
  
  return parts.length > 0 ? parts.join(' - ') : '';
}; 