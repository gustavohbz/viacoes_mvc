<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Repositories\HistoricoRepository;
use PHPUnit\Exception;

final class HistoricoController
{
    public function index(): void
    {
        $repo = new HistoricoRepository();
        $status = trim((string) ($_GET['status'] ?? ''));
        $busca = trim((string) ($_GET['busca'] ?? ''));
        if (strtolower($busca) === 'deletados') {
            $status = 'deletados';
        }

        $filtros = [
            'viacao_id' => trim((string) ($_GET['viacao_id'] ?? '')),
            'user_id'   => trim((string) ($_GET['user_id']   ?? '')),
            'acao'      => trim((string) ($_GET['acao']       ?? '')),
            'data_ini'  => trim((string) ($_GET['data_ini']   ?? '')),
            'data_fim'  => trim((string) ($_GET['data_fim']   ?? '')),
            'status'    => $status, //recebe o status tratado
            'busca'     => $busca,
        ];

        View::render('admin/historico/index', [
            'historico' => $repo->all($filtros),
            'viacoes'   => $repo->listViacoes(),
            'usuarios'  => $repo->listUsuarios(),
            'filtros'   => $filtros,
        ]);
    }

    public function index_viacao($id): void
    {
        $repo = new HistoricoRepository();

        $status = trim((string) ($_GET['status'] ?? ''));

        $filtros = [
            'viacao_id' => trim((string)$id),
            'user_id'   => trim((string)($_GET['user_id'] ?? '')),
            'acao'      => trim((string)($_GET['acao']      ?? '')),
            'data_ini'  => trim((string)($_GET['data_ini']  ?? '')),
            'data_fim'  => trim((string)($_GET['data_fim']  ?? '')),
            'status'    => $status, // ADAPTADO: Garante que a lixeira funcione aqui também
        ];

        $historico = $repo->all($filtros);

        if (!empty($historico)) {
            $filtros['nome'] = $historico[0]->viacaoNome ?? "ID #{$id}";
        } else {
            $listaViacoes = $repo->listViacoes();
            $nomeEncontrado = null;

            foreach ($listaViacoes as $v) {
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

        $status = trim((string) ($_GET['status'] ?? ''));

        $filtros = [
            'viacao_id' => trim((string)($_GET['viacao_id'] ?? '')),
            'user_id'   => trim((string)$id),
            'acao'      => trim((string)($_GET['acao']      ?? '')),
            'data_ini'  => trim((string)($_GET['data_ini']  ?? '')),
            'data_fim'  => trim((string)($_GET['data_fim']  ?? '')),
            'status'    => $status, // garante que funcione mesmo com soft delete
        ];

        $historico = $repo->all($filtros);
        if (!empty($historico)) {
            $filtros['nome'] = $historico[0]->usuarioNome ?? "ID #{$id}";
        } else {
            $listaUsuarios = $repo->listUsuarios();
            $nomeEncontrado = null;

            foreach ($listaUsuarios as $u) {
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
//    public function restaurar(array $params): void{
//        $id = (int) ($params['id'] ?? 0);
//        try {
//            $this->service->restaurar($id); //se a variavel id,convertida em inteiro receber na url um valor diferente de 0
//            //tente chamar o service restaurar com o parametro da variavel já pega
//            $_SESSION['sucess'] = 'Restaurado com sucesso';
//        } catch (Exception $e){
//            $_SESSION['error'] = 'error ao restaurar: ' . $e->getMessage();
//        }
//        header('Location: /admin/viacoes');
//    }
}