import { describe, it, expect, beforeEach, vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { useTenantStore } from '../../js/stores/tenant';
import { STORAGE_KEY_ID, STORAGE_KEY_NAME } from '../../js/utils/tenant';

describe('tenant store', () => {
  beforeEach(() => {
    localStorage.clear();
    setActivePinia(createPinia());
  });

  it('mantém seleção válida presente nas memberships', () => {
    localStorage.setItem(STORAGE_KEY_ID, '2');
    localStorage.setItem(STORAGE_KEY_NAME, 'Condomínio B');

    const store = useTenantStore();
    const result = store.hydrate({
      condominium_id: 1,
      memberships: [
        { id: 1, name: 'Condomínio A' },
        { id: 2, name: 'Condomínio B' },
      ],
    });

    expect(result.needsSelection).toBe(false);
    expect(store.selectedId).toBe('2');
    expect(store.selectedName).toBe('Condomínio B');
    expect(store.hasSelection).toBe(true);
  });

  it('usa condomínio home quando seleção é inválida', () => {
    localStorage.setItem(STORAGE_KEY_ID, '999');

    const store = useTenantStore();
    const result = store.hydrate({
      condominium_id: 1,
      memberships: [
        { id: 1, name: 'Condomínio A' },
        { id: 2, name: 'Condomínio B' },
      ],
    });

    expect(result.needsSelection).toBe(false);
    expect(store.selectedId).toBe('1');
    expect(store.selectedName).toBe('Condomínio A');
  });

  it('auto-seleciona quando há exatamente uma membership', () => {
    const store = useTenantStore();
    const result = store.hydrate({
      condominium_id: null,
      memberships: [{ id: 7, name: 'Único Condomínio' }],
    });

    expect(result.needsSelection).toBe(false);
    expect(store.selectedId).toBe('7');
    expect(store.selectedName).toBe('Único Condomínio');
  });

  it('limpa seleção quando há múltiplas memberships sem escolha válida', () => {
    localStorage.setItem(STORAGE_KEY_ID, '999');
    localStorage.setItem(STORAGE_KEY_NAME, 'Inválido');

    const store = useTenantStore();
    const result = store.hydrate({
      condominium_id: null,
      memberships: [
        { id: 1, name: 'Condomínio A' },
        { id: 2, name: 'Condomínio B' },
      ],
    });

    expect(result.needsSelection).toBe(true);
    expect(store.selectedId).toBeNull();
    expect(store.hasSelection).toBe(false);
    expect(localStorage.getItem(STORAGE_KEY_ID)).toBeNull();
  });

  it('select persiste apenas quando id pertence às memberships', () => {
    const store = useTenantStore();
    store.setMemberships([{ id: 3, name: 'Condomínio C' }]);

    const listener = vi.fn();
    window.addEventListener('condominiumSelected', listener);

    const result = store.select({ id: 3, name: 'Condomínio C' });

    expect(result.ok).toBe(true);
    expect(localStorage.getItem(STORAGE_KEY_ID)).toBe('3');
    expect(localStorage.getItem(STORAGE_KEY_NAME)).toBe('Condomínio C');
    expect(store.selectedId).toBe('3');
    expect(listener).toHaveBeenCalled();
  });

  it('select rejeita id fora das memberships e limpa seleção', () => {
    localStorage.setItem(STORAGE_KEY_ID, '1');
    localStorage.setItem(STORAGE_KEY_NAME, 'Condomínio A');

    const store = useTenantStore();
    store.setMemberships([{ id: 2, name: 'Condomínio B' }]);

    const result = store.select({ id: 99, name: 'Inválido' });

    expect(result.ok).toBe(false);
    expect(result.reason).toBe('not_member');
    expect(store.selectedId).toBeNull();
    expect(localStorage.getItem(STORAGE_KEY_ID)).toBeNull();
  });

  it('clear remove seleção do storage', () => {
    localStorage.setItem(STORAGE_KEY_ID, '1');
    localStorage.setItem(STORAGE_KEY_NAME, 'Condomínio A');

    const store = useTenantStore();
    store.clear();

    expect(store.selectedId).toBeNull();
    expect(localStorage.getItem(STORAGE_KEY_ID)).toBeNull();
  });
});
