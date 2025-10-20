# 🤖 EvolutIA - Assistente Inteligente de Licitações

## Visão Geral

A **EvolutIA** é uma assistente virtual especializada em licitações públicas brasileiras, integrada ao sistema EvolutionERP. Ela combina uma base de conhecimento robusta com inteligência artificial da Anthropic (Claude) para fornecer respostas precisas e contextualizadas.

---

## 🎯 Funcionalidades Implementadas

### 1. Base de Conhecimento Expandida

#### 📊 Informações do Sistema
- ✅ Listagem de editais abertos e em andamento
- ✅ Estatísticas e contagem de editais por status
- ✅ Busca inteligente de editais
- ✅ Resumo financeiro completo (receitas, despesas, saldo)
- ✅ Análise de despesas e receitas
- ✅ Documentos próximos ao vencimento

#### 📚 Conhecimento sobre Licitações

**Conceitos Fundamentais:**
- Como funciona uma licitação pública
- Princípios fundamentais (Legalidade, Impessoalidade, Moralidade, etc.)
- Tipos de julgamento (Menor Preço, Melhor Técnica, Técnica e Preço)
- Modalidades (Pregão, Concorrência, Concurso, Leilão, Diálogo Competitivo)

**Prazos Detalhados:**
- Prazos de publicação por modalidade
- Prazos para impugnação e recursos
- Cronograma de homologação e adjudicação
- Prazos para assinatura de contratos

**Documentação Necessária:**
- Habilitação Jurídica
- Regularidade Fiscal (CNDs)
- Qualificação Técnica
- Qualificação Econômico-Financeira
- Declarações obrigatórias

**Fases da Licitação:**
1. Planejamento
2. Publicação do Edital
3. Apresentação de Propostas
4. Abertura e Julgamento
5. Habilitação
6. Recursos
7. Adjudicação
8. Homologação
9. Contratação

**Recursos e Impugnações:**
- Impugnação ao edital
- Pedidos de esclarecimento
- Recursos administrativos
- Representações ao TCU/MP
- Prazos e procedimentos

**Sistema de Registro de Preços (SRP):**
- Conceito e funcionamento
- Ata de Registro de Preços
- Carona (adesão por outros órgãos)
- Vantagens e limitações

**Glossário de Termos:**
- Adjudicação
- Homologação
- Habilitação
- Pregão
- SRP
- CND
- Acervo Técnico
- E muito mais...

**Legislação:**
- Lei 14.133/2021 (Nova Lei de Licitações)
- Lei 8.666/1993 (Lei antiga)
- Lei 10.520/2002 (Pregão)
- Transição entre legislações

### 2. Integração com PNCP (Portal Nacional de Contratações Públicas)

A EvolutIA agora se integra diretamente com o PNCP, oferecendo acesso em tempo real a dados governamentais oficiais.

**Recursos da Integração PNCP:**
- ✅ Busca de contratos públicos registrados
- ✅ Consulta de avisos e editais publicados
- ✅ Estatísticas de contratações por ano
- ✅ Filtragem por UF, modalidade, ano e palavras-chave
- ✅ Cache de 1 hora para otimizar performance
- ✅ Formatação amigável dos dados

**Exemplos de Consultas:**
```
✅ "Buscar contratos PNCP"
✅ "Consultar avisos públicos"
✅ "Estatísticas do PNCP"
✅ "Contratos públicos de 2024"
✅ "Editais do governo em SP"
```

**API Endpoint:**
- Base URL: https://pncp.gov.br/api
- Endpoints utilizados:
  - `/pncp/v1/contratos` - Lista contratos
  - `/pncp/v1/avisos/publicados` - Lista avisos
  - `/pncp/v1/orgaos` - Lista órgãos

### 3. Integração com Claude API (Anthropic)

A EvolutIA pode ser integrada com a API do Claude para respostas ainda mais inteligentes e contextualizadas.

**Recursos da Integração:**
- ✅ Respostas em linguagem natural
- ✅ Contexto do sistema incluído nas respostas
- ✅ Análise inteligente de editais
- ✅ Sugestões proativas
- ✅ Fallback automático para base local

**Como Ativar:**

1. Obtenha uma API key em: https://console.anthropic.com/
2. Adicione ao arquivo `.env`:
```env
ANTHROPIC_API_KEY=sk-ant-api03-xxx
```
3. Reinicie o servidor backend

**Sem a API key:**
- O sistema funciona normalmente com a base de conhecimento local
- Todas as perguntas são respondidas usando as regras programadas

**Com a API key:**
- Perguntas mais complexas são enviadas para o Claude
- Respostas mais naturais e contextualizadas
- Uso do contexto do sistema (editais, finanças, documentos)

### 3. Interface do Usuário

**Botão Flutuante:**
- Localizado no canto inferior direito
- Sempre visível em todo o sistema (exceto login)
- Design moderno com gradient roxo/azul
- Animação de pulsação quando há novidades

**Janela de Chat:**
- Design responsivo (400x600px em desktop)
- Adaptação automática para mobile
- Sugestões rápidas ao abrir
- Indicador de "digitando..."
- Suporte a formatação Markdown
- Timestamps nas mensagens
- Scroll automático
- Dados estruturados em cards

**Sugestões Rápidas:**
1. "Quais editais estão abertos?"
2. "Como funciona uma licitação pública?"
3. "Buscar contratos no PNCP"
4. "Estatísticas do PNCP"
5. "Resumo financeiro do sistema"

---

## 🔍 Exemplos de Perguntas

### Dados do Sistema
```
✅ "Quais editais estão abertos?"
✅ "Quantos editais tenho cadastrados?"
✅ "Resumo financeiro do sistema"
✅ "Quais documentos estão vencendo?"
✅ "Buscar edital sobre tecnologia"
✅ "Despesas do mês"
✅ "Total de receitas"
```

### Dados Governamentais (PNCP)
```
✅ "Buscar contratos no PNCP"
✅ "Consultar contratos públicos de 2024"
✅ "Avisos do PNCP"
✅ "Editais do governo em SP"
✅ "Estatísticas do PNCP"
✅ "Contratos de pregão eletrônico"
✅ "Avisos públicos recentes"
```

### Conhecimento Geral
```
✅ "Como funciona uma licitação pública?"
✅ "Quais são os tipos de licitação?"
✅ "Modalidades de licitação"
✅ "Prazos para recurso"
✅ "Documentos necessários para habilitação"
✅ "O que é SRP?"
✅ "Fases de uma licitação"
✅ "Lei 14.133"
✅ "O que é adjudicação?"
✅ "Diferença entre pregão e concorrência"
```

---

## 🏗️ Arquitetura Técnica

### Backend

**PNCPService** (`app/Services/PNCPService.php`)
- Integração com API oficial do PNCP
- Busca de contratos públicos
- Consulta de avisos e editais
- Estatísticas de contratações
- Cache de 1 hora para performance
- Formatação de dados governamentais

**ClaudeService** (`app/Services/ClaudeService.php`)
- Comunicação com API da Anthropic
- Construção de contexto do sistema
- Análise de editais
- Sugestões inteligentes

**ChatController** (`app/Http/Controllers/ChatController.php`)
- Análise de intenções (1000+ linhas)
- Processamento de mensagens
- Base de conhecimento local
- Integração com ClaudeService
- Integração com PNCPService

**Rotas**
```php
POST /api/chat/message
```

### Frontend

**ChatBot Component** (`frontend/src/components/ChatBot.vue`)
- Interface completa do chat
- Gerenciamento de estado
- Formatação de mensagens
- Sugestões rápidas

**Integração**
- Componente global no `App.vue`
- Disponível em todas as páginas (exceto login)

---

## 📈 Estatísticas da Base de Conhecimento

- **1000+ linhas** de código de IA
- **10 categorias** principais de conhecimento (incluindo PNCP)
- **15+ tópicos** detalhados
- **50+ termos** no glossário
- **Contexto do sistema** incluído nas respostas
- **Integração com API governamental** (PNCP) em tempo real
- **Cache inteligente** para otimização de performance

---

## 🚀 Melhorias Futuras Possíveis

1. **Histórico de Conversas**
   - Salvar conversas em banco de dados
   - Recuperar contexto entre sessões
   - Buscar em conversas antigas

2. **Análise de Editais com IA**
   - Upload de PDFs para análise automática
   - Extração de informações com Claude
   - Sugestões de adequação e pontos de atenção
   - Comparação com editais similares

3. **Alertas Proativos**
   - Notificações de prazos importantes
   - Sugestões baseadas em padrões históricos
   - Lembretes de documentos vencendo
   - Recomendações de licitações do PNCP

4. **Expandir Integrações Governamentais**
   - ComprasNet (dados complementares)
   - Portal da Transparência
   - Tribunal de Contas (TCU/TCE)
   - Receita Federal (validação de CNDs)

5. **Exportação e Favoritos**
   - Salvar respostas importantes
   - Exportar conversas em PDF
   - Criar anotações e marcações
   - Compartilhar insights com equipe

6. **Análise Preditiva**
   - Prever chances de sucesso em licitações
   - Sugerir estratégias baseadas em histórico
   - Identificar padrões em editais
   - Alertar sobre editais similares no PNCP

---

## 📝 Notas Técnicas

### Performance
- Respostas locais: < 100ms
- Respostas com Claude API: 1-3 segundos
- Cache de contexto do sistema

### Segurança
- API key armazenada em variável de ambiente
- Validação de inputs
- Rate limiting (se configurado)

### Escalabilidade
- Fácil adicionar novos tópicos
- Base de conhecimento modular
- Fallback para resposta padrão

---

## 🎓 Conclusão

A **EvolutIA** transforma o EvolutionCRM em um sistema inteligente que não apenas gerencia licitações, mas também:

- 🎯 **Educa** os usuários com conhecimento especializado sobre licitações públicas brasileiras
- 🔍 **Consulta** dados governamentais oficiais em tempo real através do PNCP
- 🤖 **Integra** com Claude AI para respostas contextualizadas e inteligentes
- 📊 **Analisa** dados do sistema e oferece insights financeiros
- ⚡ **Otimiza** o fluxo de trabalho com sugestões rápidas e cache inteligente

Com a integração do PNCP, a EvolutIA agora oferece acesso direto a milhares de contratos e editais públicos, permitindo que os usuários acompanhem o mercado de licitações em tempo real e tomem decisões mais informadas.

**Desenvolvido com ❤️ para o EvolutionCRM**
