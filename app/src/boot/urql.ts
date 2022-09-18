import { boot } from 'quasar/wrappers'
import urql from '@urql/vue'
import authService from '../services/auth'

export default boot(async ({ app }) => {
  app.use(urql, {
    url: 'http://localhost/graphql',
    fetchOptions: () => {
      const token = authService.getToken()
      return {
        headers: {
          authorization: token ? `Bearer ${token}` : '',
        },
      }
    },
  })
})
