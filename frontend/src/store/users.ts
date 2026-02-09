import { defineStore } from 'pinia';
import { usersApi } from '@/api/users';

export interface UserSummary {
  id?: number;
  name?: string;
  email?: string;
  phone?: string;
  role?: string;
  [key: string]: any;
}

export const useUsersStore = defineStore('users', {
  state: () => ({
    users: [] as UserSummary[],
    loading: false as boolean,
    error: null as string | null,
  }),
  actions: {
    async fetchAll(params: any = {}) {
      this.loading = true;
      this.error = null;
      try {
        const res = await usersApi.getAll(params);
        this.users = res.data.data || [];
      } catch (err: any) {
        this.error = err.message || 'Failed to fetch users';
      } finally {
        this.loading = false;
      }
    },
    async fetchOne(id: number | string) {
      this.loading = true;
      this.error = null;
      try {
        const res = await usersApi.get(id);
        return res.data.data;
      } catch (err: any) {
        this.error = err.message || 'Failed to fetch user';
        return null;
      } finally {
        this.loading = false;
      }
    },
  },
});

export default useUsersStore;
