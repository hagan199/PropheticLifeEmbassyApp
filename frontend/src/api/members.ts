import api from './index';

export const membersApi = {
  getAll(params: any = {}) {
    return api.get('/members', { params });
  },
  get(id: number | string) {
    return api.get(`/members/${id}`);
  },
};

export default membersApi;
