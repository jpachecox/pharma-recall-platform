<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import type { Order } from '@/types/pharmaco'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'

const props = defineProps<{ orderId: number }>()
const emit = defineEmits(['close'])

const order = ref<Order | null>(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const { data } = await axios.get(`/orders/${props.orderId}`)
    order.value = data.data
  } catch (error) {
    console.error('Error cargando detalle', error)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <Dialog open @update:open="emit('close')">
    <DialogContent class="max-w-lg">
      <DialogHeader>
        <DialogTitle>Detalle de Órden #{{ orderId }}</DialogTitle>
        <DialogDescription>
          Información del comprador y listado de medicamentos incluidos en esta compra.
        </DialogDescription>
      </DialogHeader>

      <div v-if="loading" class="p-4 text-center">Cargando detalles...</div>

      <div v-else-if="order" class="space-y-4 text-sm">
        <!-- Comprador -->
        <div class="p-3 border rounded bg-muted/30">
          <h4 class="font-bold text-xs uppercase tracking-wider text-muted-foreground mb-1">Comprador</h4>
          <p><strong>Nombre:</strong> {{ order.customer?.name || 'N/A' }}</p>
          <p><strong>Email:</strong> {{ order.customer?.email || 'N/A' }}</p>
          <p><strong>Teléfono:</strong> {{ order.customer?.phone || 'N/A' }}</p>
        </div>

        <!-- Medicamentos -->
        <div>
          <h4 class="font-bold text-xs uppercase tracking-wider text-muted-foreground mb-2">Medicamentos en la órden</h4>
          <ul class="divide-y border rounded">
            <li v-for="med in order.medications" :key="med.id" class="p-2 flex justify-between items-center">
              <div>
                <p class="font-medium">{{ med.name }}</p>
                <p class="text-xs text-muted-foreground">Lote: {{ med.lot_number }}</p>
              </div>
              <div class="text-right">
                <p class="font-semibold">{{ med.pivot?.quantity ?? 1 }} unidad(es)</p>
                <p v-if="med.pivot?.unit_price" class="text-xs">${{ med.pivot.unit_price }}</p>
              </div>
            </li>
          </ul>
        </div>
      </div>

      <div class="flex justify-end pt-2">
        <Button variant="outline" @click="emit('close')">Cerrar</Button>
      </div>
    </DialogContent>
  </Dialog>
</template>