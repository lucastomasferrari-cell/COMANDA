<script lang="ts" setup>
  defineProps<{ item: any }>()
</script>

<template>
  <div v-if="item.customer" class="d-flex align-center gap-3  pa-2">
    <VAvatar size="30">
      <VImg alt="" :src="item.customer.profile_photo_url" />
    </VAvatar>

    <div class="d-flex flex-column">
      <div class="font-weight-bold">
        {{ item.customer.name }}
      </div>
      <div class="text-caption">{{ item.customer.phone }}</div>
    </div>
  </div>
</template>
