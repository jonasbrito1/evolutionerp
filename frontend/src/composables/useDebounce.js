import { ref, watch } from 'vue'

/**
 * Hook de debounce para Vue 3
 * @param {Function} fn - Função a ser executada
 * @param {Number} delay - Delay em milissegundos (padrão: 500ms)
 * @returns {Function} - Função com debounce aplicado
 */
export function useDebounce(fn, delay = 500) {
  let timeout = null

  return function(...args) {
    clearTimeout(timeout)
    timeout = setTimeout(() => {
      fn.apply(this, args)
    }, delay)
  }
}

/**
 * Hook para criar um ref com debounce
 * @param {any} initialValue - Valor inicial
 * @param {Function} callback - Função callback executada após o debounce
 * @param {Number} delay - Delay em milissegundos (padrão: 500ms)
 * @returns {Ref} - Ref reativo com debounce
 */
export function useDebouncedRef(initialValue, callback, delay = 500) {
  const value = ref(initialValue)
  let timeout = null

  watch(value, (newValue) => {
    clearTimeout(timeout)
    timeout = setTimeout(() => {
      callback(newValue)
    }, delay)
  })

  return value
}
