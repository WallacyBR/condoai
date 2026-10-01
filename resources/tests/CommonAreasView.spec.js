import { flushPromises, mount } from '@vue/test-utils';
import CommonAreasView from '../js/components/CommonAreasView.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('CommonAreasView.vue', () => {
  function mountView() {
    return mount(CommonAreasView, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<main><slot /></main>' },
          TopCard: { props: ['title', 'subtitle'], template: '<section><h1>{{ title }}</h1><p>{{ subtitle }}</p><slot /><slot name="actions" /></section>' },
          NeonButton: { template: '<button @click="$emit(\'click\')"><slot /></button>' },
          CommonAreaCalendar: { template: '<div />' },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
    localStorage.setItem('selectedCondominiumName', 'Residencial Teste');
    apiRequest.mockResolvedValue([
      { id: 1, name: 'Salão de Festas', active: true, capacity: 80, bookings: [] },
    ]);
  });

  it('carrega areas comuns usando tenant do backend, sem enviar condominium_id manual', async () => {
    const wrapper = mountView();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/common-areas');
    expect(apiRequest.mock.calls[0][0]).not.toContain('condominium_id');
    expect(wrapper.text()).toContain('Salão de Festas');
  });

  it('exclui area comum pelo cliente API central e recarrega a lista', async () => {
    const wrapper = mountView();
    await flushPromises();
    apiRequest.mockClear();
    apiRequest.mockResolvedValueOnce(null).mockResolvedValueOnce([]);
    jest.spyOn(window, 'confirm').mockReturnValueOnce(true);

    await wrapper.vm.deleteArea({ id: 1, name: 'Salão de Festas' });
    await flushPromises();

    expect(apiRequest).toHaveBeenNthCalledWith(1, '/common-areas/1', { method: 'DELETE' });
    expect(apiRequest).toHaveBeenNthCalledWith(2, '/common-areas');
    window.confirm.mockRestore();
  });
});
