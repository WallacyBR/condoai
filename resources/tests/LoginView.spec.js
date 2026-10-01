import { shallowMount } from '@vue/test-utils';
import LoginView from '../js/components/LoginView.vue';
import auth from '../js/services/auth';

const mockPush = jest.fn();

jest.mock('vue-router', () => ({
  useRouter: () => ({
    push: mockPush
  })
}));

jest.mock('../js/services/auth', () => ({
  __esModule: true,
  default: {
    login: jest.fn(),
    loginWithPasskey: jest.fn()
  }
}));

jest.mock('../js/utils/webauthn', () => ({
  browserSupportsPasskeys: jest.fn(() => true),
  passkeyOriginProblem: jest.fn(() => '')
}));

const mountLogin = () => shallowMount(LoginView, {
  global: {
    stubs: {
      NeonButton: {
        template: '<button type="submit"><slot /></button>'
      }
    }
  }
});

describe('LoginView', () => {
  beforeEach(() => {
    localStorage.clear();
    mockPush.mockReset();
    auth.login.mockResolvedValue({});
    auth.loginWithPasskey.mockResolvedValue({});
  });

  test('carrega apenas email lembrado e remove senha legada', async () => {
    localStorage.setItem('rememberedEmail', 'morador@condo.test');
    localStorage.setItem('rememberedPassword', 'senha-nao-deve-voltar');

    const wrapper = mountLogin();
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.credentials.email).toBe('morador@condo.test');
    expect(wrapper.vm.credentials.password).toBe('');
    expect(wrapper.vm.form.remember).toBe(true);
    expect(localStorage.getItem('rememberedPassword')).toBeNull();
  });

  test('salva somente o email ao autenticar com lembrar-me', async () => {
    const wrapper = mountLogin();
    wrapper.vm.credentials.email = 'sindico@condo.test';
    wrapper.vm.credentials.password = 'senha-segura';
    wrapper.vm.form.remember = true;

    await wrapper.vm.login();

    expect(auth.login).toHaveBeenCalledWith({
      email: 'sindico@condo.test',
      password: 'senha-segura'
    });
    expect(localStorage.getItem('rememberedEmail')).toBe('sindico@condo.test');
    expect(localStorage.getItem('rememberedPassword')).toBeNull();
    expect(mockPush).toHaveBeenCalledWith({ name: 'dashboard' });
  });

  test('remove credenciais lembradas quando lembrar-me esta desmarcado', async () => {
    localStorage.setItem('rememberedEmail', 'antigo@condo.test');
    localStorage.setItem('rememberedPassword', 'senha-antiga');

    const wrapper = mountLogin();
    wrapper.vm.credentials.email = 'novo@condo.test';
    wrapper.vm.credentials.password = 'senha-segura';
    wrapper.vm.form.remember = false;

    await wrapper.vm.login();

    expect(localStorage.getItem('rememberedEmail')).toBeNull();
    expect(localStorage.getItem('rememberedPassword')).toBeNull();
  });

  test('autentica usando passkey com email preenchido', async () => {
    const wrapper = mountLogin();
    wrapper.vm.credentials.email = 'morador@condo.test';

    await wrapper.vm.loginWithPasskey();

    expect(auth.loginWithPasskey).toHaveBeenCalledWith('morador@condo.test');
    expect(mockPush).toHaveBeenCalledWith({ name: 'dashboard' });
  });

  test('permite login rápido em modo dev para Admin SaaS, Síndico e Morador', async () => {
    const wrapper = mountLogin();
    expect(wrapper.vm.isDevMode).toBe(true);

    // Teste login rápido Admin SaaS
    await wrapper.vm.quickLogin('admin');
    expect(wrapper.vm.credentials.email).toBe('admin@sistema.com');
    expect(wrapper.vm.credentials.password).toBe('admin123');
    expect(auth.login).toHaveBeenCalledWith({
      email: 'admin@sistema.com',
      password: 'admin123'
    });

    // Teste login rápido Síndico
    await wrapper.vm.quickLogin('sindico');
    expect(wrapper.vm.credentials.email).toBe('sindico@teste.com');
    expect(wrapper.vm.credentials.password).toBe('teste');
    expect(auth.login).toHaveBeenCalledWith({
      email: 'sindico@teste.com',
      password: 'teste'
    });

    // Teste login rápido Morador
    await wrapper.vm.quickLogin('morador');
    expect(wrapper.vm.credentials.email).toBe('morador@teste.com');
    expect(wrapper.vm.credentials.password).toBe('teste');
    expect(auth.login).toHaveBeenCalledWith({
      email: 'morador@teste.com',
      password: 'teste'
    });
  });
});
