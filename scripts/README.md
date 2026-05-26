# Scripts PHP — Bilheteira Casa da Música

Backend PHP com base de dados SQLite3 para o projeto DEAPC.

---

## Base de Dados

Ficheiro: `data/casaMusica.db`

### Tabelas

| Tabela | Descrição |
|---|---|
| `utilizadores` | Clientes, vendedores e administradores |
| `eventos` | Eventos publicados, rascunhos e cancelados |
| `precos` | Preços por tipo de bilhete (normal, jovem, sénior) por evento |
| `compras` | Compras online e presenciais |
| `itens_compra` | Linhas de cada compra (tipo, quantidade, preço unitário) |
| `acessos` | Registo de data/hora e IP de cada login |

### Inicialização

```bash
php scripts/setup.php
```

Cria as tabelas e insere dados de exemplo (utilizadores, eventos e compras).

---

## Scripts

### Infraestrutura

| Script | Descrição |
|---|---|
| `db.php` | Ligação PDO ao SQLite, criação das tabelas (`inicializar_bd()`) |
| `session.php` | Gestão de sessões, verificação de permissões, CSRF, respostas JSON |
| `setup.php` | Inicialização da BD com dados de exemplo |

### Autenticação

| Script | User Story | Descrição |
|---|---|---|
| `login.php` | Auth | Valida email/password, inicia sessão e regista acesso |
| `novoregisto.php` | CLI07 | Regista novo utilizador, verifica email duplicado |
| `logout.php` | Auth | Termina a sessão e redireciona para o início |
| `alterar_password.php` | Q8a | Altera a palavra-passe com validação da atual |

### Cliente

| Script | User Story | Descrição |
|---|---|---|
| `listar_eventos.php` | CLI01, CLI02 | Lista eventos publicados; aceita filtros `pesquisa`, `categoria` e `data` (JSON) |
| `detalhe_evento.php` | CLI03 | Devolve detalhe de um evento com preços e lugares disponíveis (JSON) |
| `comprar_bilhete.php` | CLI04, CLI05 | Processa compra online, valida disponibilidade, gera referência e redireciona |
| `bilhete.php` | CLI06, CLI08 | Devolve detalhe de uma compra pela referência (JSON) |
| `historico.php` | CLI08 | Lista compras do utilizador autenticado; filtro `todos`, `futuros`, `passados` (JSON) |
| `perfil.php` | CLI07 | Atualiza dados pessoais do utilizador autenticado |

### Administrador

| Script | User Story | Descrição |
|---|---|---|
| `admin_criar_evento.php` | ADM01, ADM04 | Cria evento com preços (POST → JSON) |
| `admin_editar_evento.php` | ADM02, ADM04 | Edita evento e preços existentes (POST → JSON) |
| `admin_cancelar_evento.php` | ADM03 | Cancela evento e respetivas compras; devolve lista de compradores a notificar |
| `admin_vendas.php` | ADM05 | Relatório de vendas por evento (ocupação, receita, canal) e últimas 50 transações (JSON) |
| `admin_utilizadores.php` | ADM06 | Lista utilizadores com filtros; ações de suspender, ativar e alterar perfil (JSON) |

### Vendedor

| Script | User Story | Descrição |
|---|---|---|
| `vendedor_vender.php` | VND01, VND03, VND04 | Regista venda presencial; aceita pagamento em dinheiro e outros métodos |
| `vendedor_disponibilidade.php` | VND02 | Devolve lugares disponíveis em tempo real por evento (JSON) |

### Administração do sistema

| Script | Requisito | Descrição |
|---|---|---|
| `registos_acesso.php` | Q8b, Q8c | Lista registos de acesso com data/hora e IP; resumo por utilizador (JSON) |

---

## Credenciais de teste

| Utilizador | Email | Password | Perfil |
|---|---|---|---|
| Paulo Silva | `paulo@cdmusica.pt` | `admin123` | Administrador |
| João Rodrigues | `joao@cdmusica.pt` | `vendedor123` | Vendedor |
| Ana Ferreira | `ana@exemplo.com` | `cliente123` | Cliente |
| Carlos Mota | `carlos@gmail.com` | `cliente123` | Cliente |

---

## Fluxo de chamadas

```
index.html     → listar_eventos.php    (GET, JSON)
evento.html    → detalhe_evento.php    (GET, JSON)
checkout.html  → comprar_bilhete.php   (POST, redirect)
confirmacao.html → bilhete.php         (GET, JSON)
login.html     → login.php             (POST, redirect)
login.html     → novoregisto.php       (POST, redirect)
conta.html     → historico.php         (GET, JSON)
conta.html     → perfil.php            (POST, redirect)
conta.html     → alterar_password.php  (POST, redirect)
```
