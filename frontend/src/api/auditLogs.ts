import api from './index';

export interface AuditLogsParams {
  page?: number;
  per_page?: number;
  q?: string;
}

export const auditLogsApi = {
  getAll(params: AuditLogsParams = {}) {
    return api.get('/audit-logs', { params });
  },
  get(id: number | string) {
    return api.get(`/audit-logs/${id}`);
  },
};

export default auditLogsApi;
