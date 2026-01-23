import { defineStore } from 'pinia'

export const useThemeStore = defineStore('theme', {
  state: () => ({
    mode: localStorage.getItem('theme') || 'light'
  }),
  actions: {
    setMode(mode) {
      this.mode = mode
      localStorage.setItem('theme', mode)
    },
    toggle() {
      this.setMode(this.mode === 'dark' ? 'light' : 'dark')
    }
  }
})
