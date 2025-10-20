<template>
  <div class="chatbot-container">
    <!-- Modal Expandido -->
    <ChatBotExpanded v-model:isExpanded="isExpanded" />
    <!-- Botão flutuante -->
    <button
      v-if="!isOpen"
      @click="toggleChat"
      class="chat-button"
      :class="{ 'pulse': hasUnread }"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
      </svg>
      <span v-if="hasUnread" class="notification-badge">!</span>
    </button>

    <!-- Janela do Chat -->
    <div v-if="isOpen" class="chat-window">
      <!-- Header -->
      <div class="chat-header">
        <div class="flex items-center gap-3">
          <div class="avatar">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
          </div>
          <div>
            <h3 class="font-bold text-white">EvolutIA</h3>
            <p class="text-xs text-blue-100">Especialista em licitações públicas</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <button @click="openExpanded" class="text-white hover:text-blue-100 transition" title="Expandir chat">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
            </svg>
          </button>
          <button @click="toggleChat" class="text-white hover:text-blue-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Messages Area -->
      <div ref="messagesContainer" class="messages-area">
        <div v-if="messages.length === 0" class="empty-state">
          <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
          <p class="text-gray-500 text-sm mb-4">Olá! Sou a <strong>EvolutIA</strong>, sua assistente de licitações públicas!</p>
          <div class="grid grid-cols-1 gap-2">
            <button
              v-for="suggestion in quickSuggestions"
              :key="suggestion"
              @click="sendMessage(suggestion)"
              class="suggestion-button"
            >
              {{ suggestion }}
            </button>
          </div>
        </div>

        <div v-for="(message, index) in messages" :key="index" class="message" :class="message.type">
          <div class="message-bubble">
            <div v-if="message.type === 'bot'" class="message-avatar">
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
              </svg>
            </div>
            <div class="message-content">
              <div v-html="formatMessage(message.text)"></div>
              <div v-if="message.data" class="message-data">
                <!-- Dados estruturados do sistema -->
                <div v-if="message.data.editais" class="data-card">
                  <h4 class="font-bold text-sm mb-2">Editais Encontrados:</h4>
                  <div v-for="edict in message.data.editais" :key="edict.id" class="data-item">
                    <p class="font-medium">{{ edict.edict_number }}</p>
                    <p class="text-xs text-gray-600">{{ edict.organ }}</p>
                  </div>
                </div>
                <div v-if="message.data.stats" class="data-card">
                  <h4 class="font-bold text-sm mb-2">Estatísticas:</h4>
                  <p class="text-xs">{{ message.data.stats }}</p>
                </div>
              </div>
              <span class="message-time">{{ formatTime(message.timestamp) }}</span>
            </div>
          </div>
        </div>

        <div v-if="isTyping" class="message bot">
          <div class="message-bubble">
            <div class="message-avatar">
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
              </svg>
            </div>
            <div class="typing-indicator">
              <span></span>
              <span></span>
              <span></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Input Area -->
      <div class="input-area">
        <input
          v-model="userInput"
          @keypress.enter="sendUserMessage"
          type="text"
          placeholder="Digite sua pergunta sobre licitações..."
          class="chat-input"
          :disabled="isTyping"
        />
        <button
          @click="sendUserMessage"
          :disabled="!userInput.trim() || isTyping"
          class="send-button"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, watch } from 'vue'
import api from '../services/api'
import ChatBotExpanded from './ChatBotExpanded.vue'
import { useChatStore } from '../composables/useChatStore'

// Usa o store compartilhado
const { messages, sessionId, isTyping, addBotMessage, addUserMessage } = useChatStore()

const isOpen = ref(false)
const isExpanded = ref(false)
const hasUnread = ref(false)
const userInput = ref('')
const messagesContainer = ref(null)

function openExpanded() {
  console.log('Abrindo modal expandido...')
  isOpen.value = false
  isExpanded.value = true
  console.log('isExpanded:', isExpanded.value)
}

const quickSuggestions = [
  'Quais editais estão abertos?',
  'Como funciona uma licitação pública?',
  'Buscar contratos no PNCP',
  'Estatísticas do PNCP',
  'Resumo financeiro do sistema'
]

function toggleChat() {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    hasUnread.value = false
    if (messages.value.length === 0) {
      addBotMessage('Olá! Sou a **EvolutIA**, sua assistente especializada em licitações públicas do Brasil. Como posso ajudá-lo hoje? 🤖\n\n💡 **Dica:** Digite /menu para ver o menu principal')
    }
    scrollToBottom()
  }
}

async function sendUserMessage() {
  if (!userInput.value.trim() || isTyping.value) return

  const message = userInput.value.trim()
  addUserMessage(message)
  userInput.value = ''
  isTyping.value = true

  try {
    const response = await api.post('/chat/message', {
      message,
      company_id: 1,
      session_id: sessionId.value
    })

    await new Promise(resolve => setTimeout(resolve, 500)) // Simula digitação

    if (response.data.success) {
      addBotMessage(response.data.response, response.data.data)

      // Atualiza session_id se retornado
      if (response.data.session_id) {
        sessionId.value = response.data.session_id
        localStorage.setItem('evolutia_session_id', sessionId.value)
      }
    } else {
      addBotMessage('Desculpe, ocorreu um erro ao processar sua mensagem. Por favor, tente novamente.')
    }
  } catch (error) {
    console.error('Erro ao enviar mensagem:', error)
    addBotMessage('Desculpe, não consegui processar sua mensagem no momento. Por favor, tente novamente.')
  } finally {
    isTyping.value = false
  }

  scrollToBottom()
}

function sendMessage(text) {
  userInput.value = text
  sendUserMessage()
}

function formatMessage(text) {
  // Converte markdown básico para HTML
  return text
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/\n/g, '<br>')
    .replace(/- (.*?)(<br>|$)/g, '• $1$2')
}

function formatTime(date) {
  return new Date(date).toLocaleTimeString('pt-BR', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

function scrollToBottom() {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

watch(isOpen, (newVal) => {
  if (newVal) {
    nextTick(() => scrollToBottom())
  }
})

// Debug: Watch isExpanded state
watch(isExpanded, (newVal) => {
  console.log('ChatBot - isExpanded changed to:', newVal)
})
</script>

<style scoped>
.chatbot-container {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 1000;
}

.chat-button {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  transition: all 0.3s ease;
  position: relative;
}

.chat-button:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 16px rgba(102, 126, 234, 0.6);
}

.chat-button.pulse {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  }
  50% {
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.8);
  }
}

.notification-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #ef4444;
  color: white;
  font-size: 12px;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chat-window {
  width: 400px;
  height: 600px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.chat-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
}

.messages-area {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  background: #f9fafb;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
}

.suggestion-button {
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  padding: 10px 16px;
  text-align: left;
  font-size: 13px;
  color: #374151;
  transition: all 0.2s;
}

.suggestion-button:hover {
  border-color: #667eea;
  color: #667eea;
  transform: translateY(-2px);
}

.message {
  margin-bottom: 16px;
  display: flex;
}

.message.bot {
  justify-content: flex-start;
}

.message.user {
  justify-content: flex-end;
}

.message-bubble {
  display: flex;
  gap: 8px;
  max-width: 80%;
}

.message.user .message-bubble {
  flex-direction: row-reverse;
}

.message-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #ede9fe;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.message-content {
  background: white;
  padding: 12px 16px;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  font-size: 14px;
  line-height: 1.5;
}

.message.user .message-content {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.message-time {
  display: block;
  font-size: 11px;
  color: #9ca3af;
  margin-top: 4px;
}

.message.user .message-time {
  color: rgba(255, 255, 255, 0.7);
}

.message-data {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
}

.data-card {
  background: #f3f4f6;
  border-radius: 8px;
  padding: 12px;
  margin-top: 8px;
}

.data-item {
  padding: 8px;
  background: white;
  border-radius: 6px;
  margin-bottom: 8px;
}

.data-item:last-child {
  margin-bottom: 0;
}

.typing-indicator {
  display: flex;
  gap: 4px;
  padding: 12px 16px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.typing-indicator span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #667eea;
  animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) {
  animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes typing {
  0%, 60%, 100% {
    opacity: 0.3;
    transform: translateY(0);
  }
  30% {
    opacity: 1;
    transform: translateY(-10px);
  }
}

.input-area {
  padding: 16px 20px;
  background: white;
  border-top: 1px solid #e5e7eb;
  display: flex;
  gap: 12px;
}

.chat-input {
  flex: 1;
  padding: 12px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 24px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s;
}

.chat-input:focus {
  border-color: #667eea;
}

.send-button {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.send-button:hover:not(:disabled) {
  transform: scale(1.05);
}

.send-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Scrollbar personalizada */
.messages-area::-webkit-scrollbar {
  width: 6px;
}

.messages-area::-webkit-scrollbar-track {
  background: transparent;
}

.messages-area::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

.messages-area::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

/* Responsive */
@media (max-width: 640px) {
  .chat-window {
    width: calc(100vw - 48px);
    height: calc(100vh - 48px);
  }
}
</style>
