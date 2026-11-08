import { mount } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import { describe, it, expect, jest } from '@jest/globals';
import AccountsView from '../js/finance/AccountsView.vue';
import { useFinanceDataStore } from '../js/finance/stores/financeDataStore';

describe('AccountsView.vue', () => {
  it('exibe lista de contas', async () => {
    const pinia = createPinia();
    setActivePinia(pinia);
    const store = useFinanceDataStore();
    store.fetchBanks = jest.fn(async () => {
      store.banks = [{ id: 1, name: 'Conta Principal', agency: '0001', account_number: '123', balance: 100 }];
    });
    store.fetchChartAccounts = jest.fn(async () => {
      store.chartAccounts = [];
    });
    const wrapper = mount(AccountsView, {
      global: {
        plugins: [pinia],
        stubs: {
          AuthenticatedLayout: { template: '<main><slot /></main>' },
          NeonButton: { template: '<button><slot /></button>' }
        }
      }
    });
    expect(store.fetchBanks).toHaveBeenCalled();
    await wrapper.vm.$nextTick();
    expect(wrapper.text()).toContain('Conta Principal');
  });
});
