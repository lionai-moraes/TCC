# Site/
 ├── db.php              → Conexão com o banco MySQL
 ├── schema.sql          → Script para criar as tabelas
 ├── auth_login.php      → Login
 ├── auth_register.php   → Registro de usuário
 ├── auth_logout.php     → Logout
 └── public/
      ├── index.php      → Página principal (precisa estar logado)
      ├── login.html     → Tela de login
      ├── register.html  → Tela de cadastro
      ├── require_login.php → Bloqueia acesso se não tiver login
      └── api/
           ├── products_list.php   → Lista produtos
           ├── products_create.php → Cria produto
           ├── products_delete.php → Apaga produto
           └── stock_move.php      → Movimenta estoque
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


