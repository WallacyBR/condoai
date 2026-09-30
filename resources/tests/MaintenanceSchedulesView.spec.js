import { mount } from '@vue/test-utils';
import { vi } from 'vitest';
import MaintenanceSchedulesView from '@/pages/maintenance/MaintenanceSchedulesView.vue';
import api from '@/utils/api';
import { mountOptionsWithLayoutStubs } from './helpers/mountWithLayoutStubs.js';

const { mockApi } = vi.hoisted(() => ({
  mockApi: {
    get: vi.fn(),
    delete: vi.fn(),
  },
}));

const mockRouter = { push: vi.fn() };

vi.mock('@/utils/api', () => ({
  default: mockApi,
}));

vi.mock('vue-router', () => ({
  useRouter: () => mockRouter,
}));

describe('MaintenanceSchedulesView.vue', () => {
  let wrapper;

  const mockSchedules = {
    data: [
      { id: 1, title: 'Agendamento 1', equipment: { name: 'Bomba X' }, type: 'daily', start_date: '2024-08-15', is_recurring: true, responsible_users: [{ name: 'Zé' }] },
      { id: 2, title: 'Agendamento 2', equipment: { name: 'Elevador Y' }, type: 'weekly', start_date: '2024-08-16', is_recurring: false, responsible_users: [] },
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
    mockRouter.push.mockClear();
    mockApi.get.mockResolvedValue({ data: mockSchedules });
    wrapper = mount(MaintenanceSchedulesView, mountOptionsWithLayoutStubs());
    await wrapper.vm.$nextTick();
    await wrapper.vm.$nextTick();
  });

  it('renderiza o título e o botão de novo agendamento', () => {
    expect(wrapper.text()).toContain('Novo Agendamento');
    expect(wrapper.text()).toContain('Total de Agendamentos');
  });

  it('busca e exibe a lista de agendamentos', () => {
    expect(mockApi.get).toHaveBeenCalledWith(expect.stringContaining('/maintenance/schedules'));
    expect(wrapper.text()).toContain('Agendamento 1');
    expect(wrapper.text()).toContain('Bomba X');
    expect(wrapper.text()).toContain('Zé');
    expect(wrapper.text()).toContain('Agendamento 2');
  });

  it('redireciona para criação ao clicar em "Novo Agendamento"', async () => {
    const createButton = wrapper.findAll('button').find((button) => button.text().includes('Novo Agendamento'));
    await createButton.trigger('click');
    expect(mockRouter.push).toHaveBeenCalledWith('/maintenance/schedules/create');
  });

  it('redireciona para edição ao clicar em "Editar"', async () => {
    const editButton = wrapper.findAll('button').find((button) => button.text().includes('Editar'));
    await editButton.trigger('click');
    expect(mockRouter.push).toHaveBeenCalledWith('/maintenance/schedules/1/edit');
  });

  it('abre o modal de confirmação de exclusão', async () => {
    const deleteButton = wrapper.findAll('button').find((button) => button.text().includes('Excluir'));
    await deleteButton.trigger('click');
    expect(wrapper.vm.isDeleteConfirmOpen).toBe(true);
    expect(wrapper.vm.scheduleToDeleteId).toBe(1);
  });

  it('chama a API de delete e recarrega a lista ao confirmar exclusão', async () => {
    mockApi.delete.mockResolvedValue({});
    const callsBeforeDelete = mockApi.get.mock.calls.length;
    wrapper.vm.scheduleToDeleteId = 1;
    wrapper.vm.isDeleteConfirmOpen = true;

    await wrapper.vm.deleteSchedule();
    await wrapper.vm.$nextTick();

    expect(mockApi.delete).toHaveBeenCalledWith('/maintenance/schedules/1');
    expect(mockApi.get.mock.calls.length).toBeGreaterThan(callsBeforeDelete);
  });

  it('implementa a filtragem ao digitar no campo de filtro', async () => {
    vi.useFakeTimers();
    const filterInput = wrapper.find('input[placeholder*="equipamento"]');
    await filterInput.setValue('2');
    vi.advanceTimersByTime(500);
    await wrapper.vm.$nextTick();

    expect(mockApi.get).toHaveBeenCalledWith(expect.stringContaining('maintenance_equipment_id=2'));
    vi.useRealTimers();
  });
});
