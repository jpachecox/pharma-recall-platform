<script setup lang="ts">
    import { Button } from '@/components/ui/button'
    import { Label } from '@/components/ui/label'
    import { Switch } from '@/components/ui/switch'

    const message: string = 'App Pharmacovigilance cargada correctamente'
</script>

<template>
  <div class="p-4 bg-slate-900 text-white min-h-screen">
    <h1 class="text-2xl font-bold">{{ message }}</h1>
    <div class="flex items-center space-x-2 mt-4">
        <Button variant="outline">
            Button
        </Button>
        <Switch id="airplane-mode" />
        <Label for="airplane-mode">Airplane Mode</Label>
    </div>
  </div>
</template>
