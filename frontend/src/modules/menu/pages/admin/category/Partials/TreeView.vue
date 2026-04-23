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

  const emit = defineEmits(['click-category', 'add-root-category'])

  const { t } = useI18n()
  const toast = useToast()
  const {
    flatToTree,
    flattenTree,
    findTargetNode,
    findAndRemove,
    updateTree,
  } = useCategory()
  const tree = ref(flatToTree(props.categories))

  // Post-refactor Fix 3 parte C: la estructura de categorías es plana.
  // Solo aceptamos moves entre siblings (BEFORE/AFTER). Los positions
  // FIRST_CHILD / LAST_CHILD —que crearían jerarquía— se convierten a
  // AFTER target. El vendor dejó subcategorías en DB por si algún día
  // se reactivan, pero la UI no las genera ni permite anidar nuevos.
  async function handleMove (event: Record<string, any>) {
    const { id, targetId, position: rawPosition } = event
    const position = rawPosition === 'FIRST_CHILD' || rawPosition === 'LAST_CHILD'
      ? 'AFTER'
      : rawPosition

    const movedNode = findAndRemove(tree.value, id)
    if (!movedNode) return

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

  async function updateCategories () {
    try {
      const response = await updateTree(props.menuId, flattenTree(tree.value))
      toast.success(response.data.message)
    } catch {}
  }

  // findTargetNode queda importado para compat; hoy no se usa porque
  // eliminamos la lógica de FIRST_CHILD / LAST_CHILD. Referencia para
  // suprimir el warning de unused import si el linter lo flaggea:
  void findTargetNode
</script>

<template>
  <VCard min-height="350px">
    <VCardTitle class="d-flex align-center">
      <VBtn size="small" @click="emit('add-root-category')">
        <VIcon icon="tabler-plus" start />
        {{ t('category::categories.buttons.add_category') }}
      </VBtn>
    </VCardTitle>
    <VCardText>
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
