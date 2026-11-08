import { flushPromises, mount } from '@vue/test-utils';
import axios from 'axios';
import AssembliesView from '../js/views/AssembliesView.vue';

jest.mock('axios');

describe('AssembliesView.vue', () => {
  const assembliesMock = [
    {
      id: 1,
      title: 'Assembleia Geral Ordinária 2026',
      description: 'Aprovação de contas e eleição.',
      status: 'agendada',
      type: 'ordinaria',
      modality: 'virtual',
      first_call_at: '2026-10-15T19:00:00.000000Z',
      agendas_count: 2,
      creator: { name: 'Síndico Teste' },
    },
    {
      id: 2,
      title: 'Assembleia Extraordinária - Reforma',
      description: 'Obras de infraestrutura.',
      status: 'em_andamento',
      type: 'extraordinaria',
      modality: 'hibrida',
      first_call_at: '2026-10-01T10:00:00.000000Z',
      agendas_count: 1,
      creator: { name: 'Síndico Teste' },
    },
  ];

  beforeEach(() => {
    jest.clearAllMocks();
    axios.get.mockResolvedValue({
      data: { data: assembliesMock },
    });
  });

  function mountComponent() {
    return mount(AssembliesView, {
      global: {
        stubs: {
          'router-link': {
            template: '<a :href="to"><slot /></a>',
            props: ['to'],
          },
        },
      },
    });
  }

  it('renderiza o cabeçalho e carrega assembleias com sucesso', async () => {
    const wrapper = mountComponent();
    await flushPromises();

    expect(wrapper.text()).toContain('Assembleias e Votações Digitais');
    expect(axios.get).toHaveBeenCalledWith('/api/assemblies');
    expect(wrapper.text()).toContain('Assembleia Geral Ordinária 2026');
    expect(wrapper.text()).toContain('Assembleia Extraordinária - Reforma');
  });

  it('filtra assembleias ao clicar no filtro de status', async () => {
    const wrapper = mountComponent();
    await flushPromises();

    // Filtra apenas por 'Em Andamento'
    const buttons = wrapper.findAll('button');
    const emAndamentoBtn = buttons.find((b) => b.text().includes('Em Andamento'));
    expect(emAndamentoBtn).toBeTruthy();

    await emAndamentoBtn.trigger('click');

    expect(wrapper.text()).toContain('Assembleia Extraordinária - Reforma');
    expect(wrapper.text()).not.toContain('Assembleia Geral Ordinária 2026');
  });
});
