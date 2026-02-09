import api from './index';

export const followUpsApi = {
  getAll(params: any = {}) {
    return api.get('/follow-ups', { params });
  },
  create(data: any) {
    return api.post('/follow-ups', data);
  },
  getDue(params: any = {}) {
    return api.get('/follow-ups/due', { params });
  },
};

export default followUpsApi;
