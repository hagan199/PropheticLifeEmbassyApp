declare module '@/composables/useToast' {
  type Toast = {
    id: number;
    message: string;
    type: string;
    duration: number;
    visible: boolean;
  };

  export function useToast(): {
    toasts: Toast[];
    show(message: string, type?: string, duration?: number): number;
    dismiss(id: number): void;
    success(message: string, duration?: number): number;
    error(message: string, duration?: number): number;
    warning(message: string, duration?: number): number;
    info(message: string, duration?: number): number;
  };
}
