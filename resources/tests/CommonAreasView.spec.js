import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import CommonAreasView from '../js/components/CommonAreasView.vue';
import { mountOptionsWithLayoutStubs } from './helpers/mountWithLayoutStubs.js';

describe('CommonAreasView.vue', () => {
  it('componente existe', () => {
    const wrapper = mount(CommonAreasView, mountOptionsWithLayoutStubs());
    expect(wrapper.exists()).toBe(true);
  });
});
