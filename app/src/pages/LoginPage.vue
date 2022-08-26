<template>
  <q-layout>
    <q-page-container>
      <q-page
        padding
        class="flex flex-center"
      >
        <q-form
          class="q-gutter-md"
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

          <q-btn
            id="foobar"
            :ripple="{
              early: true
            }"
            :disabled="loading"
            label="se connecter"
            type="submit"
            color="primary"
            @click="submitLogin"
          />
        </q-form>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue'
import auth from 'src/services/auth'
import { useRoute, useRouter } from 'vue-router'

export default defineComponent({
  setup () {
    const email = ref('')
    const password = ref('')
    const router = useRouter()
    const { query } = useRoute()

    return {
      isPwd: ref(true),
      email,
      password,
      loading: ref(false),
      submitLogin: async (e: Event) => {
        e.preventDefault()

        const token = await fetch('http://localhost/graphql', {
          headers: {
            'content-type': 'application/json',
          },
          body: JSON.stringify({
            variables: {
              email: email.value,
              password: password.value,
            },
            query: `mutation ($email: String!, $password: String!) {
              login(
                input: {
                  email: $email, 
                  password: $password
                }) {
                  token
                }
              }`,
          }),
          method: 'POST',
        }).then(response => response.json())
          .then(data => data.data.login.token)

        auth.saveToken(token)

        if (!Array.isArray(query.redirect)) {
          await router.push(query.redirect || '/')
        }
      },
    }
  },
})
</script>
