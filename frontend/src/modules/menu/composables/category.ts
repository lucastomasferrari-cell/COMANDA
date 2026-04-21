import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import {
  destroy,
  index,
  show,
  store,
  update,
  updateTree,
} from '@/modules/menu/api/category.api.ts'

export function useCategory () {
  const toast = useToast()
  const { t } = useI18n()

  const getIndexData = async (menuId: number | null): Promise<Record<string, any>> => {
    try {
      const data = (await index(menuId)).data.body
      if (data.categories == null && data.menu == null) {
        toast.error(t('menu::menus.menu_is_not_active'))
        return { status: 200 }
      }
      return { status: 200, data }
    } catch (error: any) {
      if (error?.response?.status !== 404) {
        toast.error(t('core::errors.an_unexpected_error_occurred'))
      }
      return { status: error?.response?.status }
    }
  }

  const flatToTree = (flat: Record<string, any>[]) => {
    const map = new Map()
    for (const i of flat) {
      map.set(i.id, { ...i, children: [], expanded: true })
    }

    const roots = []
    for (const i of flat) {
      if (i.parent === '#' || i.parent == null) {
        roots.push(map.get(i.id))
      } else {
        map.get(i.parent)?.children.push(map.get(i.id))
      }
    }

    return roots
  }

  const flattenTree = (tree: Record<string, any>[], parent = null): any => {
    const flat = []
    for (const node of tree) {
      const { children, ...rest } = node
      flat.push({ ...rest, parent })
      if (children?.length) {
        flat.push(...flattenTree(children, node.id))
      }
    }
    return flat
  }

  const findAndRemove = (nodes: Record<string, any>[], id: number): any => {
    for (let i = 0; i < nodes.length; i++) {
      const node = nodes[i]
      if (!node) continue
      if (node.id === id) {
        return nodes.splice(i, 1)[0]
      }
      if (node.children) {
        const result = findAndRemove(node.children, id)
        if (result) {
          return result
        }
      }
    }
  }

  const findTargetNode = (nodes: Record<string, any>[], id: number): any => {
    for (const node of nodes) {
      if (node.id === id) {
        return node
      }
      if (node.children) {
        const result = findTargetNode(node.children, id)
        if (result) {
          return result
        }
      }
    }
  }

  const updateExpansion = (nodes: any[], expanded: boolean) => {
    for (const node of nodes) {
      node.expanded = expanded
      if (node.children && node.children.length > 0) {
        updateExpansion(node.children, expanded)
      }
    }
  }

  return {
    show,
    update,
    store,
    getIndexData,
    flatToTree,
    flattenTree,
    updateExpansion,
    findTargetNode,
    findAndRemove,
    updateTree,
    destroy,
  }
}
