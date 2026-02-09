import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from './useToast';
import { useAuthStore } from '@/store/auth';

/**
 * Session timeout composable
 * Handles automatic logout after inactivity
 *
 * @param {Object} options - Configuration options
 * @param {number} options.timeoutMinutes - Minutes of inactivity before logout (default: 120)
 * @param {number} options.warningMinutes - Minutes before timeout to show warning (default: 5)
 * @returns {Object} - Session timeout state and methods
 */
export function useSessionTimeout(options = {}) {
  const {
    timeoutMinutes = 120, // 2 hours
    warningMinutes = 5, // 5 minutes warning
  } = options;

  const router = useRouter();
  const toast = useToast();
  const authStore = useAuthStore();

  const lastActivity = ref(Date.now());
  const isWarningShown = ref(false);
  const warningCountdown = ref(0);

  let activityCheckInterval = null;
  let warningInterval = null;
  let warningToastId = null;

  const TIMEOUT_MS = timeoutMinutes * 60 * 1000;
  const WARNING_MS = warningMinutes * 60 * 1000;
  const CHECK_INTERVAL_MS = 30 * 1000; // Check every 30 seconds

  /**
   * Update last activity timestamp
   */
  const updateActivity = () => {
    lastActivity.value = Date.now();

    // Reset warning if user becomes active again
    if (isWarningShown.value) {
      isWarningShown.value = false;
      clearInterval(warningInterval);
      warningInterval = null;

      // Dismiss warning toast if it exists
      if (warningToastId) {
        toast.dismiss(warningToastId);
        warningToastId = null;
      }
    }
  };

  /**
   * Show warning before auto-logout
   */
  const showWarning = () => {
    if (isWarningShown.value) return;

    isWarningShown.value = true;
    warningCountdown.value = Math.ceil(WARNING_MS / 1000); // seconds

    // Update countdown every second
    warningInterval = setInterval(() => {
      warningCountdown.value--;

      if (warningCountdown.value <= 0) {
        clearInterval(warningInterval);
        warningInterval = null;
      }
    }, 1000);

    // Show toast warning (5 minutes = 300000ms)
    warningToastId = toast.warning(
      `⏰ Your session will expire in ${warningMinutes} minutes due to inactivity. Click anywhere to stay logged in.`,
      WARNING_MS
    );
  };

  /**
   * Auto-logout user
   */
  const autoLogout = async () => {
    toast.error('Your session has expired due to inactivity. Please log in again.', 5000);

    // Clear intervals
    if (activityCheckInterval) {
      clearInterval(activityCheckInterval);
      activityCheckInterval = null;
    }
    if (warningInterval) {
      clearInterval(warningInterval);
      warningInterval = null;
    }

    // Logout
    await authStore.logout();
    router.push('/signin');
  };

  /**
   * Check if user has been inactive
   */
  const checkInactivity = () => {
    const now = Date.now();
    const inactiveTime = now - lastActivity.value;

    // If inactive time exceeds timeout, logout
    if (inactiveTime >= TIMEOUT_MS) {
      autoLogout();
      return;
    }

    // If approaching timeout, show warning
    const timeUntilTimeout = TIMEOUT_MS - inactiveTime;
    if (timeUntilTimeout <= WARNING_MS && !isWarningShown.value) {
      showWarning();
    }
  };

  /**
   * Start monitoring user activity
   */
  const startMonitoring = () => {
    // Only monitor if user is authenticated
    if (!authStore.isAuthenticated) {
      return;
    }

    // Activity events to track
    const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];

    // Add event listeners
    events.forEach(event => {
      document.addEventListener(event, updateActivity, { passive: true });
    });

    // Start checking for inactivity
    activityCheckInterval = setInterval(checkInactivity, CHECK_INTERVAL_MS);

    // Initial activity update
    updateActivity();
  };

  /**
   * Stop monitoring user activity
   */
  const stopMonitoring = () => {
    // Remove event listeners
    const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];

    events.forEach(event => {
      document.removeEventListener(event, updateActivity);
    });

    // Clear intervals
    if (activityCheckInterval) {
      clearInterval(activityCheckInterval);
      activityCheckInterval = null;
    }
    if (warningInterval) {
      clearInterval(warningInterval);
      warningInterval = null;
    }
  };

  /**
   * Extend session manually
   */
  const extendSession = () => {
    updateActivity();
    toast.success('Session extended', 2000);
  };

  // Setup and cleanup
  onMounted(() => {
    startMonitoring();
  });

  onUnmounted(() => {
    stopMonitoring();
  });

  return {
    lastActivity,
    isWarningShown,
    warningCountdown,
    startMonitoring,
    stopMonitoring,
    extendSession,
    updateActivity,
  };
}
