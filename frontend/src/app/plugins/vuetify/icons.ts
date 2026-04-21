import type { IconAliases, IconProps } from 'vuetify'

import checkboxChecked from '@/assets/images/svg/checkbox-checked.svg'
import checkboxIndeterminate from '@/assets/images/svg/checkbox-indeterminate.svg'
import checkboxUnchecked from '@/assets/images/svg/checkbox-unchecked.svg'
import radioChecked from '@/assets/images/svg/radio-checked.svg'
import radioUnchecked from '@/assets/images/svg/radio-unchecked.svg'

const customIcons: Record<string, unknown> = {
  'mdi-checkbox-blank-outline': checkboxUnchecked,
  'mdi-checkbox-marked': checkboxChecked,
  'mdi-minus-box': checkboxIndeterminate,
  'mdi-radiobox-marked': radioChecked,
  'mdi-radiobox-blank': radioUnchecked,
}

const aliases: Partial<IconAliases> = {
  calendar: 'tabler-calendar-month',
  collapse: 'tabler-chevron-up',
  complete: 'tabler-check',
  cancel: 'bx-x',
  close: 'bx-x',
  delete: 'tabler-xbox-x-filled',
  clear: 'tabler-xbox-x',
  success: 'tabler-circle-check',
  info: 'tabler-info-circle',
  warning: 'bx-error',
  error: 'bx-error-circle',
  prev: 'tabler-chevron-left',
  ratingEmpty: 'tabler-star',
  ratingFull: 'tabler-star-filled',
  ratingHalf: 'tabler-star-half-filled',
  next: 'tabler-chevron-right',
  delimiter: 'tabler-circle',
  sort: 'tabler-arrow-up',
  expand: 'tabler-chevron-down',
  menu: 'tabler-menu-2',
  subgroup: 'tabler-caret-down-filled',
  dropdown: 'tabler-chevron-down',
  edit: 'tabler-pencil',
  loading: 'tabler-refresh',
  first: 'tabler-player-skip-back-filled',
  last: 'tabler-player-skip-forward-filled',
  unfold: 'tabler-arrows-move-vertical',
  file: 'tabler-paperclip',
  plus: 'tabler-plus',
  minus: 'tabler-minus',
  sortAsc: 'tabler-arrow-up',
  sortDesc: 'tabler-arrow-down',
}

export const iconify = {
  component: (props: IconProps) => {
    // Load custom SVG directly instead of going through icon component
    if (typeof props.icon === 'string') {
      const iconComponent = customIcons[props.icon]

      if (iconComponent) {
        return h(iconComponent)
      }
    }

    return h(
      props.tag,
      {
        ...props,

        // As we are using class based icons
        class: [props.icon],

        // Remove used props from DOM rendering
        tag: undefined,
        icon: undefined,
      },
    )
  },
}

export const icons = {
  defaultSet: 'iconify',
  aliases,
  sets: {
    iconify,
  },
}
