import { flushPromises, mount } from '@vue/test-utils';
import PetFormPage from '../js/pages/PetFormPage.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('PetFormPage.vue', () => {
  function mountPage() {
    return mount(PetFormPage, {
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

      if (url === '/pets') {
        return Promise.resolve({ id: 5 });
      }

      return Promise.resolve(null);
    });
  });

  it('carrega apartamentos sem condominium_id manual', async () => {
    mountPage();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/apartments');
    expect(apiRequest.mock.calls[0][0]).not.toContain('condominium_id');
  });

  it('cria pet com FormData sem enviar condominium_id', async () => {
    const wrapper = mountPage();
    await flushPromises();

    Object.assign(wrapper.vm.form, {
      name: 'Rex',
      species: 'Cão',
      breed: 'SRD',
      gender: 'Macho',
      age: '3 anos',
      weight: '8 kg',
      apartment_id: 4,
      photo_url: 'https://example.com/rex.jpg',
      health_info: 'Vacinado',
      notes: '',
    });

    await wrapper.vm.handleSubmit();
    await flushPromises();

    const [, options] = apiRequest.mock.calls.find(([url]) => url === '/pets');
    const payload = options.body;

    expect(options.method).toBe('POST');
    expect(payload).toBeInstanceOf(FormData);
    expect(payload.get('name')).toBe('Rex');
    expect(payload.get('species')).toBe('Cão');
    expect(payload.get('apartment_id')).toBe('4');
    expect(payload.get('photo_url')).toBe('https://example.com/rex.jpg');
    expect(payload.get('condominium_id')).toBeNull();
    expect(global.__routerPush).toHaveBeenCalledWith('/pets');
  });
});
