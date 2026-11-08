<template>
  <AuthenticatedLayout page-title="Ocorrências">
    <OccurrenceList />
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '../../components/layouts/AuthenticatedLayout.vue';
import OccurrenceList from '../../components/occurrences/OccurrenceList.vue';
</script>
