import api from './index';

export interface ExpensePayload {
  amount?: number;
  category?: string;
  date?: string;
  note?: string;
}

export const expensesApi = {
  getAll(params: any = {}) {
    return api.get('/expenses', { params });
  },
  getTypes() {
    return api.get('/expenses/types/all');
  },
  create(data: ExpensePayload) {
    return api.post('/expenses', data);
  },
  update(id: number | string, data: ExpensePayload) {
    return api.put(`/expenses/${id}`, data);
  },
  delete(id: number | string) {
    return api.delete(`/expenses/${id}`);
  },
};

export default expensesApi;
