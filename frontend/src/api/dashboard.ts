import api from './index';

export const dashboardApi = {
  stats() {
    return api.get('/dashboard/stats');
  },
  analytics(params: any = {}) {
    return api.get('/dashboard/analytics', { params });
  },
};

export default dashboardApi;
