import { shallowMount } from '@vue/test-utils';
import MyPasskeysView from '../js/security/MyPasskeysView.vue';
import { apiRequest } from '../js/utils/api';
import { createPasskeyCredential } from '../js/utils/webauthn';

jest.mock('../js/utils/api', () => ({
  apiRequest: jest.fn()
}));

jest.mock('../js/utils/webauthn', () => ({
  browserSupportsPasskeys: jest.fn(() => true),
  createPasskeyCredential: jest.fn(),
  passkeyOriginProblem: jest.fn(() => '')
}));

const mountView = () => shallowMount(MyPasskeysView, {
  global: {
    stubs: {
      AuthenticatedLayout: { template: '<main><slot /></main>' },
      TopCard: { template: '<section><slot name="actions" /><slot /></section>' },
      NeonButton: { template: '<button @click="$emit(\'click\')"><slot /></button>' }
    }
  }
});

describe('MyPasskeysView', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    apiRequest.mockResolvedValue([]);
  });

  test('lista passkeys do usuario autenticado', async () => {
    apiRequest.mockResolvedValueOnce([{ id: 1, name: 'MacBook', created_at: '2026-06-26T10:00:00Z' }]);

    const wrapper = mountView();
    await Promise.resolve();
    await wrapper.vm.$nextTick();

    expect(apiRequest).toHaveBeenCalledWith('/security/passkeys');
    expect(wrapper.text()).toContain('MacBook');
  });

  test('cria passkey usando opcoes e verificacao WebAuthn', async () => {
    apiRequest
      .mockResolvedValueOnce([])
      .mockResolvedValueOnce({ token: 'token', publicKey: { challenge: 'abc', user: { id: 'abc' } } })
      .mockResolvedValueOnce({ id: 2, name: 'iPhone' })
      .mockResolvedValueOnce([{ id: 2, name: 'iPhone', created_at: '2026-06-26T10:00:00Z' }]);
    createPasskeyCredential.mockResolvedValue({ id: 'cred' });

    const wrapper = mountView();
    await Promise.resolve();
    wrapper.vm.newName = 'iPhone';
    await wrapper.vm.createPasskey();

    expect(createPasskeyCredential).toHaveBeenCalled();
    expect(apiRequest).toHaveBeenCalledWith('/security/passkeys/register/verify', expect.objectContaining({ method: 'POST' }));
    expect(wrapper.vm.success).toBe('Passkey criada com sucesso.');
  });

  test('exclui passkey do usuario', async () => {
    apiRequest
      .mockResolvedValueOnce([{ id: 1, name: 'MacBook', created_at: '2026-06-26T10:00:00Z' }])
      .mockResolvedValueOnce(null)
      .mockResolvedValueOnce([]);

    const wrapper = mountView();
    await Promise.resolve();
    await wrapper.vm.revoke({ id: 1 });

    expect(apiRequest).toHaveBeenCalledWith('/security/passkeys/1', { method: 'DELETE' });
    expect(wrapper.vm.success).toBe('Passkey excluída.');
  });
});
