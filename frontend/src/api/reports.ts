import api from './index';

// Helper: try multiple GET paths and return the first successful response
async function tryGet(paths: string[], filters: any = {}) {
  let lastErr: any = null;
  for (const p of paths) {
    try {
      return await api.get(p, { params: filters });
    } catch (err) {
      lastErr = err;
      // If 404, try next path; for other errors, rethrow immediately
      if (err?.response?.status && err.response.status !== 404) throw err;
    }
  }
  // all failed
  throw lastErr;
}

export const reportsApi = {
  visitors: {
    // Try a few likely route variants in case backend uses different naming
    bySource(filters: any = {}) {
      return tryGet([
        '/reports/visitors/source',
        '/reports/visitors/sources',
        '/reports/visitors/by-source',
      ], filters);
    },
    conversionFunnel(filters: any = {}) {
      return tryGet([
        '/reports/visitors/funnel',
        '/reports/visitors/funnels',
        '/reports/visitors/conversion-funnel',
        '/reports/visitors/conversion',
      ], filters);
    },
    // Summary/overview for visitors
    summary(filters: any = {}) {
      return tryGet([
        '/reports/visitors/summary',
        '/reports/visitors/overview',
        '/reports/visitors/stats',
      ], filters);
    },
  },
  finance: {
    summary(filters: any = {}) {
      return tryGet([
        '/reports/finance/summary',
        '/reports/finance/overview',
      ], filters);
    },
  },
  membership: {
    summary(filters: any = {}) {
      return tryGet([
        '/reports/members/summary',
        '/reports/members/overview',
        '/reports/membership/summary',
      ], filters);
    },
  },
  attendance: {
    weekly(filters: any = {}) {
      return tryGet([
        '/reports/attendance/weekly',
        '/reports/attendance/week',
      ], filters);
    },
    // Summary / overview endpoint (may not exist on every backend)
    summary(filters: any = {}) {
      return tryGet([
        '/reports/attendance/summary',
        '/reports/attendance/overview',
      ], filters);
    },
  },
  charts: {
    // Attendance trend (chart) endpoint - try sensible fallbacks
    attendanceTrend(filters: any = {}) {
      return tryGet([
        '/reports/charts/attendance-trend',
        '/reports/attendance/trend',
        '/reports/attendance/weekly',
      ], filters);
    },
  },
};

export default reportsApi;
