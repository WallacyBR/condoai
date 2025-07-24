import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { describe, it, expect, vi } from 'vitest';
import AccountsView from '../js/finance/AccountsView.vue';
import { useAccountsStore } from '../js/finance/stores/accountsStore';

describe('AccountsView.vue', () => {
  it('exibe lista de contas', async () => {
    setActivePinia(createPinia());
    const store = useAccountsStore();
    store.fetchAccounts = vi.fn(() => {
      store.accounts = [{ id: 1, name: 'Conta Principal' }];
    });
    const wrapper = mount(AccountsView, { global: { plugins: [store.$pinia] } });
    expect(store.fetchAccounts).toHaveBeenCalled();
    await wrapper.vm.$nextTick();
    expect(wrapper.text()).toContain('Conta Principal');
  });
});
