import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import ReportsView from '../js/components/ReportsView.vue';
import api from '../js/utils/api';

vi.mock('../js/utils/api', () => ({
  default: {
    get: vi.fn(),
  },
}));

describe('ReportsView', () => {
  beforeEach(() => {
    vi.clearAllMocks();

    api.get.mockImplementation((url, config = {}) => {
      if (url === '/condominiums') {
        return Promise.resolve({
          data: [{ id: 1, name: 'Condomínio Central' }],
        });
      }

      if (url === '/finance/reports/budget') {
        expect(config).toEqual({
          params: {
            condominium_id: '1',
            year: 2026,
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

  it('gera relatório financeiro usando a rota existente do backend', async () => {
    const wrapper = mount(ReportsView, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<div><slot /></div>' },
          TopCard: { template: '<div />' },
        },
      },
    });

    await new Promise(resolve => setTimeout(resolve, 0));
    await wrapper.find('button').trigger('click');
    await new Promise(resolve => setTimeout(resolve, 0));

    expect(api.get).toHaveBeenCalledWith('/condominiums');
    expect(api.get).toHaveBeenCalledWith('/finance/reports/budget', {
      params: {
        condominium_id: '1',
        year: 2026,
      },
    });
    expect(wrapper.text()).toContain('Relatório orçamentário 2026');
    expect(wrapper.text()).toContain('Manutenção');
    expect(wrapper.text()).toContain('R$ 1.000,00');
  });

  it('bloqueia tipos sem rota implementada no backend', async () => {
    const wrapper = mount(ReportsView, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<div><slot /></div>' },
          TopCard: { template: '<div />' },
        },
      },
    });

    await new Promise(resolve => setTimeout(resolve, 0));

    const selects = wrapper.findAll('select');
    await selects[1].setValue('maintenance');
    await wrapper.find('button').trigger('click');
    await new Promise(resolve => setTimeout(resolve, 0));

    expect(wrapper.text()).toContain('integrada apenas ao relatório orçamentário disponível no backend');
    expect(api.get).not.toHaveBeenCalledWith('/finance/reports/budget', expect.anything());
  });
});
