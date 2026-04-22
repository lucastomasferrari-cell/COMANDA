<script lang="ts" setup>
  import { computed, ref } from 'vue'

  interface Notification {
    id: number
    type: 'order' | 'kitchen' | 'stock' | 'system'
    title: string
    message: string
    time: string
    read: boolean
  }

  // Mock por ahora. Cuando haya backend de notificaciones, reemplazar
  // por fetch + SSE/websocket al servicio real.
  const notifications = ref<Notification[]>([
    {
      id: 1,
      type: 'order',
      title: 'Nuevo pedido',
      message: 'Mesa 5 agregó un pedido de $3.450',
      time: 'hace 2 min',
      read: false,
    },
    {
      id: 2,
      type: 'kitchen',
      title: 'Pedido listo',
      message: 'Cocina marcó pedido #124 como listo',
      time: 'hace 7 min',
      read: false,
    },
    {
      id: 3,
      type: 'stock',
      title: 'Stock bajo',
      message: 'Tomate queda con menos de 2 kg',
      time: 'hace 30 min',
      read: false,
    },
    {
      id: 4,
      type: 'system',
      title: 'Arqueo cerrado',
      message: 'Caja 1 cerró con diferencia de -$50',
      time: 'hace 1 h',
      read: true,
    },
  ])

  const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)
  const drawerOpen = ref(false)

  function iconFor (type: Notification['type']): string {
    return {
      order: 'tabler-shopping-cart',
      kitchen: 'tabler-chef-hat',
      stock: 'tabler-box-seam',
      system: 'tabler-settings',
    }[type]
  }

  function colorFor (type: Notification['type']): string {
    return {
      order: 'primary',
      kitchen: 'warning',
      stock: 'error',
      system: 'info',
    }[type]
  }

  function markAllRead () {
    notifications.value.forEach(n => { n.read = true })
  }
</script>

<template>
  <div>
    <VBadge
      :content="unreadCount"
      :model-value="unreadCount > 0"
      color="error"
      offset-x="4"
      offset-y="4"
    >
      <VBtn
        icon
        variant="text"
        color="default"
        @click="drawerOpen = true"
      >
        <VIcon icon="tabler-bell" />
      </VBtn>
    </VBadge>

    <VNavigationDrawer
      v-model="drawerOpen"
      location="right"
      temporary
      width="380"
    >
      <div class="d-flex align-center px-4 py-3 border-b">
        <h3 class="text-h6">Notificaciones</h3>
        <VSpacer />
        <VBtn
          v-if="unreadCount > 0"
          size="small"
          variant="text"
          @click="markAllRead"
        >
          Marcar todas leídas
        </VBtn>
        <VBtn
          icon="tabler-x"
          size="small"
          variant="text"
          @click="drawerOpen = false"
        />
      </div>

      <VList density="compact" lines="two">
        <VListItem
          v-for="n in notifications"
          :key="n.id"
          :class="{ 'bg-surface-variant': !n.read }"
        >
          <template #prepend>
            <VAvatar :color="colorFor(n.type)" size="36">
              <VIcon :icon="iconFor(n.type)" color="white" size="20" />
            </VAvatar>
          </template>
          <VListItemTitle class="font-weight-medium">{{ n.title }}</VListItemTitle>
          <VListItemSubtitle>{{ n.message }}</VListItemSubtitle>
          <template #append>
            <div class="text-caption text-medium-emphasis">{{ n.time }}</div>
          </template>
        </VListItem>
      </VList>

      <div v-if="notifications.length === 0" class="text-center text-medium-emphasis py-12">
        <VIcon icon="tabler-bell-off" size="48" class="mb-2" />
        <div>No hay notificaciones</div>
      </div>
    </VNavigationDrawer>
  </div>
</template>
