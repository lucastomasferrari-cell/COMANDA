<script lang="ts" setup>
  import { computed, ref } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{ item: any }>()
  const { t } = useI18n()

  const show = ref(false)

  const mapUrl = computed(() => {
    if (!props.item.latitude || !props.item.longitude) return ''
    return `https://maps.google.com/maps?q=${props.item.latitude},${props.item.longitude}&hl=es&output=embed&z=15&disableDefaultUI=true`
  })
</script>

<template>
  <VMenu
    v-model="show"
    activator="parent"
    :close-on-content-click="false"
    location="top"
    offset="8"
  >
    <template #activator="{ props }">
      <VChip
        class="cursor-pointer"
        color="default"
        size="small"
        v-bind="props"
      >
        <VIcon
          class="text-medium-emphasis"
          icon="tabler-map-pin"
        />
        &nbsp;
        {{ t('branch::branches.table.view_on_map') }}
      </VChip>
    </template>

    <VCard class="pa-2" max-width="320" width="300">
      <template v-if="item.latitude && item.longitude">
        <iframe
          allowfullscreen
          height="200"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          :src="mapUrl"
          style="border:0;"
          width="100%"
        />
      </template>
      <div v-else class="text-caption text-center py-4">
        {{ t('branch::branches.location_not_available') }}
      </div>
    </VCard>
  </VMenu>
</template>
