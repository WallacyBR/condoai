<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-2xl overflow-hidden p-8 border-2 border-cyan-500/20">
      <div class="text-center">
        <h2 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-pink-400">
          CondoAI
        </h2>
        <p class="mt-2 text-center text-sm text-cyan-300">
          Faça login em sua conta
        </p>
        <p class="mt-1 text-center text-xs text-gray-400">
          Para teste: teste@teste.com / teste
        </p>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="login">
        <div class="space-y-4">
          <div class="relative">
            <label for="email" class="text-sm font-medium text-cyan-300 mb-1 block">Email</label>
            <input
              id="email"
              name="email"
              type="email"
              autocomplete="email"
              required
              v-model="credentials.email"
              class="appearance-none rounded-2xl relative block w-full px-4 py-3 bg-gray-800 border-2 border-cyan-500/20 placeholder-gray-400 text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500/50 transition-all duration-300 ease-in-out hover:border-cyan-500/40 hover:shadow-[0_0_15px_rgba(6,182,212,0.3)]"
              placeholder="Seu endereço de email"
            />
          </div>
          <div class="relative">
            <label for="password" class="text-sm font-medium text-cyan-300 mb-1 block">Senha</label>
            <input
              id="password"
              name="password"
              type="password"
              autocomplete="current-password"
              required
              v-model="credentials.password"
              class="appearance-none rounded-2xl relative block w-full px-4 py-3 bg-gray-800 border-2 border-cyan-500/20 placeholder-gray-400 text-gray-100 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500/50 transition-all duration-300 ease-in-out hover:border-cyan-500/40 hover:shadow-[0_0_15px_rgba(6,182,212,0.3)]"
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
              class="h-4 w-4 text-cyan-500 focus:ring-cyan-500 border-gray-600 rounded bg-gray-800"
            />
            <label for="remember-me" class="ml-2 block text-sm text-cyan-300">
              Lembrar-me
            </label>
          </div>

          <div class="text-sm">
            <a href="#" class="font-medium text-cyan-400 hover:text-pink-400 transition-colors duration-200">
              Esqueceu sua senha?
            </a>
          </div>
        </div>

        <div>
          <NeonButton
            type="submit"
            :loading="loading"
            :disabled="loading"
            size="md"
            extraClass="w-full"
          >
            Entrar
          </NeonButton>
        </div>

        <div v-if="error" class="mt-3 p-3 bg-red-900/50 text-red-300 text-sm text-center rounded-2xl border-2 border-red-500/20">
          {{ error }}
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import NeonButton from './NeonButton.vue';
import auth from '../services/auth';

export default {
  name: 'LoginView',
  components: { NeonButton },
  setup() {
    const router = useRouter();
    const credentials = ref({
      email: '',
      password: ''
    });
    const form = ref({
      remember: false
    });
    const error = ref('');
    const loading = ref(false);

    const login = async () => {
      loading.value = true;
      error.value = '';
      
      try {
        await auth.login(credentials.value);
        
        // Verifica se há uma rota salva para redirecionamento
        const savedRoute = localStorage.getItem('redirectAfterLogin');
        if (savedRoute) {
          const route = JSON.parse(savedRoute);
          localStorage.removeItem('redirectAfterLogin');
          await router.push(route);
        } else {
          await router.push({ name: 'dashboard' });
        }
      } catch (e) {
        error.value = e.message || 'Erro ao fazer login';
      } finally {
        loading.value = false;
      }
    };

    return {
      credentials,
      form,
      error,
      loading,
      login
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

@keyframes shimmer {
  0% {
    left: -60%;
  }
  100% {
    left: 120%;
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

.group:hover .btn-shimmer {
  opacity: 1;
  animation: shimmer 1.2s linear;
}

.btn-shimmer {
  content: '';
  position: absolute;
  top: 0;
  left: -60%;
  width: 60%;
  height: 100%;
  background: linear-gradient(120deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.4) 50%, rgba(255,255,255,0) 100%);
  opacity: 0;
  pointer-events: none;
  border-radius: 1rem;
  transition: opacity 0.2s;
  z-index: 2;
}

@keyframes glow {
  0% {
    box-shadow: 0 0 5px rgba(6,182,212,0.2);
  }
  50% {
    box-shadow: 0 0 20px rgba(6,182,212,0.4);
  }
  100% {
    box-shadow: 0 0 5px rgba(6,182,212,0.2);
  }
}

.animate-glow {
  animation: glow 2s ease-in-out infinite;
}

.bg-gradient-to-br {
  background-image: linear-gradient(to bottom right, var(--tw-gradient-stops));
}

.bg-gradient-to-r {
  background-image: linear-gradient(to right, var(--tw-gradient-stops));
}

.from-gray-900 {
  --tw-gradient-from: #111827;
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(17, 24, 39, 0));
}

.via-gray-800 {
  --tw-gradient-via: #1f2937;
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-via), var(--tw-gradient-to, rgba(31, 41, 55, 0));
}

.to-gray-900 {
  --tw-gradient-to: #111827;
}

.from-cyan-400 {
  --tw-gradient-from: #22d3ee;
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(34, 211, 238, 0));
}

.to-pink-400 {
  --tw-gradient-to: #f472b6;
}

.from-cyan-500 {
  --tw-gradient-from: #06b6d4;
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(6, 182, 212, 0));
}

.to-pink-500 {
  --tw-gradient-to: #ec4899;
}

.hover\:from-pink-500:hover {
  --tw-gradient-from: #ec4899;
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(236, 72, 153, 0));
}

.hover\:to-cyan-500:hover {
  --tw-gradient-to: #06b6d4;
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
  transition-duration: 300ms;
}

.duration-300 {
  transition-duration: 300ms;
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