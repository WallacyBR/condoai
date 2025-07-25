import { mount } from '@vue/test-utils';
import CommonAreasView from '../js/components/CommonAreasView.vue';

describe('CommonAreasView.vue', () => {
  it('componente existe', () => {
    const wrapper = mount(CommonAreasView);
    expect(wrapper.exists()).toBe(true);
  });
});
