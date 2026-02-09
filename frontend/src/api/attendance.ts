import api from './index';

export interface AttendanceParams {
  page?: number;
  per_page?: number;
  date?: string;
}

export interface AttendancePayload {
  member_id?: number;
  status?: string;
  date?: string;
}

export const attendanceApi = {
  getAll(params: AttendanceParams = {}) {
    return api.get('/attendance', { params });
  },
  get(id: number | string) {
    return api.get(`/attendance/${id}`);
  },
  create(data: AttendancePayload) {
    return api.post('/attendance', data);
  },
  update(id: number | string, data: AttendancePayload) {
    return api.put(`/attendance/${id}`, data);
  },
  delete(id: number | string) {
    return api.delete(`/attendance/${id}`);
  },
};

export default attendanceApi;
