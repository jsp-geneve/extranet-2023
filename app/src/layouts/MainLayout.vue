<template>
  <q-layout view="lHh Lpr lFf">
    <q-header
      elevated
      class="bg-red-7"
    >
      <q-toolbar>
        <q-btn
          flat
          dense
          round
          icon="menu"
          aria-label="Menu"
          @click="toggleLeftDrawer"
        />

        <q-toolbar-title>
          <router-link to="/">
            JSP Genève
          </router-link>
        </q-toolbar-title>

        <q-btn
          flat
          round
          dense
          @click="logout"
        >
          <q-icon name="logout" />
          <q-tooltip>
            Logout
          </q-tooltip>
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

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue'
import NavLink from 'components/NavLink.vue'
import auth from 'src/services/auth'
import { useRouter } from 'vue-router'
import { useMutation } from '@urql/vue'
import { useQuasar } from 'quasar'

const linksList = [
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
]

export default defineComponent({
  name: 'MainLayout',

  components: { NavLink },

  setup () {
    const leftDrawerOpen = ref(false)
    const router = useRouter()
    const $q = useQuasar()
    const { executeMutation: executeLogout } = useMutation(`
      mutation {
        logout {
            status
            message
        }
      }`)

    return {
      navLinks: linksList,
      leftDrawerOpen,
      toggleLeftDrawer () {
        leftDrawerOpen.value = !leftDrawerOpen.value
      },
      async logout () {
        const { error, data } = await executeLogout()

        if (!error && data.logout.status === 'TOKEN_REVOKED') {
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
      },
    }
  },
})
</script>
