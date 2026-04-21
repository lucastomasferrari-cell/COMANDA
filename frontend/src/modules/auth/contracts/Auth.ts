interface UserRole {
  name: string
  display_name: string
  permissions: string[]
}

export interface User {
  id: number
  name: string
  profile_photo_url: string
  username: string
  email: string
  branch_id: null | number
  role: UserRole
  assigned_to_branch: boolean
}

export interface Auth {
  user: User | null
  token: string | null
}

export interface AuthAccount {
  user: User
  token: string
}

export interface AuthState extends Auth {
  accounts: AuthAccount[]
  activeAccountId: number | null
}
