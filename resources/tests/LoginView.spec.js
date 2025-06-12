import { mount } from '@vue/test-utils';
import LoginView from '../js/components/LoginView.vue';

test('campos carregam valores padrao', () => {
  const wrapper = mount(LoginView);
  const email = wrapper.find('input#email').element.value;
  const password = wrapper.find('input#password').element.value;
  expect(email).toBe(import.meta.env.VITE_DEFAULT_LOGIN_EMAIL);
  expect(password).toBe(import.meta.env.VITE_DEFAULT_LOGIN_PASSWORD);
});
