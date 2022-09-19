import { boot } from 'quasar/wrappers'
import urql, { defaultExchanges } from '@urql/vue'
import authService from '../services/auth'
import { devtoolsExchange } from '@urql/devtools'

export default boot(async ({ app }) => {
  app.use(urql, {
    url: process.env.API_URL,
    exchanges: [devtoolsExchange, ...defaultExchanges],
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
