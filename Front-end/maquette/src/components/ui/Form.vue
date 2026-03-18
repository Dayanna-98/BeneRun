<template>
  <form @submit="onSubmit" novalidate>
    <slot />
  </form>
</template>

<script setup>
import { useForm } from 'vee-validate'
import * as yup from 'yup'
import { provide } from 'vue'

const props = defineProps({
  schema: { type: Object, default: null }, // schéma yup
  initialValues: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['submit'])

const form = useForm({
  validationSchema: props.schema,
  initialValues: props.initialValues,
})

provide('form', form)

const onSubmit = form.handleSubmit((values) => emit('submit', values))
</script>