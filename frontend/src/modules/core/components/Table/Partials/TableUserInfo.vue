<script lang="ts" setup>
  defineProps<{ item: any }>()

</script>

<template>
  <div v-if="item.user" class="d-flex align-center gap-3  pa-2">
    <VAvatar size="30">
      <VImg alt="" :src="item.user.profile_photo_url" />
    </VAvatar>

    <div class="d-flex flex-column">
      <div class="font-weight-bold">
        {{ item.user.name }} -
        <RoleBadge :role="item.user.role" />
      </div>
      <div class="text-caption">{{ item.user.email }}</div>
    </div>
  </div>
  <span v-else>-</span>
</template>
