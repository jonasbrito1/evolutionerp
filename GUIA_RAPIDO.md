# 🚀 Guia Rápido - Evolution CRM

## 🎯 O Sistema Está Pronto!

Todos os arquivos foram **completamente reconstruídos** com código simples, correto e moderno.

---

## ⚡ Como Usar

### 1️⃣ **Iniciar o Frontend**

```bash
cd EvolutionCRM/frontend
npm run dev
```

Acesse: **http://localhost:5173**

### 2️⃣ **Iniciar o Backend**

```bash
cd EvolutionCRM/backend
php artisan serve
```

Backend em: **http://localhost:8000**

### 3️⃣ **Iniciar MySQL** (se necessário)

```bash
cd EvolutionCRM
docker-compose up -d
```

---

## 🔐 Login

### Credenciais Demo:

```
Email: admin@licitaevolution.local
Senha: admin123456
```

Clique em **"👤 Demo"** para preencher automaticamente.

---

## 📱 Testar Responsividade

### Desktop (> 768px):
- Sidebar fixo à esquerda
- Menu completo
- KPI em 4 colunas
- Layout full

### Mobile (< 768px):
- Sidebar em overlay (hamburger menu)
- Menu compacto
- KPI em 1-2 colunas
- Touch-friendly

**Use DevTools (F12) para testar em diferentes tamanhos!**

---

## 🎨 Interface

### Cores Principais:
- **Roxo**: #667eea (Primary)
- **Preto**: #1f2937 (Text)
- **Cinza**: #e5e7eb (Border)
- **Branco**: Background

### Componentes:
1. **Navbar** - Topo com menu
2. **Sidebar** - Menu lateral
3. **Login** - Autenticação
4. **Dashboard** - KPI Cards + Content

---

## ✨ Recursos

✅ **Navbar:**
- Logo + Title
- User info
- Logout button
- Hamburger (mobile)

✅ **Sidebar:**
- 4 menu items
- Active indicator
- Icons
- Responsive

✅ **Login:**
- Gradient background
- Form validation
- Demo button
- Test credentials

✅ **Dashboard:**
- 4 KPI Cards
- Responsive grid
- Content sections
- Clean design

---

## 📂 Estrutura de Arquivos

```
EvolutionCRM/
├── frontend/
│   └── src/
│       ├── App.vue                ← Layout principal
│       ├── components/
│       │   ├── Navbar.vue         ← Barra superior
│       │   └── Sidebar.vue        ← Menu lateral
│       ├── views/
│       │   ├── LoginView.vue      ← Login page
│       │   └── DashboardView.vue  ← Dashboard
│       ├── style.css              ← CSS global
│       └── index.css              ← Tailwind
└── backend/
    └── app/
        └── ... (Laravel files)
```

---

## 🐛 Troubleshooting

### Frontend não conecta:
```bash
# Kill the port
killall node
# Restart
npm run dev
```

### Backend não conecta:
```bash
# Check if running
php artisan serve

# If error, check .env
DB_HOST=127.0.0.1
DB_PORT=3320
```

### MySQL connection error:
```bash
# Restart containers
docker-compose down
docker-compose up -d
```

---

## ✅ Checklist Final

- [ ] Frontend rodando em localhost:5173
- [ ] Backend rodando em localhost:8000
- [ ] MySQL rodando via Docker
- [ ] Login funcionando
- [ ] Dashboard carregando
- [ ] Menu navigation funcional
- [ ] Responsive em mobile
- [ ] Logout funcional

---

## 📞 Informações do Sistema

- **Framework Frontend**: Vue.js 3 + Vite
- **Framework Backend**: Laravel 12
- **Database**: MySQL 8.0
- **Styling**: CSS Puro + Tailwind CSS
- **Build Tool**: Vite 5
- **Node Version**: 18+

---

**Evolution CRM v1.0.0 - Pronto para Uso!** 🎉
