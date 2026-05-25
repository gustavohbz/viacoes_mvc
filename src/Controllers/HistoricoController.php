<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Repositories\HistoricoRepository;
use App\Repositories\UsuarioRepository;

final class HistoricoController
{
  public function index(): void //chama o index do historico
  {
    $repo = new HistoricoRepository();

    $filtros = [ //
      'viacao_id' => trim((string) ($_GET['viacao_id'] ?? '')),
      'user_id'   => trim((string) ($_GET['user_id']   ?? '')),
      'acao'      => trim((string) ($_GET['acao']       ?? '')),
      'data_ini'  => trim((string) ($_GET['data_ini']   ?? '')),
      'data_fim'  => trim((string) ($_GET['data_fim']   ?? '')),
    ];

    View::render('admin/historico/index', [
      'historico' => $repo->all($filtros),
      'viacoes'   => $repo->listViacoes(),
      'usuarios'  => $repo->listUsuarios(),
      'filtros'   => $filtros,
    ]);
  }
    public function index_viacao($id): void //chama o index do historico
    {
        $repo = new HistoricoRepository();

        $filtros = [ //
            'viacao_id' => trim((string) $id), //aqui eu recebo o id do botao
            'user_id'   => trim((string) ($_GET['user_id']   ?? '')),
            'acao'      => trim((string) ($_GET['acao']       ?? '')),
            'data_ini'  => trim((string) ($_GET['data_ini']   ?? '')),
            'data_fim'  => trim((string) ($_GET['data_fim']   ?? '')),
        ];

        View::render('admin/historico/index_viacoes', [
            'historico' => $repo->all($filtros),
            'viacoes'   => $repo->listViacoes(),
            'usuarios'  => $repo->listUsuarios(),
            'filtros'   => $filtros,
        ]);
    }


    public function index_usuarios($id): void
    {

        $repo = new HistoricoRepository();

        $filtros = [
            'user_id'   => trim((string) $id),
            'acao'      => trim((string) ($_GET['acao']       ?? '')),
            'data_ini'  => trim((string) ($_GET['data_ini']   ?? '')),
            'data_fim'  => trim((string) ($_GET['data_fim']   ?? '')),
        ];

        View::render('admin/historico/index_usuarios', [
            'historico' => $repo->all($filtros),
            'usuarios'  => $repo->listUsuarios(), // Enviado no plural
            'filtros'   => $filtros,
        ]);
    }
}
