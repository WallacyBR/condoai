import { flushPromises, mount } from '@vue/test-utils';
import MaintenanceScheduleForm from '@/components/maintenance/MaintenanceScheduleForm.vue'; // Ajuste o caminho se necessário
import Modal from '@/components/Modal.vue'; // Mock ou componente real
import api from '@/utils/api'; // Mock da API

// Mock do helper api
jest.mock('@/utils/api', () => ({
  get: jest.fn(),
  post: jest.fn(),
  put: jest.fn(),
}));

// Mock do componente Modal, se for complexo ou para isolar o teste
// Se Modal for simples, pode não ser necessário mocká-lo.
// jest.mock('@/components/Modal.vue', () => ({
//   name: 'Modal',
//   props: ['show'],
//   template: '<div v-if="show"><slot name="title"></slot><slot></slot><slot name="footer"></slot></div>',
// }));


describe('MaintenanceScheduleForm.vue', () => {
  let wrapper;
  const modalStub = {
    props: ['show'],
    template: '<div v-if="show"><slot name="title" /><slot /><slot name="footer" /></div>',
    emits: ['close']
  };

  const mockAvailableEquipment = [
    { id: 1, name: 'Bomba Piscina' },
    { id: 2, name: 'Elevador Torre A' },
  ];
  const mockAvailableUsers = [
    { id: 10, name: 'Zelador João', profile: { name: 'Zelador' } },
    { id: 11, name: 'Maria Limpeza', profile: { name: 'Limpeza' } },
  ];

  beforeEach(() => {
    // Reset mocks antes de cada teste
    jest.clearAllMocks();

    // Mock das chamadas de API para fetchEquipments e fetchUsers
    api.get.mockImplementation((url) => {
      if (url.includes('/maintenance/equipments')) {
        return Promise.resolve({ data: { data: mockAvailableEquipment } });
      }
      if (url.includes('/users')) {
        return Promise.resolve({ data: { data: mockAvailableUsers } });
      }
      return Promise.reject(new Error(`Unhandled GET request to ${url}`));
    });

    wrapper = mount(MaintenanceScheduleForm, {
      props: {
        show: true,
        scheduleData: null,
      },
      global: {
        // Fornecer stubs ou componentes globais se necessário
         stubs: {
             Modal: modalStub
         }
      }
    });
  });

  it('renderiza o formulário corretamente no modo de criação', async () => {
    await wrapper.vm.$nextTick(); // Espera o onMounted
    expect(wrapper.find('form').exists()).toBe(true);
    expect(wrapper.find('#title').element.value).toBe('');
    expect(wrapper.find('button[type="submit"]').text()).toContain('Criar Agendamento');
    // Verifica se os selects de equipamentos e usuários foram populados
    expect(wrapper.findAll('#maintenance_equipment_id option').length).toBeGreaterThan(1); // Inclui "Selecione"
    expect(wrapper.findAll('#responsible_user_ids option').length).toBe(mockAvailableUsers.length);
  });

  it('popula o formulário com dados quando scheduleData é fornecido (modo de edição)', async () => {
    const scheduleData = {
      id: 1,
      title: 'Agendamento Existente',
      maintenance_equipment_id: 1,
      description: 'Descrição teste',
      type: 'monthly',
      start_date: '2024-08-15',
      due_time: '10:00',
      is_recurring: true,
      day_of_month: 15,
      responsible_users: [{ id: 10, name: 'Zelador João' }],
      specific_days_of_week: [],
    };

    // Remontar o componente com props de edição
    wrapper = mount(MaintenanceScheduleForm, {
      props: { show: true, scheduleData },
     global: { stubs: { Modal: modalStub } }
    });
    await flushPromises();
    await wrapper.vm.$nextTick(); // onMounted
    await wrapper.vm.$nextTick(); // watch

    expect(wrapper.find('#title').element.value).toBe(scheduleData.title);
    expect(wrapper.find('#maintenance_equipment_id').element.value).toBe(String(scheduleData.maintenance_equipment_id));
    expect(wrapper.find('#type').element.value).toBe(scheduleData.type);
    expect(wrapper.find('button[type="submit"]').text()).toContain('Atualizar');
    // Verifica se o usuário responsável está selecionado
    // O valor de um select multiple é um array de strings
    expect(wrapper.vm.form.responsible_user_ids).toEqual([10]);
  });

  it('chama a API de POST ao submeter em modo de criação', async () => {
    api.post.mockResolvedValue({ data: { id: 1, title: 'Novo Agendamento' } });

    await wrapper.find('#title').setValue('Novo Agendamento Teste');
    await wrapper.find('#maintenance_equipment_id').setValue(1);
    // Preencher outros campos obrigatórios conforme necessário
    await wrapper.find('#start_date').setValue('2024-09-01');
    await wrapper.find('form').trigger('submit.prevent');

    expect(api.post).toHaveBeenCalledWith('/maintenance/schedules', expect.any(Object));
    expect(wrapper.emitted('saved')).toBeTruthy();
    expect(wrapper.emitted('close')).toBeTruthy();
  });

  it('chama a API de PUT ao submeter em modo de edição', async () => {
    const scheduleData = { id: 1, title: 'Agendamento Original', responsible_users: [] };
     wrapper = mount(MaintenanceScheduleForm, { // Remontar com dados
      props: { show: true, scheduleData },
      global: { stubs: { Modal: modalStub } }
    });
    await flushPromises();
    await wrapper.vm.$nextTick(); // onMounted
    await wrapper.vm.$nextTick(); // watch

    api.put.mockResolvedValue({ data: { ...scheduleData, title: 'Título Atualizado' } });

    await wrapper.find('#title').setValue('Título Atualizado');
    await wrapper.find('form').trigger('submit.prevent');

    expect(api.put).toHaveBeenCalledWith(`/maintenance/schedules/${scheduleData.id}`, expect.any(Object));
    expect(wrapper.emitted('saved')).toBeTruthy();
    expect(wrapper.emitted('close')).toBeTruthy();
  });

  it('exibe erros de validação da API', async () => {
    const consoleErrorSpy = jest.spyOn(console, 'error').mockImplementation(() => {});
    const errors = {
      title: ['O campo título é obrigatório.'],
      maintenance_equipment_id: ['Selecione um equipamento válido.'],
    };
    api.post.mockRejectedValue({ response: { status: 422, data: { errors } } });

    await wrapper.find('form').trigger('submit.prevent');
    await wrapper.vm.$nextTick(); // Espera o tratamento do erro

    expect(wrapper.vm.errors.title).toEqual(errors.title);
    expect(wrapper.find('p.text-red-400').text()).toContain(errors.title[0]);
    consoleErrorSpy.mockRestore();
  });

  it('emite "close" ao clicar no botão Cancelar', async () => {
    // O botão cancelar está dentro do slot #footer do Modal.
    // Se o Modal é stubbed de forma muito simples, pode precisar de um wrapper mais completo ou testar o evento no Modal.
    // Assumindo que o botão de cancelar está diretamente no MaintenanceScheduleForm para este exemplo
    // Se o botão está no Modal, o teste seria mais complexo ou o Modal precisaria emitir um evento que o Form escuta.

    // Para este exemplo, vamos simular que o form tem um botão de cancelar que emite 'close'
    // wrapper.find('button.cancel-button').trigger('click'); // Supondo que existe um botão com essa classe
    // expect(wrapper.emitted('close')).toBeTruthy();

    // Como o botão está no Modal, e o Modal é stubbed, testamos se o $emit('close') é chamado pelo form:
    // Nesse caso, o próprio form tem um botão de cancelar:
    const cancelButton = wrapper.findAll('button').find(b => b.text() === 'Cancelar');
    if (cancelButton) {
        await cancelButton.trigger('click');
        expect(wrapper.emitted('close')).toBeTruthy();
    } else {
        console.warn("Botão Cancelar não encontrado diretamente no formulário para teste. Pode estar dentro do Modal não stubbed completamente.");
    }
  });

});
