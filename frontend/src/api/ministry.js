import { departmentsApi } from './departments.js'
import { membersApi } from './members.js'

export const ministryApi = {
  getUnits: async () => {
    // Fetch all departments as ministry units
    const res = await departmentsApi.getAll()
    // Assuming API returns { data: [...] }
    return { data: res.data?.data || [] }
  },
  getUnitMembers: async (unitId) => {
    // Fetch all members for a given department (unit)
    const res = await membersApi.getAll({ department_id: unitId })
    return { data: res.data?.data || [] }
  }
}
