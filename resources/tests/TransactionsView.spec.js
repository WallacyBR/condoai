import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { describe, it, expect, vi } from 'vitest';
import TransactionsView from '../js/finance/TransactionsView.vue';
import { useTransactionsStore } from '../js/finance/stores/transactionsStore';

describe('TransactionsView.vue', () => {
  it('exibe lista de lançamentos', async () => {
    setActivePinia(createPinia());
    const store = useTransactionsStore();
    store.fetchTransactions = vi.fn(() => {
      store.transactions = [
        { id: 1, description: 'Mensalidade', amount: 100 }
      ];
    });
    const wrapper = mount(TransactionsView, { global: { plugins: [store.$pinia] } });
    expect(store.fetchTransactions).toHaveBeenCalled();
    await wrapper.vm.$nextTick();
    expect(wrapper.text()).toContain('Mensalidade');
  });
});
