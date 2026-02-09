import { defineStore } from 'pinia';
import { dashboardApi } from '@/api';

type ChartDataset = any;

export interface AnalyticsState {
  attendanceData: { labels: string[]; datasets: ChartDataset[] };
  visitorData: { labels: string[]; datasets: ChartDataset[] };
  financeData: any;
  isLoading: boolean;
  error: string | null;
}

export const useAnalyticsStore = defineStore('analytics', {
  state: (): AnalyticsState => ({
    attendanceData: { labels: [], datasets: [] },
    visitorData: { labels: [], datasets: [] },
    financeData: {
      trend: { labels: [], income: [], expenses: [] },
      categories: { labels: [], data: [] },
      methods: { labels: [], data: [] },
      expenses: { labels: [], data: [] },
    },
    isLoading: false,
    error: null,
  }),
  actions: {
    async fetchAttendancemetrics(period = 'monthly') {
      this.isLoading = true;
      this.error = null;
      try {
        const response = await dashboardApi.analytics({ range: period });
        const data = response.data.data || {};

        this.attendanceData = {
          labels: data.attendance_trend?.labels || [],
          datasets: [
            {
              label: 'Attendance %',
              backgroundColor: 'rgba(13,110,253,.2)',
              borderColor: '#0d6efd',
              data: data.attendance_trend?.data || [],
              tension: 0.35,
              fill: true,
            },
          ],
        };
      } catch (err: any) {
        this.error = err.message || 'Failed to load attendance metrics';
        // keep console for debugging
        // eslint-disable-next-line no-console
        console.error('Analytics Error:', err);
      } finally {
        this.isLoading = false;
      }
    },

    async fetchFinanceMetrics(period = 'monthly') {
      this.isLoading = true;
      this.error = null;
      try {
        const response = await dashboardApi.analytics({ range: period });
        const data = response.data.data || {};

        this.financeData = {
          trend: {
            labels: data.finance_trend?.labels || [],
            income: data.finance_trend?.contributions || [],
            expenses: data.finance_trend?.expenses || [],
          },
          categories: {
            labels: data.finance_category?.labels || [],
            data: data.finance_category?.data || [],
            backgroundColor: ['#198754', '#20c997', '#0d6efd', '#ffc107', '#0dcaf0'],
          },
          methods: {
            labels: data.finance_method?.labels || [],
            data: data.finance_method?.data || [],
          },
          expenses: {
            labels: data.expense_category?.labels || [],
            data: data.expense_category?.data || [],
          },
        };
      } catch (err: any) {
        // eslint-disable-next-line no-console
        console.error('Finance Analytics Error:', err);
      } finally {
        this.isLoading = false;
      }
    },
  },
});

export default useAnalyticsStore;
