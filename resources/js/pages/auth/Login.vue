<template>
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <!-- Background Pattern -->
    <div class="absolute inset-0 -z-10">
      <div class="absolute inset-0 bg-gradient-to-br from-background via-muted/30 to-accent/20"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_40%,rgba(120,119,198,0.3),transparent_50%)]"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_70%,rgba(255,192,203,0.2),transparent_50%)]"></div>
    </div>

    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div class="text-center">
        <div class="flex justify-center mb-6">
          <div class="w-16 h-16 bg-gradient-to-br from-primary to-accent rounded-2xl flex items-center justify-center shadow-lg">
            <span class="text-primary-foreground font-bold text-2xl">C</span>
          </div>
        </div>
        <h2 class="text-3xl font-bold text-foreground">
          Faça login na sua conta
        </h2>
        <p class="mt-2 text-sm text-muted-foreground">
          Acesse o painel de controle do seu condomínio
        </p>
      </div>

      <!-- Login Form -->
      <div class="bg-card/80 backdrop-blur-sm rounded-2xl p-8 shadow-xl border border-border">
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Email -->
          <FormField
            v-model="form.email"
            type="email"
            name="email"
            label="E-mail"
            placeholder="seu@email.com"
            :required="true"
            :error="errors.email"
            :icon="EmailIcon"
          />

          <!-- Password -->
          <FormField
            v-model="form.password"
            type="password"
            name="password"
            label="Senha"
            placeholder="Sua senha"
            :required="true"
            :error="errors.password"
            :icon="LockIcon"
            :show-password-toggle="true"
          />

          <!-- Remember Me -->
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input 
                id="remember" 
                v-model="form.remember"
                name="remember" 
                type="checkbox" 
                class="h-4 w-4 text-primary focus:ring-ring border-border rounded transition-all"
              />
              <label for="remember" class="ml-2 block text-sm text-foreground">
                Lembrar-me
              </label>
            </div>

            <div class="text-sm">
              <a href="#" class="font-medium text-primary hover:text-primary/80 transition-colors">
                Esqueceu a senha?
              </a>
            </div>
          </div>

          <!-- Submit Button -->
          <ActionButton
            type="submit"
            variant="primary"
            size="lg"
            :loading="loading"
            :full-width="true"
            text="Entrar"
            :icon="LoginIcon"
          />

          <!-- Register Link -->
          <div class="text-center">
            <p class="text-sm text-muted-foreground">
              Não tem uma conta?
              <router-link 
                :to="{ name: 'register' }" 
                class="font-medium text-primary hover:text-primary/80 transition-colors"
              >
                Registre-se aqui
              </router-link>
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const loading = ref(false)

// Form data
const form = reactive({
  email: '',
  password: '',
  remember: false
})

// Errors
const errors = ref({
  email: '',
  password: ''
})

// Icons
const EmailIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
    </svg>
  `
}

const LockIcon = {
  template: `
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
    </svg>
  `
}

const LoginIcon = {
  template: `
    <svg fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
    </svg>
  `
}

// Methods
const clearErrors = () => {
  errors.value = {
    email: '',
    password: ''
  }
}

const handleLogin = async () => {
  clearErrors()
  loading.value = true

  try {
    const response = await axios.post('/api/auth/login', {
      email: form.email,
      password: form.password,
      remember: form.remember
    })

    // Sucesso - redirecionar para dashboard
    if (window.showFlash) {
      window.showFlash('success', 'Login realizado com sucesso!')
    }
    
    // Atualizar estado de autenticação
    const metaTag = document.querySelector('meta[name="user-authenticated"]')
    if (metaTag) {
      metaTag.content = 'true'
    }
    
    router.push({ name: 'dashboard' })
    
  } catch (error) {
    console.error('Erro no login:', error)
    
    if (error.response?.status === 422) {
      // Erros de validação
      const validationErrors = error.response.data.errors || {}
      errors.value = {
        email: validationErrors.email?.[0] || '',
        password: validationErrors.password?.[0] || ''
      }
    } else if (error.response?.status === 401) {
      // Credenciais inválidas
      if (window.showFlash) {
        window.showFlash('error', 'Credenciais inválidas. Verifique seu e-mail e senha.')
      }
    } else {
      // Erro genérico
      if (window.showFlash) {
        window.showFlash('error', 'Erro ao fazer login. Tente novamente.')
      }
    }
  } finally {
    loading.value = false
  }
}
</script>
