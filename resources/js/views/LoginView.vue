<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'

const router = useRouter()

const email = ref('admin@farmacia.com')
const password = ref('password')
const loading = ref(false)
const errorMessage = ref('')

const handleLogin = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const { data } = await axios.post('/login', {
      email: email.value,
      password: password.value,
    })

    // Guardar token Sanctum y configurar header global
    localStorage.setItem('auth_token', data.access_token)
    axios.defaults.headers.common['Authorization'] = `Bearer ${data.access_token}`

    // Redirigir al panel de búsqueda de órdenes
    router.push({ name: 'search-orders' })
  } catch (error: any) {
    errorMessage.value =
      error.response?.data?.message || 'Error al iniciar sesión. Verifica tus credenciales.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-muted/40 p-4">
    <Card class="w-full max-w-sm">
      <CardHeader class="space-y-1">
        <CardTitle class="text-xl font-bold text-center">
          Farmacovigilancia API
        </CardTitle>
        <CardDescription class="text-center">
          Ingresa tus credenciales para continuar
        </CardDescription>
      </CardHeader>
      
      <CardContent>
        <form @submit.prevent="handleLogin" class="space-y-4">
          <div class="space-y-2">
            <label class="text-sm font-medium leading-none">Correo Electrónico</label>
            <Input
              v-model="email"
              type="email"
              placeholder="admin@farmacia.com"
              required
            />
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium leading-none">Contraseña</label>
            <Input
              v-model="password"
              type="password"
              placeholder="••••••••"
              required
            />
          </div>

          <div v-if="errorMessage" class="p-2 text-xs bg-destructive/10 text-destructive rounded font-medium">
            {{ errorMessage }}
          </div>

          <Button type="submit" class="w-full" :disabled="loading">
            {{ loading ? 'Ingresando...' : 'Iniciar Sesión' }}
          </Button>
        </form>
      </CardContent>
    </Card>
  </div>
</template>