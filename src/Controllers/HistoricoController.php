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
  //embaixo e a mesma função do usuarios_index apenas adaptei
    public function index_viacao($id): void
    {
        $repo = new HistoricoRepository();

        $filtros = [
            'viacao_id' => trim((string)$id),
            'user_id'   => trim((string)($_GET['user_id'] ?? '')),
            'acao'      => trim((string)($_GET['acao']      ?? '')),
            'data_ini'  => trim((string)($_GET['data_ini']  ?? '')),
            'data_fim'  => trim((string)($_GET['data_fim']  ?? '')),
        ];

        $historico = $repo->all($filtros);

        if (!empty($historico)) {
            // AJUSTADO: Forçando o camelCase padrão do projeto (v minúsculo)
            $filtros['nome'] = $historico[0]->viacaoNome ?? "ID #{$id}";
        } else {
            $listaViacoes = $repo->listViacoes();
            $nomeEncontrado = null;

            foreach ($listaViacoes as $v) {
                // CORRIGIDO: Como vem do FETCH_ASSOC, a leitura correta é como array: $v['id']
                $idViacao = $v['id'] ?? 0;

                if ((int)$idViacao === (int)$id) {
                    $nomeEncontrado = $v['nome'] ?? null;
                    break;
                }
            }
            $filtros['nome'] = $nomeEncontrado ?? "ID #{$id}";
        }

        View::render('admin/historico/index_viacoes', [
            'historico' => $historico,
            'viacoes'   => $repo->listViacoes(),
            'usuarios'  => $repo->listUsuarios(),
            'filtros'   => $filtros
        ]);
    }


    public function index_usuarios($id): void
    {
        $repo = new HistoricoRepository();

        $filtros = [
            'viacao_id' => trim((string)($_GET['viacao_id'] ?? '')),
            'user_id'   => trim((string)$id),
            'acao'      => trim((string)($_GET['acao']      ?? '')),
            'data_ini'  => trim((string)($_GET['data_ini']  ?? '')),
            'data_fim'  => trim((string)($_GET['data_fim']  ?? '')),
        ];

        $historico = $repo->all($filtros);
        if (!empty($historico)) {
            $filtros['nome'] = $historico[0]->usuarioNome ?? "ID #{$id}";
        } else {
            $listaUsuarios = $repo->listUsuarios();
            $nomeEncontrado = null;

            foreach ($listaUsuarios as $u) {
                // Garante a checagem do ID independente se for propriedade ou camelCase
                $idUsuario = $u->id ?? ($u->userId ?? 0);
                if ((int)$idUsuario === (int)$id) {
                    $nomeEncontrado = $u->nome ?? null;
                    break;
                }
            }
            $filtros['nome'] = $nomeEncontrado ?? "ID #{$id}";
        }
        View::render('admin/historico/index_usuarios', [
            'historico' => $historico,
            'viacoes'   => $repo->listViacoes(),
            'usuarios'  => $repo->listUsuarios(),
            'filtros'   => $filtros
        ]);
    }
}
