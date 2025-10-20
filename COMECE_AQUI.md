# 🚀 Evolution ERP - Comece Aqui!

## ✅ Sistema Pronto para Usar

Bem-vindo ao **Evolution ERP** - um sistema ERP inteligente para gerenciamento de licitações públicas.

---

## 🎯 ACESSO IMEDIATO (3 PASSOS)

### 1️⃣ Abra o Navegador
```
http://localhost:5173
```

### 2️⃣ Faça Login
```
Email:  admin@licitaevolution.local
Senha:  admin123456
```

### 3️⃣ Pronto!
Você estará dentro do sistema.

---

## 🔧 INICIAR O SISTEMA

### Se NÃO estiver rodando:

#### **Opção A: Super Simples (Recomendado)**
```bash
cd c:\Users\Home\Desktop\Projects\EvolutionCRM

# Terminal 1
cd backend && php artisan serve --host=127.0.0.1 --port=8000

# Terminal 2
cd ../frontend && npm run dev

# Terminal 3 (MySQL - se necessário)
docker run -d --name evcrm-mysql \
  -e MYSQL_DATABASE=licita_evolution \
  -e MYSQL_USER=licita_user \
  -e MYSQL_PASSWORD=licita_password_secure \
  -p 3320:3306 mysql:8.0
```

#### **Opção B: Docker Automático**
```bash
cd c:\Users\Home\Desktop\Projects\EvolutionCRM
docker-compose up -d
```

---

## 👥 USUÁRIOS DE TESTE

| Papel | Email | Senha |
|-------|-------|-------|
| **Admin** | admin@licitaevolution.local | admin123456 |
| **Gerente** | gerente@licitaevolution.local | gerente123456 |

---

## 🌐 URLS IMPORTANTES

| Serviço | URL |
|---------|-----|
| Frontend | http://localhost:5173 |
| Backend API | http://localhost:8000/api |
| Teste de Login | [TEST_LOGIN.html](TEST_LOGIN.html) |
| phpMyAdmin | http://localhost:9000 |

---

## 🐛 PROBLEMAS?

### "Não consegue conectar ao localhost:5173"
```bash
# Reinicie o frontend
cd frontend
npm install  # se necessário
npm run dev
```

### "Erro ao fazer login"
```bash
# Reinicie o backend
cd backend
php artisan serve --host=127.0.0.1 --port=8000
```

### "Erro de conexão ao banco"
```bash
# Verifique MySQL
docker ps | grep mysql

# Se não está rodando:
docker run -d --name evcrm-mysql \
  -e MYSQL_DATABASE=licita_evolution \
  -e MYSQL_USER=licita_user \
  -e MYSQL_PASSWORD=licita_password_secure \
  -p 3320:3306 mysql:8.0
```

---

## 📊 ESTRUTURA DO PROJETO

```
EvolutionCRM/
├── backend/              # Laravel 12 API
│   ├── app/Http/Controllers/AuthController.php
│   ├── app/Models/
│   ├── routes/api.php
│   └── database/
│
├── frontend/             # Vue 3 + Vite
│   ├── src/views/LoginView.vue
│   ├── src/stores/
│   └── src/services/
│
└── TEST_LOGIN.html       # Teste rápido
```

---

## 🧹 LIMPEZA

### Limpar Cache
```bash
cd backend
php artisan cache:clear
php artisan config:cache
```

### Reset Completo
```bash
docker-compose down -v
docker-compose up -d
# Ou reiniciar os 3 terminais manualmente
```

---

## 💾 CREDENCIAIS DO BANCO

```
Host:     localhost:3320
Database: licita_evolution
User:     licita_user
Password: licita_password_secure
```

---

## 📝 Próximas Ações

1. ✅ Acesse http://localhost:5173
2. ✅ Faça login com admin/admin123456
3. ✅ Explore o dashboard
4. ✅ Crie seus dados de teste

---

## 🆘 SUPORTE

- **Backend Logs**: `php artisan pail` (real-time)
- **Frontend Console**: Abra F12 > Console
- **Database**: http://localhost:9000 (phpMyAdmin)

---

**Versão**: Evolution CRM v1.0.0 Beta
**Status**: ✅ Pronto para Usar
**Ambiente**: Local Development

---

**Aproveite! 🎉**
