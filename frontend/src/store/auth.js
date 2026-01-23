import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null
  }),
  actions: {
    async signIn(email, password) {
      await new Promise(r => setTimeout(r, 700))
      if (email && password && password.length >= 4) {
        this.user = {
          email,
          name: 'Admin User',
          role: 'Super Admin',
          avatar: 'https://i.pravatar.cc/80?img=12'
        }
        this.token = 'demo'
        localStorage.setItem('token', this.token)
        localStorage.setItem('user', JSON.stringify(this.user))
        return true
      }
      return false
    },
    signOut() {
      this.user = null
      this.token = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  }
})
