import { mount } from '@vue/test-utils';
import EmptyState from '../js/components/ui/EmptyState.vue';

describe('EmptyState component', () => {
  test('renderiza título, descrição e botão de ação', async () => {
    const wrapper = mount(EmptyState, {
      props: {
        title: 'Nenhum Morador Encontrado',
        description: 'Cadastre o primeiro morador da unidade.',
        actionText: 'Cadastrar Morador'
      }
    });

    expect(wrapper.text()).toContain('Nenhum Morador Encontrado');
    expect(wrapper.text()).toContain('Cadastre o primeiro morador da unidade.');
    expect(wrapper.text()).toContain('Cadastrar Morador');

    await wrapper.find('button').trigger('click');
    expect(wrapper.emitted('action')).toBeTruthy();
  });
});
