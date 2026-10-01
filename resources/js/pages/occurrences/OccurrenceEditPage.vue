<template>
  <AuthenticatedLayout page-title="Editar Ocorrência">
    <OccurrenceForm />
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '../../components/layouts/AuthenticatedLayout.vue';
import OccurrenceForm from '../../components/occurrences/OccurrenceForm.vue';
</script>
