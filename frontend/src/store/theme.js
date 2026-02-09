import { defineStore } from 'pinia';

export const useThemeStore = defineStore('theme', {
  state: () => ({
    mode: localStorage.getItem('theme') || 'light',
  }),
  actions: {
    setMode(mode) {
      this.mode = mode;
      localStorage.setItem('theme', mode);
      document.documentElement.setAttribute('data-theme', mode);
    },
    toggle() {
      this.setMode(this.mode === 'dark' ? 'light' : 'dark');
    },
    toggleMode() {
      this.toggle();
    },
    init() {
      document.documentElement.setAttribute('data-theme', this.mode);
    },
  },
});
