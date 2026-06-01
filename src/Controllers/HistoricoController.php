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
        $status = trim((string)($_GET['status'] ?? ''));
        $busca = trim((string)($_GET['busca'] ?? ''));
        if (strtolower($busca) === 'deletados') {
            $status = 'deletados';
        }
        $paginaAtual = max(1, (int)($_REQUEST['pagina'] ?? 1));

        $filtros = [
            'viacao_id' => trim((string)($_GET['viacao_id'] ?? '')),
            'user_id' => trim((string)($_GET['user_id'] ?? '')),
            'acao' => trim((string)($_GET['acao'] ?? '')),
            'data_ini' => trim((string)($_GET['data_ini'] ?? '')),
            'data_fim' => trim((string)($_GET['data_fim'] ?? '')),
            'status' => $status,
            'busca' => $busca,
            'limite'    => 10,
            'offset'    => ($paginaAtual - 1) * 10,
        ];

        View::render('admin/historico/index', [
            'historico' => $repo->all($filtros),
            'viacoes' => $repo->listViacoes(),
            'usuarios' => $repo->listUsuarios(),
            'filtros' => $filtros,
            'paginaAtual' => $paginaAtual
        ]);
    }

    public function index_viacao($id): void
    {
        $repo = new HistoricoRepository();
        $status = trim((string)($_GET['status'] ?? ''));

        $filtros = [
            'viacao_id' => trim((string)$id),
            'user_id' => trim((string)($_GET['user_id'] ?? '')),
            'acao' => trim((string)($_GET['acao'] ?? '')),
            'data_ini' => trim((string)($_GET['data_ini'] ?? '')),
            'data_fim' => trim((string)($_GET['data_fim'] ?? '')),
            'status' => $status,
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

        $viacaoSelecionada = [
            'nome' => $filtros['nome'],
            'url' => '—',
            'cidade' => '—',
            'status' => '—'
        ];

        if (!empty($historico)) {
            $detalhes = $historico[0]->detalhesArray();
            $dadosRecentes = $detalhes['novo'] ?? $detalhes['antigo'] ?? [];

            if (!empty($dadosRecentes)) {
                $viacaoSelecionada['url'] = $dadosRecentes['url'] ?? '—';
                $viacaoSelecionada['cidade'] = $dadosRecentes['cidade'] ?? '—';
                $viacaoSelecionada['status'] = $dadosRecentes['status'] ?? '—';
            }
        }

        View::render('admin/historico/index_viacoes', [
            'historico' => $historico,
            'viacoes' => $repo->listViacoes(),
            'usuarios' => $repo->listUsuarios(),
            'filtros' => $filtros,
            'viacaoSelecionada' => $viacaoSelecionada
        ]);
    }

    public function index_usuarios($id): void
    {
        $repo = new HistoricoRepository();
        $status = trim((string)($_GET['status'] ?? ''));
        $userIdAtual = !empty($_POST['user_id']) ? trim((string)$_POST['user_id']) : trim((string)$id);
        //A variável userIdAtual recebe: se a superglobal _POST na chave user_id não estiver vazia, então limpe os espaços em branco e
        // transforme em texto o valor que veio desse _POST na chave user_id. Caso contrário, limpe os espaços em branco e transforme em texto o id que veio lá da rota da URL."
        $paginaAtual = max(1, (int)($_REQUEST['pagina'] ?? 1));
        $filtros = [
            'viacao_id' => trim((string)($_GET['viacao_id'] ?? '')),
            'user_id' => $userIdAtual,
            'usuario_id' => $userIdAtual,
            'acao' => trim((string)($_REQUEST['acao'] ?? '')), //ajuste para o filtro do usuarios...so aqui
            'data_ini' => trim((string)($_GET['data_ini'] ?? '')),
            'data_fim' => trim((string)($_GET['data_fim'] ?? '')),
            'status' => $status,
            'limite'     => 10,
            'offset'     => ($paginaAtual - 1) * 10,
        ];

        $historico = $repo->all($filtros);

        // 1. Define o nome base do usuário usando o join ou a lista geral
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

        // Estrutura inicial padrão do painel de usuário
        $usuarioSelecionado = [
            'nome' => $filtros['nome'],
            'email' => '—',
            'role' => '—',
            'status' => '—'
        ];

        // 2. Varre o histórico para tentar extrair e-mail, cargo (role) e status do JSON
        foreach ($historico as $log) {
            $detalhes = $log->detalhesArray();
            $dadosRecentes = $detalhes['novo'] ?? $detalhes['antigo'] ?? [];

            if (!empty($dadosRecentes)) {
                if (!empty($dadosRecentes['email']) && $usuarioSelecionado['email'] === '—') {
                    $usuarioSelecionado['email'] = $dadosRecentes['email'];
                }
                // Ajuste as chaves 'role' ou 'status' se no seu banco usarem outros nomes
                if (!empty($dadosRecentes['role']) && $usuarioSelecionado['role'] === '—') {
                    $usuarioSelecionado['role'] = $dadosRecentes['role'];
                }
                if (!empty($dadosRecentes['status']) && $usuarioSelecionado['status'] === '—') {
                    $usuarioSelecionado['status'] = $dadosRecentes['status'];
                }
            }

            // Se achou o principal, poupa processamento
            if ($usuarioSelecionado['email'] !== '—') {
                break;
            }
        }

        View::render('admin/historico/index_usuarios', [
            'historico' => $historico,
            'viacoes' => $repo->listViacoes(),
            'usuarios' => $repo->listUsuarios(),
            'filtros' => $filtros,
            'usuarioSelecionado' => $usuarioSelecionado, // Injeta a variável na view de usuários
            'paginaAtual'        => $paginaAtual
        ]);
    }
}