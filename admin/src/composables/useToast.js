import { reactive } from 'vue'

/**
 * Minimal global toast store. Import useToast() anywhere and call
 * success/error/info; ToastContainer renders the stack and auto-dismisses.
 */
const toasts = reactive([])
let seq = 0

function push(type, title, message, timeout = 4200) {
  const id = ++seq
  toasts.push({ id, type, title, message })
  if (timeout) {
    setTimeout(() => dismiss(id), timeout)
  }
  return id
}

function dismiss(id) {
  const i = toasts.findIndex((t) => t.id === id)
  if (i !== -1) toasts.splice(i, 1)
}

export function useToast() {
  return {
    toasts,
    dismiss,
    success: (title, message) => push('success', title, message),
    error: (title, message) => push('error', title, message, 6000),
    info: (title, message) => push('info', title, message),
  }
}
