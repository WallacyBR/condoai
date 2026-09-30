import { mount } from '@vue/test-utils';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import BudgetView from '../js/finance/BudgetView.vue';
import api from '../js/utils/api';
import { useUser } from '../js/composables/useUser';
import { useToast } from '../js/composables/useToast';
import { mountOptionsWithLayoutStubs } from './helpers/mountWithLayoutStubs.js';

vi.mock('../js/utils/api', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
  },
}));

vi.mock('../js/composables/useUser', () => ({
  useUser: vi.fn(() => ({
    user: { value: { condominium_id: 1 } },
    setUser: vi.fn(),
  })),
}));

vi.mock('../js/composables/useToast', () => ({
  useToast: vi.fn(() => ({
    showToast: vi.fn(),
  })),
}));

vi.mock('../js/services/auth', () => ({
  default: {
    getToken: vi.fn(() => null),
    refreshToken: vi.fn(),
    logout: vi.fn(),
  },
}));

describe('BudgetView.vue', () => {
  let mockUser;
  let mockToast;

  beforeEach(() => {
    // Reset mocks before each test
    vi.clearAllMocks();

    // Setup mock return values for composables
    mockUser = {
      value: {
        condominium_id: 1,
      },
    };
    useUser.mockReturnValue({ user: mockUser });

    mockToast = {
      showToast: vi.fn(),
    };
    useToast.mockReturnValue(mockToast);

    // Mock API calls
    api.get.mockImplementation((url) => {
      if (url.includes('/finance/chart-accounts')) {
        return Promise.resolve({
          data: [
            { id: 1, name: 'Salários', type: 'despesa' },
            { id: 2, name: 'Manutenção', type: 'despesa' },
          ],
        });
      }
      if (url.includes('/finance/budget-items')) {
        return Promise.resolve({
          data: [
            { chart_account_id: 1, month: 1, year: 2025, amount: '1500.00' },
          ],
        });
      }
      return Promise.resolve({ data: [] });
    });

    api.post.mockResolvedValue({ data: { message: 'Success' } });
  });

  it('renders the component and loads initial data', async () => {
    const wrapper = mount(BudgetView, mountOptionsWithLayoutStubs({
      global: {
        stubs: {
          TopCard: true,
          NeonButton: true,
        },
      },
    }));

    // Wait for the component to finish its async operations
    await wrapper.vm.$nextTick();
    await new Promise(resolve => setTimeout(resolve, 0)); // Wait for promises to resolve

    // Check if the initial API calls were made
    expect(api.get).toHaveBeenCalledWith('/finance/chart-accounts', expect.any(Object));
    expect(api.get).toHaveBeenCalledWith('/finance/budget-items', expect.any(Object));

    // Check if the component renders the accounts
    expect(wrapper.text()).toContain('Salários');
    expect(wrapper.text()).toContain('Manutenção');
  });

  it('allows editing a budget item and saving', async () => {
    const wrapper = mount(BudgetView, mountOptionsWithLayoutStubs({
      global: {
        stubs: {
          TopCard: true,
          NeonButton: {
            template: '<button @click="$emit(\'click\')"><slot/></button>',
          },
        },
      },
    }));

    // Wait for async operations
    await wrapper.vm.$nextTick();
    await new Promise(resolve => setTimeout(resolve, 0));

    // Find a specific input field and change its value
    const maintenanceJanInput = wrapper.findAll('input[type="number"]').filter(input => {
      const row = input.element.closest('tr');
      const accountName = row.querySelector('td').textContent;
      // This is a bit brittle, depends on column order. A better way would be data-attributes.
      return accountName === 'Manutenção';
    })[0]; // Assuming first month is Jan

    await maintenanceJanInput.setValue(500.75);

    // Find and click the save button
    await wrapper.find('button').trigger('click');

    // Check if the API was called with the correct payload
    expect(api.post).toHaveBeenCalledWith('/finance/budget-items/bulk', expect.objectContaining({
      condominium_id: 1,
      items: expect.arrayContaining([
        expect.objectContaining({
          chart_account_id: '2', // Manutenção
          month: '1', // Jan
          amount: 500.75,
        }),
        expect.objectContaining({
          chart_account_id: '1', // Salários
          month: '1', // Jan
          amount: 1500, // from initial load
        })
      ]),
    }));

    // Check for success toast
    expect(mockToast.showToast).toHaveBeenCalledWith('Orçamento salvo com sucesso!', 'success');
  });
});
