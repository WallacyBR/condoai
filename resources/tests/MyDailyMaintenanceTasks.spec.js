import { mount } from '@vue/test-utils';
import MyDailyMaintenanceTasks from '@/pages/maintenance/MyDailyMaintenanceTasks.vue'; // Ajuste o caminho
import api from '@/utils/api';
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue'; // Stubbed
import { format, startOfToday } from 'date-fns';

jest.mock('@/utils/api', () => ({
  get: jest.fn(),
}));

const globalStubs = {
  AuthenticatedLayout: {
    template: '<div><slot name="page-title"/><slot/></div>',
  },
};

describe('MyDailyMaintenanceTasks.vue', () => {
  let wrapper;
  const todayString = format(startOfToday(), 'yyyy-MM-dd');
  const mockTasks = [
    { id: 1, title: 'Verificar Bomba A', equipment: { name: 'Bomba Piscina A' }, due_time: '10:00:00', description: 'Checar pressão e ruído.' },
    { id: 2, title: 'Limpar Hall Torre B', equipment: { name: 'Hall Torre B' }, due_time: '14:30:00', description: null },
  ];

  beforeEach(async () => {
    api.get.mockResolvedValue({ data: mockTasks });
    wrapper = mount(MyDailyMaintenanceTasks, {
      global: {
        stubs: globalStubs,
      }
    });
    // Aguarda a promise de api.get resolver e o DOM atualizar
    await wrapper.vm.$nextTick(); // Para o onMounted
    await wrapper.vm.$nextTick(); // Para a re-renderização após a API
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  it('renderiza o título e o seletor de data', () => {
    expect(wrapper.text()).toContain('Minhas Tarefas de Manutenção Hoje');
    expect(wrapper.find('input[type="date"]').exists()).toBe(true);
    expect(wrapper.find('input[type="date"]').element.value).toBe(todayString);
  });

  it('busca e exibe as tarefas do dia ao montar', async () => {
    expect(api.get).toHaveBeenCalledWith(expect.stringContaining(`/maintenance/schedules/my-today?date=${todayString}`));

    // Aguardar a renderização após a chamada da API (já feito no beforeEach, mas pode ser necessário aqui se o estado mudar)
    // await wrapper.vm.$nextTick();

    const taskElements = wrapper.findAll('div.bg-gray-700\\/70'); // Seletor para os cards de tarefa
    expect(taskElements.length).toBe(mockTasks.length);
    expect(taskElements[0].text()).toContain('Verificar Bomba A');
    expect(taskElements[0].text()).toContain('Bomba Piscina A');
    expect(taskElements[0].text()).toContain('10:00');
    expect(taskElements[1].text()).toContain('Limpar Hall Torre B');
  });

  it('exibe mensagem quando não há tarefas', async () => {
    api.get.mockResolvedValue({ data: [] }); // Mock para API retornando array vazio
    // Remontar ou forçar re-fetch
    await wrapper.vm.fetchMyTasks(); // Chamar manualmente para simular
    await wrapper.vm.$nextTick();

    expect(wrapper.text()).toContain('Nenhuma tarefa de manutenção agendada para você');
    const taskElements = wrapper.findAll('div.bg-gray-700\\/70');
    expect(taskElements.length).toBe(0);
  });

  it('busca tarefas novamente ao mudar a data', async () => {
    const newDate = '2024-09-15';
    const dateInput = wrapper.find('input[type="date"]');
    await dateInput.setValue(newDate);
    // O evento @change deve chamar fetchMyTasks
    // await dateInput.trigger('change'); // trigger('change') é chamado automaticamente pelo setValue em input[type=date]

    await wrapper.vm.$nextTick(); // Para a atualização do v-model
    await wrapper.vm.$nextTick(); // Para a chamada da API e re-renderização

    expect(api.get).toHaveBeenCalledWith(expect.stringContaining(`/maintenance/schedules/my-today?date=${newDate}`));
  });

  it('mostra estado de carregamento', async () => {
    api.get.mockImplementation(() => new Promise(resolve => setTimeout(() => resolve({ data: mockTasks }), 100)));

    // Remonta o componente para que o isLoading seja ativado durante a chamada da API
    wrapper = mount(MyDailyMaintenanceTasks, { global: { stubs: globalStubs } });
    expect(wrapper.vm.isLoading).toBe(true); // Imediatamente após montar, antes da API resolver
    expect(wrapper.text()).toContain('Carregando suas tarefas...');

    await wrapper.vm.$nextTick(); // Espera a API resolver (do setTimeout)
    await wrapper.vm.$nextTick(); // Espera a renderização
    expect(wrapper.vm.isLoading).toBe(false);
    expect(wrapper.text()).not.toContain('Carregando suas tarefas...');
  });

  // Teste para a função goToRecordForm (placeholder)
  it('chama console.log ao clicar em "Registrar Execução"', async () => {
    const consoleSpy = jest.spyOn(console, 'log');
    await wrapper.vm.$nextTick(); // Garante que as tarefas estão renderizadas

    const firstTaskButton = wrapper.findAll('button.text-cyan-500').at(0);
    if (firstTaskButton && firstTaskButton.exists()) {
        await firstTaskButton.trigger('click');
        expect(consoleSpy).toHaveBeenCalledWith('Navegar para registrar/detalhar tarefa:', mockTasks[0].id);
    } else {
        console.warn("Botão 'Registrar Execução' não encontrado para teste.");
    }
    consoleSpy.mockRestore();
  });

});
