import { createFolder, destroy, get, update } from '@/modules/media/api/media.api.ts'

export function useMedia () {
  return { get, update, destroy, createFolder }
}
