Passo a passo executado

> Necessário rodar `php artisan optimize a cada atualizaca!

## 01 - Instalar API

`php artisan install:api`

Isto vai instalar:

- Rotas de API
- Sanctum
- Migration de personal_access_tokens

## 02 - Criando autenticação

- `php artisan make:controller BaseController`
- `php artisan make:controller AuthController`

## 03 - Criar entidade produto

- `php artisan make:model Product -m`
- `php artisan make:resource Product`
- `php artisan make:controller ProductController`