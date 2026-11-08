import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import {
  CONDOMINIUM_SELECTED_EVENT,
  getSelectedCondominiumId,
  getSelectedCondominiumName,
  resetMembershipCache,
  setCachedMembershipIds,
  STORAGE_KEY_ID,
  STORAGE_KEY_NAME,
} from '../utils/tenant';

export const useTenantStore = defineStore('tenant', () => {
  const selectedId = ref(getSelectedCondominiumId());
  const selectedName = ref(getSelectedCondominiumName());
  const memberships = ref([]);
  const membershipsLoaded = ref(false);

  const hasSelection = computed(() => {
    if (!selectedId.value) {
      return false;
    }

    if (!membershipsLoaded.value) {
      return true;
    }

    return membershipIds().includes(String(selectedId.value));
  });

  function syncFromStorage() {
    selectedId.value = getSelectedCondominiumId();
    selectedName.value = getSelectedCondominiumName();

    if (!membershipsLoaded.value) {
      return;
    }

    if (selectedId.value && !membershipIds().includes(String(selectedId.value))) {
      clear();
    }
  }

  function setMemberships(list) {
    memberships.value = Array.isArray(list) ? list : [];
    setCachedMembershipIds(memberships.value.map((membership) => membership.id));
    membershipsLoaded.value = true;
  }

  function resetMembershipCatalog() {
    memberships.value = [];
    membershipsLoaded.value = false;
    resetMembershipCache();
  }

  function membershipIds() {
    return memberships.value.map((membership) => String(membership.id));
  }

  function select(condominium) {
    if (!condominium?.id) {
      return { ok: false, reason: 'invalid' };
    }

    const id = String(condominium.id);

    if (membershipsLoaded.value && !membershipIds().includes(id)) {
      clear();
      return { ok: false, reason: 'not_member', needsSelection: true };
    }

    const name = condominium.name || '';

    localStorage.setItem(STORAGE_KEY_ID, id);
    localStorage.setItem(STORAGE_KEY_NAME, name);
    selectedId.value = id;
    selectedName.value = name;
    window.dispatchEvent(new CustomEvent(CONDOMINIUM_SELECTED_EVENT));

    return { ok: true };
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
      resetMembershipCatalog();
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
    membershipsLoaded,
    hasSelection,
    select,
    clear,
    setMemberships,
    resetMembershipCatalog,
    hydrate,
    syncFromStorage,
  };
});
