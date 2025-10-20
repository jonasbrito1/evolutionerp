import { ref, watch } from 'vue'

// Estado compartilhado entre ChatBot e ChatBotExpanded
const messages = ref([])
const sessionId = ref('')
const isTyping = ref(false)

// Carrega do localStorage ao inicializar
const loadFromStorage = () => {
  const stored = localStorage.getItem('evolutia_session_id')
  if (stored) {
    sessionId.value = stored
  } else {
    sessionId.value = generateUUID()
    localStorage.setItem('evolutia_session_id', sessionId.value)
  }

  // Carrega mensagens salvas (opcional, para persistir entre reloads)
  const storedMessages = localStorage.getItem('evolutia_messages')
  if (storedMessages) {
    try {
      messages.value = JSON.parse(storedMessages)
    } catch (e) {
      messages.value = []
    }
  }
}

// Salva mensagens no localStorage
watch(messages, (newMessages) => {
  localStorage.setItem('evolutia_messages', JSON.stringify(newMessages))
}, { deep: true })

function generateUUID() {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    const r = Math.random() * 16 | 0
    const v = c === 'x' ? r : (r & 0x3 | 0x8)
    return v.toString(16)
  })
}

export function useChatStore() {
  // Carrega na primeira vez
  if (!sessionId.value) {
    loadFromStorage()
  }

  const addBotMessage = (text, data = null) => {
    messages.value.push({
      type: 'bot',
      text,
      data,
      timestamp: new Date()
    })
  }

  const addUserMessage = (text) => {
    messages.value.push({
      type: 'user',
      text,
      timestamp: new Date()
    })
  }

  const clearMessages = () => {
    messages.value = []
    sessionId.value = generateUUID()
    localStorage.setItem('evolutia_session_id', sessionId.value)
    localStorage.removeItem('evolutia_messages')
  }

  const resetToMenu = () => {
    messages.value = []
    localStorage.removeItem('evolutia_messages')
    // Mantém o mesmo session_id
  }

  return {
    messages,
    sessionId,
    isTyping,
    addBotMessage,
    addUserMessage,
    clearMessages,
    resetToMenu
  }
}
