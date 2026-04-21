<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'

  defineProps<{ item: any }>()

  const { t } = useI18n()
</script>

<template>
  <VChip
    :color="item.built_in ?'info':'success'"
    size="small"
  >
    {{ t(`user::roles.role_types.${item.built_in ? 'built_in' : 'custom'}`) }}
  </VChip>
</template>
