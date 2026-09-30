import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import ReportsView from '../js/components/ReportsView.vue';
import api from '../js/utils/api';

vi.mock('../js/composables/useUser', () => ({
  useUser: () => ({
    user: {
      value: {
        condominium_id: 1,
        condominium: { name: 'Condomínio Central' },
      },
    },
  }),
}));

vi.mock('../js/utils/api', () => ({
  default: {
    get: vi.fn(),
  },
}));

describe('ReportsView', () => {
  const currentYear = new Date().getFullYear();

  beforeEach(() => {
    vi.clearAllMocks();

    api.get.mockImplementation((url, config = {}) => {
      if (url === '/finance/reports/budget') {
        expect(config).toEqual({
          params: {
            condominium_id: 1,
            year: currentYear,
          },
        });

        return Promise.resolve({
          data: [
            {
              account_name: 'Manutenção',
              budgeted_amount: 1000,
              actual_amount: 800,
              variance: 200,
            },
          ],
        });
      }

      return Promise.resolve({ data: [] });
    });
  });

  it('gera relatório financeiro usando o condomínio do usuário autenticado', async () => {
    const wrapper = mount(ReportsView, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<div><slot /></div>' },
          TopCard: { template: '<div />' },
        },
      },
    });

    await wrapper.vm.$nextTick();
    await wrapper.find('button').trigger('click');
    await wrapper.vm.$nextTick();

    expect(api.get).toHaveBeenCalledWith('/finance/reports/budget', {
      params: {
        condominium_id: 1,
        year: currentYear,
      },
    });
    expect(wrapper.text()).toContain(`Relatório orçamentário ${currentYear}`);
    expect(wrapper.text()).toContain('Manutenção');
    expect(wrapper.text()).toContain('R$ 1.000,00');
  });

  it('mantém o relatório visível ao tentar exportar', async () => {
    const wrapper = mount(ReportsView, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<div><slot /></div>' },
          TopCard: { template: '<div />' },
        },
      },
    });

    await wrapper.vm.$nextTick();
    await wrapper.find('button').trigger('click');
    await wrapper.vm.$nextTick();

    const exportButtons = wrapper.findAll('button').filter(button => button.text().includes('Exportar'));
    await exportButtons[0].trigger('click');
    await wrapper.vm.$nextTick();

    expect(wrapper.text()).toContain(`Relatório orçamentário ${currentYear}`);
    expect(wrapper.text()).toContain('A exportação desta visão consolidada ainda não está disponível nesta tela.');
  });
});
