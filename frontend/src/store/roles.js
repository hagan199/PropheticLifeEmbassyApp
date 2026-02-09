import { defineStore } from 'pinia';
import { rolesApi } from '../api/roles';

export const useRolesStore = defineStore('roles', {
  state: () => ({
    roles: [],
    loading: false,
    error: null,
  }),
  actions: {
    async fetchAll(params = {}) {
      this.loading = true;
      this.error = null;
      try {
        const res = await rolesApi.getAll(params);
        this.roles = res.data.data || [];
      } catch (err) {
        this.error = err.message || 'Failed to fetch roles';
      } finally {
        this.loading = false;
      }
    },
    async fetchOne(id) {
      this.loading = true;
      this.error = null;
      try {
        const res = await rolesApi.get(id);
        return res.data.data;
      } catch (err) {
        this.error = err.message || 'Failed to fetch role';
        return null;
      } finally {
        this.loading = false;
      }
    },
  },
});
