<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import type { Order } from '@/types/pharmaco'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Checkbox } from '@/components/ui/checkbox'
import OrderDetailModal from './OrderDetailModal.vue'
import AlertModal from './AlertModal.vue'

const lot = ref('951357')
const startDate = ref('')
const endDate = ref('')
const orders = ref<Order[]>([])
const selectedOrderIds = ref<number[]>([])
const loading = ref(false)

const selectedOrderForModal = ref<number | null>(null)
const isAlertModalOpen = ref(false)

const searchOrders = async () => {
  if (!lot.value) return
  loading.value = true
  try {
    const { data } = await axios.get('/orders', {
      params: {
        lot: lot.value,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
      },
    })
    orders.value = data.data
    selectedOrderIds.value = []
  } catch (error) {
    console.error('Error cargando órdenes', error)
  } finally {
    loading.value = false
  }
}

const toggleSelectAll = (checked: boolean) => {
  selectedOrderIds.value = checked ? orders.value.map((o) => o.id) : []
}

const toggleSelectOrder = (id: number) => {
  const index = selectedOrderIds.value.indexOf(id)
  if (index > -1) selectedOrderIds.value.splice(index, 1)
  else selectedOrderIds.value.push(id)
}
</script>

<template>
  <div class="p-6 max-w-6xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold tracking-tight">Investigación de Farmacovigilancia</h1>
      <Button
        :disabled="selectedOrderIds.length === 0"
        variant="destructive"
        @click="isAlertModalOpen = true"
      >
        Emitir Alerta ({{ selectedOrderIds.length }})
      </Button>
    </div>

    <!-- Filtros de búsqueda -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 border rounded-lg bg-card">
      <div>
        <label class="text-sm font-medium">Lote Afectado (*)</label>
        <Input v-model="lot" placeholder="Ej: 951357" />
      </div>
      <div>
        <label class="text-sm font-medium">Fecha Inicio</label>
        <Input v-model="startDate" type="date" />
      </div>
      <div>
        <label class="text-sm font-medium">Fecha Fin</label>
        <Input v-model="endDate" type="date" />
      </div>
      <div class="flex items-end">
        <Button class="w-full" :disabled="loading" @click="searchOrders">
          {{ loading ? 'Buscando...' : 'Buscar Órdenes' }}
        </Button>
      </div>
    </div>

    <!-- Tabla de resultados -->
    <div class="border rounded-lg overflow-hidden">
      <table class="w-full text-left text-sm">
        <thead class="bg-muted border-b">
          <tr>
            <th class="p-3 w-10">
              <Checkbox
                :checked="orders.length > 0 && selectedOrderIds.length === orders.length"
                @update:checked="toggleSelectAll"
              />
            </th>
            <th class="p-3">ID Órden</th>
            <th class="p-3">Fecha de Compra</th>
            <th class="p-3">Alertas Previas</th>
            <th class="p-3 text-right">Acción</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id" class="border-b hover:bg-muted/50">
            <td class="p-3">
              <Checkbox
                :checked="selectedOrderIds.includes(order.id)"
                @update:checked="() => toggleSelectOrder(order.id)"
              />
            </td>
            <td class="p-3 font-medium">#{{ order.id }}</td>
            <td class="p-3">{{ order.purchase_date }}</td>
            <td class="p-3">
              <span
                class="px-2 py-1 rounded text-xs font-semibold"
                :class="order.alerts_count > 0 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600'"
              >
                {{ order.alerts_count }} enviadas
              </span>
            </td>
            <td class="p-3 text-right">
              <Button size="sm" variant="outline" @click="selectedOrderForModal = order.id">
                Ver Detalle
              </Button>
            </td>
          </tr>
          <tr v-if="orders.length === 0">
            <td colspan="5" class="p-4 text-center text-muted-foreground">
              No se encontraron órdenes para este lote.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modales -->
    <OrderDetailModal
      v-if="selectedOrderForModal"
      :order-id="selectedOrderForModal"
      @close="selectedOrderForModal = null"
    />

    <AlertModal
      v-if="isAlertModalOpen"
      :lot-number="lot"
      :order-ids="selectedOrderIds"
      @close="isAlertModalOpen = false"
      @sent="searchOrders"
    />
  </div>
</template>