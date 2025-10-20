================================================================================
                    EVOLUTION ERP - SISTEMA PRONTO!
================================================================================

ACESSO IMEDIATO:
================================================================================

1. ABRA NO NAVEGADOR:
   http://localhost:5173

2. LOGIN:
   Email: admin@licitaevolution.local
   Senha: admin123456

3. PRONTO!
   Você está dentro do sistema.

================================================================================
INICIAR O SISTEMA (Se não estiver rodando):
================================================================================

OPÇÃO A - SUPER SIMPLES (Recomendado para iniciantes):
------------------------------------------------------
1. Abra 3 Command Prompts separados

2. TERMINAL 1 - Backend:
   cd c:\Users\Home\Desktop\Projects\EvolutionERP\backend
   php artisan serve --host=127.0.0.1 --port=8000

3. TERMINAL 2 - Frontend:
   cd c:\Users\Home\Desktop\Projects\EvolutionCRM\frontend
   npm run dev

4. TERMINAL 3 - Banco de Dados (opcional, se não tiver Docker):
   docker run -d --name evcrm-mysql \
     -e MYSQL_DATABASE=licita_evolution \
     -e MYSQL_USER=licita_user \
     -e MYSQL_PASSWORD=licita_password_secure \
     -p 3320:3306 mysql:8.0

5. ABRA NO NAVEGADOR:
   http://localhost:5173


OPÇÃO B - AUTO (Se tem Docker):
-------------------------------
1. Abra Command Prompt no diretório do projeto:
   cd c:\Users\Home\Desktop\Projects\EvolutionCRM

2. Execute:
   docker-compose up -d

3. Aguarde 30 segundos

4. Abra:
   http://localhost:5173


OPÇÃO C - BOTÃO (Windows):
---------------------------
1. Dê duplo clique em:
   INICIAR.bat

2. Espere abrir o navegador automaticamente

================================================================================
INFORMAÇÕES IMPORTANTES:
================================================================================

URLS:
  Frontend:   http://localhost:5173
  Backend:    http://localhost:8000/api
  PhpMyAdmin: http://localhost:9000
  Teste API:  [abra TEST_LOGIN.html no navegador]

CREDENCIAIS:
  Admin:   admin@licitaevolution.local / admin123456
  Gerente: gerente@licitaevolution.local / gerente123456

BANCO DE DADOS:
  Host:     localhost:3320
  Database: licita_evolution
  User:     licita_user
  Password: licita_password_secure

PORTAS USADAS:
  Laravel:     8000
  Vue.js:      5173
  MySQL:       3320
  PhpMyAdmin:  9000

================================================================================
SE HOUVER PROBLEMAS:
================================================================================

❌ "Não consegue conectar a localhost:5173"
   → Verifique se o Terminal 2 (Frontend) está rodando
   → Rode novamente: npm run dev no diretório frontend

❌ "Erro ao fazer login"
   → Verifique se o Terminal 1 (Backend) está rodando
   → Rode novamente: php artisan serve

❌ "Erro de conexão ao banco"
   → Verifique se MySQL está rodando (docker ps)
   → Se não tiver, rode o Terminal 3 com o comando docker

❌ "Porta já em uso"
   → Mude a porta no comando:
   → php artisan serve --host=127.0.0.1 --port=8001 (para 8001)
   → npm run dev -- --port 5174 (para 5174)

================================================================================
TECNOLOGIAS:
================================================================================

Frontend:
  - Vue.js 3 com Composition API
  - Vite (Build tool)
  - TailwindCSS (Styling)
  - Pinia (State management)
  - Axios (HTTP client)

Backend:
  - Laravel 12
  - PHP 8.2
  - MySQL 8.0
  - Eloquent ORM

Autenticação:
  - Tokens simples (sem Sanctum - mais fácil)
  - Armazenados em cache (servidor)
  - JWT-like (80 caracteres aleatórios)

================================================================================
ESTRUTURA:
================================================================================

EvolutionCRM/
├── backend/                    # Laravel API
│   ├── app/Http/Controllers/
│   ├── app/Models/
│   ├── routes/
│   ├── database/
│   └── .env
│
├── frontend/                   # Vue.js App
│   ├── src/
│   │   ├── views/
│   │   ├── stores/
│   │   ├── services/
│   │   └── router/
│   ├── package.json
│   └── .env
│
├── COMECE_AQUI.md             # Guia principal
├── README.txt                  # Este arquivo
├── TEST_LOGIN.html            # Teste de login
├── INICIAR.bat                # Script auto-start
├── docker-compose.yml         # Docker config
└── .gitignore

================================================================================
PRÓXIMAS AÇÕES:
================================================================================

1. ✅ Acesse http://localhost:5173
2. ✅ Faça login com admin@licitaevolution.local / admin123456
3. ✅ Explore o dashboard
4. ✅ Crie novos editais (licitações)
5. ✅ Customize conforme necessidade
6. 🚀 Deploy para produção (Hostinger VPS)

================================================================================
SUPORTE & DOCUMENTAÇÃO:
================================================================================

📚 Arquivos de referência:
   - COMECE_AQUI.md     - Guia completo em Markdown
   - TEST_LOGIN.html    - Teste a API de login
   - README.txt         - Este arquivo

🔧 Logs em tempo real:
   Backend: php artisan pail
   Frontend: Abra F12 > Console no navegador
   Database: http://localhost:9000 (phpMyAdmin)

================================================================================
VERSÃO & STATUS:
================================================================================

Nome:       Evolution CRM
Versão:     1.0.0 Beta
Ambiente:   Local Development
Status:     ✅ Pronto para Usar
Atualizado: 16/10/2025

================================================================================

Aproveite! 🎉

Se precisar de ajuda, consulte os arquivos de documentação ou abra o
TEST_LOGIN.html para testar a API.

================================================================================
