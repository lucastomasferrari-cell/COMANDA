<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useToast } from 'vue-toastification'
  import VueTreeDnd from 'vue-tree-dnd'
  import { useCategory } from '@/modules/menu/composables/category.ts'
  import NodeRenderer from './NodeRenderer.vue'

  const props = defineProps<{
    categories: Record<string, any>[]
    activeCategory: Record<string, any> | null
    menuId: number
    refreshing: boolean
  }>()

  const emit = defineEmits(['click-category', 'add-sub-category', 'add-root-category'])

  const { t } = useI18n()
  const toast = useToast()
  const {
    flatToTree,
    flattenTree,
    updateExpansion,
    findTargetNode,
    findAndRemove,
    updateTree,
  } = useCategory()
  const tree = ref(flatToTree(props.categories))

  async function handleMove (event: Record<string, any>) {
    const { id, targetId, position } = event

    const movedNode = findAndRemove(tree.value, id)
    if (!movedNode) return

    if (position === 'FIRST_CHILD') {
      const target = findTargetNode(tree.value, targetId)
      if (!target.children) target.children = []
      movedNode.parent = target.id
      target.children.unshift(movedNode)
    } else if (position === 'LAST_CHILD') {
      const target = findTargetNode(tree.value, targetId)
      if (!target.children) target.children = []
      movedNode.parent = target.id
      target.children.push(movedNode)
    } else {
      const insertAt = (nodes: Record<string, any>[]) => {
        for (let i = 0; i < nodes.length; i++) {
          const node = nodes[i]
          if (!node) continue
          if (node.id === targetId) {
            const insertIndex = position === 'BEFORE' ? i : i + 1
            movedNode.parent = nodes === tree.value ? '#' : null
            nodes.splice(insertIndex, 0, movedNode)
            return true
          }
          if (node.children && insertAt(node.children)) return true
        }
      }
      insertAt(tree.value)
    }
    await updateCategories()
  }

  function handleClick (item: Record<string, any>) {
    emit('click-category', item)
  }

  const NodeWrapper = defineComponent({
    props: ['item', 'depth', 'expanded'],
    emits: ['click'],
    setup (nodeProps) {
      return () => h(NodeRenderer, {
        ...nodeProps,
        activeId: props.activeCategory?.id,
        onClick: handleClick,
      })
    },
  })

  function collapseAll () {
    updateExpansion(tree.value, false)
  }

  function expandAll () {
    updateExpansion(tree.value, true)
  }

  async function updateCategories () {
    try {
      const response = await updateTree(props.menuId, flattenTree(tree.value))
      toast.success(response.data.message)
    } catch {}
  }
</script>

<template>
  <VCard min-height="350px">
    <VCardTitle class="d-flex align-center">
      <VBtn class="me-2" size="small" @click="emit('add-root-category')">
        <VIcon icon="tabler-plus" start />
        {{ t('category::categories.buttons.add_root_category') }}
      </VBtn>
      <VBtn
        color="secondary"
        :disabled="activeCategory==null"
        size="small"
        @click="emit('add-sub-category')"
      >
        <VIcon icon="tabler-playlist-add" start />
        {{ t('category::categories.buttons.add_sub_category') }}
      </VBtn>
    </VCardTitle>
    <VCardText>
      <div class="mb-2 tree-actions">
        <VBtn size="small" variant="text" @click="expandAll">
          {{ t('category::categories.buttons.expand_all') }}
        </VBtn>
        |
        <VBtn size="small" variant="text" @click="collapseAll">
          {{ t('category::categories.buttons.collapse_all') }}
        </VBtn>
      </div>
      <VueTreeDnd
        v-model="tree"
        :component="NodeWrapper"
        @move="handleMove"
      />
    </VCardText>
    <div v-if="refreshing" class="tree-view-overlay">
      <VProgressCircular color="primary" indeterminate size="35" width="3" />
    </div>
  </VCard>
</template>

<style lang="scss" scoped>
.tree-view-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.4);
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>
