<script lang="ts" setup>
import { gql, useQuery } from '@urql/vue'

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
</script>

<template>
  <q-page padding>
    <h2 class="text-h5 text-grey-8 q-my-lg">
      Utilisateurs
    </h2>
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
          // field: row => row.roles.map((role: any) => role.name).join(', '),
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
    </q-table>
  </q-page>
</template>
