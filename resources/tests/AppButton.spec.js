import { mount } from '@vue/test-utils';
import AppButton from '../js/components/ui/AppButton.vue';

describe('AppButton component', () => {
  test('renderiza texto do slot corretamente', () => {
    const wrapper = mount(AppButton, {
      slots: { default: 'Salvar Registro' }
    });
    expect(wrapper.text()).toContain('Salvar Registro');
    expect(wrapper.classes()).toContain('bg-indigo-600');
  });

  test('aplica classes corretas para variante secondary', () => {
    const wrapper = mount(AppButton, {
      props: { variant: 'secondary' },
      slots: { default: 'Cancelar' }
    });
    expect(wrapper.classes()).toContain('bg-slate-100');
  });

  test('emite evento de clique quando habilitado', async () => {
    const wrapper = mount(AppButton, {
      slots: { default: 'Clique Aqui' }
    });
    await wrapper.trigger('click');
    expect(wrapper.emitted('click')).toBeTruthy();
  });

  test('não emite clique quando desabilitado ou carregando', async () => {
    const wrapper = mount(AppButton, {
      props: { disabled: true },
      slots: { default: 'Desabilitado' }
    });
    await wrapper.trigger('click');
    expect(wrapper.emitted('click')).toBeFalsy();

    const loadingWrapper = mount(AppButton, {
      props: { loading: true },
      slots: { default: 'Carregando' }
    });
    await loadingWrapper.trigger('click');
    expect(loadingWrapper.emitted('click')).toBeFalsy();
    expect(loadingWrapper.find('svg.animate-spin').exists()).toBe(true);
  });
});
