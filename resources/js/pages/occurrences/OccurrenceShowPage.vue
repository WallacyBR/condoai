<template>
  <AuthenticatedLayout page-title="Ocorrência">
    <OccurrenceView />
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '../../components/layouts/AuthenticatedLayout.vue';
import OccurrenceView from '../../components/occurrences/OccurrenceView.vue';
</script>
