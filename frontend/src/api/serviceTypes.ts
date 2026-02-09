import api from './index'

export interface ServiceType {
  id: number
  name: string
}

export const serviceTypesApi = {
  getAll() {
    return api.get('/service-types')
  },
  create(data: { name: string }) {
    return api.post('/service-types', data)
  },
  update(id: number | string, data: { name: string }) {
    return api.put(`/service-types/${id}`, data)
  },
  delete(id: number | string) {
    return api.delete(`/service-types/${id}`)
  },
}

export default serviceTypesApi
