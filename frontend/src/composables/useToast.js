import { reactive } from 'vue';

const state = reactive({
  toasts: [],
});

let nextId = 1;

export function useToast() {
  const show = (message, type = 'info', duration = 3000) => {
    const id = nextId++;
    const toast = {
      id,
      message,
      type, // 'success', 'danger', 'warning', 'info'
      duration,
      visible: true,
    };

    state.toasts.push(toast);

    // Auto-dismiss after duration
    if (duration > 0) {
      setTimeout(() => {
        dismiss(id);
      }, duration);
    }

    return id;
  };

  const dismiss = id => {
    const index = state.toasts.findIndex(t => t.id === id);
    if (index !== -1) {
      state.toasts.splice(index, 1);
    }
  };

  const success = (message, duration) => show(message, 'success', duration);
  const error = (message, duration) => show(message, 'danger', duration);
  const warning = (message, duration) => show(message, 'warning', duration);
  const info = (message, duration) => show(message, 'info', duration);

  return {
    toasts: state.toasts,
    show,
    dismiss,
    success,
    error,
    warning,
    info,
  };
}
