import { ref, computed } from 'vue';

const storedUser = localStorage.getItem('user');
const user = ref(storedUser ? JSON.parse(storedUser) : null);

export function useUser() {
  const setUser = (data) => {
    user.value = data;
    if (data) {
      localStorage.setItem('user', JSON.stringify(data));
    } else {
      localStorage.removeItem('user');
    }
  };

  const clearUser = () => {
    setUser(null);
  };

  return {
    user: computed(() => user.value),
    setUser,
    clearUser,
  };
}
