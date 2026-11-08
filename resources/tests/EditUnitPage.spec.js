import { flushPromises, mount } from '@vue/test-utils';
import EditUnitPage from '../js/pages/EditUnitPage.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('EditUnitPage.vue', () => {
  function mountPage() {
    global.__mockVueRoute.params = { id: '12' };

    return mount(EditUnitPage, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<main><slot /></main>' },
          NeonButton: { template: '<button type="submit"><slot /></button>' },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
    apiRequest.mockImplementation((url) => {
      if (url === '/apartments/12') {
        return Promise.resolve({ id: 12, number: '101', floor: 1, active: true, condominium_id: 99 });
      }

      return Promise.resolve(null);
    });
  });

  it('carrega unidade pelo cliente central sem depender de tenant local', async () => {
    const wrapper = mountPage();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/apartments/12');
    expect(apiRequest.mock.calls[0][0]).not.toContain('condominium_id');
    expect(wrapper.vm.form).toEqual({ number: '101', floor: 1, active: true });
  });

  it('salva unidade sem reenviar condominium_id', async () => {
    const wrapper = mountPage();
    await flushPromises();

    apiRequest.mockClear();
    wrapper.vm.form.number = '102';
    await wrapper.vm.submit();
    await flushPromises();

    const [, options] = apiRequest.mock.calls.find(([url]) => url === '/apartments/12');
    const payload = JSON.parse(options.body);

    expect(options.method).toBe('PUT');
    expect(payload).toEqual({ number: '102', floor: 1, active: true });
    expect(payload).not.toHaveProperty('condominium_id');
    expect(global.__routerPush).toHaveBeenCalledWith('/units-list');
  });
});
