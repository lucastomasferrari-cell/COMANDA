import type { IconValue } from 'vuetify/lib/composables/icons.js'

export interface AclProperties {
  action: string
  subject: string
}

export interface NavSectionTitle extends Partial<AclProperties> {
  heading: string
  badgeContent?: string
  badgeClass?: string
}

export declare type ATagTargetAttrValues = '_blank' | '_self' | '_parent' | '_top' | 'framename'
export declare type ATagRelAttrValues
  = | 'alternate'
    | 'author'
    | 'bookmark'
    | 'external'
    | 'help'
    | 'license'
    | 'next'
    | 'nofollow'
    | 'noopener'
    | 'noreferrer'
    | 'prev'
    | 'search'
    | 'tag'

export interface NavLinkProps {
  to?: any
  href?: string
  target?: ATagTargetAttrValues
  rel?: ATagRelAttrValues
}

export interface NavLink extends NavLinkProps, Partial<AclProperties> {
  title: string
  icon?: IconValue | undefined
  badgeContent?: string
  badgeClass?: string
  disable?: boolean
}

export interface NavGroup extends Partial<AclProperties> {
  title: string
  icon?: IconValue | undefined
  badgeContent?: string
  badgeClass?: string
  children: (NavLink | NavGroup)[]
  disable?: boolean
}

export interface Theme {
  name: string
  icon: string
}
