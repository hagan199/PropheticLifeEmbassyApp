import api from './index';

export interface DepartmentParams {
  page?: number;
  per_page?: number;
}

export const departmentsApi = {
  getAll(params: DepartmentParams = {}) {
    return api.get('/departments', { params });
  },
  get(id: number | string) {
    return api.get(`/departments/${id}`);
  },
  create(data: any) {
    return api.post('/departments', data);
  },
  update(id: number | string, data: any) {
    return api.put(`/departments/${id}`, data);
  },
  delete(id: number | string) {
    return api.delete(`/departments/${id}`);
  },
  getMembers(unitId: number | string) {
    return api.get(`/departments/${unitId}/members`);
  },
};

export default departmentsApi;
