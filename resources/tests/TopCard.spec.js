import { mount } from '@vue/test-utils';
import TopCard from '../js/components/ui/TopCard.vue';

const DummyIcon = {
  template: '<svg></svg>'
};

test('renderiza título e subtítulo', () => {
  const wrapper = mount(TopCard, {
    props: {
      title: 'Exemplo',
      subtitle: 'Detalhes',
      icon: DummyIcon
    },
    slots: {
      actions: '<button>Ação</button>'
    }
  });
  await wrapper.vm.$nextTick();
  expect(wrapper.find('h1').text()).toContain('Exemplo');
  expect(wrapper.find('p').text()).toContain('Detalhes');
  expect(wrapper.find('button').exists()).toBe(true);
});
