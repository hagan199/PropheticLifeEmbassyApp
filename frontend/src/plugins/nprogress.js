import NProgress from 'nprogress';
import 'nprogress/nprogress.css';

export default function setupNProgress(router) {
  router.beforeEach((to, from, next) => {
    NProgress.start();
    next();
  });
  router.afterEach(() => {
    NProgress.done();
  });
}
