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
  convert(id: number | string, data: any = {}) {
    return api.post(`/follow-ups/${id}/convert`, data);
  },
};

export default followUpsApi;
