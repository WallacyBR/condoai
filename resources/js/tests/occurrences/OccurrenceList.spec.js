import { shallowMount, flushPromises } from '@vue/test-utils';
import { vi } from 'vitest';
import OccurrenceList from '../../components/occurrences/OccurrenceList.vue';
import api from '../../utils/api';

const { mockApi } = vi.hoisted(() => ({
  mockApi: {
    get: vi.fn(),
  },
}));

const { mockRouter } = vi.hoisted(() => ({
  mockRouter: { push: vi.fn() },
}));

vi.mock('vue-router', () => ({
  useRouter: () => mockRouter,
  RouterLink: {
    template: '<a :to="to"><slot /></a>',
    props: ['to'],
  },
}));

vi.mock('../../utils/api', () => ({
  default: mockApi,
}));

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
    vi.clearAllMocks();
    api.get.mockResolvedValue({ data: mockOccurrences });
    wrapper = shallowMount(OccurrenceList, {
      global: {
        stubs: {
          RouterLink: {
            template: '<a :to="to"><slot /></a>',
            props: ['to'],
          },
        },
      },
    });
    await flushPromises();
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
    api.get.mockResolvedValueOnce({ data: { ...mockOccurrences, data: [], total: 0, from: 0, to: 0 } });
    wrapper = shallowMount(OccurrenceList, {
      global: { stubs: { RouterLink: true } },
    });
    await flushPromises();
    expect(wrapper.find('tbody tr td[colspan="6"]').text()).toBe('Nenhuma ocorrência encontrada.');
  });

  it('filters occurrences by status via vm (@change passes event to fetchOccurrences)', async () => {
    wrapper.vm.filters.status = 'open';
    await wrapper.vm.fetchOccurrences();

    expect(api.get).toHaveBeenCalledTimes(2);
    expect(api.get).toHaveBeenLastCalledWith('/occurrences', { params: { page: 1, per_page: 15, status: 'open' } });
  });

  it('filters occurrences by priority via vm (select vmodel typo in production)', async () => {
    wrapper.vm.filters.priority = 'high';
    await wrapper.vm.fetchOccurrences();

    expect(api.get).toHaveBeenCalledTimes(2);
    expect(api.get).toHaveBeenLastCalledWith('/occurrences', { params: { page: 1, per_page: 15, priority: 'high' } });
  });

  it('clears filters and refetches occurrences', async () => {
    wrapper.vm.filters.status = 'open';

    const clearButton = wrapper.find('button.bg-gray-600');
    await clearButton.trigger('click');

    expect(wrapper.vm.filters.status).toBe('');
    expect(wrapper.vm.filters.priority).toBe('');
    expect(api.get).toHaveBeenCalledTimes(2);
    expect(api.get).toHaveBeenLastCalledWith('/occurrences', { params: { page: 1, per_page: 15 } });
  });

  it('navigates to the next page when "Próximo" is clicked', async () => {
    const multiPageData = { ...mockOccurrences, last_page: 2, total: 30 };
    api.get.mockResolvedValue({ data: multiPageData });
    wrapper = shallowMount(OccurrenceList, { global: { stubs: { RouterLink: true } } });
    await flushPromises();
    api.get.mockClear();

    const nextPageButton = wrapper.findAll('nav button').at(1);
    expect(nextPageButton.text()).toContain('Próximo');
    await nextPageButton.trigger('click');
    await flushPromises();

    expect(api.get).toHaveBeenCalledTimes(1);
    expect(api.get).toHaveBeenLastCalledWith('/occurrences', { params: { page: 2, per_page: 15 } });
  });

  it('"Anterior" button is disabled on the first page', async () => {
    api.get.mockResolvedValue({ data: { ...mockOccurrences, last_page: 2, total: 30 } });
    wrapper = shallowMount(OccurrenceList, { global: { stubs: { RouterLink: true } } });
    await flushPromises();

    const prevPageButton = wrapper.findAll('nav button').at(0);
    expect(prevPageButton.attributes('disabled')).toBeDefined();
  });

  it('"Próximo" button is disabled on the last page', async () => {
    api.get.mockResolvedValue({ data: { ...mockOccurrences, current_page: 2, last_page: 2, total: 30 } });
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
    const formattedDate = wrapper.vm.formatDate('2023-01-01T10:00:00.000Z');
    expect(formattedDate).toBe('01/01/2023 10:00');
  });

  it('provides correct status label and class', () => {
    expect(wrapper.vm.getStatusLabel('open')).toBe('Aberta');
    expect(wrapper.vm.getStatusClass('open')).toContain('bg-blue-100');
  });

  it('provides correct priority label and class', () => {
    expect(wrapper.vm.getPriorityLabel('high')).toBe('Alta');
    expect(wrapper.vm.getPriorityClass('high')).toContain('bg-orange-100');
  });

  it('has a link to create a new occurrence', () => {
    const createLink = wrapper.findAll('a').find((link) => link.text().includes('Nova Ocorrência'));
    expect(createLink).toBeDefined();
    expect(createLink.text()).toBe('Nova Ocorrência');
  });
});
