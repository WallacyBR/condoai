import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/utils/api';

export const useTransactionsStore = defineStore('transactions', () => {
  const transactions = ref([]);
  const payable = ref(null);
  const receivable = ref(null);
  const expenseRequest = ref(null);

  async function fetchTransactions(filters = {}) {
    try {
      const { data } = await api.get('/finance/transactions', { params: filters });
      transactions.value = data;
    } catch (e) {
      console.error('Erro ao carregar lançamentos:', e);
    }
  }

  async function createTransaction(payload) {
    try {
      const { data } = await api.post('/finance/transactions', payload);
      return data;
    } catch (e) {
      console.error('Erro ao criar lançamento:', e);
      throw e;
    }
  }

  async function updatePayable(id, payload) {
    try {
      const { data } = await api.put(`/finance/payables/${id}`, payload);
      return data;
    } catch (e) {
      console.error('Erro ao atualizar despesa:', e);
      throw e;
    }
  }

  async function deletePayable(id) {
    try {
      await api.delete(`/finance/payables/${id}`);
    } catch (e) {
      console.error('Erro ao deletar despesa:', e);
      throw e;
    }
  }

  async function updateReceivable(id, payload) {
    try {
      const { data } = await api.put(`/finance/receivables/${id}`, payload);
      return data;
    } catch (e) {
      console.error('Erro ao atualizar receita:', e);
      throw e;
    }
  }

  async function deleteReceivable(id) {
    try {
      await api.delete(`/finance/receivables/${id}`);
    } catch (e) {
      console.error('Erro ao deletar receita:', e);
      throw e;
    }
  }

  async function fetchPayable(id) {
    try {
      const { data } = await api.get(`/finance/payables/${id}`);
      payable.value = data;
      return data;
    } catch (e) {
      console.error('Erro ao carregar despesa:', e);
      throw e;
    }
  }

  async function fetchReceivable(id) {
    try {
      const { data } = await api.get(`/finance/receivables/${id}`);
      receivable.value = data;
      return data;
    } catch (e) {
      console.error('Erro ao carregar receita:', e);
      throw e;
    }
  }

  async function uploadPayableAttachment(payableId, file) {
    try {
      const formData = new FormData();
      formData.append('file', file);
      const { data } = await api.post(`/finance/payables/${payableId}/attachments`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return data;
    } catch (e) {
      console.error('Erro ao fazer upload de anexo de despesa:', e);
      throw e;
    }
  }

  async function deletePayableAttachment(payableId, attachmentId) {
    try {
      await api.delete(`/finance/payables/${payableId}/attachments/${attachmentId}`);
    } catch (e) {
      console.error('Erro ao deletar anexo de despesa:', e);
      throw e;
    }
  }

  async function uploadReceivableAttachment(receivableId, file) {
    try {
      const formData = new FormData();
      formData.append('file', file);
      const { data } = await api.post(`/finance/receivables/${receivableId}/attachments`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return data;
    } catch (e) {
      console.error('Erro ao fazer upload de anexo de receita:', e);
      throw e;
    }
  }

  async function deleteReceivableAttachment(receivableId, attachmentId) {
    try {
      await api.delete(`/finance/receivables/${receivableId}/attachments/${attachmentId}`);
    } catch (e) {
      console.error('Erro ao deletar anexo de receita:', e);
      throw e;
    }
  }

  async function fetchExpenseRequest(id) {
    try {
      const { data } = await api.get(`/finance/expense-requests/${id}`);
      expenseRequest.value = data;
      return data;
    } catch (e) {
      console.error('Erro ao carregar solicitação de despesa:', e);
      throw e;
    }
  }

  async function createExpenseRequest(payload) {
    try {
      const { data } = await api.post('/finance/expense-requests', payload);
      return data;
    } catch (e) {
      console.error('Erro ao criar solicitação de despesa:', e);
      throw e;
    }
  }

  async function updateExpenseRequest(id, payload) {
    try {
      const { data } = await api.put(`/finance/expense-requests/${id}`, payload);
      return data;
    } catch (e) {
      console.error('Erro ao atualizar solicitação de despesa:', e);
      throw e;
    }
  }

  async function deleteExpenseRequest(id) {
    try {
      await api.delete(`/finance/expense-requests/${id}`);
    } catch (e) {
      console.error('Erro ao deletar solicitação de despesa:', e);
      throw e;
    }
  }

  async function approveExpenseRequest(id) {
    try {
      await api.post(`/finance/expense-requests/${id}/approve`);
    } catch (e) {
      console.error('Erro ao aprovar solicitação de despesa:', e);
      throw e;
    }
  }

  async function uploadExpenseRequestAttachment(expenseRequestId, file) {
    try {
      const formData = new FormData();
      formData.append('file', file);
      const { data } = await api.post(`/finance/expense-requests/${expenseRequestId}/attachments`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return data;
    } catch (e) {
      console.error('Erro ao fazer upload de anexo de solicitação de despesa:', e);
      throw e;
    }
  }

  async function deleteExpenseRequestAttachment(expenseRequestId, attachmentId) {
    try {
      await api.delete(`/finance/expense-requests/${expenseRequestId}/attachments/${attachmentId}`);
    } catch (e) {
      console.error('Erro ao deletar anexo de solicitação de despesa:', e);
      throw e;
    }
  }

  return {
    transactions,
    payable,
    receivable,
    expenseRequest,
    fetchTransactions,
    createTransaction,
    updatePayable,
    deletePayable,
    updateReceivable,
    deleteReceivable,
    fetchPayable,
    fetchReceivable,
    uploadPayableAttachment,
    deletePayableAttachment,
    uploadReceivableAttachment,
    deleteReceivableAttachment,
    fetchExpenseRequest,
    createExpenseRequest,
    updateExpenseRequest,
    deleteExpenseRequest,
    approveExpenseRequest,
    uploadExpenseRequestAttachment,
    deleteExpenseRequestAttachment,
  };
});