/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_API_URL: string
  readonly VITE_STORAGE_ENCRYPTION_KEY?: string
  readonly VITE_STORAGE_ENCRYPT_ALL?: string
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}
