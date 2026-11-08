<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-foreground">Criar Morador</h1>
      <router-link 
        :to="{ name: 'residents.index' }"
        class="inline-flex items-center px-4 py-2 border border-border rounded-lg text-sm font-medium text-muted-foreground hover:text-foreground hover:border-foreground transition-colors"
      >
        ← Voltar
      </router-link>
    </div>

    <div class="bg-card rounded-lg border border-border p-6">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <FormField
            v-model="form.name"
            type="text"
            name="name"
            label="Nome Completo"
            placeholder="Nome do morador"
            :required="true"
            :error="errors.name"
          />

          <FormField
            v-model="form.email"
            type="email"
            name="email"
            label="E-mail"
            placeholder="email@exemplo.com"
            :error="errors.email"
          />

          <FormField
            v-model="form.phone"
            type="text"
            name="phone"
            label="Telefone"
            placeholder="(11) 99999-9999"
            :error="errors.phone"
          />

          <FormField
            v-model="form.document"
            type="text"
            name="document"
            label="CPF"
            placeholder="000.000.000-00"
            :error="errors.document"
          />
        </div>

        <div class="flex justify-end space-x-4">
          <ActionButton
            type="button"
            variant="outline"
            @click="$router.push({ name: 'residents.index' })"
            text="Cancelar"
          />
          <ActionButton
            type="submit"
            variant="primary"
            :loading="loading"
            text="Criar Morador"
          />
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const loading = ref(false)

const form = reactive({
  name: '',
  email: '',
  phone: '',
  document: ''
})

const errors = ref({})

const clearErrors = () => {
  errors.value = {}
}

const handleSubmit = async () => {
  clearErrors()
  loading.value = true

  try {
    await axios.post('/api/residents', form)
    
    router.push({ name: 'residents.index' })
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    }
  } finally {
    loading.value = false
  }
}
</script> 