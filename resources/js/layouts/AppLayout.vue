<template>
  <div class="min-h-screen bg-background font-sans antialiased">
    <div class="min-h-screen flex flex-col">
      <!-- Navigation -->
      <nav v-if="isAuthenticated" class="bg-card border-b border-border shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16">
            <div class="flex items-center">
              <!-- Logo -->
              <router-link :to="{ name: 'dashboard' }" class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-gradient-to-br from-primary to-accent rounded-lg flex items-center justify-center">
                    <span class="text-primary-foreground font-bold text-lg">C</span>
                  </div>
                </div>
                <div class="ml-3">
                  <span class="text-xl font-bold text-foreground">CondoAI</span>
                </div>
              </router-link>

              <!-- Navigation Links -->
              <div class="hidden md:ml-10 md:flex md:space-x-8">
                <router-link 
                  :to="{ name: 'dashboard' }" 
                  class="px-1 py-2 text-sm font-medium transition-colors"
                  :class="$route.name === 'dashboard' ? 'text-primary border-b-2 border-primary' : 'text-muted-foreground hover:text-foreground'">
                  Dashboard
                </router-link>
                <router-link 
                  :to="{ name: 'condominiums.index' }" 
                  class="px-1 py-2 text-sm font-medium transition-colors"
                  :class="$route.name?.startsWith('condominiums') ? 'text-primary border-b-2 border-primary' : 'text-muted-foreground hover:text-foreground'">
                  Condomínios
                </router-link>
                <router-link 
                  :to="{ name: 'towers.index' }" 
                  class="px-1 py-2 text-sm font-medium transition-colors"
                  :class="$route.name?.startsWith('towers') ? 'text-primary border-b-2 border-primary' : 'text-muted-foreground hover:text-foreground'">
                  Torres
                </router-link>
                <router-link 
                  :to="{ name: 'apartments.index' }" 
                  class="px-1 py-2 text-sm font-medium transition-colors"
                  :class="$route.name?.startsWith('apartments') ? 'text-primary border-b-2 border-primary' : 'text-muted-foreground hover:text-foreground'">
                  Apartamentos
                </router-link>
                <router-link 
                  :to="{ name: 'residents.index' }" 
                  class="px-1 py-2 text-sm font-medium transition-colors"
                  :class="$route.name?.startsWith('residents') ? 'text-primary border-b-2 border-primary' : 'text-muted-foreground hover:text-foreground'">
                  Moradores
                </router-link>
              </div>
            </div>

            <!-- User Menu -->
            <div v-if="user" class="flex items-center space-x-4">
              <div class="relative" ref="userMenu">
                <button 
                  @click="showUserMenu = !showUserMenu" 
                  class="flex items-center space-x-2 text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background">
                  <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                    <span class="text-primary-foreground font-medium text-sm">
                      {{ user.name.charAt(0) }}
                    </span>
                  </div>
                  <span class="text-foreground font-medium">{{ user.name }}</span>
                  <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>

                <div 
                  v-show="showUserMenu" 
                  class="absolute right-0 mt-2 w-48 bg-card rounded-lg shadow-lg border border-border z-50">
                  <div class="py-1">
                    <a href="#" class="block px-4 py-2 text-sm text-foreground hover:bg-accent hover:text-accent-foreground transition-colors">
                      Perfil
                    </a>
                    <a href="#" class="block px-4 py-2 text-sm text-foreground hover:bg-accent hover:text-accent-foreground transition-colors">
                      Configurações
                    </a>
                    <div class="border-t border-border"></div>
                    <button 
                      @click="logout" 
                      class="block w-full text-left px-4 py-2 text-sm text-foreground hover:bg-accent hover:text-accent-foreground transition-colors">
                      Sair
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Content -->
      <main class="flex-1" :class="{ 'bg-gradient-to-br from-background via-muted/20 to-accent/10': !isAuthenticated }">
        <div :class="isAuthenticated ? 'max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8' : ''">
          <!-- Flash Messages -->
          <div v-if="flashMessage.success" class="mb-6 bg-card border border-border rounded-lg p-4 shadow-sm">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-primary" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-foreground">{{ flashMessage.success }}</p>
              </div>
              <div class="ml-auto pl-3">
                <button @click="flashMessage.success = null" class="text-muted-foreground hover:text-foreground">
                  <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <div v-if="flashMessage.error" class="mb-6 bg-destructive/10 border border-destructive/20 rounded-lg p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-destructive" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-destructive">{{ flashMessage.error }}</p>
              </div>
              <div class="ml-auto pl-3">
                <button @click="flashMessage.error = null" class="text-destructive/70 hover:text-destructive">
                  <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Router View -->
          <router-view />
        </div>
      </main>

      <!-- Footer -->
      <footer v-if="!isAuthenticated" class="bg-card border-t border-border">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between items-center">
            <div class="text-sm text-muted-foreground">
              © {{ new Date().getFullYear() }} CondoAI. Todos os direitos reservados.
            </div>
            <div class="flex space-x-6">
              <a href="#" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                Privacidade
              </a>
              <a href="#" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                Termos
              </a>
              <a href="#" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                Suporte
              </a>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const showUserMenu = ref(false)
const userMenu = ref(null)
const user = ref(null)
const flashMessage = ref({
  success: null,
  error: null
})

const isAuthenticated = computed(() => {
  return document.querySelector('meta[name="user-authenticated"]')?.content === 'true'
})

const logout = async () => {
  try {
    await fetch('/logout', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Content-Type': 'application/json'
      }
    })
    window.location.href = '/login'
  } catch (error) {
    console.error('Erro no logout:', error)
  }
}

const handleClickOutside = (event) => {
  if (userMenu.value && !userMenu.value.contains(event.target)) {
    showUserMenu.value = false
  }
}

onMounted(() => {
  // Buscar dados do usuário se autenticado
  if (isAuthenticated.value) {
    const userScript = document.querySelector('script[type="application/json"][data-user]')
    if (userScript) {
      user.value = JSON.parse(userScript.textContent)
    }
  }
  
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

// Método para exibir mensagens flash
const showFlash = (type, message) => {
  flashMessage.value[type] = message
}

// Disponibilizar globalmente
window.showFlash = showFlash
</script>