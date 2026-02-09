import api from './index';

export interface UserParams {
  page?: number;
  per_page?: number;
  search?: string;
  role?: string;
  is_active?: boolean;
  department_id?: number;
}

export interface UserPayload {
  name?: string;
  email?: string;
  phone?: string;
  password?: string;
  role?: string;
  role_ids?: number[] | string | any;
  department_id?: number;
}

/**
 * Users API endpoints (TypeScript)
 */
export const usersApi = {
  getAll(params: UserParams = {}) {
    return api.get('/users', { params });
  },

  get(id: number | string) {
    return api.get(`/users/${id}`);
  },

  create(data: UserPayload) {
    return api.post('/users', data);
  },

  update(id: number | string, data: UserPayload) {
    return api.put(`/users/${id}`, data);
  },

  delete(id: number | string) {
    return api.delete(`/users/${id}`);
  },

  deactivate(id: number | string, payload: any = {}) {
    return api.post(`/users/${id}/deactivate`, payload);
  },

  reactivate(id: number | string) {
    return api.post(`/users/${id}/reactivate`);
  },
};

export default usersApi;
