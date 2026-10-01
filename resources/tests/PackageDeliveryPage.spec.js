import { flushPromises, mount } from '@vue/test-utils';
import PackageDeliveryPage from '../js/pages/PackageDeliveryPage.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn(),
}));

describe('PackageDeliveryPage', () => {
  function mountPage() {
    return mount(PackageDeliveryPage, {
      global: {
        stubs: {
          AuthenticatedLayout: { template: '<main><slot /></main>' },
          SignaturePad: { template: '<div />' },
          WebcamCapture: { template: '<div />' },
        },
      },
    });
  }

  beforeEach(() => {
    jest.clearAllMocks();
    apiRequest.mockResolvedValue([]);
  });

  test('exibe erro de segunda retirada retornado pela API sem remover a encomenda', async () => {
    const wrapper = mountPage();
    await flushPromises();

    wrapper.vm.form.token = 'ABC123';
    wrapper.vm.form.package_id = 10;
    wrapper.vm.packages = [{ id: 10, code: 'ENC-10' }];
    apiRequest.mockRejectedValueOnce(new Error('Encomenda já retirada'));

    await wrapper.vm.submit();
    await flushPromises();

    expect(apiRequest).toHaveBeenCalledWith('/packages/10/withdraw', {
      method: 'POST',
      body: JSON.stringify({
        code: 'ABC123',
        signature: '',
        document_photo: '',
      }),
    });
    expect(wrapper.text()).toContain('Encomenda já retirada');
    expect(wrapper.vm.packages).toEqual([{ id: 10, code: 'ENC-10' }]);
  });
});
