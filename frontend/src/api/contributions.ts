import api from './index';

export interface ContributionParams {
  page?: number;
  per_page?: number;
  start_date?: string;
  end_date?: string;
}

export interface ContributionPayload {
  member_id?: number;
  amount?: number;
  date?: string;
  note?: string;
}

export const contributionsApi = {
  getAll(params: ContributionParams = {}) {
    return api.get('/contributions', { params });
  },
  get(id: number | string) {
    return api.get(`/contributions/${id}`);
  },
  create(data: ContributionPayload) {
    return api.post('/contributions', data);
  },
  update(id: number | string, data: ContributionPayload) {
    return api.put(`/contributions/${id}`, data);
  },
  delete(id: number | string) {
    return api.delete(`/contributions/${id}`);
  },
};

export default contributionsApi;
