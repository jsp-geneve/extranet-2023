<script lang="ts" setup>
import { ref } from 'vue'
import NavLink from 'components/NavLink.vue'
import auth from 'src/services/auth'
import { useRouter } from 'vue-router'
import { gql, useMutation } from '@urql/vue'
import { useQuasar } from 'quasar'

const navLinks = [
  {
    title: 'Contacts',
    caption: 'Liste des contacts',
    icon: 'person',
    link: '/contacts',
  },
  {
    title: 'Cours',
    caption: 'Liste des cours',
    icon: 'school',
    link: '/cours',
  },
  {
    title: 'Utilisateurs',
    caption: 'Gestion des utilisateurs',
    icon: 'groups_3',
    link: '/users',
  },
]

const leftDrawerOpen = ref(false)
const router = useRouter()
const $q = useQuasar()

  type LogoutMutation = {
    logout: {
      status: string,
      message: string,
    }
  }
const { executeMutation: executeLogout } = useMutation<LogoutMutation>(gql`
    mutation {
      logout {
          status
          message
      }
    }`)

function toggleLeftDrawer () {
  leftDrawerOpen.value = !leftDrawerOpen.value
}

async function logout () {
  const { error, data } = await executeLogout({})

  if (!error && data?.logout.status === 'TOKEN_REVOKED') {
    auth.removeToken()

    $q.notify({
      type: 'positive',
      message: data.logout.message,
    })

    router.push({
      name: 'Login',
    })
  } else {
    $q.notify({
      type: 'negative',
      message: `${error}`,
    })
  }
}
</script>

<template>
  <q-layout view="lHh Lpr lFf">
    <q-header
      bordered
      class="bg-white text-grey-8"
    >
      <q-toolbar>
        <q-btn
          flat
          round
          icon="menu"
          aria-label="Menu"
          :ripple="{
            early: true,
          }"
          @click="toggleLeftDrawer"
        />

        <q-space />

        <img
          src="~/src/assets/ISG_Logo_Rouge.png"
          height="36"
        >

        <q-space />
        <q-btn
          round
          flat
          :ripple="{
            early: true,
          }"
        >
          <q-avatar size="32px">
            <img src="https://cdn.quasar.dev/img/boy-avatar.png">
          </q-avatar>
          <q-tooltip>Account</q-tooltip>
          <q-menu
            auto-close
            transition-show="jump-down"
            transition-hide="jump-up"
          >
            <q-list>
              <q-item
                v-ripple
                clickable
                @click="logout"
              >
                <q-item-section avatar>
                  <q-icon
                    name="logout"
                  />
                </q-item-section>
                <q-item-section>
                  Logout
                </q-item-section>
              </q-item>
            </q-list>
          </q-menu>
        </q-btn>
      </q-toolbar>
    </q-header>

    <q-drawer
      v-model="leftDrawerOpen"
      show-if-above
      bordered
    >
      <q-list>
        <q-item-label header>
          Nav Links
        </q-item-label>

        <navLink
          v-for="link in navLinks"
          :key="link.title"
          v-bind="link"
        />
      </q-list>
    </q-drawer>

    <q-page-container class="bg-grey-1">
      <router-view />
    </q-page-container>
  </q-layout>
</template>
