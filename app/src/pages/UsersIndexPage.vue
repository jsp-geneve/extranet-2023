<script lang="ts" setup>
import { gql, useQuery } from '@urql/vue'
import { ref } from 'vue'

type Role = {
    name: string
}

type User = {
  id: number
  name: string
  email: string
  roles: Array<Role>
}

type UsersQuery = {
  users: Array<User>
}

const { fetching, data /* error */ } = useQuery<UsersQuery>({
  query: gql`query allUsers {
    users {
      id
      name
      email
      roles {
        name
      }
    }
  }`,
})

const layout = ref<'table' | 'cards'>('table')
</script>

<template>
  <q-page padding>
    <h2 class="text-h5 text-grey-8 q-my-lg flex justify-between">
      Utilisateurs
      <q-btn-toggle
        v-model="layout"
        flat
        rounded
        dense
        text-color="grey-5"
        toggle-text-color="grey-8"
        padding="sm"
        :ripple="{
          early: true,
        }"
        :options="[
          { icon: 'table_rows', value: 'table' },
          { icon: 'dashboard', value: 'cards' },
        ]"
      />
    </h2>
    <div
      v-if="layout == 'table'"
    >
      <q-table
        :rows="data?.users"
        :loading="fetching"
        :pagination="{
          rowsPerPage: 0,
        }"
        class="shadow-10"
        :hide-pagination="true"
        :columns="[
          {
            name: 'avatar',
            label: '',
            field: row => row.email,
            align: 'right',
            style: 'width: 48px; padding: 16px',
          },
          {
            name: 'name',
            label: 'Nom',
            field: 'name',
            align: 'left',
          },
          {
            name: 'email',
            label: 'Adresse mail',
            field: 'email',
            align: 'left',
          },
          {
            name: 'roles',
            label: 'Rôles',
            field: 'roles',
            align: 'left',
          },
        ]"
      >
        <template #body-cell-roles="{ value } : { value: Array<Role> }">
          <q-td>
            <span class="q-gutter-x-md">
              <q-badge
                v-for="({ name }, index) in value"
                :key="index"
                color="grey"
                :label="name"
              />
            </span>
          </q-td>
        </template>
        <template #body-cell-avatar="props">
          <q-td :props="props">
            <q-avatar size="lg">
              <img :src="`https://avatars.dicebear.com/api/personas/${props.row.email}.svg?b=%23eee`">
            </q-avatar>
          </q-td>
        </template>
      </q-table>
    </div>
    <div
      v-if="layout == 'cards'"
      class="row q-col-gutter-md"
    >
      <div
        v-for="({ name, email, roles }, index) in data?.users"
        :key="index"
        class="col-4"
      >
        <q-card
          class="q-pa-sm"
        >
          <q-item>
            <q-item-section avatar>
              <q-avatar>
                <img :src="`https://avatars.dicebear.com/api/personas/${email}.svg?b=%23eee`">
              </q-avatar>
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ name }}</q-item-label>
              <q-item-label caption>
                {{ email }}
              </q-item-label>
              <q-item-label
                class="q-gutter-x-md"
              >
                <q-badge
                  v-for="{ name: rolename }, i of roles"
                  :key="i"
                  color="grey"
                  :label="rolename"
                />
                &nbsp;
              </q-item-label>
            </q-item-section>
          </q-item>
        </q-card>
      </div>
    </div>
  </q-page>
</template>
