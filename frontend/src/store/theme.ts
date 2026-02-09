import { defineStore } from 'pinia';

export const useThemeStore = defineStore('theme', {
  state: () => ({
    mode: (localStorage.getItem('theme') || 'light') as string,
  }),
  actions: {
    setMode(mode: string) {
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

export default useThemeStore;
