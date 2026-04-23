<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  const { t } = useI18n()

  // Landing de Configuración. 4 cards grandes en grid 2x2 (desktop)
  // / 1x4 (mobile). El hub creció a 9 tabs y se volvió pesado de
  // escanear visualmente — agruparlas en 4 familias baja la carga
  // cognitiva y deja espacio para que cada grupo crezca.
  // La Formas de cobro también vive acá ahora (Cobros se eliminó
  // del sidebar top-level).
  const cards = [
    {
      key: 'restaurante',
      icon: 'tabler-building-store',
      to: { name: 'admin.configuracion.restaurante' },
    },
    {
      key: 'operacion',
      icon: 'tabler-briefcase',
      to: { name: 'admin.configuracion.operacion' },
    },
    {
      key: 'users_and_security',
      icon: 'tabler-user-shield',
      to: { name: 'admin.configuracion.usuarios_seguridad' },
    },
    {
      key: 'system',
      icon: 'tabler-plug-connected',
      to: { name: 'admin.configuracion.sistema' },
    },
  ] as const
</script>

<template>
  <VRow>
    <VCol
      v-for="card in cards"
      :key="card.key"
      cols="12"
      md="6"
    >
      <VCard
        class="configuracion-card h-100"
        :to="card.to"
        variant="outlined"
      >
        <VCardText class="d-flex align-start ga-4 pa-6">
          <VAvatar
            color="primary"
            rounded="lg"
            size="56"
            variant="tonal"
          >
            <VIcon :icon="card.icon" size="32" />
          </VAvatar>
          <div class="flex-grow-1">
            <div class="text-h6 font-weight-medium mb-1">
              {{ t(`admin::admin.configuracion_landing.cards.${card.key}.title`) }}
            </div>
            <div class="text-body-2 text-medium-emphasis">
              {{ t(`admin::admin.configuracion_landing.cards.${card.key}.subtitle`) }}
            </div>
          </div>
          <VIcon
            class="align-self-center text-medium-emphasis"
            icon="tabler-chevron-right"
            size="20"
          />
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style lang="scss" scoped>
  .configuracion-card {
    transition: box-shadow 0.2s ease, transform 0.2s ease;

    &:hover {
      box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
      transform: translateY(-2px);
    }
  }
</style>
