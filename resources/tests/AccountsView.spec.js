import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { describe, it, expect, vi } from 'vitest';
import AccountsView from '../js/finance/AccountsView.vue';
import { useFinanceDataStore } from '../js/finance/stores/financeDataStore';
import { mountOptionsWithLayoutStubs } from './helpers/mountWithLayoutStubs.js';

vi.mock('../js/utils/api', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
  },
}));

vi.mock('../js/composables/useUser', () => ({
  useUser: () => ({
    user: { value: { condominium_id: 1 } },
  }),
}));

describe('AccountsView.vue', () => {
  it('exibe lista de contas', async () => {
    setActivePinia(createPinia());
    const store = useFinanceDataStore();
    store.fetchBanks = vi.fn(async () => {
      store.banks = [{ id: 1, name: 'Conta Principal' }];
    });
    store.fetchChartAccounts = vi.fn(async () => {
      store.chartAccounts = [];
    });

    const wrapper = mount(AccountsView, mountOptionsWithLayoutStubs({
      global: { plugins: [store.$pinia] },
    }));

    expect(store.fetchBanks).toHaveBeenCalled();
    await wrapper.vm.$nextTick();
    expect(wrapper.text()).toContain('Conta Principal');
  });
});
