import { defineStore } from 'pinia';
import { auditLogsApi } from '@/api/auditLogs';

export interface AuditLogSummary {
  id?: number;
  action?: string;
  user_id?: number;
  model_type?: string;
  model_id?: number;
  created_at?: string;
  [key: string]: any;
}

export const useAuditLogsStore = defineStore('auditLogs', {
  state: () => ({
    logs: [] as AuditLogSummary[],
    loading: false as boolean,
    error: null as string | null,
  }),
  actions: {
    async fetchAll(params: any = {}) {
      this.loading = true;
      this.error = null;
      try {
        const res = await auditLogsApi.getAll(params);
        this.logs = res.data.data || [];
      } catch (err: any) {
        this.error = err.message || 'Failed to fetch audit logs';
      } finally {
        this.loading = false;
      }
    },
    async fetchOne(id: number | string) {
      this.loading = true;
      this.error = null;
      try {
        const res = await auditLogsApi.get(id);
        return res.data.data;
      } catch (err: any) {
        this.error = err.message || 'Failed to fetch audit log';
        return null;
      } finally {
        this.loading = false;
      }
    },
  },
});

export default useAuditLogsStore;
