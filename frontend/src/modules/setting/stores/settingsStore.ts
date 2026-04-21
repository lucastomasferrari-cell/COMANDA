import { defineStore } from 'pinia'

export const useSettingsStore = defineStore('settings', {
  state: () => ({
    loading: false as boolean,
  }),
  actions: {
    setLoading (value: boolean) {
      this.loading = value
    },
  },
})
