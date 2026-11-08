import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';

import TransactionsView from '../js/finance/FinancialTransactionsView.vue';
import { useTransactionsStore } from '../js/finance/stores/transactionsStore';

jest.mock('@/utils/api', () => ({
  get: jest.fn(() => Promise.resolve({ data: [] })),
  post: jest.fn(() => Promise.resolve({ data: {} })),
  put: jest.fn(() => Promise.resolve({ data: {} })),
  delete: jest.fn(() => Promise.resolve({ data: {} }))
}));

describe('TransactionsView.vue', () => {
  it('exibe lista de lançamentos', async () => {
    const pinia = createPinia();
    setActivePinia(pinia);
    const store = useTransactionsStore();
    store.fetchTransactions = jest.fn(async () => {
      store.transactions = [
        { id: 1, description: 'Mensalidade', amount: 100, type: 'receivable', status: 'open', due_date: '2026-06-25' }
      ];
    });
    const wrapper = mount(TransactionsView, {
      global: {
        plugins: [pinia],
        stubs: {
          AuthenticatedLayout: { template: '<main><slot /></main>' },
          NeonButton: { template: '<button><slot /></button>' }
        }
      }
    });
    expect(store.fetchTransactions).toHaveBeenCalled();
    await wrapper.vm.$nextTick();
    expect(wrapper.text()).toContain('Mensalidade');
  });
});
