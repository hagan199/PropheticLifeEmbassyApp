import { useQuery } from '@tanstack/vue-query';
import { attendanceApi } from '../api/attendance';

export function useAttendanceAnalytics() {
  // Fetch attendance data using Vue Query
  const query = useQuery(['attendance-analytics'], async () => {
    const response = await attendanceApi.getAll();
    // Transform to chart.js format if needed
    const records = response.data.data || [];
    const labels = records.map(r => r.service_date);
    const sundayData = records.filter(r => r.service_type === 'Sunday').map(r => r.count);
    const midweekData = records.filter(r => r.service_type === 'Wednesday').map(r => r.count);
    return {
      labels,
      datasets: [
        {
          label: 'Sunday Service',
          backgroundColor: '#f87979',
          data: sundayData,
        },
        {
          label: 'Midweek Service',
          backgroundColor: '#36A2EB',
          data: midweekData,
        },
      ],
    };
  });
  return query;
}
