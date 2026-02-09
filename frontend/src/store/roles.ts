import { defineStore } from 'pinia';
import { rolesApi } from '@/api/roles';

export interface RoleSummary {
  id?: number;
  name?: string;
  permissions?: string[];
  [key: string]: any;
}

export const useRolesStore = defineStore('roles', {
  state: () => ({
    roles: [] as RoleSummary[],
    loading: false as boolean,
    error: null as string | null,
  }),
  actions: {
    async fetchAll(params: any = {}) {
      this.loading = true;
      this.error = null;
      try {
        const res = await rolesApi.getAll(params);
        this.roles = res.data.data || [];
      } catch (err: any) {
        this.error = err.message || 'Failed to fetch roles';
      } finally {
        this.loading = false;
      }
    },
    async fetchOne(id: number | string) {
      this.loading = true;
      this.error = null;
      try {
        const res = await rolesApi.get(id);
        return res.data.data as RoleSummary;
      } catch (err: any) {
        this.error = err.message || 'Failed to fetch role';
        return null;
      } finally {
        this.loading = false;
      }
    },
  },
});

export default useRolesStore;
