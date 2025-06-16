import { shallowMount } from '@vue/test-utils';
import LoginView from '../js/components/LoginView.vue';

test.skip('campos de email e senha estao presentes', () => {
  const wrapper = shallowMount(LoginView, { global: { stubs: { NeonButton: true } } });
  expect(wrapper.find('input#email').exists()).toBe(true);
  expect(wrapper.find('input#password').exists()).toBe(true);
});
