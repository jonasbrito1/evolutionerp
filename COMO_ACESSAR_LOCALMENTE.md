# 🚀 Como Acessar o Sistema Localmente

## 📱 Frontend (Interface Visual)

**URL:** http://localhost:5173

### Login:
- **Email:** admin@licitaevolution.local
- **Password:** admin123456

### O que você verá:
- ✅ Dashboard com métricas
- ✅ Lista de editais com filtros
- ✅ Análise inteligente com IA
- ✅ Gestão de documentos
- ✅ Estatísticas visuais

---

## 🔧 Backend API (Endpoints)

**URL Base:** http://localhost:8000/api

### 1. Fazer Login e Obter Token

**Windows (CMD):**
```cmd
curl -X POST http://localhost:8000/api/auth/login -H "Content-Type: application/json" -d "{\"email\":\"admin@licitaevolution.local\",\"password\":\"admin123456\"}"
```

**Resposta:**
```json
{
  "token": "1|xxxxxxxxxxxxx...",
  "user": {...}
}
```

**⚠️ IMPORTANTE:** Copie o token para usar nos próximos comandos!

---

### 2. Testar Endpoints (substitua SEU_TOKEN pelo token copiado)

#### 📋 Listar Editais
```cmd
curl -X GET http://localhost:8000/api/edicts -H "Authorization: Bearer SEU_TOKEN" -H "Accept: application/json"
```

#### 📊 Estatísticas de Editais
```cmd
curl -X GET http://localhost:8000/api/edicts/stats -H "Authorization: Bearer SEU_TOKEN" -H "Accept: application/json"
```

#### 📄 Ver Detalhes de um Edital
```cmd
curl -X GET http://localhost:8000/api/edicts/1 -H "Authorization: Bearer SEU_TOKEN" -H "Accept: application/json"
```

#### 📤 Upload de Edital (PDF)
```cmd
curl -X POST http://localhost:8000/api/edicts/upload ^
  -H "Authorization: Bearer SEU_TOKEN" ^
  -F "pdf=@C:\caminho\para\seu\edital.pdf" ^
  -F "company_id=1"
```

#### 📑 Tipos de Documentos Disponíveis
```cmd
curl -X GET http://localhost:8000/api/company/documents/types -H "Authorization: Bearer SEU_TOKEN" -H "Accept: application/json"
```

#### 📂 Listar Documentos da Empresa
```cmd
curl -X GET http://localhost:8000/api/company/documents -H "Authorization: Bearer SEU_TOKEN" -H "Accept: application/json"
```

#### ⏰ Documentos Expirando em 30 Dias
```cmd
curl -X GET "http://localhost:8000/api/company/documents/expiring?days=30" -H "Authorization: Bearer SEU_TOKEN" -H "Accept: application/json"
```

#### 📈 Estatísticas de Documentos
```cmd
curl -X GET http://localhost:8000/api/company/documents/stats -H "Authorization: Bearer SEU_TOKEN" -H "Accept: application/json"
```

---

## 🌐 Usando Postman/Insomnia

### 1. Criar Requisição de Login
- **Método:** POST
- **URL:** http://localhost:8000/api/auth/login
- **Headers:**
  - Content-Type: application/json
- **Body (JSON):**
```json
{
  "email": "admin@licitaevolution.local",
  "password": "admin123456"
}
```

### 2. Copiar Token da Resposta

### 3. Criar Outras Requisições
- **Headers:**
  - Authorization: Bearer SEU_TOKEN_AQUI
  - Accept: application/json

---

## 📊 Endpoints Disponíveis

### Autenticação
- `POST /api/auth/login` - Login
- `POST /api/auth/logout` - Logout
- `GET /api/auth/me` - Dados do usuário
- `POST /api/auth/refresh` - Renovar token

### Editais
- `GET /api/edicts` - Listar (com filtros)
- `POST /api/edicts/upload` - Upload e análise IA
- `GET /api/edicts/stats` - Estatísticas
- `GET /api/edicts/{id}` - Detalhes
- `PUT /api/edicts/{id}` - Atualizar
- `DELETE /api/edicts/{id}` - Remover
- `POST /api/edicts/{id}/reanalyze` - Reprocessar IA
- `GET /api/edicts/{id}/download` - Download PDF

### Documentos da Empresa
- `GET /api/company/documents` - Listar
- `POST /api/company/documents/upload` - Upload
- `GET /api/company/documents/types` - Tipos disponíveis
- `GET /api/company/documents/expiring` - Expirando em breve
- `GET /api/company/documents/stats` - Estatísticas
- `GET /api/company/documents/{id}` - Detalhes
- `PUT /api/company/documents/{id}` - Atualizar
- `DELETE /api/company/documents/{id}` - Remover
- `GET /api/company/documents/{id}/download` - Download

---

## 🎯 Filtros Disponíveis

### Editais
```
GET /api/edicts?worth_participating=true
GET /api/edicts?status=analyzed
GET /api/edicts?category=Tecnologia
GET /api/edicts?modality=Pregão Eletrônico
GET /api/edicts?search=software
GET /api/edicts?company_id=1
GET /api/edicts?sort_by=estimated_value&sort_order=desc
GET /api/edicts?per_page=50
```

### Documentos
```
GET /api/company/documents?company_id=1
GET /api/company/documents?type=cnpj
GET /api/company/documents?status=valid
GET /api/company/documents?expiring_days=30
GET /api/company/documents?expired=true
GET /api/company/documents?search=certidão
```

---

## 🔍 Verificar Status dos Serviços

### Backend (Laravel)
```cmd
curl http://localhost:8000/api/health
```
**Resposta esperada:** `{"status":"ok"}`

### Frontend (Vue.js)
Abra http://localhost:5173 no navegador

Se aparecer a tela de login, está funcionando!

---

## 🎨 Dados de Teste Pré-Carregados

O sistema já vem com **8 editais de exemplo**:
1. PE-2024-001 - Prefeitura SP - R$ 850.000
2. CP-2024-085 - Governo MG - R$ 1.200.000
3. TP-2024-042 - TJ Rio de Janeiro - R$ 480.000
4. CV-2024-128 - Educação Campinas - R$ 180.000
5. PP-2024-067 - Ministério da Saúde - R$ 2.400.000
6. DL-2024-203 - SEBRAE - R$ 95.000
7. PE-2024-112 - Banco Central - R$ 3.200.000
8. CC-2024-009 - Ministério da Cultura - R$ 420.000

---

## 🚨 Resolução de Problemas

### Frontend não abre?
1. Verifique se o processo está rodando
2. Acesse: http://localhost:5173
3. Se necessário, reinicie: `cd frontend && npm run dev`

### Backend retorna "Unauthenticated"?
1. Faça login novamente para obter novo token
2. Verifique se está usando o formato: `Bearer TOKEN`
3. Token tem validade - faça novo login se expirou

### Porta ocupada?
- Backend: porta 8000
- Frontend: porta 5173
- MySQL: porta 3320

---

## 📸 Screenshots do Sistema

Acesse http://localhost:5173 para ver:
- 🏠 Dashboard com gráficos
- 📋 Lista de editais com análise IA
- ✅ Score de recomendação (0-100)
- 📊 Estatísticas em tempo real
- 📁 Gestão de documentos
- ⚠️ Alertas de vencimento

---

**Sistema Evolution CRM - Análise Inteligente de Editais**
Desenvolvido com Laravel 12 + Vue 3 + Claude AI
