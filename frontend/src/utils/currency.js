/**
 * Utilitários para formatação monetária brasileira (R$)
 */

/**
 * Formata um número para moeda brasileira (R$ 1.000,00)
 * @param {number|string} value - Valor a ser formatado
 * @returns {string} Valor formatado em R$
 */
export function formatCurrency(value) {
  if (!value && value !== 0) return 'R$ 0,00'

  const numValue = typeof value === 'string' ? parseFloat(value) : value

  if (isNaN(numValue)) return 'R$ 0,00'

  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(numValue)
}

/**
 * Remove formatação de moeda e retorna número puro
 * @param {string} formattedValue - Valor formatado (ex: "R$ 1.000,00")
 * @returns {number} Número sem formatação
 */
export function parseCurrency(formattedValue) {
  if (!formattedValue) return 0

  const cleaned = String(formattedValue)
    .replace(/[R$\s]/g, '')      // Remove R$ e espaços
    .replace(/\./g, '')          // Remove pontos (separador de milhar)
    .replace(',', '.')           // Substitui vírgula por ponto decimal

  return parseFloat(cleaned) || 0
}

/**
 * Formata valor enquanto o usuário digita (máscara de input)
 * @param {string} value - Valor digitado
 * @returns {string} Valor formatado para exibição
 */
export function maskCurrency(value) {
  if (!value) return ''

  // Remove tudo que não é número
  let numericValue = value.replace(/\D/g, '')

  // Se vazio, retorna vazio
  if (!numericValue) return ''

  // Converte para centavos (últimos 2 dígitos)
  const cents = parseInt(numericValue, 10)
  const reais = cents / 100

  // Formata
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(reais)
}

/**
 * Aplica máscara monetária em um input
 * Uso: <input @input="applyCurrencyMask" @blur="formatCurrencyOnBlur">
 */
export function applyCurrencyMask(event) {
  const input = event.target
  let value = input.value

  // Remove formatação anterior
  value = value.replace(/\D/g, '')

  if (!value) {
    input.value = ''
    return
  }

  // Converte para número com centavos
  const number = parseInt(value, 10) / 100

  // Formata para exibição
  input.value = new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(number)
}

/**
 * Formata com símbolo R$ quando perde foco
 */
export function formatCurrencyOnBlur(event) {
  const input = event.target
  const value = parseCurrency(input.value)
  input.value = formatCurrency(value)
}

/**
 * Remove R$ quando ganha foco (para facilitar edição)
 */
export function removeCurrencyOnFocus(event) {
  const input = event.target
  const value = input.value.replace('R$ ', '')
  input.value = value
}

/**
 * Componível Vue para usar formatação monetária
 */
export function useCurrency() {
  return {
    formatCurrency,
    parseCurrency,
    maskCurrency,
    applyCurrencyMask,
    formatCurrencyOnBlur,
    removeCurrencyOnFocus
  }
}
