import { RouteRecordRaw } from 'vue-router'
import UsersIndexPage from 'src/pages/UsersIndexPage.vue'

const routes: RouteRecordRaw[] = [
  {
    name: 'Login',
    path: '/login',
    component: () => import('pages/LoginPage.vue'),
  },
  {
    name: 'Home',
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/IndexPage.vue') },
      { path: 'contacts', component: () => import('pages/ContactsPage.vue') },
      { path: 'users', component: UsersIndexPage },
    ],
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue'),
  },
]

export default routes
