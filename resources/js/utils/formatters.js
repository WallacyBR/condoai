/**
 * Utilitários para formatação de dados
 */

/**
 * Formata a descrição de uma unidade/apartamento
 * @param {Object} apartment - Objeto do apartamento
 * @returns {string} Descrição formatada da unidade
 */
export function formatApartmentDescription(apartment) {
  if (!apartment) return '';
  
  const parts = [];
  
  if (apartment.number) {
    parts.push(`Unidade ${apartment.number}`);
  }
  
  if (apartment.floor) {
    parts.push(`${apartment.floor}º andar`);
  }
  
  if (apartment.block?.name) {
    parts.push(`Bloco ${apartment.block.name}`);
  }
  
  if (apartment.tower?.name) {
    parts.push(`Torre ${apartment.tower.name}`);
  }
  
  return parts.length > 0 ? parts.join(' - ') : `Unidade ${apartment.id}`;
}

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