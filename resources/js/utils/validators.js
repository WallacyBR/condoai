/**
 * Utilitários de validação reutilizáveis
 */

/**
 * Valida se um CPF é válido
 * @param {string} cpf - CPF a ser validado (pode conter formatação)
 * @returns {boolean} - true se o CPF for válido, false caso contrário
 */
export const validateCPF = (cpf) => {
  if (!cpf) return false;
  
  // Remove caracteres não numéricos
  cpf = cpf.toString().replace(/\D/g, '');
  
  // Verifica se tem 11 dígitos
  if (cpf.length !== 11) {
    return false;
  }
  
  // Verifica se todos os dígitos são iguais
  if (/^(\d)\1{10}$/.test(cpf)) {
    return false;
  }
  
  // Validação do primeiro dígito verificador
  let sum = 0;
  for (let i = 0; i < 9; i++) {
    sum += parseInt(cpf.charAt(i)) * (10 - i);
  }
  let remainder = (sum * 10) % 11;
  if (remainder === 10 || remainder === 11) remainder = 0;
  if (remainder !== parseInt(cpf.charAt(9))) {
    return false;
  }
  
  // Validação do segundo dígito verificador
  sum = 0;
  for (let i = 0; i < 10; i++) {
    sum += parseInt(cpf.charAt(i)) * (11 - i);
  }
  remainder = (sum * 10) % 11;
  if (remainder === 10 || remainder === 11) remainder = 0;
  if (remainder !== parseInt(cpf.charAt(10))) {
    return false;
  }
  
  return true;
};

/**
 * Formata um CPF com pontos e hífen
 * @param {string} value - Valor a ser formatado
 * @returns {string} - CPF formatado
 */
export const formatCPF = (value) => {
  if (!value) return '';
  const numbers = value.toString().replace(/\D/g, '');
  if (numbers.length <= 11) {
    return numbers.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
  }
  return value;
};

/**
 * Valida se um email é válido
 * @param {string} email - Email a ser validado
 * @returns {boolean} - true se o email for válido, false caso contrário
 */
export const validateEmail = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

/**
 * Formata um telefone brasileiro
 * @param {string} value - Valor a ser formatado
 * @returns {string} - Telefone formatado
 */
export const formatPhone = (value) => {
  if (!value) return '';
  const numbers = value.toString().replace(/\D/g, '');
  if (numbers.length === 11) {
    return numbers.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
  } else if (numbers.length === 10) {
    return numbers.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
  }
  return numbers;
};

/**
 * Formata um CEP brasileiro
 * @param {string} value - Valor a ser formatado
 * @returns {string} - CEP formatado
 */
export const formatCEP = (value) => {
  if (!value) return '';
  const numbers = value.toString().replace(/\D/g, '');
  return numbers.replace(/(\d{5})(\d{3})/, '$1-$2');
};

/**
 * Valida se um CEP é válido
 * @param {string} cep - CEP a ser validado
 * @returns {boolean} - true se o CEP for válido, false caso contrário
 */
export const validateCEP = (cep) => {
  if (!cep) return false;
  const numbers = cep.toString().replace(/\D/g, '');
  return numbers.length === 8;
}; 