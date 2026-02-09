import api from './index';

export const financeApi = {
  getOverview() {
    return api.get('/finance/overview');
  },
  getContributions(params: any = {}) {
    return api.get('/contributions', { params });
  },
};

export default financeApi;
