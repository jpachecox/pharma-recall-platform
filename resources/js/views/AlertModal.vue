<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import { Button } from '@/components/ui/button'
import { Textarea } from '@/components/ui/textarea'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'

const props = defineProps<{
  lotNumber: string
  orderIds: number[]
}>()

const emit = defineEmits(['close', 'sent'])

const message = ref('Se ha detectado una anomalía sanitaria en el lote de su medicamento. Por favor suspenda su uso.')
const loading = ref(false)
const errorMessage = ref('')

const sendAlerts = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    await axios.post('/api/v1/alerts/send', {
      lot_number: props.lotNumber,
      order_ids: props.orderIds,
      channel: 'email',
      message: message.value,
    })
    emit('sent')
    emit('close')
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Ocurrió un error al despachar las alertas.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <Dialog open @update:open="emit('close')">
    <DialogContent class="max-w-md">
      <DialogHeader>
        <DialogTitle>Notificar Retiro de Lote {{ lotNumber }}</DialogTitle>
        <DialogDescription>
          Confirme el mensaje que se enviará a los usuarios afectados por este retiro sanitario.
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-4">
        <p class="text-xs text-muted-foreground">
          Se enviará una alerta sanitaria por correo a <strong>{{ orderIds.length }}</strong> orden(es) seleccionada(s).
        </p>

        <div>
          <label class="text-xs font-semibold">Mensaje para los clientes</label>
          <Textarea v-model="message" rows="4" class="mt-1" />
        </div>

        <div v-if="errorMessage" class="p-2 text-xs bg-destructive/10 text-destructive rounded">
          {{ errorMessage }}
        </div>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <Button variant="outline" :disabled="loading" @click="emit('close')">Cancelar</Button>
        <Button variant="destructive" :disabled="loading" @click="sendAlerts">
          {{ loading ? 'Enviando...' : 'Confirmar y Enviar' }}
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>