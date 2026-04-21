import { storageEncryption } from '@/modules/core/services/StorageEncryption.ts'

interface IndexedDbEngineOptions {
  databaseName: string
  storeName: string
  version?: number
}

interface IndexedDbRecord {
  key: string
  value: string
  updatedAt: number
}

type StorageRecordMap = Record<string, string | null>

class IndexedDbEngine {
  private readonly databaseName: string
  private readonly storeName: string
  private readonly version: number
  private readonly cache = new Map<string, string>()
  private databasePromise: Promise<IDBDatabase> | null = null

  constructor ({ databaseName, storeName, version = 1 }: IndexedDbEngineOptions) {
    this.databaseName = databaseName
    this.storeName = storeName
    this.version = version
  }

  hasCachedItem (key: string): boolean {
    return this.cache.has(key)
  }

  getCachedItem (key: string): string | null {
    return this.cache.get(key) ?? null
  }

  async getItem (key: string): Promise<string | null> {
    const values = await this.getItems([key])
    return values[key] ?? null
  }

  async getItems (keys: string[]): Promise<StorageRecordMap> {
    const uniqueKeys = [...new Set(keys)]
    const values = this.createEmptyRecordMap(uniqueKeys)
    const missingKeys = this.hydrateFromCache(uniqueKeys, values)

    if (missingKeys.length === 0) {
      return values
    }

    const storedIndexedDbValues = await this.readIndexedDbRecords(missingKeys)
    const unresolvedKeys = await this.hydrateFromStoredValues(missingKeys, storedIndexedDbValues, values)

    if (unresolvedKeys.length === 0) {
      return values
    }

    const localStorageValues = this.readLocalStorageRecords(unresolvedKeys)
    await this.hydrateFromStoredValues(unresolvedKeys, localStorageValues, values)

    return values
  }

  async setItem (key: string, value: string): Promise<void> {
    await this.setItems({ [key]: value })
  }

  async setItems (entries: Record<string, string>): Promise<void> {
    const keys = Object.keys(entries)
    if (keys.length === 0) {
      return
    }

    for (const [key, value] of Object.entries(entries)) {
      this.cache.set(key, value)
    }

    const storedEntries = await this.encodeEntries(entries)
    const persisted = await this.writeIndexedDbRecords(storedEntries)

    if (persisted) {
      this.removeLocalStorageItems(keys)
      return
    }

    this.writeLocalStorageRecords(storedEntries)
  }

  async getJSON<T>(key: string): Promise<T | null> {
    const value = await this.getItem(key)
    if (!value) {
      return null
    }

    try {
      return JSON.parse(value) as T
    } catch {
      return null
    }
  }

  async setJSON (key: string, value: unknown): Promise<void> {
    await this.setItem(key, JSON.stringify(value))
  }

  async removeItem (key: string): Promise<void> {
    await this.removeItems([key])
  }

  async removeItems (keys: string[]): Promise<void> {
    const uniqueKeys = [...new Set(keys)]
    if (uniqueKeys.length === 0) {
      return
    }

    for (const key of uniqueKeys) {
      this.cache.delete(key)
    }

    this.removeLocalStorageItems(uniqueKeys)

    try {
      await this.deleteIndexedDbRecords(uniqueKeys)
    } catch {
      /* empty */
    }
  }

  private createEmptyRecordMap (keys: string[]): StorageRecordMap {
    return Object.fromEntries(keys.map(key => [key, null]))
  }

  private hydrateFromCache (keys: string[], values: StorageRecordMap): string[] {
    const missingKeys: string[] = []

    for (const key of keys) {
      const cachedValue = this.cache.get(key)
      if (cachedValue === undefined) {
        missingKeys.push(key)
        continue
      }

      values[key] = cachedValue
    }

    return missingKeys
  }

  private async hydrateFromStoredValues (
    keys: string[],
    storedValues: StorageRecordMap,
    target: StorageRecordMap,
  ): Promise<string[]> {
    const missingKeys: string[] = []
    const migrationEntries: Record<string, string> = {}

    for (const key of keys) {
      const storedValue = storedValues[key]
      if (typeof storedValue !== 'string') {
        missingKeys.push(key)
        continue
      }

      const decodedValue = await storageEncryption.decrypt(storedValue)
      if (decodedValue === null) {
        missingKeys.push(key)
        continue
      }

      this.cache.set(key, decodedValue)
      target[key] = decodedValue

      if (storageEncryption.shouldEncrypt(key) && !storageEncryption.isEncryptedPayload(storedValue)) {
        migrationEntries[key] = decodedValue
      }
    }

    if (Object.keys(migrationEntries).length > 0) {
      void this.setItems(migrationEntries)
    }

    return missingKeys
  }

  private async encodeEntries (entries: Record<string, string>): Promise<Record<string, string>> {
    const encodedEntries = await Promise.all(
      Object.entries(entries).map(async ([key, value]) => {
        return [key, await storageEncryption.encrypt(key, value)] as const
      }),
    )

    return Object.fromEntries(encodedEntries)
  }

  private async readIndexedDbRecords (keys: string[]): Promise<StorageRecordMap> {
    if (keys.length === 0) {
      return {}
    }

    try {
      return await this.withStore('readonly', (store, resolve, reject) => {
        const values = this.createEmptyRecordMap(keys)

        for (const key of keys) {
          const request = store.get(key)

          request.addEventListener('success', () => {
            const result = request.result as IndexedDbRecord | undefined
            values[key] = result?.value ?? null
          }, { once: true })

          request.addEventListener('error', () => {
            reject(request.error ?? new Error(`IndexedDB request failed for key "${key}".`))
          }, { once: true })
        }

        return {
          onComplete: () => resolve(values),
        }
      })
    } catch {
      return this.createEmptyRecordMap(keys)
    }
  }

  private async writeIndexedDbRecords (entries: Record<string, string>): Promise<boolean> {
    const records = Object.entries(entries).map(([key, value]) => ({
      key,
      value,
      updatedAt: Date.now(),
    } satisfies IndexedDbRecord))

    if (records.length === 0) {
      return true
    }

    try {
      await this.withStore('readwrite', (store, resolve, reject) => {
        for (const record of records) {
          const request = store.put(record)
          request.addEventListener('error', () => {
            reject(request.error ?? new Error(`IndexedDB request failed for key "${record.key}".`))
          }, { once: true })
        }

        return {
          onComplete: () => resolve(undefined),
        }
      })

      return true
    } catch {
      return false
    }
  }

  private async deleteIndexedDbRecords (keys: string[]): Promise<void> {
    if (keys.length === 0) {
      return
    }

    await this.withStore('readwrite', (store, resolve, reject) => {
      for (const key of keys) {
        const request = store.delete(key)
        request.addEventListener('error', () => {
          reject(request.error ?? new Error(`IndexedDB delete failed for key "${key}".`))
        }, { once: true })
      }

      return {
        onComplete: () => resolve(undefined),
      }
    })
  }

  private async withStore<T>(
    mode: IDBTransactionMode,
    handler: (
      store: IDBObjectStore,
      resolve: (value: T | PromiseLike<T>) => void,
      reject: (reason?: unknown) => void,
    ) => { onComplete?: () => void },
  ): Promise<T> {
    const database = await this.getDatabase()

    return await new Promise<T>((resolve, reject) => {
      const transaction = database.transaction(this.storeName, mode)
      const store = transaction.objectStore(this.storeName)
      const { onComplete } = handler(store, resolve, reject)

      transaction.addEventListener('complete', () => {
        onComplete?.()
      }, { once: true })

      transaction.addEventListener('error', () => {
        reject(transaction.error ?? new Error(`IndexedDB transaction failed for ${this.storeName}.`))
      }, { once: true })

      transaction.addEventListener('abort', () => {
        reject(transaction.error ?? new Error(`IndexedDB transaction aborted for ${this.storeName}.`))
      }, { once: true })
    })
  }

  private async getDatabase (): Promise<IDBDatabase> {
    if (!this.databasePromise) {
      this.databasePromise = new Promise<IDBDatabase>((resolve, reject) => {
        if (!this.isIndexedDbAvailable()) {
          this.databasePromise = null
          reject(new Error('IndexedDB is not available in this environment.'))
          return
        }

        const request = window.indexedDB.open(this.databaseName, this.version)

        request.addEventListener('upgradeneeded', () => {
          const database = request.result
          if (!database.objectStoreNames.contains(this.storeName)) {
            database.createObjectStore(this.storeName, { keyPath: 'key' })
          }
        }, { once: true })

        request.addEventListener('success', () => {
          const database = request.result
          database.addEventListener('close', () => {
            this.databasePromise = null
          }, { once: true })
          database.addEventListener('versionchange', () => {
            database.close()
            this.databasePromise = null
          }, { once: true })
          resolve(database)
        }, { once: true })

        request.addEventListener('error', () => {
          this.databasePromise = null
          reject(request.error ?? new Error(`Unable to open IndexedDB database "${this.databaseName}".`))
        }, { once: true })

        request.addEventListener('blocked', () => {
          this.databasePromise = null
          reject(new Error(`IndexedDB database "${this.databaseName}" is blocked.`))
        }, { once: true })
      })
    }

    return await this.databasePromise
  }

  private isIndexedDbAvailable (): boolean {
    return typeof window !== 'undefined' && 'indexedDB' in window
  }

  private readLocalStorageRecords (keys: string[]): StorageRecordMap {
    const values = this.createEmptyRecordMap(keys)

    if (!this.isLocalStorageAvailable()) {
      return values
    }

    for (const key of keys) {
      try {
        values[key] = window.localStorage.getItem(key)
      } catch {
        values[key] = null
      }
    }

    return values
  }

  private writeLocalStorageRecords (entries: Record<string, string>): void {
    if (!this.isLocalStorageAvailable()) {
      return
    }

    for (const [key, value] of Object.entries(entries)) {
      try {
        window.localStorage.setItem(key, value)
      } catch {
        /* empty */
      }
    }
  }

  private removeLocalStorageItems (keys: string[]): void {
    if (!this.isLocalStorageAvailable()) {
      return
    }

    for (const key of keys) {
      try {
        window.localStorage.removeItem(key)
      } catch {
        /* empty */
      }
    }
  }

  private isLocalStorageAvailable (): boolean {
    return typeof window !== 'undefined' && 'localStorage' in window
  }
}

export const indexedDbEngine = new IndexedDbEngine(
  {
    databaseName: 'forkiva',
    storeName: 'app_storage',
  },
)

export default indexedDbEngine
