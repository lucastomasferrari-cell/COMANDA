const ENCRYPTED_PAYLOAD_PREFIX = '__forkiva_encrypted__:'
const ENCRYPTED_PAYLOAD_VERSION = 1
const KEY_DERIVATION_ITERATIONS = 250_000
const AES_KEY_LENGTH = 256
const SALT_BYTES = 16
const IV_BYTES = 12
const BASE64_CHUNK_SIZE = 32_768

const textEncoder = new TextEncoder()
const textDecoder = new TextDecoder()

const SENSITIVE_STORAGE_KEYS = new Set([
  'auth_accounts',
  'auth_active_account_id',
  'token',
  'user',
])

interface EncryptedPayload {
  version: typeof ENCRYPTED_PAYLOAD_VERSION
  salt: string
  iv: string
  cipherText: string
}

interface StorageEncryptionConfig {
  secret: string
  encryptAll: boolean
}

function createStorageEncryptionConfig (): StorageEncryptionConfig {
  return {
    secret: import.meta.env.VITE_STORAGE_ENCRYPTION_KEY?.trim() || '',
    encryptAll: import.meta.env.VITE_STORAGE_ENCRYPT_ALL === 'true',
  }
}

class StorageEncryption {
  private readonly secret: string
  private readonly encryptAll: boolean
  private baseKeyPromise: Promise<CryptoKey> | null = null

  constructor ({ secret, encryptAll }: StorageEncryptionConfig) {
    this.secret = secret
    this.encryptAll = encryptAll
  }

  isEnabled (): boolean {
    return this.secret.length > 0 && this.getCrypto() !== null
  }

  shouldEncrypt (key: string): boolean {
    return this.isEnabled() && (this.encryptAll || SENSITIVE_STORAGE_KEYS.has(key))
  }

  isEncryptedPayload (value: string): boolean {
    return value.startsWith(ENCRYPTED_PAYLOAD_PREFIX)
  }

  async encrypt (key: string, plainText: string): Promise<string> {
    if (!this.shouldEncrypt(key)) {
      return plainText
    }

    const cryptoApi = this.getRequiredCrypto()
    const salt = cryptoApi.getRandomValues(new Uint8Array(SALT_BYTES))
    const iv = cryptoApi.getRandomValues(new Uint8Array(IV_BYTES))
    const derivedKey = await this.deriveKey(salt)
    const cipherText = await cryptoApi.subtle.encrypt(
      { name: 'AES-GCM', iv: toArrayBuffer(iv) },
      derivedKey,
      toArrayBuffer(textEncoder.encode(plainText)),
    )

    return this.serialize({
      version: ENCRYPTED_PAYLOAD_VERSION,
      salt: toBase64(salt),
      iv: toBase64(iv),
      cipherText: toBase64(new Uint8Array(cipherText)),
    })
  }

  async decrypt (value: string): Promise<string | null> {
    if (!this.isEncryptedPayload(value)) {
      return value
    }

    if (!this.isEnabled()) {
      return null
    }

    const payload = this.deserialize(value)
    if (!payload) {
      return null
    }

    try {
      const cryptoApi = this.getRequiredCrypto()
      const derivedKey = await this.deriveKey(fromBase64(payload.salt))
      const plainText = await cryptoApi.subtle.decrypt(
        { name: 'AES-GCM', iv: toArrayBuffer(fromBase64(payload.iv)) },
        derivedKey,
        toArrayBuffer(fromBase64(payload.cipherText)),
      )

      return textDecoder.decode(plainText)
    } catch {
      return null
    }
  }

  private async deriveKey (salt: Uint8Array): Promise<CryptoKey> {
    const cryptoApi = this.getRequiredCrypto()
    const baseKey = await this.getBaseKey()

    return await cryptoApi.subtle.deriveKey(
      {
        name: 'PBKDF2',
        hash: 'SHA-256',
        iterations: KEY_DERIVATION_ITERATIONS,
        salt: toArrayBuffer(salt),
      },
      baseKey,
      { name: 'AES-GCM', length: AES_KEY_LENGTH },
      false,
      ['encrypt', 'decrypt'],
    )
  }

  private async getBaseKey (): Promise<CryptoKey> {
    if (!this.baseKeyPromise) {
      const cryptoApi = this.getRequiredCrypto()
      this.baseKeyPromise = cryptoApi.subtle.importKey(
        'raw',
        textEncoder.encode(this.secret),
        'PBKDF2',
        false,
        ['deriveKey'],
      ).catch(error => {
        this.baseKeyPromise = null
        throw error
      })
    }

    return this.baseKeyPromise
  }

  private serialize (payload: EncryptedPayload): string {
    return `${ENCRYPTED_PAYLOAD_PREFIX}${JSON.stringify(payload)}`
  }

  private deserialize (value: string): EncryptedPayload | null {
    try {
      const payload = JSON.parse(value.slice(ENCRYPTED_PAYLOAD_PREFIX.length)) as Partial<EncryptedPayload>

      if (
        payload.version !== ENCRYPTED_PAYLOAD_VERSION
        || typeof payload.salt !== 'string'
        || typeof payload.iv !== 'string'
        || typeof payload.cipherText !== 'string'
      ) {
        return null
      }

      return payload as EncryptedPayload
    } catch {
      return null
    }
  }

  private getCrypto (): Crypto | null {
    if (
      typeof globalThis === 'undefined'
      || globalThis.crypto === undefined
      || globalThis.crypto.subtle === undefined
    ) {
      return null
    }

    return globalThis.crypto
  }

  private getRequiredCrypto (): Crypto {
    const cryptoApi = this.getCrypto()
    if (!cryptoApi) {
      throw new Error('Web Crypto is not available in this environment.')
    }

    return cryptoApi
  }
}

function toBase64 (bytes: Uint8Array): string {
  let binary = ''

  for (let index = 0; index < bytes.length; index += BASE64_CHUNK_SIZE) {
    const chunk = bytes.subarray(index, index + BASE64_CHUNK_SIZE)
    binary += String.fromCodePoint(...chunk)
  }

  return btoa(binary)
}

function fromBase64 (value: string): Uint8Array {
  const binary = atob(value)
  const bytes = new Uint8Array(binary.length)

  for (let index = 0; index < binary.length; index++) {
    bytes[index] = <number>binary.codePointAt(index)
  }

  return bytes
}

function toArrayBuffer (bytes: Uint8Array): ArrayBuffer {
  return bytes.buffer.slice(bytes.byteOffset, bytes.byteOffset + bytes.byteLength) as ArrayBuffer
}

export const storageEncryption = new StorageEncryption(createStorageEncryptionConfig())

export default storageEncryption
