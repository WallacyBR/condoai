import { flushPromises, mount } from '@vue/test-utils';
import CommonAreaFormPage from '../js/pages/CommonAreaFormPage.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('CommonAreaFormPage.vue', () => {
  function mountPage(routeParams = {}) {
    global.__mockVueRoute.params = routeParams;

    return mount(CommonAreaFormPage, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<main><slot /></main>' },
          NeonButton: { template: '<button type="submit"><slot /></button>' },
          WebcamCapture: { template: '<div />' },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
    apiRequest.mockImplementation((url) => {
      if (url === '/common-areas/7') {
        return Promise.resolve({
          id: 7,
          name: 'Salão de Festas',
          description: 'Área principal',
          capacity: 80,
        });
      }

      if (url === '/common-areas') {
        return Promise.resolve({ id: 8 });
      }

      return Promise.resolve(null);
    });
  });

  it('carrega area para edicao pelo cliente central', async () => {
    const wrapper = mountPage({ id: '7' });
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/common-areas/7');
    expect(apiRequest.mock.calls[0][0]).not.toContain('condominium_id');
    expect(wrapper.vm.form.name).toBe('Salão de Festas');
  });

  it('cria area comum sem enviar condominium_id no payload', async () => {
    const wrapper = mountPage();
    await flushPromises();

    Object.assign(wrapper.vm.form, {
      name: 'Churrasqueira',
      description: 'Área externa',
      capacity: 30,
    });

    await wrapper.vm.submit();
    await flushPromises();

    const [, options] = apiRequest.mock.calls.find(([url]) => url === '/common-areas');
    const payload = JSON.parse(options.body);

    expect(options.method).toBe('POST');
    expect(payload).toMatchObject({
      name: 'Churrasqueira',
      description: 'Área externa',
      capacity: 30,
    });
    expect(payload).not.toHaveProperty('condominium_id');
    expect(global.__routerPush).toHaveBeenCalledWith({ name: 'areas' });
  });
});
