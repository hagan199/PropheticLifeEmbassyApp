import { defineStore } from 'pinia';
import { financeApi } from '../api/finance';

export const useFinanceStore = defineStore('finance', {
  state: () => ({
    reports: [],
    loading: false,
    error: null,
  }),
  actions: {
    async fetchMonthly(params = {}) {
      this.loading = true;
      this.error = null;
      try {
        const res = await financeApi.getMonthlyReport(params);
        this.reports = res.data.data || [];
      } catch (err) {
        this.error = err.message || 'Failed to fetch finance reports';
      } finally {
        this.loading = false;
      }
    },
  },
});
