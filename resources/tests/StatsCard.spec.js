import { mount } from '@vue/test-utils';
import StatsCard from '../js/components/ui/StatsCard.vue';

describe('StatsCard component', () => {
  test('renderiza rótulo e valor corretamente', () => {
    const wrapper = mount(StatsCard, {
      props: {
        label: 'Total de Moradores',
        value: 142,
        variant: 'blue'
      }
    });

    expect(wrapper.text()).toContain('Total de Moradores');
    expect(wrapper.text()).toContain('142');
    expect(wrapper.find('.rounded-xl.border').exists()).toBe(true);
  });

  test('aceita prop title como alternativa para label', () => {
    const wrapper = mount(StatsCard, {
      props: {
        title: 'Unidades Ocupadas',
        value: '95%'
      }
    });

    expect(wrapper.text()).toContain('Unidades Ocupadas');
    expect(wrapper.text()).toContain('95%');
  });
});
