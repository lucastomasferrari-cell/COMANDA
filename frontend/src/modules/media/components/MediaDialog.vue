<script lang="ts" setup>
  import MediaManager from '@/modules/media/pages/admin/media/Partials/Manager.vue'

  const props = defineProps<{
    modelValue: boolean
    mime?: string
    multiple?: boolean
  }>()

  const emit = defineEmits(['update:modelValue', 'callback'])

  const dialogVisible = computed({
    get: () => props.modelValue,
    set: (val: boolean) => emit('update:modelValue', val),
  })

  function callback (file: any) {
    emit('callback', file)
  }
</script>

<template>
  <VDialog v-model="dialogVisible" max-width="90%">
    <VCard>
      <MediaManager :is-picker="true" :mime="mime" :multiple="multiple" @callback="callback" />
    </VCard>
  </VDialog>
</template>
