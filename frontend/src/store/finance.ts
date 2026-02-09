import { defineStore } from 'pinia';
import { financeApi } from '@/api/finance';

export interface FinanceReport {
  id?: number;
  date?: string;
  amount?: number;
  [key: string]: any;
}

export const useFinanceStore = defineStore('finance', {
  state: () => ({
    reports: [] as FinanceReport[],
    loading: false as boolean,
    error: null as string | null,
  }),
  actions: {
    async fetchMonthly(params: any = {}) {
      this.loading = true;
      this.error = null;
      try {
        const res = await financeApi.getMonthlyReport(params);
        this.reports = res.data.data || [];
      } catch (err: any) {
        this.error = err.message || 'Failed to fetch finance reports';
      } finally {
        this.loading = false;
      }
    },
  },
});

export default useFinanceStore;
