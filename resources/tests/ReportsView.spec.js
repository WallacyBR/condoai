import { flushPromises, mount } from '@vue/test-utils';
import ReportsView from '../js/components/ReportsView.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
  authHeader: jest.fn(() => ({ headers: { Authorization: 'Bearer token-teste' } })),
}));

describe('ReportsView.vue', () => {
  const reportPayload = {
    type: 'overview',
    title: 'Visão Geral Operacional',
    start_date: '2026-05-26',
    end_date: '2026-06-25',
    generated_at: '2026-06-25T12:00:00.000000Z',
    summary: [
      { label: 'Moradores ativos', value: '12', tone: 'blue' },
      { label: 'Encomendas pendentes', value: '3', tone: 'orange' },
    ],
    columns: [
      { key: 'module', label: 'Módulo' },
      { key: 'indicator', label: 'Indicador' },
      { key: 'value', label: 'Valor' },
    ],
    rows: [
      { module: 'Moradores', indicator: 'Moradores ativos', value: '12' },
      { module: 'Encomendas', indicator: 'Pendentes no período', value: '3' },
    ],
  };

  function mountView() {
    return mount(ReportsView, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<main><slot /></main>' },
          NeonButton: {
            props: ['disabled', 'loading'],
            template: '<button :disabled="disabled || loading" @click="$emit(\'click\')"><slot /></button>',
          },
          TopCard: {
            props: ['title', 'subtitle'],
            template: '<section><h1>{{ title }}</h1><p>{{ subtitle }}</p><slot /><slot name="actions" /></section>',
          },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
    apiRequest.mockResolvedValue({ ...reportPayload });
    global.fetch = jest.fn();
    global.URL.createObjectURL = jest.fn(() => 'blob:relatorio');
    global.URL.revokeObjectURL = jest.fn();
  });

  afterEach(() => {
    jest.restoreAllMocks();
  });

  it('carrega o relatório padrão sem exigir condominium_id', async () => {
    const wrapper = mountView();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledTimes(1);
    expect(apiRequest.mock.calls[0][0]).toContain('/reports/operational?type=overview');
    expect(apiRequest.mock.calls[0][0]).toContain('start_date=');
    expect(apiRequest.mock.calls[0][0]).toContain('end_date=');
    expect(apiRequest.mock.calls[0][0]).not.toContain('condominium_id');
    expect(wrapper.text()).toContain('Visão Geral Operacional');
    expect(wrapper.text()).toContain('Moradores ativos');
    expect(wrapper.text()).toContain('Encomendas');
  });

  it('troca tipo e período usando a API operacional', async () => {
    const wrapper = mountView();
    await flushPromises();

    apiRequest.mockClear();
    wrapper.vm.filters.type = 'packages';
    wrapper.vm.filters.startDate = '2026-06-01';
    wrapper.vm.filters.endDate = '2026-06-25';
    await wrapper.vm.loadReport();

    expect(apiRequest).toHaveBeenCalledWith('/reports/operational?type=packages&start_date=2026-06-01&end_date=2026-06-25');
  });

  it('exibe estado vazio quando o relatório não possui linhas', async () => {
    apiRequest.mockResolvedValueOnce({ ...reportPayload, rows: [] });
    const wrapper = mountView();
    await flushPromises();

    expect(wrapper.find('[data-test="empty-state"]').exists()).toBe(true);
    expect(wrapper.text()).toContain('Nenhum registro encontrado');
  });

  it('exibe erro quando a API falha', async () => {
    apiRequest.mockRejectedValueOnce(new Error('Falha ao consultar relatório'));
    const wrapper = mountView();
    await flushPromises();

    expect(wrapper.find('[data-test="report-error"]').text()).toContain('Falha ao consultar relatório');
  });

  it('exporta CSV real pelo endpoint de exportação', async () => {
    const originalCreateElement = document.createElement.bind(document);
    jest.spyOn(document, 'createElement').mockImplementation((tagName) => {
      const element = originalCreateElement(tagName);

      if (tagName === 'a') {
        element.click = jest.fn();
      }

      return element;
    });

    global.fetch.mockResolvedValueOnce({
      ok: true,
      blob: () => Promise.resolve(new Blob(['Módulo;Valor'], { type: 'text/csv' })),
    });

    const wrapper = mountView();
    await flushPromises();
    await wrapper.vm.exportCsv();
    await flushPromises();

    expect(global.fetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/reports/operational/export?type=overview'),
      expect.objectContaining({
        headers: expect.objectContaining({
          Accept: 'text/csv',
          Authorization: 'Bearer token-teste',
        }),
      })
    );
    expect(global.URL.createObjectURL).toHaveBeenCalled();
  });
});
