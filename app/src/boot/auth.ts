import { boot } from 'quasar/wrappers'
import auth from 'src/services/auth'

export default boot(({ router }) => {
  router.beforeEach((to) => {
    if (
      !auth.isAuthenticated && to.name !== 'Login'
    ) {
      return {
        name: 'Login',
        query: { redirect: to.fullPath },
      }
    }

    if (
      auth.isAuthenticated && to.name === 'Login'
    ) {
      return {
        name: 'Home',
      }
    }
  })
})
