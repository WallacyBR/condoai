import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { describe, it, expect, vi } from 'vitest';
import FinancialTransactionsView from '../js/finance/FinancialTransactionsView.vue';
import { useTransactionsStore } from '../js/finance/stores/transactionsStore';
import { mountOptionsWithLayoutStubs } from './helpers/mountWithLayoutStubs.js';

vi.mock('../js/utils/api', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
  },
}));

describe('FinancialTransactionsView.vue', () => {
  it('exibe lista de lançamentos', async () => {
    setActivePinia(createPinia());
    const store = useTransactionsStore();
    store.fetchTransactions = vi.fn(async () => {
      store.transactions = [
        { id: 1, description: 'Mensalidade', amount: 100, type: 'receivable' },
      ];
    });
    const wrapper = mount(FinancialTransactionsView, mountOptionsWithLayoutStubs({
      global: { plugins: [store.$pinia] },
    }));
    expect(store.fetchTransactions).toHaveBeenCalled();
    await wrapper.vm.$nextTick();
    expect(wrapper.text()).toContain('Mensalidade');
  });
});
