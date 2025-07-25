import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { describe, it, expect, jest } from '@jest/globals';
import AccountsView from '../js/finance/AccountsView.vue';
import { useAccountsStore } from '../js/finance/stores/accountsStore';

describe('AccountsView.vue', () => {
  it('exibe lista de contas', async () => {
    setActivePinia(createPinia());
    const store = useAccountsStore();
    store.fetchAccounts = jest.fn(() => {
      store.accounts = [{ id: 1, name: 'Conta Principal' }];
    });
    const wrapper = mount(AccountsView, { global: { plugins: [store.$pinia] } });
    expect(store.fetchAccounts).toHaveBeenCalled();
    await wrapper.vm.$nextTick();
    expect(wrapper.text()).toContain('Conta Principal');
  });
});
