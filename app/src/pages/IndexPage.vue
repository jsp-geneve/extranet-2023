<template>
  <q-page class="row items-center justify-evenly">
    <default-component />
  </q-page>
</template>

<script lang="ts">
import DefaultComponent from 'components/DefaultComponent.vue'
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'IndexPage',
  components: { DefaultComponent },
})
</script>
