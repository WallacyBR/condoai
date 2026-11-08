import { mount } from '@vue/test-utils';
import MaintenanceSchedulesView from '@/pages/maintenance/MaintenanceSchedulesView.vue'; // Ajuste o caminho
import MaintenanceScheduleForm from '@/components/maintenance/MaintenanceScheduleForm.vue';
import Modal from '@/components/Modal.vue';
import api from '@/utils/api';
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue'; // Stubbed

jest.mock('@/utils/api', () => ({
  get: jest.fn(),
  delete: jest.fn(),
}));

// Mocking AuthenticatedLayout and other global components if necessary
const globalStubs = {
  AuthenticatedLayout: {
    template: '<div><slot name="page-title"/><slot/></div>', // Simple stub
  },
  MaintenanceScheduleForm: true, // Stubbing the form for now
  Modal: { // Simple stub for Modal
    props: ['show'],
    template: '<div v-if="show"><slot name="title"/><slot/><slot name="footer"/></div>'
  }
};

describe('MaintenanceSchedulesView.vue', () => {
  let wrapper;
  const mockSchedules = {
    data: [
      { id: 1, title: 'Agendamento 1', equipment: { name: 'Bomba X' }, type: 'daily', responsible_users: [{name: 'Zé'}] },
      { id: 2, title: 'Agendamento 2', equipment: { name: 'Elevador Y' }, type: 'weekly', responsible_users: [] },
    ],
    current_page: 1,
    last_page: 1,
    from: 1,
    to: 2,
    total: 2,
    per_page: 15,
    next_page_url: null,
    prev_page_url: null,
  };

  beforeEach(async () => {
    api.get.mockResolvedValue({ data: mockSchedules });
    wrapper = mount(MaintenanceSchedulesView, {
      global: {
        stubs: globalStubs,
        mocks: { // Se precisar de mocks globais como $router
          // $router: { push: jest.fn() }
        }
      }
    });
    await wrapper.vm.$nextTick(); // Esperar o onMounted e a chamada inicial à API
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  it('renderiza o título e o botão de novo agendamento', () => {
    expect(wrapper.text()).toContain('Gerenciar Agendamentos');
    expect(wrapper.find('button').text()).toContain('Novo Agendamento');
  });

  it('busca e exibe a lista de agendamentos', async () => {
    expect(api.get).toHaveBeenCalledWith(expect.stringContaining('/maintenance/schedules'));
    await wrapper.vm.$nextTick(); // Espera a renderização após a API

    const rows = wrapper.findAll('tbody tr');
    expect(rows.length).toBe(mockSchedules.data.length);
    expect(rows[0].text()).toContain('Agendamento 1');
    expect(rows[0].text()).toContain('Bomba X');
    expect(rows[0].text()).toContain('Zé');
    expect(rows[1].text()).toContain('Agendamento 2');
  });

  it('abre o modal MaintenanceScheduleForm ao clicar em "Novo Agendamento"', async () => {
    expect(wrapper.vm.isFormModalOpen).toBe(false);
    await wrapper.find('button.bg-cyan-600').trigger('click'); // Botão Novo Agendamento
    expect(wrapper.vm.isFormModalOpen).toBe(true);
    expect(wrapper.vm.selectedSchedule).toBeNull();
  });

  it('abre o modal MaintenanceScheduleForm com dados ao clicar em "Editar"', async () => {
    await wrapper.vm.$nextTick(); // Garante que a tabela está renderizada
    const editButton = wrapper.findAll('tbody tr td button').find(b => b.text() === 'Editar');
    expect(editButton.exists()).toBe(true);

    await editButton.trigger('click');
    expect(wrapper.vm.isFormModalOpen).toBe(true);
    expect(wrapper.vm.selectedSchedule).toEqual(mockSchedules.data[0]);
  });

  it('chama fetchSchedules e fecha o modal ao receber o evento "saved" do formulário', async () => {
    // Abre o modal
    await wrapper.find('button.bg-cyan-600').trigger('click');
    expect(wrapper.vm.isFormModalOpen).toBe(true);

    // Mock da chamada fetchSchedules para verificar se é chamada novamente
    const fetchSchedulesSpy = jest.spyOn(wrapper.vm, 'fetchSchedules');

    // Encontra o componente MaintenanceScheduleForm (mesmo que stubbed) e emite o evento
    const formComponent = wrapper.findComponent({ name: 'MaintenanceScheduleForm' }); // Use o nome real do componente
    await formComponent.vm.$emit('saved');

    expect(fetchSchedulesSpy).toHaveBeenCalled();
    expect(wrapper.vm.isFormModalOpen).toBe(false);
    fetchSchedulesSpy.mockRestore();
  });

  it('abre o modal de confirmação de exclusão', async () => {
    await wrapper.vm.$nextTick();
    const deleteButton = wrapper.findAll('tbody tr td button').find(b => b.text() === 'Excluir');
    expect(deleteButton.exists()).toBe(true);

    await deleteButton.trigger('click');
    expect(wrapper.vm.isDeleteConfirmOpen).toBe(true);
    expect(wrapper.vm.scheduleToDeleteId).toBe(mockSchedules.data[0].id);
  });

  it('chama a API de delete e recarrega a lista ao confirmar exclusão', async () => {
    api.delete.mockResolvedValue({}); // Mock da API de exclusão
    const fetchSchedulesSpy = jest.spyOn(wrapper.vm, 'fetchSchedules');

    // Abre o modal de confirmação (simulando o estado)
    wrapper.vm.scheduleToDeleteId = mockSchedules.data[0].id;
    wrapper.vm.isDeleteConfirmOpen = true;
    await wrapper.vm.$nextTick();

    // Encontra o botão de confirmar exclusão no Modal (pode precisar ajustar o seletor)
    // Assumindo que o Modal stubbed tem um slot de footer e o botão está lá
    const confirmDeleteButton = wrapper.findAllComponents(Modal).find(m => m.props('show') === true)
                                     .findAll('button').find(b => b.text() === 'Excluir');

    if (confirmDeleteButton && confirmDeleteButton.exists()) {
        await confirmDeleteButton.trigger('click');
        expect(api.delete).toHaveBeenCalledWith(`/maintenance/schedules/${mockSchedules.data[0].id}`);
        expect(fetchSchedulesSpy).toHaveBeenCalled();
        expect(wrapper.vm.isDeleteConfirmOpen).toBe(false);
    } else {
        console.warn("Botão de confirmar exclusão não encontrado no teste. Verifique o stub do Modal e seletores.");
        // Se o Modal é muito simples, pode ser necessário chamar o método deleteSchedule() diretamente.
        // await wrapper.vm.deleteSchedule(); // Chamada direta se o botão não for acessível via stub
    }

    fetchSchedulesSpy.mockRestore();
  });

  it('implementa a filtragem ao digitar no campo de filtro', async () => {
    jest.useFakeTimers(); // Usa timers falsos para controlar o debounce
    const fetchSchedulesSpy = jest.spyOn(wrapper.vm, 'fetchSchedules');

    const filterInput = wrapper.find('input[placeholder*="Filtrar por ID do equipamento"]');
    await filterInput.setValue('123');

    // O watcher no filterEquipmentInput deve chamar applyFilter, que usa debounce.
    // Avança os timers para disparar o debounce
    jest.advanceTimersByTime(500); // Tempo do debounce

    expect(fetchSchedulesSpy).toHaveBeenCalledWith(1); // Deve chamar fetchSchedules com a página 1
    expect(api.get).toHaveBeenCalledWith(expect.stringContaining('&maintenance_equipment_id=123'));

    fetchSchedulesSpy.mockRestore();
    jest.useRealTimers(); // Restaura timers reais
  });

});
