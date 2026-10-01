import { mount } from '@vue/test-utils';

jest.mock('@/utils/api', () => ({
  __esModule: true,
  default: { get: jest.fn() },
}));

const FinancialReportsView = require('../js/finance/FinancialReportsView.vue').default;

describe('FinancialReportsView', () => {
  const stubs = {
    AuthenticatedLayout: { template: '<main><slot /></main>' },
    TopCard: { template: '<section><slot /><slot name="actions" /></section>' },
    NeonButton: { template: '<button @click="$emit(\'click\')"><slot /></button>' },
  };

  it('exibe filtros e comandos dos relatórios gerenciais', () => {
    const wrapper = mount(FinancialReportsView, { global: { stubs } });
    expect(wrapper.findAll('input[type="date"]')).toHaveLength(2);
    expect(wrapper.text()).toContain('DRE');
    expect(wrapper.text()).toContain('Balancete');
  });
});
