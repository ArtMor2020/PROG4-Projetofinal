# TagScribe!

MVP do projeto TagScribe!, desenvolvido como parte da disciplina de Programação IV.

---

## Equipe

Arthur Romanatto Moro

---

## Descrição do Projeto

Este projeto é um MVP para um sistema de organização de arquivos armazenados na nuvem.

O sistema é composto por um frontend em Next.js para interface com o usuário e um backend em CodeIgniter 4 para processamento e regras de negócio. Os dados são armazenados em um banco de dados MySQL.

---

## Stack Tecnológica

Frontend:

  * Framework: Next.js (TypeScript)
  * Build tool: integrado do Next.js
  * Gerenciador de pacotes: npm/yarn/pnpm

Backend:

  * Framework: CodeIgniter 4 (PHP 8+)
  * Arquitetura: MVC + Repository/Service/Entity
  * Configuração: .env para variáveis sensíveis

Banco de Dados:

  * SGBD: MySQL
  * ORM: Query Builder nativo do CodeIgniter 4
  * Migrations: suportado pelo CodeIgniter

---

## Configuração e Execução

1. Pré-requisitos

  * Node.js 18+
  * PHP 8.1+
  * Composer
  * MySQL 8+

2. Configuração do Backend

  ```bash
  cd backend
  cp .env.example .env
  composer install
  php spark migrate
  php spark serve
  ```

3. Configuração do Frontend

  ```bash
  cd frontend
  cp env .env
  npm install
  npm run dev
  ```

🗄️ Banco de Dados

Criar banco tagscribe no MySQL:

  ```bash
  CREATE DATABASE tagscribe;
  ```

Configurar o .env:

  ```bash
  database.default.hostname = localhost
  database.default.database = tagscribe
  database.default.username = seu_username
  database.default.password = sua_senha
  database.default.DBDriver = MySQLi
  ```
---

## Proximos Passos

* Definir requisitos funcionais detalhados
* Criar seeds mínimas
* Implementar autenticação
* Criar rotas básicas no backend
* Integração inicial frontend-backend

