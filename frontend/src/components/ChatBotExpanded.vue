<template>
  <!-- Modal Expandido do Chat -->
  <Teleport to="body">
    <div v-if="props.isExpanded" class="chat-modal-overlay" @click.self="toggleExpanded">
      <div class="chat-modal">
        <!-- Header do Modal -->
        <div class="chat-modal-header">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
              </svg>
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-800">EvolutIA</h2>
              <p class="text-sm text-gray-600">Assistente Inteligente de Licitações</p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button @click="clearHistory" class="p-2 hover:bg-gray-100 rounded-lg transition-colors" title="Limpar conversa">
              <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
            <button @click="toggleExpanded" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
              <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Messages Area -->
        <div ref="messagesContainer" class="chat-modal-messages">
          <div v-for="(message, index) in messages" :key="index" :class="['message', message.type]">
            <div class="message-bubble">
              <div v-if="message.type === 'bot'" class="message-avatar">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
              </div>
              <div class="message-content">
                <div v-html="formatMessage(message.text)"></div>

                <!-- Data cards se houver -->
                <div v-if="message.data" class="message-data">
                  <div v-if="message.data.editais" class="data-card">
                    <h4 class="font-bold text-sm mb-2">Editais Encontrados:</h4>
                    <div v-for="edict in message.data.editais" :key="edict.id" class="data-item">
                      <p class="font-medium">{{ edict.edict_number }}</p>
                      <p class="text-xs text-gray-600">{{ edict.organ }}</p>
                    </div>
                  </div>
                </div>
              </div>
              <span class="message-time">{{ formatTime(message.timestamp) }}</span>
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

        <!-- Input Area with Upload -->
        <div class="chat-modal-input">
          <!-- Upload button -->
          <label class="upload-button">
            <input type="file" ref="fileInput" @change="handleFileUpload" accept=".pdf,.doc,.docx,.txt" class="hidden" />
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
            </svg>
          </label>

          <!-- File preview -->
          <div v-if="selectedFile" class="file-preview">
            <span class="file-name">{{ selectedFile.name }}</span>
            <button @click="clearFile" class="clear-file">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <input
            v-model="userInput"
            @keypress.enter="selectedFile ? uploadAndSend() : sendUserMessage()"
            type="text"
            placeholder="Digite sua pergunta ou anexe um documento..."
            class="chat-input-modal"
            :disabled="isTyping"
          />
          <button @click="selectedFile ? uploadAndSend() : sendUserMessage()" :disabled="isTyping" class="send-button">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'
import api from '../services/api'
import { useChatStore } from '../composables/useChatStore'

const props = defineProps({
  isExpanded: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:isExpanded'])

// Usa o store compartilhado
const { messages, sessionId, isTyping, addBotMessage, addUserMessage, clearMessages } = useChatStore()

const userInput = ref('')
const messagesContainer = ref(null)
const selectedFile = ref(null)
const fileInput = ref(null)

// Inicializa mensagem de boas-vindas se necessário
onMounted(() => {
  if (messages.value.length === 0) {
    addBotMessage('Olá! Sou a **EvolutIA**, sua assistente especializada em licitações públicas do Brasil. Como posso ajudá-lo hoje? 🤖\n\n💡 **Dica:** Digite /menu para ver o que posso fazer!')
  }
  scrollToBottom()
})

function toggleExpanded() {
  emit('update:isExpanded', false)
}

function clearHistory() {
  if (confirm('Deseja limpar todo o histórico da conversa?')) {
    clearMessages()
    addBotMessage('Histórico limpo! Como posso ajudá-lo agora?\n\n💡 **Dica:** Digite /menu para ver o menu principal')
  }
}

async function sendUserMessage() {
  if (!userInput.value.trim()) return

  const message = userInput.value
  addUserMessage(message)
  userInput.value = ''
  isTyping.value = true

  try {
    const response = await api.post('/chat/message', {
      message,
      company_id: 1,
      session_id: sessionId.value
    })

    if (response.data.success) {
      addBotMessage(response.data.response, response.data.data)

      // Atualiza session_id se retornado
      if (response.data.session_id) {
        sessionId.value = response.data.session_id
        localStorage.setItem('evolutia_session_id', sessionId.value)
      }
    }
  } catch (error) {
    console.error('Erro ao enviar mensagem:', error)
    addBotMessage('Desculpe, ocorreu um erro ao processar sua mensagem. Por favor, tente novamente.')
  } finally {
    isTyping.value = false
  }

  scrollToBottom()
}

function handleFileUpload(event) {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 10 * 1024 * 1024) {
      alert('Arquivo muito grande! Tamanho máximo: 10MB')
      return
    }
    selectedFile.value = file
  }
}

function clearFile() {
  selectedFile.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

async function uploadAndSend() {
  if (!selectedFile.value) {
    sendUserMessage()
    return
  }

  const file = selectedFile.value
  addUserMessage(`📎 Enviando arquivo: ${file.name}`)

  const formData = new FormData()
  formData.append('document', file)
  formData.append('company_id', 1)
  formData.append('session_id', sessionId.value)

  isTyping.value = true
  clearFile()

  try {
    const response = await api.post('/chat/upload-document', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (response.data.success) {
      addBotMessage(response.data.response)

      if (response.data.session_id) {
        sessionId.value = response.data.session_id
        localStorage.setItem('evolutia_session_id', sessionId.value)
      }
    }
  } catch (error) {
    console.error('Erro ao fazer upload:', error)
    addBotMessage('Desculpe, ocorreu um erro ao processar o documento. Por favor, tente novamente.')
  } finally {
    isTyping.value = false
  }
}

function formatMessage(text) {
  return text
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/\n/g, '<br>')
}

function formatTime(timestamp) {
  return timestamp.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

function scrollToBottom() {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

watch(messages, () => {
  scrollToBottom()
})

// Debug: Watch prop changes
watch(() => props.isExpanded, (newVal) => {
  console.log('ChatBotExpanded - isExpanded changed to:', newVal)
  if (newVal) {
    console.log('Modal should now be visible!')
  }
})
</script>

<style scoped>
.chat-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  backdrop-filter: blur(4px);
}

.chat-modal {
  width: 90%;
  max-width: 1200px;
  height: 85vh;
  background: white;
  border-radius: 20px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(50px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.chat-modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  background: white;
}

.chat-modal-messages {
  flex: 1;
  overflow-y: auto;
  padding: 2rem;
  background: #f9fafb;
}

.chat-modal-input {
  padding: 1.5rem;
  border-top: 1px solid #e5e7eb;
  display: flex;
  gap: 0.75rem;
  align-items: center;
  background: white;
}

.upload-button {
  padding: 0.75rem;
  background: #f3f4f6;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.upload-button:hover {
  background: #e5e7eb;
  transform: scale(1.05);
}

.file-preview {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #dbeafe;
  border-radius: 0.5rem;
  font-size: 0.875rem;
}

.file-name {
  color: #1e40af;
  font-weight: 500;
}

.clear-file {
  padding: 0.25rem;
  background: transparent;
  border: none;
  cursor: pointer;
  color: #1e40af;
  transition: all 0.2s;
}

.clear-file:hover {
  color: #1e3a8a;
  transform: scale(1.1);
}

.chat-input-modal {
  flex: 1;
  padding: 0.875rem 1.25rem;
  border: 2px solid #e5e7eb;
  border-radius: 0.75rem;
  font-size: 1rem;
  transition: all 0.2s;
}

.chat-input-modal:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.send-button {
  padding: 0.875rem 1.25rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.send-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.send-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.message {
  margin-bottom: 1.5rem;
  display: flex;
}

.message.user {
  justify-content: flex-end;
}

.message.bot {
  justify-content: flex-start;
}

.message-bubble {
  max-width: 70%;
  position: relative;
}

.message.user .message-bubble {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1rem 1.25rem;
  border-radius: 1.25rem 1.25rem 0.25rem 1.25rem;
  box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
}

.message.bot .message-bubble {
  background: white;
  padding: 1rem 1.25rem;
  border-radius: 1.25rem 1.25rem 1.25rem 0.25rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

.message-avatar {
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 50%;
  background: #eff6ff;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.5rem;
}

.message-content {
  line-height: 1.6;
}

.message-time {
  font-size: 0.75rem;
  color: #9ca3af;
  display: block;
  margin-top: 0.5rem;
}

.message.user .message-time {
  color: rgba(255, 255, 255, 0.7);
}

.typing-indicator {
  display: flex;
  gap: 0.25rem;
  padding: 0.5rem;
}

.typing-indicator span {
  width: 0.5rem;
  height: 0.5rem;
  background: #667eea;
  border-radius: 50%;
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
    transform: translateY(0);
    opacity: 0.7;
  }
  30% {
    transform: translateY(-10px);
    opacity: 1;
  }
}

.message-data {
  margin-top: 1rem;
}

.data-card {
  background: #f9fafb;
  padding: 1rem;
  border-radius: 0.5rem;
  margin-top: 0.5rem;
  border-left: 3px solid #667eea;
}

.data-item {
  padding: 0.5rem;
  margin: 0.25rem 0;
  background: white;
  border-radius: 0.25rem;
}
</style>
