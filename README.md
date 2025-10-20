# 📊 EvolutionERP

![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)
![Laravel](https://img.shields.io/badge/Laravel-12.0-red.svg)
![Vue.js](https://img.shields.io/badge/Vue.js-3.5-green.svg)
![PHP](https://img.shields.io/badge/PHP-8.2-purple.svg)
![License](https://img.shields.io/badge/license-MIT-yellow.svg)

> Sistema ERP completo especializado em gestão de licitações públicas brasileiras

---

## 🎯 Sobre o Projeto

**EvolutionERP** é um sistema ERP (Enterprise Resource Planning) moderno e inteligente, desenvolvido especificamente para empresas que participam de processos licitatórios no Brasil. O sistema integra gestão de editais, controle financeiro, documentação e uma assistente de IA para automatizar processos.

### ✨ Principais Características

- 🤖 **EvolutIA** - Assistente inteligente com Claude AI integrado
- 📝 **Gestão Completa de Editais** - Acompanhamento do início ao fim
- 💰 **Controle Financeiro** - Receitas, despesas e relatórios automatizados
- 📄 **Gestão Documental** - Upload, categorização e versionamento
- 📊 **Dashboard Analítico** - Métricas e KPIs em tempo real
- 🔄 **Integração PNCP** - Portal Nacional de Contratações Públicas
- 📱 **Interface Responsiva** - Design moderno e mobile-first
- 🔐 **Multi-tenant** - Suporte a múltiplas empresas isoladas

---

## 🛠️ Stack Tecnológica

### Backend
- **Laravel 12.0** - Framework PHP moderno
- **PHP 8.2+** - Linguagem de programação
- **MySQL 8.0+** - Banco de dados
- **Laravel Sanctum** - Autenticação API
- **Claude AI** - Inteligência artificial da Anthropic

### Frontend
- **Vue.js 3.5** - Framework JavaScript progressivo
- **Vite 5.4** - Build tool rápido
- **TailwindCSS 3.4** - Framework CSS utility-first
- **Chart.js 4.5** - Gráficos e visualizações
- **Pinia 3.0** - Gerenciamento de estado

---

## 🚀 Instalação Rápida

### Pré-requisitos

- PHP >= 8.2
- Composer >= 2.0
- Node.js >= 18.x
- MySQL >= 8.0
- Git

### Passo a Passo

1. **Clone o repositório**
```bash
git clone https://github.com/jonasbrito1/evolutionerp.git
cd evolutionerp
```

2. **Configure o Backend**
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
```

3. **Configure o Frontend**
```bash
cd ../frontend
npm install
```

4. **Inicie os Servidores**

**Terminal 1 (Backend):**
```bash
cd backend
php artisan serve
```

**Terminal 2 (Frontend):**
```bash
cd frontend
npm run dev
```

5. **Acesse o Sistema**
- Frontend: http://localhost:5173
- Backend API: http://localhost:8000/api

### Login Padrão
```
Email: admin@evolutionerp.com
Senha: password123
```

---

## 📚 Documentação

A documentação completa está disponível em:

- **[DOCUMENTACAO_COMPLETA_ERP.md](DOCUMENTACAO_COMPLETA_ERP.md)** - Documentação técnica completa
- **[COMECE_AQUI.md](COMECE_AQUI.md)** - Guia rápido de início
- **[EVOLUTIA_README.md](EVOLUTIA_README.md)** - Documentação da EvolutIA
- **[COMO_ACESSAR_LOCALMENTE.md](COMO_ACESSAR_LOCALMENTE.md)** - Guia de instalação local

---

## 🧩 Módulos Principais

### 1. 📝 Gestão de Editais
- CRUD completo de licitações
- Upload de PDFs e anexos
- Filtros avançados (status, modalidade, período)
- Integração com PNCP
- Exportação para Excel/PDF

### 2. 💰 Controle Financeiro
- Gestão de receitas e despesas
- Categorização de transações
- Resumo financeiro com gráficos
- Relatórios por período
- Formatação monetária brasileira

### 3. 📄 Gestão Documental
- Upload multi-formato (PDF, DOC, XLS, etc.)
- Categorização e tags
- Controle de vencimento
- Versionamento de documentos
- Alertas automáticos

### 4. 📋 Kanban de Processos
- Visualização de fluxo de editais
- Drag & drop entre colunas
- Histórico de movimentações
- Filtros por status e valor

### 5. 🤖 EvolutIA - Assistente Inteligente
- 20+ intenções reconhecidas
- Cadastros via linguagem natural
- Base de conhecimento sobre licitações
- Análise automática de documentos
- Integração com PNCP
- Guias passo a passo

### 6. 📊 Dashboard Analítico
- KPIs em tempo real
- Gráficos interativos (Chart.js)
- Resumo financeiro
- Alertas de vencimento
- Estatísticas de licitações

---

## 🤖 EvolutIA - Exemplos de Uso

### Cadastros Automáticos
```
"Cadastrar edital PE 001/2024 da Prefeitura Municipal,
objeto aquisição de computadores, valor R$ 150.000"

"Registrar receita de R$ 50.000 do contrato X em 20/10/2024"
```

### Consultas Inteligentes
```
"Quais editais estão abertos?"
"Resumo financeiro do sistema"
"Documentos vencendo"
```

### Guias e Tutoriais
```
"Como cadastrar um edital?"
"O que é pregão eletrônico?"
"Passo a passo para cadastrar despesa"
```

---

## 🔒 Segurança

- ✅ Laravel Sanctum - Autenticação baseada em tokens
- ✅ CSRF Protection - Proteção contra Cross-Site Request Forgery
- ✅ SQL Injection Prevention - Eloquent ORM
- ✅ XSS Protection - Sanitização de inputs/outputs
- ✅ Multi-tenant Isolation - Dados isolados por empresa
- ✅ Role-Based Access Control (RBAC)

---

## 📈 Performance

- ⚡ First Contentful Paint (FCP): ~1.2s
- ⚡ Time to Interactive (TTI): ~2.5s
- ⚡ Bundle Size: ~450KB (gzipped)
- ⚡ API Response Time: ~200ms (média)

---

## 🗺️ Roadmap

### v2.1 (Próximo Release)
- [ ] Notificações Push em tempo real
- [ ] Relatórios avançados customizáveis
- [ ] Modo Offline (PWA)
- [ ] App Mobile (React Native)

### v2.2
- [ ] IA de Análise de Editais
- [ ] OCR Automático
- [ ] Integração Contábil
- [ ] Workflow Customizável

### v3.0
- [ ] Marketplace de Editais
- [ ] Machine Learning
- [ ] Blockchain para registro de propostas

---

## 🤝 Contribuindo

Contribuições são bem-vindas! Para contribuir:

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

---

## 📄 Licença

Este projeto está sob a licença **MIT**. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

---

## 👨‍💻 Autor

**Jonas Brito**

- GitHub: [@jonasbrito1](https://github.com/jonasbrito1)
- LinkedIn: [Jonas Brito](https://linkedin.com/in/jonasbrito1)

---

## 🙏 Agradecimentos

- **Laravel** - Framework PHP excepcional
- **Vue.js** - Framework JavaScript reativo
- **Anthropic** - Claude AI que potencializa a EvolutIA
- **TailwindCSS** - Framework CSS produtivo
- **PNCP** - Portal de dados públicos

---

<p align="center">
  <strong>Desenvolvido com ❤️ para facilitar a gestão de licitações públicas no Brasil</strong>
</p>

<p align="center">
  <sub>© 2024 EvolutionERP - Todos os direitos reservados</sub>
</p>
