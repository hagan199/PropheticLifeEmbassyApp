import api from './index';

export const rolesApi = {
  getAll() {
    return api.get('/roles');
  },
  getPermissions(role: string | number) {
    return api.get(`/roles/${role}/permissions`);
  },
  updatePermissions(role: string | number, permissions: string[]) {
    return api.put(`/roles/${role}/permissions`, { permissions });
  },
};

export default rolesApi;
