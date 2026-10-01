import { flushPromises, mount } from '@vue/test-utils';
import UnitModal from '../js/components/UnitModal.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('UnitModal.vue', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    apiRequest.mockResolvedValue({ id: 8, number: '101' });
  });

  it('cria unidade sem enviar condominium_id no payload', async () => {
    const wrapper = mount(UnitModal, {
      props: { show: true },
    });

    Object.assign(wrapper.vm.form, {
      number: '101',
      type: 'apartment',
      status: 'vacant',
      floor: 1,
      area: 80,
    });

    await wrapper.vm.submit();
    await flushPromises();

    const [, options] = apiRequest.mock.calls.find(([url]) => url === '/units');
    const payload = JSON.parse(options.body);

    expect(options.method).toBe('POST');
    expect(payload).toMatchObject({
      number: '101',
      type: 'apartment',
      status: 'vacant',
      floor: 1,
      area: 80,
    });
    expect(payload).not.toHaveProperty('condominium_id');
    expect(wrapper.emitted('submit')[0][0]).toEqual({ id: 8, number: '101' });
  });
});
