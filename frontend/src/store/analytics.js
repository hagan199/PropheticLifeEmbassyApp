
import { defineStore } from 'pinia'
import axios from 'axios' 
import { dashboardApi } from '../api'

export const useAnalyticsStore = defineStore('analytics', {
  state: () => ({
    attendanceData: {
      labels: [],
      datasets: []
    },
    visitorData: {
      labels: [],
      datasets: []
    },
    financeData: {
      trend: { labels: [], income: [], expenses: [] },
      categories: { labels: [], data: [] },
      methods: { labels: [], data: [] },
      expenses: { labels: [], data: [] }
    },
    isLoading: false,
    error: null
  }),

  actions: {
    async fetchAttendancemetrics(period = 'monthly') {
      this.isLoading = true;
      this.error = null;
      try {
        // In a real implementation, this endpoint would exist on your Laravel backend
        // const response = await axios.get(`/api/analytics/attendance?period=${period}`);
        
        // MOCK DATA for demonstration purposes
        // Replace this with actual API call result
        const mockResponse = {
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
          datasets: [
            {
              label: 'Sunday Service',
              backgroundColor: '#f87979',
              data: [120, 135, 140, 155, 160, 175]
            },
            {
              label: 'Midweek Service',
              backgroundColor: '#36A2EB',
              data: [45, 50, 48, 60, 55, 65]
            }
          ]
        };
        
        this.attendanceData = mockResponse;
      } catch (err) {
        this.error = err.message || 'Failed to load attendance metrics';
        console.error('Analytics Error:', err);
      } finally {
        this.isLoading = false;
      }
    },

    async fetchFinanceMetrics(period = 'monthly') {
      this.isLoading = true;
      try {
        // Fetch from the UPDATED DashboardController endpoint
        const response = await dashboardApi.getAnalytics({ range: period });
        const data = response.data.data;

        // Map Backend API -> Store State
        this.financeData = {
          trend: {
            labels: data.finance_trend.labels,
            income: data.finance_trend.contributions,
            expenses: data.finance_trend.expenses
          },
          categories: {
            labels: data.finance_category.labels,
            data: data.finance_category.data,
            backgroundColor: ['#198754', '#20c997', '#0d6efd', '#ffc107', '#0dcaf0'] // Predefined colors
          },
          methods: {
            labels: data.finance_method.labels,
            data: data.finance_method.data
          },
          expenses: {
            labels: data.expense_category.labels,
            data: data.expense_category.data
          }
        };
      } catch (err) {
        console.error('Finance Analytics Error:', err);
      } finally {
        this.isLoading = false;
      }
    }
  }
})
