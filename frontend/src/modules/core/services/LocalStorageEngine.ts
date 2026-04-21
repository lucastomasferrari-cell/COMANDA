type LocalStorageRecordMap = Record<string, string | null>

class LocalStorageEngine {
  hasItem (key: string): boolean {
    return this.getItem(key) !== null
  }

  getItem (key: string): string | null {
    if (!this.isAvailable()) {
      return null
    }

    try {
      return window.localStorage.getItem(key)
    } catch {
      return null
    }
  }

  getItems (keys: string[]): LocalStorageRecordMap {
    const uniqueKeys = [...new Set(keys)]
    const values: LocalStorageRecordMap = {}

    for (const key of uniqueKeys) {
      values[key] = this.getItem(key)
    }

    return values
  }

  setItem (key: string, value: string): void {
    if (!this.isAvailable()) {
      return
    }

    try {
      window.localStorage.setItem(key, value)
    } catch {
      /* empty */
    }
  }

  setItems (entries: Record<string, string>): void {
    for (const [key, value] of Object.entries(entries)) {
      this.setItem(key, value)
    }
  }

  removeItem (key: string): void {
    if (!this.isAvailable()) {
      return
    }

    try {
      window.localStorage.removeItem(key)
    } catch {
      /* empty */
    }
  }

  removeItems (keys: string[]): void {
    const uniqueKeys = [...new Set(keys)]

    for (const key of uniqueKeys) {
      this.removeItem(key)
    }
  }

  private isAvailable (): boolean {
    return typeof window !== 'undefined' && 'localStorage' in window
  }
}

export const localStorageEngine = new LocalStorageEngine()

export default localStorageEngine
