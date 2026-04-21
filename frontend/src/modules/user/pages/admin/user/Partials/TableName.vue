<script lang="ts" setup>
  defineProps<{ item: any }>()

</script>

<template>
  <div class="d-flex align-center gap-3  pa-2">
    <div class="d-flex flex-column">
      <div class="font-weight-bold">
        {{ item.name }} -
        <RoleBadge :role="item.role" />
      </div>
      <div class="text-caption">
        @{{ item.username }}
      </div>
    </div>
  </div>
</template>
