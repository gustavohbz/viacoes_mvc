<?php
declare(strict_types=1);

use App\Core\Router;
use App\Core\AuthMiddleware;
use App\Core\GuestMiddleware;
use App\Controllers\HomeController;
use App\Controllers\ViacaoController;
use App\Controllers\HistoricoController;
use App\Controllers\AuthController;
use App\Controllers\UsuarioController;


$router = new Router();

// ── Pública ───────────────────────────────────────────────
$router->get('/', [HomeController::class, 'index']);

// ── Autenticação ──────────────────────────────────────────
// CORREÇÃO: GuestMiddleware agora é registrado corretamente.
// Na versão original, o Router retornava void, logo ->middleware() nunca funcionou.
$router->get('/login',  [AuthController::class, 'showLogin'])
  ->middleware(GuestMiddleware::class);

$router->post('/login', [AuthController::class, 'login'])
  ->middleware(GuestMiddleware::class);

$router->get('/logout', [AuthController::class, 'logout']);

// ── Admin — Viações ───────────────────────────────────────
// CORREÇÃO: todas as rotas admin protegidas com AuthMiddleware.
//rota de index,criacao,edição,infos sobre uma em especifico com metodo PUT
$router->get('/admin/viacoes',           [ViacaoController::class, 'index'])
  ->middleware(AuthMiddleware::class);

$router->get('/admin/viacoes/create',    [ViacaoController::class, 'create'])
  ->middleware(AuthMiddleware::class);

$router->post('/admin/viacoes',          [ViacaoController::class, 'store'])
  ->middleware(AuthMiddleware::class);

$router->get('/admin/viacoes/{id}/edit', [ViacaoController::class, 'edit'])
  ->middleware(AuthMiddleware::class);
//rota com metodo PUT,atualiza os dados existentes
$router->put('/admin/viacoes/{id}',      [ViacaoController::class, 'update'])
  ->middleware(AuthMiddleware::class);

$router->post('/admin/viacoes/{id}/restore',      [ViacaoController::class, 'restore'])
    ->middleware(AuthMiddleware::class);

$router->delete('/admin/viacoes/{id}',   [ViacaoController::class, 'destroy'])
  ->middleware(AuthMiddleware::class);
//rota de historico proprio
$router->get('/admin/viacoes/{id}/historico-viacao', [HistoricoController::class, 'index_viacao'])
    ->middleware(AuthMiddleware::class);


// ── Admin — Usuários ──────────────────────────────────────
// NOVAS ROTAS: Mapeamento completo do CRUD de Usuários protegido por login
$router->get('/admin/usuarios',           [UsuarioController::class, 'index'])
  ->middleware(AuthMiddleware::class);

$router->get('/admin/usuarios/create',    [UsuarioController::class, 'create'])
  ->middleware(AuthMiddleware::class);

$router->post('/admin/usuarios',          [UsuarioController::class, 'store'])
  ->middleware(AuthMiddleware::class);

$router->get('/admin/usuarios/{id}/edit', [UsuarioController::class, 'edit'])
  ->middleware(AuthMiddleware::class);

$router->put('/admin/usuarios/{id}',      [UsuarioController::class, 'update'])
  ->middleware(AuthMiddleware::class);

$router->delete('/admin/usuarios/{id}',   [UsuarioController::class, 'destroy'])
  ->middleware(AuthMiddleware::class);
//historico especifico do usuario
$router->get('/admin/usuarios/{id}/historico-usuarios', [HistoricoController::class, 'index_usuarios'])
    ->middleware(AuthMiddleware::class);

// ── Admin — Histórico ─────────────────────────────────────
$router->get('/admin/historico', [HistoricoController::class, 'index'])
  ->middleware(AuthMiddleware::class);

return $router;
