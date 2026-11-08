import { mount } from '@vue/test-utils';
import AppTopBar from '../js/components/layouts/AppTopBar.vue';

describe('AppTopBar component', () => {
  const defaultProps = {
    pageTitle: 'Gestão de Moradores',
    selectedCondoName: 'Residencial Flores',
    userName: 'Carlos Silva',
    userInitials: 'CS',
    isDesktop: true,
    isSidebarOpen: true,
    isCollapsed: false
  };

  const globalStubs = {
    global: {
      stubs: {
        'router-link': {
          template: '<a><slot /></a>'
        }
      }
    }
  };

  test('renderiza título da página e nome do condomínio', () => {
    const wrapper = mount(AppTopBar, {
      props: defaultProps,
      ...globalStubs
    });

    expect(wrapper.text()).toContain('Gestão de Moradores');
    expect(wrapper.text()).toContain('Residencial Flores');
    expect(wrapper.text()).toContain('Carlos Silva');
    expect(wrapper.text()).toContain('CS');
  });

  test('emite toggle-collapse quando em desktop', async () => {
    const wrapper = mount(AppTopBar, {
      props: { ...defaultProps, isDesktop: true },
      ...globalStubs
    });

    const toggleButton = wrapper.find('button[aria-label="Alternar Menu"]');
    await toggleButton.trigger('click');

    expect(wrapper.emitted('toggle-collapse')).toBeTruthy();
  });

  test('emite toggle-sidebar quando em mobile', async () => {
    const wrapper = mount(AppTopBar, {
      props: { ...defaultProps, isDesktop: false },
      ...globalStubs
    });

    const toggleButton = wrapper.find('button[aria-label="Alternar Menu"]');
    await toggleButton.trigger('click');

    expect(wrapper.emitted('toggle-sidebar')).toBeTruthy();
  });

  test('abre dropdown de notificações ao clicar no sino', async () => {
    const wrapper = mount(AppTopBar, {
      props: defaultProps,
      ...globalStubs
    });

    const bellButton = wrapper.find('button[aria-label="Abrir notificações"]');
    await bellButton.trigger('click');

    expect(wrapper.text()).toContain('Notificações');
  });

  test('abre menu de usuário e emite logout', async () => {
    const wrapper = mount(AppTopBar, {
      props: defaultProps,
      ...globalStubs
    });

    // Clica no avatar do usuário
    const userButton = wrapper.find('button.flex.items-center.space-x-2.rounded-full');
    await userButton.trigger('click');

    expect(wrapper.text()).toContain('Sair do Sistema');

    // Clica em Sair
    const logoutBtn = wrapper.findAll('button').filter(b => b.text().includes('Sair do Sistema'))[0];
    await logoutBtn.trigger('click');

    expect(wrapper.emitted('logout')).toBeTruthy();
  });
});
