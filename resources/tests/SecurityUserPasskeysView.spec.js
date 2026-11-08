import { shallowMount } from '@vue/test-utils';
import SecurityUserPasskeysView from '../js/security/SecurityUserPasskeysView.vue';
import { apiRequest } from '../js/utils/api';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn()
}));

const mountView = () => shallowMount(SecurityUserPasskeysView, {
  global: {
    stubs: {
      AuthenticatedLayout: { template: '<main><slot /></main>' },
      TopCard: { template: '<section><slot /></section>' },
      NeonButton: { template: '<button @click="$emit(\'click\')"><slot /></button>' }
    }
  }
});

describe('SecurityUserPasskeysView', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    apiRequest.mockResolvedValue({ data: [] });
  });

  test('carrega usuarios e consulta passkeys', async () => {
    apiRequest
      .mockResolvedValueOnce({ data: [{ id: 5, name: 'Ana', email: 'ana@condo.test' }] })
      .mockResolvedValueOnce({ user: { id: 5, name: 'Ana', email: 'ana@condo.test' }, passkeys: [{ id: 9, name: 'Windows', created_at: '2026-06-26T10:00:00Z' }] });

    const wrapper = mountView();
    await Promise.resolve();
    wrapper.vm.selectedUserId = 5;
    await wrapper.vm.loadUserPasskeys();

    expect(apiRequest).toHaveBeenCalledWith('/security/users/5/passkeys');
    expect(wrapper.text()).toContain('Windows');
  });

  test('revoga passkey de usuario selecionado', async () => {
    apiRequest
      .mockResolvedValueOnce({ data: [{ id: 5, name: 'Ana', email: 'ana@condo.test' }] })
      .mockResolvedValueOnce(null)
      .mockResolvedValueOnce({ user: { id: 5, name: 'Ana', email: 'ana@condo.test' }, passkeys: [] });

    const wrapper = mountView();
    await Promise.resolve();
    await Promise.resolve();
    wrapper.vm.selectedUserId = 5;
    await wrapper.vm.revoke({ id: 9 });

    expect(apiRequest).toHaveBeenCalledWith('/security/users/5/passkeys/9', { method: 'DELETE' });
    expect(wrapper.vm.success).toBe('Passkey revogada.');
  });
});
