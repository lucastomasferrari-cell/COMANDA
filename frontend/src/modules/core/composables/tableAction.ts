import type { IconValue } from 'vuetify/lib/composables/icons.js'
import type { TableAction } from '@/modules/core/contracts/Table.ts'
import { useI18n } from 'vue-i18n'
import { useAuth } from '@/modules/auth/composables/auth.ts'
import { useConfirmDialog } from '@/modules/core/composables/confirmDialog.ts'
import { useTableDefaultActionClick } from '@/modules/core/composables/tableDefaultClick.ts'

export function useTableAction (resource: string,
  name: string,
  module: string,
  itemId: string,
  refresh: () => void,
  isBulk = false) {
  const auth = useAuth()
  const { t } = useI18n()
  const { handleClick } = useTableDefaultActionClick(resource, refresh)

  const colors: Record<string, string> = {
    edit: 'success',
    destroy: 'error',
    create: 'primary',
    show: 'info',
  }
  const icons: Record<string, IconValue> = {
    edit: 'tabler-edit',
    destroy: 'tabler-trash',
    show: 'tabler-eye',
    create: 'tabler-plus',
  }

  const isHidden = (action: TableAction, item: any = null): boolean => typeof action.hidden === 'function' ? action.hidden(item) : (action.hidden || false)

  const isDisabled = (action: TableAction, item: any = null): boolean =>
    (typeof action.disabled === 'function' ? action.disabled(item) : action.disabled) || (action.loading || false)

  const onClick = async (action: TableAction, item: any = null) => {
    action.loading = true
    await (typeof action.onClick === 'function'
      ? action.onClick(item)
      : handleClick(action, Array.isArray(item) ? item : item?.[itemId]))
    action.loading = false
  }

  const handleAction = async (action: TableAction, item: any = null) => {
    if (action.confirm) {
      const confirmed = await useConfirmDialog(action.confirm)
      if (!confirmed) {
        return
      }
    }

    await onClick(action, item)
  }

  const getPermission = (action: TableAction): string | string[] => {
    return action?.permission ?? `admin.${resource}.${action.key}`
  }

  const hasPermission = (action: TableAction): boolean => getPermission(action) ? auth.hasPermission(getPermission(action)) : true

  const isVisible = (action: TableAction, item: any = null): boolean => !isHidden(action, item) && hasPermission(action)

  const getColor = (action: TableAction): string => {
    if (action?.color) {
      return action.color
    } else if (colors[action.key]) {
      return colors[action.key] || 'default'
    }
    return 'primary'
  }

  const getIcon = (action: TableAction): IconValue | undefined => {
    if (action?.icon) {
      return action.icon
    } else if (icons[action.key]) {
      return icons[action.key]
    }
    return undefined
  }

  const getLabel = (action: TableAction): string => {
    if (action?.label) {
      return action.label
    }
    return t(`admin::resource.${action.key}`, { resource: t(`${module}::${resource}.${isBulk ? resource : name}`) })
  }

  return {
    isHidden,
    hasPermission,
    getLabel,
    getIcon,
    getColor,
    isVisible,
    isDisabled,
    onClick: handleAction,
  }
}
