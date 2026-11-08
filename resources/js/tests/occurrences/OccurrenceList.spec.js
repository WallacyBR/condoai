import { shallowMount, flushPromises } from '@vue/test-utils';
import OccurrenceList from '../../components/occurrences/OccurrenceList.vue';
import api from '../../utils/api';
import { vi } from 'vitest';

// Mock para vue-router
const mockRouter = {
  push: vi.fn(),
};

vi.mock('vue-router', () => ({
  useRouter: () => mockRouter,
  RouterLink: { template: '<a><slot /></a>' } // Stub RouterLink
}));

// Mock para o composable useToast
vi.mock('../../composables/useToast', () => ({
  useToast: () => ({
    success: vi.fn(),
    error: vi.fn(),
  }),
}));

// Mock para o módulo API
vi.mock('../../utils/api');

const mockOccurrences = {
  data: [
    { id: 1, title: 'Lâmpada Queimada', status: 'open', priority: 'high', user: { name: 'João Silva' }, created_at: '2023-01-01T10:00:00.000Z' },
    { id: 2, title: 'Vazamento Torneira', status: 'in_progress', priority: 'medium', user: { name: 'Maria Oliveira' }, created_at: '2023-01-02T11:00:00.000Z' },
  ],
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 2,
  from: 1,
  to: 2,
};

describe('OccurrenceList.vue', () => {
  let wrapper;

  beforeEach(async () => {
    api.get.mockResolvedValue({ data: mockOccurrences });
    wrapper = shallowMount(OccurrenceList, {
      global: {
        stubs: {
          RouterLink: { template: '<a><slot /></a>' }
        }
      }
    });
    await flushPromises(); // Espera que todas as promises pendentes (como a chamada API no onMounted) sejam resolvidas
  });

  afterEach(() => {
    vi.clearAllMocks();
  });

  it('renders the component title', () => {
    expect(wrapper.find('h1').text()).toBe('Lista de Ocorrências');
  });

  it('fetches and displays occurrences on mount', () => {
    expect(api.get).toHaveBeenCalledTimes(1);
    expect(api.get).toHaveBeenCalledWith('/occurrences', { params: { page: 1, per_page: 15 } });
    expect(wrapper.findAll('tbody tr').length).toBe(mockOccurrences.data.length);
    expect(wrapper.find('tbody tr:first-child td:first-child').text()).toBe('Lâmpada Queimada');
  });

  it('displays a message when no occurrences are found', async () => {
    api.get.mockResolvedValueOnce({ data: { ...mockOccurrences, data: [], total:0, from:0, to:0 } });
    wrapper = shallowMount(OccurrenceList, {
        global: { stubs: { RouterLink: true } }
    });
    await flushPromises();
    expect(wrapper.find('tbody tr td[colspan="6"]').text()).toBe('Nenhuma ocorrência encontrada.');
  });

  it('filters occurrences by status', async () => {
    const statusSelect = wrapper.find('select#statusFilter');
    await statusSelect.setValue('open');
    // O evento @change no select chama fetchOccurrences

    expect(api.get).toHaveBeenCalledTimes(2); // 1 onMount + 1 onFilterChange
    expect(api.get).toHaveBeenLastCalledWith('/occurrences', { params: { page: 1, per_page: 15, status: 'open' } });
  });

  it('filters occurrences by priority', async () => {
    const prioritySelect = wrapper.find('select#priorityFilter');
    await prioritySelect.setValue('high');

    expect(api.get).toHaveBeenCalledTimes(2);
    expect(api.get).toHaveBeenLastCalledWith('/occurrences', { params: { page: 1, per_page: 15, priority: 'high' } });
  });

  it('clears filters and refetches occurrences', async () => {
    // Simula que um filtro foi aplicado
    wrapper.vm.filters.status = 'open';

    const clearButton = wrapper.find('button.bg-gray-600'); // Botão "Limpar Filtros"
    await clearButton.trigger('click');

    expect(wrapper.vm.filters.status).toBe('');
    expect(wrapper.vm.filters.priority).toBe('');
    expect(api.get).toHaveBeenCalledTimes(2); // 1 onMount + 1 onClear
    expect(api.get).toHaveBeenLastCalledWith('/occurrences', { params: { page: 1, per_page: 15 } });
  });


  it('navigates to the next page when "Próximo" is clicked', async () => {
    // Configura para ter múltiplas páginas
    const multiPageData = { ...mockOccurrences, last_page: 2, total: 30 };
    api.get.mockResolvedValue({ data: multiPageData });
    wrapper = shallowMount(OccurrenceList, { global: { stubs: { RouterLink: true } } });
    await flushPromises();

    const nextPageButton = wrapper.findAll('nav button').at(1); // Botão "Próximo"
    expect(nextPageButton.text()).toContain('Próximo');
    await nextPageButton.trigger('click');

    expect(api.get).toHaveBeenCalledTimes(2); // 1 onMount + 1 onPageChange
    expect(api.get).toHaveBeenLastCalledWith('/occurrences', { params: { page: 2, per_page: 15 } });
  });

  it('"Anterior" button is disabled on the first page', () => {
    const prevPageButton = wrapper.findAll('nav button').at(0);
    expect(prevPageButton.attributes('disabled')).toBeDefined();
  });

  it('"Próximo" button is disabled on the last page', async () => {
     // Garante que estamos na última página
    api.get.mockResolvedValue({ data: { ...mockOccurrences, current_page: 1, last_page: 1 } });
    wrapper = shallowMount(OccurrenceList, { global: { stubs: { RouterLink: true } } });
    await flushPromises();
    const nextPageButton = wrapper.findAll('nav button').at(1);
    expect(nextPageButton.attributes('disabled')).toBeDefined();
  });

  it('displays an error message if API call fails', async () => {
    const errorMessage = 'Network Error';
    api.get.mockRejectedValueOnce({ response: { data: { message: errorMessage } } });
    wrapper = shallowMount(OccurrenceList, { global: { stubs: { RouterLink: true } } });
    await flushPromises();

    expect(wrapper.find('.text-red-500 p').text()).toContain(errorMessage);
  });

  it('formats date correctly', () => {
      // Acessa o método diretamente, pois o template já está testado pela exibição
      const formattedDate = wrapper.vm.formatDate('2023-01-01T10:00:00.000Z');
      expect(formattedDate).toBe('01/01/2023 10:00');
  });

  it('provides correct status label and class', () => {
    expect(wrapper.vm.getStatusLabel('open')).toBe('Aberta');
    expect(wrapper.vm.getStatusClass('open')).toContain('bg-blue-100');
  });

  it('provides correct priority label and class', () => {
    expect(wrapper.vm.getPriorityLabel('high')).toBe('Alta');
    expect(wrapper.vm.getPriorityClass('high')).toContain('bg-orange-100'); // Ajuste se a classe for diferente
  });

  it('has a link to create a new occurrence', () => {
    const newOccurrenceLink = wrapper.findComponent({ template: '<a><slot /></a>' }); // Encontra o stub do RouterLink
    // Verifica se existe algum link que aponte para a rota de criação.
    // Esta verificação pode ser melhorada se o RouterLink tivesse um `to` prop explícito no stub ou se testássemos a navegação.
    const createLink = wrapper.findAllComponents({ template: '<a><slot /></a>' })
                             .find(link => link.attributes('to') && JSON.parse(link.attributes('to').replace(/'/g, '"')).name === 'OccurrenceCreate');
    expect(createLink).toBeDefined();
    expect(createLink.text()).toBe('Nova Ocorrência');
  });
});
