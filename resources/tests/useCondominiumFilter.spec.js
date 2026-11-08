import { describe, it, expect, beforeEach, vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { useCondominiumFilter } from '../js/composables/useCondominiumFilter';
import { CONDOMINIUM_SELECTED_EVENT } from '../js/utils/tenant';

describe('useCondominiumFilter listeners', () => {
  beforeEach(() => {
    localStorage.clear();
    setActivePinia(createPinia());
  });

  it('remove os mesmos listeners registrados em cleanup', () => {
    const addSpy = vi.spyOn(window, 'addEventListener');
    const removeSpy = vi.spyOn(window, 'removeEventListener');

    const { setupCondominiumListeners, cleanupCondominiumListeners } = useCondominiumFilter();
    const callback = vi.fn();

    setupCondominiumListeners(callback);

    const condoListener = addSpy.mock.calls.find(([event]) => event === CONDOMINIUM_SELECTED_EVENT)?.[1];
    const storageListener = addSpy.mock.calls.find(([event]) => event === 'storage')?.[1];

    expect(condoListener).toBeTypeOf('function');
    expect(storageListener).toBeTypeOf('function');

    cleanupCondominiumListeners();

    expect(removeSpy).toHaveBeenCalledWith(CONDOMINIUM_SELECTED_EVENT, condoListener);
    expect(removeSpy).toHaveBeenCalledWith('storage', storageListener);
  });
});
