import { mount } from '@vue/test-utils';
import PageHeader from '../js/components/ui/PageHeader.vue';

describe('PageHeader component', () => {
  test('renderiza título, subtítulo e badge', () => {
    const wrapper = mount(PageHeader, {
      props: {
        title: 'Controle de Acessos',
        subtitle: 'Histórico de portaria',
        badge: 'Ativo'
      }
    });

    expect(wrapper.text()).toContain('Controle de Acessos');
    expect(wrapper.text()).toContain('Histórico de portaria');
    expect(wrapper.text()).toContain('Ativo');
    expect(wrapper.find('button[title="Voltar"]').exists()).toBe(false);
  });

  test('mostra botão voltar quando showBack for true e emite back', async () => {
    const wrapper = mount(PageHeader, {
      props: {
        title: 'Novo Morador',
        showBack: true
      }
    });

    const backBtn = wrapper.find('button[title="Voltar"]');
    expect(backBtn.exists()).toBe(true);

    await backBtn.trigger('click');
    expect(wrapper.emitted('back')).toBeTruthy();
  });
});
