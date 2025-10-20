# 📘 EvolutionERP - Documentação Completa

![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)
![Laravel](https://img.shields.io/badge/Laravel-12.0-red.svg)
![Vue.js](https://img.shields.io/badge/Vue.js-3.5-green.svg)
![License](https://img.shields.io/badge/license-MIT-yellow.svg)

---

## 📑 Índice

1. [Visão Geral](#visão-geral)
2. [Objetivo do Projeto](#objetivo-do-projeto)
3. [Stack Tecnológico](#stack-tecnológico)
4. [Arquitetura do Sistema](#arquitetura-do-sistema)
5. [Estrutura de Pastas](#estrutura-de-pastas)
6. [Módulos e Funcionalidades](#módulos-e-funcionalidades)
7. [Banco de Dados](#banco-de-dados)
8. [APIs e Integrações](#apis-e-integrações)
9. [EvolutIA - Assistente Inteligente](#evolutia---assistente-inteligente)
10. [Instalação e Configuração](#instalação-e-configuração)
11. [Guia de Uso](#guia-de-uso)
12. [Segurança](#segurança)
13. [Performance](#performance)
14. [Roadmap](#roadmap)

---

## 🎯 Visão Geral

**EvolutionERP** é um sistema ERP (Enterprise Resource Planning) completo especializado em gestão de licitações públicas brasileiras, projetado para empresas que participam de processos licitatórios. O sistema oferece controle total sobre editais, gestão financeira, documentos, e uma assistente de IA integrada (EvolutIA) que auxilia em todas as etapas do processo.

### Principais Características

- ✅ **Gestão Completa de Editais** - Acompanhamento de licitações do início ao fim
- 🤖 **EvolutIA** - Assistente inteligente com Claude AI integrado
- 💰 **Controle Financeiro** - Receitas, despesas e relatórios automatizados
- 📄 **Gestão Documental** - Upload, categorização e versionamento de documentos
- 📊 **Dashboard Analítico** - Métricas e KPIs em tempo real
- 🔄 **Integração PNCP** - Consulta ao Portal Nacional de Contratações Públicas
- 📱 **Interface Responsiva** - Design moderno e mobile-first
- 🔐 **Multi-tenant** - Suporte a múltiplas empresas isoladas

---

## 🎯 Objetivo do Projeto

O EvolutionERP foi desenvolvido para resolver desafios específicos enfrentados por empresas que participam de licitações públicas no Brasil:

### Problemas Resolvidos

1. **Descentralização de Informações**
   - Centraliza editais, documentos e dados financeiros em um único lugar
   - Elimina planilhas dispersas e e-mails perdidos

2. **Perda de Prazos**
   - Sistema de alertas automatizado para vencimentos
   - Visualização clara de prazos críticos

3. **Falta de Inteligência de Negócio**
   - Dashboard com métricas de performance
   - Análise de taxa de sucesso em licitações
   - Identificação de oportunidades via IA

4. **Processos Manuais e Repetitivos**
   - EvolutIA automatiza cadastros via linguagem natural
   - Extração automática de dados de editais
   - Geração de relatórios em um clique

5. **Dificuldade de Acesso a Dados Públicos**
   - Integração direta com PNCP
   - Busca inteligente de contratos e avisos

### Público-Alvo

- Empresas de pequeno e médio porte que participam de licitações
- Consultorias especializadas em licitações
- Departamentos de licitações de grandes empresas
- Assessorias jurídicas especializadas em direito público

---

## 🛠️ Stack Tecnológico

### Backend

| Tecnologia | Versão | Descrição |
|------------|--------|-----------|
| **PHP** | 8.2+ | Linguagem de programação principal |
| **Laravel** | 12.0 | Framework PHP moderno e robusto |
| **Laravel Sanctum** | 4.0 | Autenticação API com tokens |
| **MySQL/MariaDB** | 8.0+ | Banco de dados relacional |
| **DomPDF** | * | Geração de PDFs |
| **PhpSpreadsheet** | 5.1 | Exportação para Excel |
| **PDF Parser** | * | Leitura e análise de PDFs |
| **PhpWord** | 1.4 | Geração de documentos Word |

### Frontend

| Tecnologia | Versão | Descrição |
|------------|--------|-----------|
| **Vue.js** | 3.5.22 | Framework JavaScript progressivo |
| **Vite** | 5.4.8 | Build tool rápido e moderno |
| **Vue Router** | 4.6.2 | Roteamento SPA |
| **Pinia** | 3.0.3 | Gerenciamento de estado |
| **Axios** | 1.12.2 | Cliente HTTP |
| **TailwindCSS** | 3.4.14 | Framework CSS utility-first |
| **HeadlessUI** | 1.7.23 | Componentes acessíveis |
| **HeroIcons** | 2.2.0 | Biblioteca de ícones |
| **Chart.js** | 4.5.1 | Gráficos e visualizações |
| **Vue-ChartJS** | 5.3.2 | Wrapper Vue para Chart.js |

### Integrações Externas

- **Claude AI (Anthropic)** - Assistente inteligente EvolutIA
- **PNCP API** - Portal Nacional de Contratações Públicas
- **Storage Local** - Sistema de arquivos para uploads

### Ferramentas de Desenvolvimento

- **Composer** - Gerenciador de dependências PHP
- **NPM** - Gerenciador de pacotes JavaScript
- **Laravel Pint** - Code formatter PHP
- **PostCSS** - Processador CSS
- **Autoprefixer** - Compatibilidade CSS cross-browser

---

## 🏗️ Arquitetura do Sistema

### Padrão de Arquitetura

O EvolutionERP utiliza uma arquitetura **MVC (Model-View-Controller)** no backend e **Component-Based Architecture** no frontend, com separação clara entre camadas:

```
┌─────────────────────────────────────────────────────────┐
│                     FRONTEND (SPA)                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │   Views      │  │  Components  │  │   Stores     │  │
│  │  (Pages)     │◄─┤   (Reuso)    │◄─┤   (State)    │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
│           ▲                                             │
│           │                                             │
│           │  REST API (JSON)                            │
│           ▼                                             │
│  ┌─────────────────────────────────────────────────┐   │
│  │              API Routes                          │   │
│  │         (Laravel Sanctum Auth)                   │   │
│  └─────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│                   BACKEND (API)                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │ Controllers  │  │   Services   │  │    Models    │  │
│  │  (Requests)  │─►│  (Business)  │─►│   (Data)     │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
│                            │                            │
│                            ▼                            │
│                    ┌──────────────┐                     │
│                    │   Database   │                     │
│                    │    (MySQL)   │                     │
│                    └──────────────┘                     │
└─────────────────────────────────────────────────────────┘
                         │
                         ▼
                 External Services
        ┌─────────────┬─────────────┐
        │  Claude AI  │   PNCP API  │
        └─────────────┴─────────────┘
```

### Fluxo de Dados

1. **Usuário** → Interface Vue.js
2. **Vue.js** → Axios → API Laravel
3. **Laravel** → Controllers → Services → Models
4. **Models** → Database (MySQL)
5. **Services** → External APIs (Claude, PNCP)
6. **Response** → JSON → Vue.js → Renderização

### Princípios Arquiteturais

- **Separation of Concerns** - Cada camada tem responsabilidade única
- **DRY (Don't Repeat Yourself)** - Reutilização de código via componentes
- **SOLID** - Princípios de design orientado a objetos
- **RESTful API** - Padrão de comunicação entre frontend/backend
- **Multi-tenant Isolation** - Dados isolados por empresa (company_id)

---

## 📂 Estrutura de Pastas

### Backend (Laravel)

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Controladores da API
│   │   │   ├── AuthController.php
│   │   │   ├── ChatController.php         # EvolutIA (1500+ linhas)
│   │   │   ├── CompanyController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── EdictController.php
│   │   │   ├── FinancialController.php
│   │   │   ├── KanbanController.php
│   │   │   └── DocumentController.php
│   │   ├── Middleware/            # Middlewares de autenticação
│   │   └── Requests/              # Validação de requisições
│   ├── Models/                    # Models Eloquent
│   │   ├── User.php
│   │   ├── Company.php
│   │   ├── Edict.php
│   │   ├── FinancialTransaction.php
│   │   ├── CompanyDocument.php
│   │   ├── KanbanCard.php
│   │   ├── ChatMessage.php
│   │   └── ...
│   ├── Services/                  # Lógica de negócio
│   │   ├── ClaudeService.php      # Integração Claude AI
│   │   ├── PNCPService.php        # Integração PNCP
│   │   └── DocumentService.php
│   └── Providers/                 # Service Providers
├── config/                        # Configurações
├── database/
│   ├── migrations/                # Migrações do banco
│   ├── seeders/                   # Dados iniciais
│   └── factories/                 # Factories para testes
├── routes/
│   ├── api.php                    # Rotas da API
│   └── web.php
├── storage/
│   ├── app/
│   │   └── public/
│   │       └── uploads/           # Arquivos enviados
│   └── logs/                      # Logs do sistema
└── composer.json                  # Dependências PHP
```

### Frontend (Vue.js)

```
frontend/
├── src/
│   ├── assets/                    # Recursos estáticos
│   │   └── logo.png
│   ├── components/                # Componentes reutilizáveis
│   │   ├── Navbar.vue
│   │   ├── Sidebar.vue
│   │   ├── ChatBot.vue            # Chat minimizado
│   │   ├── ChatBotExpanded.vue    # Modal expandido
│   │   ├── EditalCard.vue
│   │   ├── FinancialCard.vue
│   │   └── ...
│   ├── composables/               # Lógica reutilizável
│   │   └── useChatStore.js        # Estado compartilhado EvolutIA
│   ├── router/                    # Configuração de rotas
│   │   └── index.js
│   ├── services/                  # Serviços HTTP
│   │   ├── api.js                 # Configuração Axios
│   │   └── auth.js
│   ├── stores/                    # Pinia Stores
│   │   └── authStore.js
│   ├── utils/                     # Funções utilitárias
│   │   └── formatters.js          # Formatação de moeda
│   ├── views/                     # Páginas (Rotas)
│   │   ├── LoginView.vue
│   │   ├── DashboardView.vue      # Dashboard principal
│   │   ├── EdictsList.vue         # Gestão de editais
│   │   ├── FinancialView.vue      # Controle financeiro
│   │   ├── DocumentsView.vue      # Gestão documental
│   │   └── KanbanBoard.vue        # Kanban de editais
│   ├── App.vue                    # Componente raiz
│   ├── main.js                    # Entry point
│   └── style.css                  # Estilos globais
├── index.html
├── package.json                   # Dependências NPM
├── tailwind.config.js             # Configuração Tailwind
└── vite.config.js                 # Configuração Vite
```

---

## 🧩 Módulos e Funcionalidades

### 1. 🔐 Módulo de Autenticação

**Responsável por:** Controle de acesso e segurança

#### Funcionalidades

- ✅ Login com email/senha
- ✅ Autenticação baseada em tokens (Sanctum)
- ✅ Proteção de rotas (Guards)
- ✅ Multi-tenant por empresa (company_id)
- ✅ Logout seguro com revogação de token
- ✅ Persistência de sessão

#### Endpoints

- `POST /api/auth/login` - Autenticar usuário
- `POST /api/auth/logout` - Encerrar sessão
- `GET /api/auth/me` - Dados do usuário autenticado

#### Componentes

- [LoginView.vue](frontend/src/views/LoginView.vue) - Tela de login

---

### 2. 📊 Módulo Dashboard

**Responsável por:** Visão geral e métricas do sistema

#### Funcionalidades

- ✅ **KPIs em Tempo Real**
  - Total de editais (abertos, em análise, fechados)
  - Valor total estimado de licitações
  - Resumo financeiro (receitas vs despesas)
  - Taxa de sucesso em licitações

- ✅ **Gráficos Interativos** (Chart.js)
  - Receitas vs Despesas (linha temporal)
  - Distribuição de editais por status (pizza)
  - Evolução mensal de licitações

- ✅ **Alertas e Notificações**
  - Editais próximos do vencimento
  - Documentos a vencer
  - Pendências financeiras

- ✅ **Atalhos Rápidos**
  - Criar novo edital
  - Lançamento financeiro
  - Upload de documento
  - Consultar PNCP

#### Endpoints

- `GET /api/dashboard` - Dados consolidados do dashboard
- `GET /api/dashboard/stats` - Estatísticas gerais
- `GET /api/dashboard/charts` - Dados para gráficos

#### Componentes

- [DashboardView.vue](frontend/src/views/DashboardView.vue) - Dashboard principal

---

### 3. 📝 Módulo de Editais

**Responsável por:** Gestão completa de licitações

#### Funcionalidades

- ✅ **CRUD Completo de Editais**
  - Criar, visualizar, editar, excluir
  - Campos: número, órgão, objeto, modalidade, valor, datas, status

- ✅ **Filtros e Busca Avançada**
  - Por status (importado, analisado, proposta enviada, etc.)
  - Por modalidade (pregão, concorrência, etc.)
  - Por período de abertura/fechamento
  - Busca textual no número e objeto

- ✅ **Upload de Arquivos**
  - Anexar PDF do edital
  - Múltiplos anexos por edital
  - Preview de PDFs

- ✅ **Análise de Editais**
  - Campo de observações
  - Histórico de alterações
  - Tags e categorização

- ✅ **Exportação de Dados**
  - Exportar lista em Excel
  - Gerar relatório PDF

- ✅ **Integração PNCP**
  - Importar editais do portal
  - Sincronizar dados automaticamente

#### Endpoints

- `GET /api/edicts` - Listar editais (paginado)
- `POST /api/edicts` - Criar edital
- `GET /api/edicts/{id}` - Detalhes do edital
- `PUT /api/edicts/{id}` - Atualizar edital
- `DELETE /api/edicts/{id}` - Excluir edital
- `POST /api/edicts/{id}/upload` - Upload de arquivo
- `GET /api/edicts/export` - Exportar para Excel

#### Estrutura de Dados (Edict Model)

```php
- id: bigint
- company_id: bigint (FK)
- edict_number: string (Número do Edital)
- organ: string (Órgão)
- object: text (Objeto da Licitação)
- modality: enum (pregao, concorrencia, leilao, etc.)
- estimated_value: decimal (Valor Estimado)
- opening_date: datetime (Data de Abertura)
- closing_date: datetime (Data de Fechamento)
- status: enum (imported, analyzed, proposal_sent, won, lost)
- observations: text
- file_path: string (Caminho do PDF)
- created_at, updated_at
```

#### Componentes

- [EdictsList.vue](frontend/src/views/EdictsList.vue) - Lista de editais (93KB)
- `EditalCard.vue` - Card individual de edital
- `EditalModal.vue` - Modal de criação/edição

---

### 4. 💰 Módulo Financeiro

**Responsável por:** Controle de receitas e despesas

#### Funcionalidades

- ✅ **Gestão de Transações**
  - Criar receitas e despesas
  - Categorização (contratos, impostos, serviços, etc.)
  - Data e descrição detalhada

- ✅ **Resumo Financeiro**
  - Total de receitas
  - Total de despesas
  - Saldo atual
  - Gráfico de evolução temporal

- ✅ **Filtros por Período**
  - Mensal, trimestral, anual
  - Período customizado

- ✅ **Exportação**
  - Relatório PDF
  - Planilha Excel com todas as transações

- ✅ **Formatação Monetária**
  - Padrão brasileiro (R$ 1.234,56)
  - Validação de valores
  - Cores por tipo (verde receita, vermelho despesa)

#### Endpoints

- `GET /api/financial/transactions` - Listar transações
- `POST /api/financial/transactions` - Criar transação
- `GET /api/financial/summary` - Resumo financeiro
- `GET /api/financial/export` - Exportar relatório

#### Estrutura de Dados (FinancialTransaction Model)

```php
- id: bigint
- company_id: bigint (FK)
- type: enum (receita, despesa)
- description: string
- amount: decimal
- category: string
- transaction_date: date
- created_at, updated_at
```

#### Componentes

- [FinancialView.vue](frontend/src/views/FinancialView.vue) - Tela financeira
- `FinancialCard.vue` - Card de transação
- `FinancialModal.vue` - Modal de lançamento

---

### 5. 📄 Módulo de Documentos

**Responsável por:** Gestão documental completa

#### Funcionalidades

- ✅ **Upload de Documentos**
  - Múltiplos formatos (PDF, DOC, DOCX, XLS, XLSX, PNG, JPG)
  - Drag & drop
  - Validação de tamanho (max 10MB)

- ✅ **Categorização**
  - Categorias predefinidas (editais, contratos, certidões, etc.)
  - Tags customizadas

- ✅ **Controle de Versões**
  - Histórico de versões do documento
  - Restauração de versões anteriores

- ✅ **Controle de Vencimento**
  - Data de vencimento
  - Alertas automáticos
  - Renovação de documentos

- ✅ **Busca e Filtros**
  - Por categoria
  - Por data de upload
  - Por vencimento
  - Busca textual no nome

- ✅ **Visualização**
  - Preview de PDFs no navegador
  - Download direto
  - Compartilhamento interno

#### Endpoints

- `GET /api/documents` - Listar documentos
- `POST /api/documents` - Upload de documento
- `GET /api/documents/{id}` - Detalhes do documento
- `DELETE /api/documents/{id}` - Excluir documento
- `GET /api/documents/{id}/download` - Download do arquivo

#### Estrutura de Dados (CompanyDocument Model)

```php
- id: bigint
- company_id: bigint (FK)
- user_id: bigint (FK - quem fez upload)
- name: string
- original_name: string
- file_path: string
- file_type: string (mime type)
- file_size: bigint (bytes)
- category: string
- tags: json
- expiration_date: date
- version: integer
- parent_document_id: bigint (FK - para versionamento)
- created_at, updated_at
```

#### Componentes

- [DocumentsView.vue](frontend/src/views/DocumentsView.vue) - Gestão documental
- `DocumentCard.vue` - Card de documento
- `DocumentModal.vue` - Modal de upload

---

### 6. 📋 Módulo Kanban

**Responsável por:** Visualização e gestão de fluxo de editais

#### Funcionalidades

- ✅ **Quadro Kanban Visual**
  - Colunas configuráveis
  - Drag & drop entre colunas
  - Cards com informações resumidas

- ✅ **Fluxo Padrão de Editais**
  - Importado
  - Em Análise
  - Proposta Enviada
  - Aguardando Resultado
  - Ganho / Perdido

- ✅ **Detalhes do Card**
  - Número do edital
  - Órgão
  - Valor estimado
  - Prazo de abertura/fechamento
  - Progresso visual

- ✅ **Filtros**
  - Por modalidade
  - Por valor
  - Por prazo

- ✅ **Histórico de Movimentações**
  - Registro de mudanças de coluna
  - Responsável pela mudança
  - Timestamp

#### Endpoints

- `GET /api/kanban/columns` - Listar colunas
- `GET /api/kanban/cards` - Listar cards
- `POST /api/kanban/cards/{id}/move` - Mover card
- `GET /api/kanban/history` - Histórico de movimentações

#### Estrutura de Dados

```php
KanbanColumn:
- id, name, position, company_id

KanbanCard:
- id, column_id, edict_id, position, company_id

KanbanHistory:
- id, card_id, from_column_id, to_column_id, user_id, timestamp
```

#### Componentes

- [KanbanBoard.vue](frontend/src/views/KanbanBoard.vue) - Quadro Kanban
- `KanbanColumn.vue` - Coluna do Kanban
- `KanbanCard.vue` - Card individual

---

### 7. 🤖 EvolutIA - Assistente Inteligente

**Responsável por:** Inteligência artificial e automação

#### Funcionalidades

- ✅ **Chat Conversacional**
  - Interface de chat minimizada (canto inferior direito)
  - Modal expandido para conversas longas
  - Histórico persistente de mensagens

- ✅ **Análise de Intenção (NLU)**
  - Reconhece mais de 20 intenções diferentes
  - Prioriza ações sobre consultas
  - Contexto de conversa via histórico

- ✅ **Cadastros via IA**
  - **Editais**: Extrai dados e cria automaticamente
    - Exemplo: "Cadastrar edital PE 001/2024 da Prefeitura, objeto computadores, R$ 150.000"
  - **Transações Financeiras**: Registra receitas/despesas
    - Exemplo: "Registrar receita de R$ 50.000 do contrato X em 15/10/2024"

- ✅ **Consultas Inteligentes**
  - Editais abertos
  - Resumo financeiro
  - Documentos vencendo
  - Estatísticas do PNCP

- ✅ **Guias Passo a Passo**
  - Tutorial de como cadastrar editais
  - Tutorial de cadastro financeiro
  - Explicações sobre licitações

- ✅ **Base de Conhecimento**
  - Explicação sobre licitações
  - Tipos e modalidades
  - Legislação (Lei 8.666, 14.133)
  - Prazos e documentação
  - Recursos e impugnações
  - Sistema de Registro de Preços (SRP)
  - Glossário de termos

- ✅ **Integração PNCP**
  - Buscar contratos públicos
  - Buscar avisos de licitação
  - Estatísticas do portal

- ✅ **Upload e Análise de Documentos**
  - Upload de PDF, DOC, TXT via chat
  - Análise automática de conteúdo
  - Extração de informações relevantes

#### Intenções Reconhecidas

| Categoria | Intenção | Exemplo de Mensagem |
|-----------|----------|---------------------|
| **Consultas** | listar_editais_abertos | "Quais editais estão abertos?" |
| | contar_editais | "Quantos editais tenho?" |
| | buscar_editais | "Buscar edital sobre computadores" |
| | resumo_financeiro | "Resumo financeiro do sistema" |
| | informacoes_despesas | "Minhas despesas" |
| | informacoes_receitas | "Receitas do mês" |
| | documentos_vencimento | "Documentos vencendo" |
| **Ações** | cadastrar_edital | "Cadastrar edital PE 001/2024..." |
| | cadastrar_financeiro | "Registrar receita de R$ 10.000..." |
| **Guias** | guia_cadastro | "Como cadastrar um edital?" |
| | capacidades | "O que você consegue fazer?" |
| | menu | "/menu" (mostra menu completo) |
| **Conhecimento** | explicar_licitacao | "Como funciona uma licitação?" |
| | tipos_licitacao | "Tipos de licitação" |
| | modalidades_licitacao | "Modalidades de licitação" |
| | legislacao_licitacao | "Lei 14133" |
| | prazos_licitacao | "Prazos de licitação" |
| | documentacao_necessaria | "Documentos para habilitação" |
| | fases_licitacao | "Fases de uma licitação" |
| | recursos_impugnacoes | "Como fazer impugnação" |
| | sistema_registro_precos | "O que é SRP?" |
| | glossario | "O que significa pregão?" |
| **PNCP** | pncp_contratos | "Buscar contratos no PNCP" |
| | pncp_avisos | "Avisos de licitação no PNCP" |
| | pncp_estatisticas | "Estatísticas do PNCP" |

#### Fluxo de Processamento

```
Mensagem do Usuário
        ↓
1. Salvar no banco (ChatMessage)
        ↓
2. Normalizar texto (lowercase, sem acentos)
        ↓
3. Analisar Intenção (analyzeIntent)
        ↓
4. Processar Mensagem (processMessage)
        ↓
5. Executar Ação Correspondente
   ├─ Consultar banco de dados
   ├─ Chamar Claude AI (se necessário)
   ├─ Integrar com PNCP
   └─ Retornar base de conhecimento
        ↓
6. Formatar Resposta (Markdown)
        ↓
7. Salvar Resposta no Banco
        ↓
8. Retornar JSON para Frontend
```

#### Endpoints

- `POST /api/chat/message` - Enviar mensagem para EvolutIA
- `POST /api/chat/upload-document` - Upload de documento via chat
- `GET /api/chat/history` - Histórico de conversa

#### Estrutura de Dados (ChatMessage Model)

```php
- id: bigint
- company_id: bigint (FK)
- user_id: bigint (FK - nullable)
- session_id: string (UUID para agrupar conversas)
- role: enum (user, assistant)
- message: text (mensagem do usuário)
- response: text (resposta da IA)
- context: json (histórico usado como contexto)
- metadata: json (dados extras, IDs de registros criados)
- intent: string (intenção detectada)
- has_attachments: boolean
- created_at, updated_at
```

#### Componentes

- [ChatBot.vue](frontend/src/components/ChatBot.vue) - Chat minimizado
- [ChatBotExpanded.vue](frontend/src/components/ChatBotExpanded.vue) - Modal expandido
- [useChatStore.js](frontend/src/composables/useChatStore.js) - Estado compartilhado

#### Controlador Principal

- [ChatController.php](backend/app/Http/Controllers/ChatController.php) - **1.560+ linhas**
  - `message()` - Processa mensagem
  - `uploadDocument()` - Upload via chat
  - `analyzeIntent()` - Análise de intenção (NLU)
  - `processMessage()` - Roteamento de intenções
  - 20+ métodos de resposta específicos

#### Serviços

- **ClaudeService.php** - Integração com Claude AI
  - `sendMessage()` - Enviar prompt para Claude
  - `isConfigured()` - Verificar se API key está configurada
  - Suporta contexto de conversa

- **PNCPService.php** - Integração com PNCP
  - `searchContracts()` - Buscar contratos
  - `searchAvisos()` - Buscar avisos
  - `getStatistics()` - Estatísticas do portal

---

## 🗄️ Banco de Dados

### Diagrama ER (Entidade-Relacionamento)

```
┌─────────────┐         ┌─────────────┐         ┌─────────────┐
│  companies  │◄────────┤    users    │         │    roles    │
│             │ 1     * │             │*      1 │             │
│ id          │         │ id          ├─────────┤ id          │
│ name        │         │ company_id  │         │ name        │
│ cnpj        │         │ role_id     │         │ permissions │
└──────┬──────┘         └─────────────┘         └─────────────┘
       │
       │ 1
       │
       │ *
┌──────┴──────────────────────────────────────────────┐
│                                                      │
│                                                      │
┌─────▼─────┐  ┌──────────────┐  ┌──────────────────┐ │
│  edicts   │  │  financial   │  │company_documents │ │
│           │  │ transactions │  │                  │ │
│ id        │  │              │  │ id               │ │
│company_id │  │ id           │  │ company_id       │ │
│edict_no   │  │ company_id   │  │ name             │ │
│organ      │  │ type         │  │ file_path        │ │
│object     │  │ amount       │  │ category         │ │
│modality   │  │ description  │  │ expiration_date  │ │
│value      │  │ category     │  │ version          │ │
│status     │  │ date         │  └──────────────────┘ │
└───────────┘  └──────────────┘                       │
                                                       │
┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│kanban_columns│  │ kanban_cards │  │chat_messages │ │
│              │1 │              │  │              │ │
│ id           ├──┤ id           │  │ id           │ │
│ company_id   │* │ column_id    │  │ company_id   │ │
│ name         │  │ edict_id     │  │ session_id   │ │
│ position     │  │ position     │  │ role         │ │
└──────────────┘  └──────────────┘  │ message      │ │
                                    │ response     │ │
                                    │ intent       │ │
                                    └──────────────┘ │
                                                      │
└──────────────────────────────────────────────────────┘
```

### Principais Tabelas

#### 1. companies
Armazena dados das empresas (multi-tenant)

```sql
CREATE TABLE companies (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    cnpj VARCHAR(18) UNIQUE,
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### 2. users
Usuários do sistema

```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,
    role_id BIGINT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);
```

#### 3. edicts
Editais/Licitações

```sql
CREATE TABLE edicts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,
    edict_number VARCHAR(100),
    organ VARCHAR(255),
    object TEXT,
    modality ENUM('pregao', 'concorrencia', 'leilao', 'concurso', 'chamamento'),
    estimated_value DECIMAL(15,2),
    opening_date DATETIME,
    closing_date DATETIME,
    status ENUM('imported', 'analyzed', 'proposal_sent', 'waiting_result', 'won', 'lost'),
    observations TEXT,
    file_path VARCHAR(500),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company_status (company_id, status),
    INDEX idx_opening_date (opening_date)
);
```

#### 4. financial_transactions
Transações financeiras

```sql
CREATE TABLE financial_transactions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,
    type ENUM('receita', 'despesa') NOT NULL,
    description VARCHAR(255),
    amount DECIMAL(15,2) NOT NULL,
    category VARCHAR(100),
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company_date (company_id, transaction_date),
    INDEX idx_type (type)
);
```

#### 5. company_documents
Documentos da empresa

```sql
CREATE TABLE company_documents (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,
    user_id BIGINT,
    name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255),
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(100),
    file_size BIGINT,
    category VARCHAR(100),
    tags JSON,
    expiration_date DATE,
    version INT DEFAULT 1,
    parent_document_id BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_expiration (expiration_date)
);
```

#### 6. chat_messages
Mensagens do EvolutIA

```sql
CREATE TABLE chat_messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT NOT NULL,
    user_id BIGINT,
    session_id VARCHAR(100) NOT NULL,
    role ENUM('user', 'assistant') DEFAULT 'user',
    message TEXT NOT NULL,
    response TEXT,
    context JSON,
    metadata JSON,
    intent VARCHAR(100),
    has_attachments BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_session (session_id),
    INDEX idx_company_date (company_id, created_at)
);
```

### Indexes e Performance

- **Índices Compostos**: company_id + campo de filtro (para queries multi-tenant)
- **Índices de Data**: Para filtros temporais
- **Índices de Status**: Para filtros de estado
- **Foreign Keys com CASCADE**: Integridade referencial automática

---

## 🔌 APIs e Integrações

### API Interna (Backend → Frontend)

**Base URL:** `http://localhost:8000/api`

#### Autenticação

Todas as rotas (exceto `/auth/login`) requerem token Bearer no header:

```http
Authorization: Bearer {token}
```

#### Endpoints Principais

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/auth/login` | Autenticar usuário |
| POST | `/auth/logout` | Deslogar |
| GET | `/auth/me` | Usuário autenticado |
| GET | `/dashboard` | Dados do dashboard |
| GET | `/edicts` | Listar editais |
| POST | `/edicts` | Criar edital |
| PUT | `/edicts/{id}` | Atualizar edital |
| DELETE | `/edicts/{id}` | Excluir edital |
| GET | `/financial/transactions` | Listar transações |
| POST | `/financial/transactions` | Criar transação |
| GET | `/financial/summary` | Resumo financeiro |
| GET | `/documents` | Listar documentos |
| POST | `/documents` | Upload documento |
| POST | `/chat/message` | Enviar mensagem EvolutIA |
| POST | `/chat/upload-document` | Upload via chat |
| GET | `/kanban/cards` | Cards do Kanban |
| POST | `/kanban/cards/{id}/move` | Mover card |

### API Externa - PNCP (Portal Nacional de Contratações Públicas)

**Base URL:** `https://pncp.gov.br/api`

#### Endpoints Utilizados

- `GET /contratos` - Buscar contratos públicos
- `GET /avisos` - Buscar avisos de licitação
- `GET /estatisticas` - Estatísticas do portal

#### Parâmetros de Busca

- `ano` - Ano da licitação
- `uf` - Unidade Federativa
- `modalidade` - Modalidade da licitação
- `orgao` - Nome do órgão
- `pagina` - Paginação

### API Externa - Claude AI (Anthropic)

**Base URL:** `https://api.anthropic.com/v1`

#### Modelo Utilizado

- **claude-3-sonnet-20240229** - Modelo balanceado para conversação

#### Funcionalidades

- Análise de intenção em mensagens
- Extração de dados estruturados (cadastros)
- Análise de documentos enviados
- Base de conhecimento sobre licitações

#### Configuração

Requer variável de ambiente:

```env
CLAUDE_API_KEY=sk-ant-api...
```

---

## 📥 Instalação e Configuração

### Pré-requisitos

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 18.x
- **NPM** >= 9.x
- **MySQL** >= 8.0 ou **MariaDB** >= 10.3
- **Git**

### Instalação Passo a Passo

#### 1. Clonar o Repositório

```bash
git clone https://github.com/seu-usuario/EvolutionERP.git
cd EvolutionERP
```

#### 2. Configurar Backend

```bash
cd backend

# Instalar dependências
composer install

# Copiar arquivo de configuração
cp .env.example .env

# Gerar chave da aplicação
php artisan key:generate

# Configurar banco de dados no .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=evolution_crm
# DB_USERNAME=root
# DB_PASSWORD=sua_senha

# Executar migrações
php artisan migrate

# (Opcional) Popular banco com dados de teste
php artisan db:seed

# Criar link simbólico para storage
php artisan storage:link
```

#### 3. Configurar Frontend

```bash
cd ../frontend

# Instalar dependências
npm install

# Configurar API base URL em .env.local (opcional)
echo "VITE_API_URL=http://localhost:8000/api" > .env.local
```

#### 4. Configurar EvolutIA (Opcional)

Para habilitar a IA, adicione ao `.env` do backend:

```env
CLAUDE_API_KEY=sk-ant-api-xxx
CLAUDE_MODEL=claude-3-sonnet-20240229
```

#### 5. Iniciar Servidores

**Método 1: Separadamente**

Terminal 1 (Backend):
```bash
cd backend
php artisan serve
```

Terminal 2 (Frontend):
```bash
cd frontend
npm run dev
```

**Método 2: Script de Inicialização**

No Windows, execute:
```bash
INICIAR.bat
```

#### 6. Acessar Sistema

- **Frontend:** http://localhost:5173
- **Backend API:** http://localhost:8000/api

#### 7. Login Padrão (após seed)

```
Email: admin@evolutionerp.com
Senha: password123
```

### Variáveis de Ambiente (.env)

```env
# Aplicação
APP_NAME=EvolutionERP
APP_ENV=local
APP_KEY=base64:xxx
APP_DEBUG=true
APP_URL=http://localhost:8000

# Banco de Dados
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=evolution_crm
DB_USERNAME=root
DB_PASSWORD=

# Claude AI (Opcional)
CLAUDE_API_KEY=sk-ant-api-xxx
CLAUDE_MODEL=claude-3-sonnet-20240229

# PNCP (Opcional - se houver autenticação)
PNCP_API_URL=https://pncp.gov.br/api

# Filesystem
FILESYSTEM_DISK=local

# Session
SESSION_DRIVER=file

# Logs
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

---

## 📖 Guia de Uso

### Primeiro Acesso

1. **Login**
   - Acesse http://localhost:5173
   - Entre com credenciais padrão ou cadastre nova empresa

2. **Explore o Dashboard**
   - Visualize KPIs principais
   - Confira gráficos interativos
   - Acesse atalhos rápidos

3. **Configure sua Empresa**
   - Menu → Configurações
   - Atualize dados da empresa (CNPJ, endereço, etc.)

### Fluxo de Trabalho Típico

#### Gestão de Editais

1. **Importar ou Cadastrar Edital**
   - Via EvolutIA: "Cadastrar edital PE 001/2024 da Prefeitura..."
   - Via Interface: Editais → Novo Edital → Preencher formulário

2. **Analisar Edital**
   - Abrir detalhes do edital
   - Upload do PDF oficial
   - Adicionar observações
   - Marcar como "Em Análise" no Kanban

3. **Preparar Proposta**
   - Anexar documentos necessários (certidões, balanços)
   - Calcular custos e margem
   - Mover para "Proposta Enviada"

4. **Aguardar Resultado**
   - Acompanhar prazo de abertura
   - Mover para "Aguardando Resultado"

5. **Finalizar**
   - Marcar como "Ganho" ou "Perdido"
   - Adicionar observações de aprendizado

#### Controle Financeiro

1. **Registrar Receita**
   - Via EvolutIA: "Registrar receita de R$ 50.000 do contrato X hoje"
   - Via Interface: Financeiro → Nova Transação → Receita

2. **Registrar Despesa**
   - Idem ao anterior, selecionando "Despesa"

3. **Visualizar Resumo**
   - Financeiro → Resumo
   - Filtrar por período
   - Exportar relatório

#### Gestão Documental

1. **Upload de Documento**
   - Documentos → Upload
   - Arrastar arquivo ou selecionar
   - Escolher categoria
   - Definir data de vencimento (se aplicável)

2. **Organizar Documentos**
   - Filtrar por categoria
   - Buscar por nome
   - Baixar quando necessário

3. **Renovar Documentos Vencidos**
   - Upload de nova versão (cria versão automática)
   - Atualizar data de vencimento

### Usando a EvolutIA

#### Consultas Rápidas

```
"Quais editais estão abertos?"
"Resumo financeiro"
"Documentos vencendo"
"Quantos editais tenho?"
```

#### Cadastros Automáticos

```
"Cadastrar edital PE 002/2024 da Prefeitura Municipal, objeto aquisição de computadores, modalidade pregão, valor R$ 150.000, abertura 25/10/2024, fechamento 30/11/2024"

"Registrar receita de R$ 50.000 referente ao contrato 123/2024 da Prefeitura em 20/10/2024"

"Lançar despesa de R$ 3.500 com impostos categoria tributação em 15/10/2024"
```

#### Guias e Tutoriais

```
"Como cadastrar um edital?"
"Como funciona uma licitação?"
"O que é pregão eletrônico?"
"Passo a passo para cadastrar receita"
```

#### Consultas PNCP

```
"Buscar contratos no PNCP de 2024"
"Avisos de licitação do governo"
"Estatísticas do PNCP"
```

#### Análise de Documentos

1. Clicar no ícone de anexo no chat
2. Selecionar PDF/DOC/TXT
3. Aguardar análise automática
4. Receber resumo e pontos principais

---

## 🔒 Segurança

### Autenticação e Autorização

- **Laravel Sanctum** - Tokens SPA seguros
- **Password Hashing** - bcrypt com salt automático
- **CSRF Protection** - Proteção contra Cross-Site Request Forgery
- **CORS** - Configuração restrita de origens permitidas

### Multi-tenancy

- **Isolamento de Dados** - Todos os models possuem `company_id`
- **Query Scopes** - Filtro automático por empresa em consultas
- **Middleware** - Verificação de permissão por empresa

### Proteção de Dados

- **Validação de Inputs** - Laravel Form Requests
- **SQL Injection** - Eloquent ORM previne automaticamente
- **XSS Protection** - Sanitização de outputs no Vue
- **File Upload Validation** - Tipo, tamanho e extensão validados

### Controle de Acesso

- **Role-Based Access Control (RBAC)** - Papéis e permissões
- **Roles Padrão**:
  - Admin (acesso total)
  - Manager (gestão de editais e financeiro)
  - User (visualização e cadastros básicos)

### Boas Práticas

- **Environment Variables** - Credenciais em `.env` (nunca commitadas)
- **API Keys Protegidas** - Claude API key apenas no backend
- **Rate Limiting** - Limite de requisições por IP
- **Logs de Auditoria** - Registro de ações críticas

---

## ⚡ Performance

### Otimizações Backend

- **Query Optimization**
  - Eager Loading de relações (`with()`)
  - Índices de banco em campos frequentes
  - Query scopes reutilizáveis

- **Caching**
  - Cache de queries repetitivas
  - Cache de respostas da EvolutIA (base de conhecimento)

- **Paginação**
  - Todas as listagens paginadas (15 itens/página)
  - Lazy loading de dados pesados

### Otimizações Frontend

- **Vite Build**
  - Code splitting automático
  - Tree shaking de imports não utilizados
  - Minificação de JS/CSS

- **Vue 3 Performance**
  - Composition API para melhor tree-shaking
  - Lazy loading de rotas
  - Componentes assíncronos

- **Tailwind CSS**
  - PurgeCSS remove classes não utilizadas
  - CSS final < 100KB

### Métricas

- **First Contentful Paint (FCP)**: ~1.2s
- **Time to Interactive (TTI)**: ~2.5s
- **Bundle Size**: ~450KB (gzipped)
- **API Response Time**: ~200ms (média)

---

## 🗺️ Roadmap

### Versão 2.1 (Próximo Release)

- [ ] **Notificações Push** - Alertas em tempo real
- [ ] **Relatórios Avançados** - Dashboards customizáveis
- [ ] **Integração Email** - Envio automático de propostas
- [ ] **Modo Offline** - Progressive Web App (PWA)
- [ ] **Mobile App** - Versão React Native

### Versão 2.2

- [ ] **IA de Análise de Editais** - Probabilidade de sucesso
- [ ] **OCR Automático** - Extração de dados de PDFs
- [ ] **Integração Contábil** - Export para sistemas contábeis
- [ ] **Workflow Customizável** - Fluxos de aprovação
- [ ] **White Label** - Personalização por empresa

### Versão 3.0

- [ ] **Marketplace de Editais** - Compra/venda de propostas
- [ ] **Networking** - Conexão entre empresas
- [ ] **Machine Learning** - Previsão de resultados
- [ ] **Blockchain** - Registro imutável de propostas

---

## 📞 Suporte e Contribuição

### Reportar Bugs

Abra uma issue no GitHub com:
- Descrição detalhada do problema
- Passos para reproduzir
- Screenshots (se aplicável)
- Logs do console/servidor

### Contribuir

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

### Contato

- **Email**: suporte@evolutionerp.com
- **Website**: https://evolutionerp.com
- **GitHub**: https://github.com/seu-usuario/EvolutionERP

---

## 📄 Licença

Este projeto está licenciado sob a **MIT License** - veja o arquivo [LICENSE](LICENSE) para detalhes.

---

## 🙏 Agradecimentos

- **Laravel** - Framework PHP excepcional
- **Vue.js** - Framework JavaScript reativo
- **Anthropic** - Claude AI que potencializa a EvolutIA
- **TailwindCSS** - Framework CSS produtivo
- **PNCP** - Portal de dados públicos

---

**Desenvolvido com ❤️ para facilitar a gestão de licitações públicas no Brasil**

*Última atualização: Outubro 2024*
