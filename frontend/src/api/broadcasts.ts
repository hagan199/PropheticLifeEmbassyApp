import api from './index';

export interface BroadcastParams {
  page?: number;
  per_page?: number;
}

export interface BroadcastPayload {
  title?: string;
  message?: string;
  channels?: string[];
}

export const broadcastsApi = {
  getAll(params: BroadcastParams = {}) {
    return api.get('/broadcasts', { params });
  },
  get(id: number | string) {
    return api.get(`/broadcasts/${id}`);
  },
  create(data: BroadcastPayload) {
    return api.post('/broadcasts', data);
  },
  update(id: number | string, data: BroadcastPayload) {
    return api.put(`/broadcasts/${id}`, data);
  },
  delete(id: number | string) {
    return api.delete(`/broadcasts/${id}`);
  },
};

export default broadcastsApi;
