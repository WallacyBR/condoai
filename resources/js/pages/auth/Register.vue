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
          Criar nova conta
        </h2>
        <p class="mt-2 text-sm text-muted-foreground">
          Registre-se para acessar o sistema
        </p>
      </div>

      <!-- Register Form -->
      <div class="bg-card/80 backdrop-blur-sm rounded-2xl p-8 shadow-xl border border-border">
        <form @submit.prevent="handleRegister" class="space-y-6">
          <!-- Name -->
          <FormField
            v-model="form.name"
            type="text"
            name="name"
            label="Nome Completo"
            placeholder="Seu nome completo"
            :required="true"
            :error="errors.name"
          />

          <!-- Email -->
          <FormField
            v-model="form.email"
            type="email"
            name="email"
            label="E-mail"
            placeholder="seu@email.com"
            :required="true"
            :error="errors.email"
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
            :show-password-toggle="true"
          />

          <!-- Confirm Password -->
          <FormField
            v-model="form.password_confirmation"
            type="password"
            name="password_confirmation"
            label="Confirmar Senha"
            placeholder="Confirme sua senha"
            :required="true"
            :error="errors.password_confirmation"
            :show-password-toggle="true"
          />

          <!-- Submit Button -->
          <ActionButton
            type="submit"
            variant="primary"
            size="lg"
            :loading="loading"
            :full-width="true"
            text="Criar Conta"
          />

          <!-- Login Link -->
          <div class="text-center">
            <p class="text-sm text-muted-foreground">
              Já tem uma conta?
              <router-link 
                :to="{ name: 'login' }" 
                class="font-medium text-primary hover:text-primary/80 transition-colors"
              >
                Faça login aqui
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
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

// Errors
const errors = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

// Methods
const clearErrors = () => {
  errors.value = {
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
  }
}

const handleRegister = async () => {
  clearErrors()
  loading.value = true

  try {
    const response = await axios.post('/register', form)

    // Sucesso - redirecionar para dashboard
    if (window.showFlash) {
      window.showFlash('success', 'Conta criada com sucesso!')
    }
    
    // Atualizar estado de autenticação
    const metaTag = document.querySelector('meta[name="user-authenticated"]')
    if (metaTag) {
      metaTag.content = 'true'
    }
    
    router.push({ name: 'dashboard' })
    
  } catch (error) {
    console.error('Erro no registro:', error)
    
    if (error.response?.status === 422) {
      // Erros de validação
      const validationErrors = error.response.data.errors || {}
      errors.value = {
        name: validationErrors.name?.[0] || '',
        email: validationErrors.email?.[0] || '',
        password: validationErrors.password?.[0] || '',
        password_confirmation: validationErrors.password_confirmation?.[0] || ''
      }
    } else {
      // Erro genérico
      if (window.showFlash) {
        window.showFlash('error', 'Erro ao criar conta. Tente novamente.')
      }
    }
  } finally {
    loading.value = false
  }
}
</script>
