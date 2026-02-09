import api from './index'

export interface VisitorType {
  id: number
  name: string
}

export const visitorTypesApi = {
  getAll() {
    return api.get('/visitor-types')
  },
  create(data: { name: string }) {
    return api.post('/visitor-types', data)
  },
  update(id: number | string, data: { name: string }) {
    return api.put(`/visitor-types/${id}`, data)
  },
  delete(id: number | string) {
    return api.delete(`/visitor-types/${id}`)
  },
}

export default visitorTypesApi
