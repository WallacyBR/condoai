<template>
  <div class="sticky bottom-0 z-20 -mx-4 -mb-4 mt-8 border-t border-slate-200/80 bg-white/95 px-4 py-3.5 backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-900/95 sm:-mx-6 sm:-mb-6 sm:px-6 lg:-mx-8 lg:-mb-8 lg:px-8">
    <div class="flex items-center justify-between gap-3">
      <div>
        <slot name="left" />
      </div>
      <div class="flex items-center space-x-3">
        <AppButton
          v-if="showCancel"
          @click="$emit('cancel')"
          type="button"
          variant="secondary"
          size="md"
          :disabled="disabled || loading"
        >
          {{ cancelText }}
        </AppButton>
        <AppButton
          @click="$emit('save')"
          :type="submitButton ? 'submit' : 'button'"
          variant="primary"
          size="md"
          :loading="loading"
          :disabled="disabled"
        >
          {{ saveText }}
        </AppButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import AppButton from './AppButton.vue';

defineProps({
  saveText: { type: String, default: 'Salvar' },
  cancelText: { type: String, default: 'Cancelar' },
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  showCancel: { type: Boolean, default: true },
  submitButton: { type: Boolean, default: false }
});

defineEmits(['save', 'cancel']);
</script>
