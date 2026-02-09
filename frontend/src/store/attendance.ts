import { defineStore } from 'pinia';
import { attendanceApi } from '@/api/attendance';

export interface AttendanceRecord {
  id?: number;
  user_id?: number;
  date?: string;
  status?: string;
  [key: string]: any;
}

export const useAttendanceStore = defineStore('attendance', {
  state: () => ({
    records: [] as AttendanceRecord[],
    loading: false as boolean,
    error: null as string | null,
  }),
  actions: {
    async fetchAll(params: any = {}) {
      this.loading = true;
      this.error = null;
      try {
        const res = await attendanceApi.getAll(params);
        this.records = res.data.data || [];
      } catch (err: any) {
        this.error = err.message || 'Failed to fetch attendance records';
      } finally {
        this.loading = false;
      }
    },
    async fetchOne(id: number | string) {
      this.loading = true;
      this.error = null;
      try {
        const res = await attendanceApi.get(id);
        return res.data.data as AttendanceRecord;
      } catch (err: any) {
        this.error = err.message || 'Failed to fetch attendance record';
        return null;
      } finally {
        this.loading = false;
      }
    },
  },
});

export default useAttendanceStore;
