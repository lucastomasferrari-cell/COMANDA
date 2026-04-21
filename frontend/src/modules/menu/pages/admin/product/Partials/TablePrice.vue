<script lang="ts" setup>
  defineProps<{ item: any }>()

</script>

<template>
  <div class="d-flex align-center gap-2">
    <span class="text-h6">
      {{ item.selling_price.formatted }}
    </span>

    <span
      v-if="item.has_special_price"
      class="text-body-2 text-grey-darken-1 text-decoration-line-through"
    >
      {{ item.price.formatted }}
    </span>
  </div>
</template>
