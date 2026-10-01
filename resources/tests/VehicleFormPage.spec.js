import { flushPromises, mount } from '@vue/test-utils';
import VehicleFormPage from '../js/pages/VehicleFormPage.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('VehicleFormPage.vue', () => {
  function mountPage() {
    return mount(VehicleFormPage, {
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
      if (url === '/apartments') {
        return Promise.resolve([{ id: 4, number: '101', block: { name: 'A' } }]);
      }

      if (url === '/parking-spots') {
        return Promise.resolve([{ id: 9, identifier: 'G1' }]);
      }

      if (url === '/vehicles') {
        return Promise.resolve({ id: 3 });
      }

      return Promise.resolve(null);
    });
  });

  it('carrega dependencias sem condominium_id manual', async () => {
    mountPage();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/apartments');
    expect(apiRequest).toHaveBeenCalledWith('/parking-spots');
    expect(apiRequest.mock.calls.map(([url]) => url).join(' ')).not.toContain('condominium_id');
  });

  it('cria veiculo sem enviar condominium_id no payload', async () => {
    const wrapper = mountPage();
    await flushPromises();

    Object.assign(wrapper.vm.form, {
      license_plate: 'ABC-1234',
      model: 'Civic',
      color: 'Preto',
      year: '2020',
      apartment_id: 4,
      parking_spot_id: 9,
      notes: '',
    });

    await wrapper.vm.handleSubmit();
    await flushPromises();

    const [, options] = apiRequest.mock.calls.find(([url]) => url === '/vehicles');
    const payload = JSON.parse(options.body);

    expect(options.method).toBe('POST');
    expect(payload).toMatchObject({
      license_plate: 'ABC-1234',
      model: 'Civic',
      color: 'Preto',
      apartment_id: 4,
      parking_spot_id: 9,
      year: '2020',
      notes: null,
    });
    expect(payload).not.toHaveProperty('condominium_id');
    expect(global.__routerPush).toHaveBeenCalledWith('/vehicles');
  });
});
