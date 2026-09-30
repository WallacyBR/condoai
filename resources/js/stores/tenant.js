import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import {
  CONDOMINIUM_SELECTED_EVENT,
  getSelectedCondominiumId,
  getSelectedCondominiumName,
  STORAGE_KEY_ID,
  STORAGE_KEY_NAME,
} from '../utils/tenant';

export const useTenantStore = defineStore('tenant', () => {
  const selectedId = ref(getSelectedCondominiumId());
  const selectedName = ref(getSelectedCondominiumName());
  const memberships = ref([]);

  const hasSelection = computed(() => !!selectedId.value);

  function syncFromStorage() {
    selectedId.value = getSelectedCondominiumId();
    selectedName.value = getSelectedCondominiumName();
  }

  function setMemberships(list) {
    memberships.value = Array.isArray(list) ? list : [];
  }

  function membershipIds() {
    return memberships.value.map((membership) => String(membership.id));
  }

  function select(condominium) {
    if (!condominium?.id) {
      return;
    }

    const id = String(condominium.id);
    const name = condominium.name || '';

    localStorage.setItem(STORAGE_KEY_ID, id);
    localStorage.setItem(STORAGE_KEY_NAME, name);
    selectedId.value = id;
    selectedName.value = name;
    window.dispatchEvent(new CustomEvent(CONDOMINIUM_SELECTED_EVENT));
  }

  function clear() {
    localStorage.removeItem(STORAGE_KEY_ID);
    localStorage.removeItem(STORAGE_KEY_NAME);
    selectedId.value = null;
    selectedName.value = null;
    window.dispatchEvent(new CustomEvent(CONDOMINIUM_SELECTED_EVENT));
  }

  function hydrate(user) {
    if (!user) {
      clear();
      setMemberships([]);
      return { needsSelection: true };
    }

    setMemberships(user.memberships || []);

    const ids = membershipIds();
    const storedId = getSelectedCondominiumId();

    if (storedId && ids.includes(String(storedId))) {
      syncFromStorage();
      return { needsSelection: false };
    }

    const homeId = user.condominium_id != null ? String(user.condominium_id) : null;
    if (homeId && ids.includes(homeId)) {
      const homeMembership = memberships.value.find((membership) => String(membership.id) === homeId);
      if (homeMembership) {
        select(homeMembership);
        return { needsSelection: false };
      }
    }

    if (memberships.value.length === 1) {
      select(memberships.value[0]);
      return { needsSelection: false };
    }

    clear();
    return { needsSelection: true };
  }

  return {
    selectedId,
    selectedName,
    memberships,
    hasSelection,
    select,
    clear,
    setMemberships,
    hydrate,
    syncFromStorage,
  };
});
