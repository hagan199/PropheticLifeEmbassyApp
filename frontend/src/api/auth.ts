import api from './index';

export interface LoginPayload {
  email?: string;
  phone?: string;
  password: string;
}

export const authApi = {
  // Accept either a payload object or (phone/email, password)
  login(payloadOrPhone: LoginPayload | string, password?: string) {
    if (typeof payloadOrPhone === 'string') {
      const phone = payloadOrPhone;
      return api.post('/auth/login', { phone, password });
    }
    return api.post('/auth/login', payloadOrPhone);
  },
  logout() {
    return api.post('/auth/logout');
  },
  user() {
    return api.get('/auth/user');
  },
  // alias used in some stores
  getUser() {
    return api.get('/auth/user');
  },
  forgotPassword(email: string) {
    return api.post('/auth/forgot-password', { email });
  },
  resetPassword(payload: any) {
    return api.post('/auth/reset-password', payload);
  },
};

export default authApi;
