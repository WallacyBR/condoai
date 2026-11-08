<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 to-blue-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-xl shadow-2xl overflow-hidden p-8">
      <div class="text-center">
        <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-500">
          CondoAI
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Faça login em sua conta
        </p>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
        <div class="space-y-4">
          <div class="relative">
            <label for="email" class="text-sm font-medium text-gray-700 mb-1 block">Email</label>
            <input
              id="email"
              name="email"
              type="email"
              autocomplete="email"
              required
              v-model="form.email"
              class="appearance-none rounded-lg relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 ease-in-out"
              placeholder="Seu endereço de email"
            />
          </div>
          <div class="relative">
            <label for="password" class="text-sm font-medium text-gray-700 mb-1 block">Senha</label>
            <input
              id="password"
              name="password"
              type="password"
              autocomplete="current-password"
              required
              v-model="form.password"
              class="appearance-none rounded-lg relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 ease-in-out"
              placeholder="Sua senha"
            />
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember-me"
              name="remember-me"
              type="checkbox"
              v-model="form.remember"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            />
            <label for="remember-me" class="ml-2 block text-sm text-gray-700">
              Lembrar-me
            </label>
          </div>

          <div class="text-sm">
            <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors duration-200">
              Esqueceu sua senha?
            </a>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-indigo-600 to-blue-500 hover:from-indigo-700 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 ease-in-out transform hover:-translate-y-0.5 shadow-md"
          >
            <span v-if="loading" class="mr-2">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ loading ? 'Entrando...' : 'Entrar' }}
          </button>
        </div>

        <div v-if="error" class="mt-3 p-3 bg-red-50 text-red-600 text-sm text-center rounded-lg border border-red-200">
          {{ error }}
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';

export default {
  name: 'LoginView',
  setup() {
    const form = ref({
      email: import.meta.env.VITE_DEFAULT_LOGIN_EMAIL || '',
      password: import.meta.env.VITE_DEFAULT_LOGIN_PASSWORD || '',
      remember: false
    });
    
    const loading = ref(false);
    const error = ref('');

    const handleLogin = async () => {
      console.log('Tentando fazer login com:', form.value);
      loading.value = true;
      error.value = '';
      
      try {
        const response = await fetch('/api/auth/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(form.value)
        });

        const data = await response.json();

        if (response.ok) {
          console.log('Login bem-sucedido:', data);
          localStorage.setItem('token', data.authorization.token);
          window.location.href = '/dashboard';
        } else {
          console.error('Erro ao fazer login:', data);
          error.value = data.message || 'Erro ao fazer login';
        }
      } catch (err) {
        console.error('Erro de conexão:', err);
        error.value = 'Erro de conexão';
      } finally {
        loading.value = false;
      }
    };

    return {
      form,
      loading,
      error,
      handleLogin
    };
  }
};
</script>

<style>
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.bg-gradient-to-br {
  background-image: linear-gradient(to bottom right, var(--tw-gradient-stops));
}

.bg-gradient-to-r {
  background-image: linear-gradient(to right, var(--tw-gradient-stops));
}

.from-indigo-50 {
  --tw-gradient-from: #eef2ff;
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(238, 242, 255, 0));
}

.to-blue-100 {
  --tw-gradient-to: #dbeafe;
}

.from-indigo-600 {
  --tw-gradient-from: #4f46e5;
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(79, 70, 229, 0));
}

.to-blue-500 {
  --tw-gradient-to: #3b82f6;
}

.from-indigo-700 {
  --tw-gradient-from: #4338ca;
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(67, 56, 202, 0));
}

.to-blue-600 {
  --tw-gradient-to: #2563eb;
}

.hover\:from-indigo-700:hover {
  --tw-gradient-from: #4338ca;
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(67, 56, 202, 0));
}

.hover\:to-blue-600:hover {
  --tw-gradient-to: #2563eb;
}

.hover\:-translate-y-0\.5:hover {
  --tw-translate-y: -0.125rem;
  transform: var(--tw-transform);
}

.transform {
  transform: var(--tw-transform);
}

.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

.duration-200 {
  transition-duration: 200ms;
}

.ease-in-out {
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

.bg-clip-text {
  -webkit-background-clip: text;
  background-clip: text;
}

.text-transparent {
  color: transparent;
}
</style> 