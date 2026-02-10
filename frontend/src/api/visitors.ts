import api from './index'

export interface VisitorParams {
  page?: number
  per_page?: number
  search?: string
  date_from?: string
  date_to?: string
}

export interface VisitorPayload {
  name?: string
  phone?: string
  category?: string
  service_type?: string
  occupation?: string
  date?: string
}

export const visitorsApi = {
  getAll(params: VisitorParams = {}) {
    return api.get('/visitors', { params })
  },
  get(id: number | string) {
    return api.get(`/visitors/${id}`)
  },
  create(data: VisitorPayload) {
    return api.post('/visitors', data)
  },
  update(id: number | string, data: VisitorPayload) {
    return api.put(`/visitors/${id}`, data)
  },
  delete(id: number | string) {
    return api.delete(`/visitors/${id}`)
  },
  getFollowUps(id: number | string, params: any = {}) {
    return api.get(`/visitors/${id}/follow-ups`, { params })
  }
  ,
  convert(id: number | string, data: any = {}) {
    return api.post(`/visitors/${id}/convert`, data)
  }
}

export default visitorsApi
