<template>
  <q-layout>
    <q-page-container class="bg-grey-1 text-grey-8">
      <q-page
        padding
        class="flex flex-center"
      >
        <q-card>
          <q-card-section
            class="q-pa-lg"
          >
            <q-form
              class="q-gutter-lg"
            >
              <q-input
                v-model="email"
                filled
                label="Adresse mail"
                color="grey-8"
                autocomplete="username"
              />
              <q-input
                v-model="password"
                filled
                color="grey-8"
                :type="isPwd ? 'password' : 'text'"
                label="Mot de passe"
                autocomplete="current-password"
              >
                <template #append>
                  <q-icon
                    :name="isPwd ? 'visibility_off' : 'visibility'"
                    class="cursor-pointer"
                    @click="isPwd = !isPwd"
                  />
                </template>
              </q-input>
              <div class="row">
                <q-btn
                  id="foobar"
                  unelevated
                  :ripple="{
                    early: true
                  }"
                  :loading="loading"
                  label="se connecter"
                  type="submit"
                  class="bg-red-8 text-grey-1 full-width"
                  @click="submitLogin"
                />
              </div>
            </q-form>
          </q-card-section>
        </q-card>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue'
import auth from 'src/services/auth'
import { useRoute, useRouter } from 'vue-router'
import { gql, useMutation } from '@urql/vue'
import { useQuasar } from 'quasar'

export default defineComponent({
  setup () {
    const email = ref('')
    const password = ref('')
    const router = useRouter()
    const { query } = useRoute()
    const $q = useQuasar()

    type LoginMutation = {
      login: {
        token: string
      }
    }

    type LoginInput = {
      email: string
      password: string
    }

    const { fetching, executeMutation: executeLogin } = useMutation<LoginMutation, LoginInput>(
      gql`mutation ($email: String!, $password: String!) {
        login(
          input: {
            email: $email, 
            password: $password
          }) {
            token
          }
        }`)

    return {
      isPwd: ref(true),
      email,
      password,
      loading: fetching,
      submitLogin: async (e: Event) => {
        e.preventDefault()

        const { data, error } = await executeLogin({
          email: email.value,
          password: password.value,
        })

        if (!error && data) {
          auth.saveToken(data.login.token)

          if (!Array.isArray(query.redirect)) {
            await router.push(query.redirect || '/')
          }
        } else {
          $q.notify({
            type: 'negative',
            message: `${error}`,
          })
        }
      },
    }
  },
})
</script>
