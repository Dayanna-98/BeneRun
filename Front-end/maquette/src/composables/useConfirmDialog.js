import { ref } from 'vue'

export function useConfirmDialog() {
  const confirmDialog = ref({
    open: false,
    title: 'Confirmation',
    message: '',
    confirmLabel: 'Confirmer',
    cancelLabel: 'Annuler',
    confirmVariant: 'primary',
  })

  let pendingResolve = null

  const askConfirmation = (options = {}) => {
    confirmDialog.value = {
      open: true,
      title: options.title || 'Confirmation',
      message: options.message || '',
      confirmLabel: options.confirmLabel || 'Confirmer',
      cancelLabel: options.cancelLabel || 'Annuler',
      confirmVariant: options.confirmVariant || 'primary',
    }

    return new Promise((resolve) => {
      pendingResolve = resolve
    })
  }

  const finish = (result) => {
    confirmDialog.value.open = false
    if (pendingResolve) {
      pendingResolve(result)
      pendingResolve = null
    }
  }

  const handleConfirmDialogConfirm = () => finish(true)
  const handleConfirmDialogCancel = () => finish(false)

  return {
    confirmDialog,
    askConfirmation,
    handleConfirmDialogConfirm,
    handleConfirmDialogCancel,
  }
}
