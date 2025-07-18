import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/Home.vue';
import Templates from '../views/Templates.vue';

const routes = [
  {
    path: '/',
    name: 'home',
    component: Home
  },
  {
    path: '/templates',
    name: 'templates',
    component: Templates
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
];

const router = createRouter({
  history: createWebHistory('/'),
  routes
});

router.beforeEach((to, from, next) => {
  console.log('路由变化:', { from: from.path, to: to.path });
  next();
});

export default router; 