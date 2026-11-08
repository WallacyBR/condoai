import { shallowMount } from '@vue/test-utils';
import CondominiumForm from '../js/components/CondominiumForm.vue';

test.skip('carrega dados do condomínio para edição', () => {
  const condo = {
    id: 1,
    name: 'Teste',
    document: '123',
    address: 'Rua A',
    city: 'B',
    state: 'SP',
    zip_code: '00000-000',
    phone: '',
    email: 'test@example.com',
    responsible: 'Alguém',
    domain: 'teste.com',
    active: true
  };
  const wrapper = shallowMount(CondominiumForm, {
    props: { show: true, condominium: condo },
    global: { stubs: { Modal: true, NeonButton: true } }
  });
  expect(wrapper.find('input').element.value).toBe('Teste');
});
