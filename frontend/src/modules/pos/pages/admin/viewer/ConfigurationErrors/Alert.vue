<script lang="ts" setup>
  withDefaults(
    defineProps<{
      title: string
      message: string
      icon?: string
      actionText?: string
      onClickAction?: () => void
      hasActionButton?: boolean
    }>(),
    {
      hasActionButton: false,
    })
</script>

<template>
  <v-sheet
    class="px-7 py-8 rounded-xl modern-card"
    elevation="0"
    width="500"
  >
    <div class="d-flex align-start">
      <v-icon class="me-4" color="warning" :icon="icon||'tabler-alert-circle'" size="32" />
      <div class="flex-grow-1">
        <div class="text-h6 font-weight-bold mb-2">
          {{ title }}
        </div>

        <div class="text-body-1 text-medium-emphasis mb-5" style="white-space: pre-line;">
          {{ message }}
        </div>

        <div v-if="hasActionButton && actionText && onClickAction" class="d-flex justify-end">
          <v-btn
            class="px-6"
            color="primary"
            prepend-icon="tabler-plus"
            @click="onClickAction"
          >
            {{ actionText }}
          </v-btn>
        </div>

      </div>
    </div>
  </v-sheet>
</template>

<style lang="scss" scoped>
.modern-card {
  background: #ffffff !important;
  border: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
}
</style>
