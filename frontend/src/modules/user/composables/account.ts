import { me, updatePassword, updateProfile } from '@/modules/user/api/account.api.ts'

export function useAccount () {
  return { me, updateProfile, updatePassword }
}
