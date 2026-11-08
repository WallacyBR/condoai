import { ref, computed } from 'vue';

// Estado global das notificações toast
const toasts = ref([]);
let toastId = 0;

export function useToast() {
  
  // Função para adicionar um toast
  const addToast = (message, type = 'info', duration = 5000) => {
    const id = ++toastId;
    const toast = {
      id,
      message,
      type, // 'success', 'error', 'warning', 'info'
      duration,
      timestamp: Date.now()
    };
    
    toasts.value.push(toast);
    
    // Remover automaticamente após a duração especificada
    if (duration > 0) {
      setTimeout(() => {
        removeToast(id);
      }, duration);
    }
    
    return id;
  };
  
  // Função para remover um toast
  const removeToast = (id) => {
    const index = toasts.value.findIndex(toast => toast.id === id);
    if (index > -1) {
      toasts.value.splice(index, 1);
    }
  };
  
  // Função para limpar todos os toasts
  const clearAllToasts = () => {
    toasts.value = [];
  };
  
  // Funções de conveniência para diferentes tipos
  const success = (message, duration = 4000) => {
    return addToast(message, 'success', duration);
  };
  
  const error = (message, duration = 6000) => {
    return addToast(message, 'error', duration);
  };
  
  const warning = (message, duration = 5000) => {
    return addToast(message, 'warning', duration);
  };
  
  const info = (message, duration = 4000) => {
    return addToast(message, 'info', duration);
  };
  
  // Função para obter classes CSS baseadas no tipo
  const getToastClasses = (type) => {
    const baseClasses = 'fixed top-4 right-4 z-50 max-w-sm w-full rounded-lg shadow-lg p-4 border transition-all duration-300 ease-in-out';
    
    switch (type) {
      case 'success':
        return `${baseClasses} bg-green-500/10 border-green-500/30 text-green-300`;
      case 'error':
        return `${baseClasses} bg-red-500/10 border-red-500/30 text-red-300`;
      case 'warning':
        return `${baseClasses} bg-yellow-500/10 border-yellow-500/30 text-yellow-300`;
      case 'info':
        return `${baseClasses} bg-blue-500/10 border-blue-500/30 text-blue-300`;
      default:
        return `${baseClasses} bg-gray-500/10 border-gray-500/30 text-gray-300`;
    }
  };
  
  // Função para obter ícone baseado no tipo
  const getToastIcon = (type) => {
    switch (type) {
      case 'success':
        return 'M5 13l4 4L19 7';
      case 'error':
        return 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
      case 'warning':
        return 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
      case 'info':
        return 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
      default:
        return 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
    }
  };
  
  return {
    toasts: computed(() => toasts.value),
    addToast,
    removeToast,
    clearAllToasts,
    success,
    error,
    warning,
    info,
    successToast: success,
    errorToast: error,
    warningToast: warning,
    infoToast: info,
    getToastClasses,
    getToastIcon
  };
}
