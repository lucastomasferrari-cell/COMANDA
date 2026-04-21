<script lang="ts" setup>
  import { computed } from 'vue'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'

  const props = defineProps<{
    item: Record<string, any>
    activeId?: number | null
    depth: number
  }>()
  const emit = defineEmits(['click'])

  const isOpenFolder = computed(() => props.item.children?.length > 0 && props.item.expanded)
  const appStore = useAppStore()
</script>

<template>

  <div
    class="flex items-center cursor-pointer justify-center  mb-1"
    :style="{[`${appStore.isRtl ? 'paddingRight':'paddingLeft'}`]: `${depth * 30}px` }"
    @click.stop="emit('click', item)"
  >
    <v-icon
      v-if="item.children?.length"
      class="me-1"
      size="18"
      @click.stop="item.expanded=!item.expanded"
    >
      {{ isOpenFolder ? 'tabler-chevron-down' : (appStore.isRtl ? 'tabler-chevron-left' : 'tabler-chevron-right') }}
    </v-icon>
    <span
      class="node-name"
      :class="{active:activeId == item.id}"
    >
      <VIcon
        class="me-1"
        color="primary"
        :icon="isOpenFolder?'tabler-folder-open':'tabler-folder'"
        size="18"
      />
      <span>{{ item.name }}</span>
    </span>
  </div>
</template>
<style lang="scss" scoped>
.node-name {
  padding: 0 7px;
  border-radius: 2px;
  font-size: 13px;
  font-weight: 500;
}

.node-name.active {
  background-color: rgba(var(--v-theme-primary), 0.2);
}
</style>
