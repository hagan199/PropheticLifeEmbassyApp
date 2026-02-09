import { departmentsApi } from './departments';

export const ministryApi = {
  getDepartments() {
    return departmentsApi.getAll();
  },
  getMembers(unitId: number | string) {
    return departmentsApi.getMembers(unitId);
  },
};

export default ministryApi;
