<script lang="ts" setup>
  import TableEnum from '@/modules/core/components/Table/Partials/TableEnum.vue'

  defineProps<{ item: any }>()

</script>

<template>
  <div class="d-flex align-center gap-3  pa-2">
    <VAvatar size="32">
      <VImg alt="" :src="item.profile_photo_url" />
    </VAvatar>
    <div class="d-flex flex-column">
      <div class="d-flex align-center gap-2">
        <div class="font-weight-bold">
          {{ item.name }}
        </div>
        <TableEnum v-if="item.customer_type" column="customer_type" :item="item" />
      </div>
      <div v-if="item.username" class="text-caption mt-1">
        @{{ item.username }}
      </div>
    </div>
  </div>
</template>
