# Site/
/Site
├── db.php                → Responsável pela conexão com o banco de dados MySQL.
├── schema.sql            → Script de criação das tabelas e estrutura do banco de dados.
├── auth_login.php        → Script de login de usuários.
├── auth_register.php     → Script de registro/cadastro de novos usuários.
├── auth_logout.php       → Realiza o logout do usuário e finaliza a sessão.
├── check_login.php       → Verifica se as credenciais de login são válidas.
├── index.html            → Página inicial estática ou pública.
├── loading.html          → Tela de carregamento inicial do sistema.
├── README.md             → Documento de instruções ou descrição do projeto.
└── public/               
    ├── index.php             → Painel principal do sistema (acesso restrito).
    ├── login.html            → Página de login do sistema.
    ├── register.html         → Página de cadastro de novos usuários.
    ├── require_login.php     → Script que restringe o acesso às páginas sem autenticação.
    └── api/                  
        ├── products_list.php     → Lista todos os produtos cadastrados.
        ├── products_create.php   → Cria um novo produto no sistema.
        ├── products_delete.php   → Exclui um produto do banco de dados.
        └── stock_move.php        → Realiza movimentações de estoque (entrada/saída).

# Como funciona

- `db.php` → conecta no MySQL (ajuste usuário/senha).
- `schema.sql` → cria tabelas do sistema.
- `auth_*` → controla login, cadastro e logout.
- `require_login.php` → protege páginas internas.
- `public/` → parte visível no navegador.
- `api/` → endpoints para mexer em produtos e estoque.

Como rodar

Crie o banco e rode o `schema.sql`.
Configure db.php com seus dados do banco.
Inicie um servidor PHP dentro da pasta `Site/public`:
php -S localhost:8080 -t `Site/public`


# Abra http://localhost:8080
 no navegador.


