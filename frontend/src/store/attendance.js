import { defineStore } from 'pinia';
import { attendanceApi } from '../api/attendance';

export const useAttendanceStore = defineStore('attendance', {
  state: () => ({
    records: [],
    loading: false,
    error: null,
  }),
  actions: {
    async fetchAll(params = {}) {
      this.loading = true;
      this.error = null;
      try {
        const res = await attendanceApi.getAll(params);
        this.records = res.data.data || [];
      } catch (err) {
        this.error = err.message || 'Failed to fetch attendance records';
      } finally {
        this.loading = false;
      }
    },
    async fetchOne(id) {
      this.loading = true;
      this.error = null;
      try {
        const res = await attendanceApi.get(id);
        return res.data.data;
      } catch (err) {
        this.error = err.message || 'Failed to fetch attendance record';
        return null;
      } finally {
        this.loading = false;
      }
    },
  },
});
