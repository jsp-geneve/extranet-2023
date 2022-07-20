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

    return {
      navLinks: linksList,
      leftDrawerOpen,
      toggleLeftDrawer () {
        leftDrawerOpen.value = !leftDrawerOpen.value
      },
    }
  },
})
</script>
