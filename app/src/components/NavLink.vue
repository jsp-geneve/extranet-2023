<script lang="ts" setup>
withDefaults(defineProps<{
  title: string,
  caption: string,
  link: string,
  icon: string,
}>(), {
  caption: '',
  link: '#',
  icon: '',
})
</script>

<template>
  <q-item
    v-ripple
    clickable
    :to="link"
  >
    <q-item-section
      v-if="icon"
      avatar
    >
      <q-icon :name="icon" />
    </q-item-section>

    <q-item-section>
      <q-item-label>{{ title }}</q-item-label>
      <q-item-label caption>
        {{ caption }}
      </q-item-label>
    </q-item-section>
  </q-item>
</template>
