import { defineStore } from 'pinia';
import { authApi } from '@/api/auth';
import { updateApiToken } from '@/api';
import type { LoginPayload } from '@/api/auth';

export interface User {
  id?: number;
  name?: string;
  email?: string;
  phone?: string;
  role?: string;
  [key: string]: any;
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: (JSON.parse(localStorage.getItem('auth_user') || 'null') as User) || null,
    token: (localStorage.getItem('auth_token') as string) || null,
    isLoading: false as boolean,
    error: null as string | null,
  }),

  getters: {
    isAuthenticated: state => !!state.token && !!state.user,
    userRole: state => (state.user ? state.user.role : null),
    userName: state => (state.user ? state.user.name : ''),
    userInitials: state => {
      const name = state.user?.name || 'U';
      return name
        .split(' ')
        .map(w => w[0])
        .join('')
        .substring(0, 2)
        .toUpperCase();
    },
    hasRole: state => (roles: string | string[]) => {
      if (!state.user?.role) return false;
      if (typeof roles === 'string') return state.user.role === roles;
      return (roles as string[]).includes(state.user.role);
    },
  },

  actions: {
    async signIn(phoneOrPayload: string | LoginPayload, password?: string) {
      this.isLoading = true;
      this.error = null;
      try {
        const response = await authApi.login(phoneOrPayload as any, password as any);
        const data = response.data;
        if (data.success) {
          this.setAuthData(data.data.token, data.data.user);
          return { success: true };
        }
        return { success: false, message: data.message || 'Login failed' };
      } catch (error: any) {
        const message = error?.response?.data?.message || 'Connection error. Please try again.';
        this.error = message;
        return { success: false, message };
      } finally {
        this.isLoading = false;
      }
    },

    async logout() {
      try {
        if (this.token) await authApi.logout();
      } catch (e) {
        console.warn('Logout API error:', e);
      } finally {
        this.clearAuthData();
      }
    },

    async fetchUser() {
      if (!this.token) return { success: false, message: 'Not authenticated' };
      try {
        const response = await authApi.getUser();
        if (response.data.success) {
          this.user = response.data.data.user;
          localStorage.setItem('auth_user', JSON.stringify(this.user));
          return { success: true };
        }
        return { success: false };
      } catch (error) {
        this.clearAuthData();
        return { success: false, message: 'Session expired' };
      }
    },

    initAuth() {
      const token = localStorage.getItem('auth_token');
      const user = localStorage.getItem('auth_user');
      if (token && user) {
        this.token = token;
        this.user = JSON.parse(user);
        updateApiToken(token);
      }
    },

    setAuthData(token: string, user: User) {
      this.token = token;
      this.user = user;
      localStorage.setItem('auth_token', token);
      localStorage.setItem('auth_user', JSON.stringify(user));
      updateApiToken(token);
    },

    clearAuthData() {
      this.user = null;
      this.token = null;
      this.error = null;
      localStorage.removeItem('auth_token');
      localStorage.removeItem('auth_user');
      updateApiToken(null);
    },
  },
});

export default useAuthStore;
