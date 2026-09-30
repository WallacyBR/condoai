import { mount } from '@vue/test-utils';
import { format, startOfToday } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { vi } from 'vitest';
import MyDailyMaintenanceTasks from '@/pages/maintenance/MyDailyMaintenanceTasks.vue';
import api from '@/utils/api';
import { mountOptionsWithLayoutStubs } from './helpers/mountWithLayoutStubs.js';

const { mockApi } = vi.hoisted(() => ({
  mockApi: {
    get: vi.fn(),
  },
}));

const mockRouter = { push: vi.fn() };

vi.mock('@/utils/api', () => ({
  default: mockApi,
}));

vi.mock('vue-router', () => ({
  useRouter: () => mockRouter,
}));

describe('MyDailyMaintenanceTasks.vue', () => {
  let wrapper;
  const todayString = format(startOfToday(), 'yyyy-MM-dd');
  const formattedToday = format(startOfToday(), "d 'de' MMMM 'de' yyyy", { locale: ptBR });
  const mockTasks = [
    { id: 1, title: 'Verificar Bomba A', equipment: { name: 'Bomba Piscina A' }, due_time: '10:00:00', description: 'Checar pressão e ruído.' },
    { id: 2, title: 'Limpar Hall Torre B', equipment: { name: 'Hall Torre B' }, due_time: '14:30:00', description: null },
  ];

  beforeEach(async () => {
    mockApi.get.mockResolvedValue({ data: mockTasks });
    wrapper = mount(MyDailyMaintenanceTasks, mountOptionsWithLayoutStubs());
    await wrapper.vm.$nextTick();
    await wrapper.vm.$nextTick();
  });

  it('renderiza o título e o seletor de data', () => {
    expect(wrapper.text()).toContain(`Minhas Tarefas para ${formattedToday}`);
    expect(wrapper.find('input[type="date"]').exists()).toBe(true);
    expect(wrapper.find('input[type="date"]').element.value).toBe(todayString);
  });

  it('busca e exibe as tarefas do dia ao montar', () => {
    expect(mockApi.get).toHaveBeenCalledWith(`/maintenance/schedules/my-today?date=${todayString}`);
    expect(wrapper.text()).toContain('Verificar Bomba A');
    expect(wrapper.text()).toContain('Bomba Piscina A');
    expect(wrapper.text()).toContain('10:00');
    expect(wrapper.text()).toContain('Limpar Hall Torre B');
  });

  it('exibe mensagem quando não há tarefas', async () => {
    mockApi.get.mockResolvedValue({ data: [] });
    await wrapper.vm.fetchMyTasks();
    await wrapper.vm.$nextTick();

    expect(wrapper.text()).toContain('Nenhuma tarefa encontrada');
    expect(wrapper.text()).not.toContain('Verificar Bomba A');
  });

  it('busca tarefas novamente ao mudar a data', async () => {
    const newDate = '2024-09-15';
    await wrapper.find('input[type="date"]').setValue(newDate);
    await wrapper.vm.$nextTick();
    await wrapper.vm.$nextTick();

    expect(mockApi.get).toHaveBeenCalledWith(`/maintenance/schedules/my-today?date=${newDate}`);
  });

  it('mostra estado de carregamento', async () => {
    mockApi.get.mockImplementation(() => new Promise((resolve) => {
      setTimeout(() => resolve({ data: mockTasks }), 50);
    }));

    const loadingWrapper = mount(MyDailyMaintenanceTasks, mountOptionsWithLayoutStubs());
    expect(loadingWrapper.vm.isLoading).toBe(true);
    expect(loadingWrapper.text()).toContain('Carregando suas tarefas...');

    await new Promise((resolve) => setTimeout(resolve, 60));
    await loadingWrapper.vm.$nextTick();

    expect(loadingWrapper.vm.isLoading).toBe(false);
  });

  it('aciona fluxo de registro ao clicar em "Registrar Execução"', async () => {
    mockRouter.push.mockClear();
    const actionButton = wrapper.findAll('button').find((button) => button.text().includes('Registrar Execução'));
    expect(actionButton).toBeTruthy();
    await actionButton.trigger('click');
    expect(mockRouter.push).toHaveBeenCalled();
  });
});
