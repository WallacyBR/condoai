import { flushPromises, mount } from '@vue/test-utils';
import MaintenanceSchedulesView from '@/pages/maintenance/MaintenanceSchedulesView.vue'; // Ajuste o caminho
import api from '@/utils/api';

jest.mock('@/utils/api', () => ({
  get: jest.fn(),
  delete: jest.fn(),
}));

// Mocking AuthenticatedLayout and other global components if necessary
const globalStubs = {
  AuthenticatedLayout: {
    template: '<div><slot name="page-title"/><slot/></div>', // Simple stub
  },
  TopCard: { props: ['title', 'subtitle'], template: '<section><h1>{{ title }}</h1><p>{{ subtitle }}</p><slot /><slot name="actions" /></section>' },
  StatsCard: { template: '<div></div>' },
  NeonButton: { template: '<button @click="$emit(\'click\')"><slot /></button>' },
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
    await flushPromises();
    await wrapper.vm.$nextTick();
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  it('renderiza o título e o botão de novo agendamento', () => {
    expect(wrapper.text()).toContain('Gerenciar Agendamentos');
    expect(wrapper.findAll('button').some(button => button.text().includes('Novo Agendamento'))).toBe(true);
  });

  it('busca e exibe a lista de agendamentos', async () => {
    expect(api.get).toHaveBeenCalledWith(expect.stringContaining('/maintenance/schedules'));
    await flushPromises();
    await wrapper.vm.$nextTick();

    expect(wrapper.text()).toContain('Agendamento 1');
    expect(wrapper.text()).toContain('Bomba X');
    expect(wrapper.text()).toContain('Zé');
    expect(wrapper.text()).toContain('Agendamento 2');
  });

  it('redireciona ao clicar em "Novo Agendamento"', async () => {
    const createButton = wrapper.findAll('button').find(button => button.text().includes('Novo Agendamento'));
    expect(createButton.exists()).toBe(true);
    await createButton.trigger('click');
    expect(global.__routerPush).toHaveBeenCalledWith('/maintenance/schedules/create');
  });

  it('redireciona ao clicar em "Editar"', async () => {
    await flushPromises();
    await wrapper.vm.$nextTick();
    const editButton = wrapper.findAll('button').find(b => b.text().includes('Editar'));
    expect(editButton.exists()).toBe(true);

    await editButton.trigger('click');
    expect(global.__routerPush).toHaveBeenCalledWith('/maintenance/schedules/1/edit');
  });

  it('abre o modal de confirmação de exclusão', async () => {
    await flushPromises();
    await wrapper.vm.$nextTick();
    const deleteButton = wrapper.findAll('button').find(b => b.text().includes('Excluir'));
    expect(deleteButton.exists()).toBe(true);

    await deleteButton.trigger('click');
    expect(wrapper.vm.isDeleteConfirmOpen).toBe(true);
    expect(wrapper.vm.scheduleToDeleteId).toBe(mockSchedules.data[0].id);
  });

  it('chama a API de delete e recarrega a lista ao confirmar exclusão', async () => {
    api.delete.mockResolvedValue({}); // Mock da API de exclusão
    const callsBeforeDelete = api.get.mock.calls.length;

    // Abre o modal de confirmação (simulando o estado)
    wrapper.vm.scheduleToDeleteId = mockSchedules.data[0].id;
    wrapper.vm.isDeleteConfirmOpen = true;
    await wrapper.vm.$nextTick();

    await wrapper.vm.deleteSchedule();
    await flushPromises();
    expect(api.delete).toHaveBeenCalledWith(`/maintenance/schedules/${mockSchedules.data[0].id}`);
    expect(api.get.mock.calls.length).toBeGreaterThan(callsBeforeDelete);
    expect(wrapper.vm.isDeleteConfirmOpen).toBe(false);
  });

  it('implementa a filtragem ao digitar no campo de filtro', async () => {
    jest.useFakeTimers(); // Usa timers falsos para controlar o debounce
    const callsBeforeFilter = api.get.mock.calls.length;

    const filterInput = wrapper.find('#equipment-filter');
    await filterInput.setValue('123');

    // O watcher no filterEquipmentInput deve chamar applyFilter, que usa debounce.
    // Avança os timers para disparar o debounce
    jest.advanceTimersByTime(500); // Tempo do debounce
    await flushPromises();

    expect(api.get.mock.calls.length).toBeGreaterThan(callsBeforeFilter);
    expect(api.get).toHaveBeenCalledWith(expect.stringContaining('&maintenance_equipment_id=123'));

    jest.useRealTimers(); // Restaura timers reais
  });

});
