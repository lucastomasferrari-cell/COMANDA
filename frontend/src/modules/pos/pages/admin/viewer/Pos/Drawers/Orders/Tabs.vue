<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const props = defineProps<{
    disabled: boolean
    modelValue: string
  }>()

  const emit = defineEmits<{
    (e: 'on-click'): void
    (e: 'update:modelValue', value: string): void
  }>()

  const tab = computed({
    get: () => props.modelValue,
    set: (val: string) => emit('update:modelValue', val),
  })

  const { can } = useAuth()
  const { t } = useI18n()
</script>

<template>
  <VTabs
    v-if="can('admin.orders.upcoming') && can('admin.orders.active')"
    v-model="tab"
    :disabled="disabled"
    grow
    @click="$emit('on-click')"
  >
    <v-tab value="active">
      {{ t('pos::pos_viewer.active_orders') }}
    </v-tab>
    <v-tab value="upcoming">
      {{ t('pos::pos_viewer.upcoming_orders') }}
    </v-tab>
  </VTabs>
</template>
