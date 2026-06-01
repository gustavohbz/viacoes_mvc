<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Alterações</title>
    <link rel="stylesheet" href="/styles.css">
</head>

<body class="admin-page">
<div class="page-container">

    <header class="page-header">
        <div>
            <h1 class="page-title">Histórico de Alterações</h1>
            <p class="page-subtitle">Registro de todas as ações realizadas na plataforma</p>
        </div>
    </header>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $e): ?>
                <p>⚠ <?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>


    <?php if (!empty($viacaoSelecionada)): ?>
        <div class="form-card">

            <div class="page-header">
                <div>
                    <h2 class="page-title" style="font-size: 1.2rem; margin-bottom: 15px;">
                        Dados da Viação Selecionada
                    </h2>
                </div>
            </div>

            <form onsubmit="return false;">

                <div class="form-grid">

                    <div class="campo">
                        <label>Nome</label>
                        <input
                                type="text"
                                value="<?= htmlspecialchars($viacaoSelecionada['nome'] ?? '—') ?>"
                                readonly
                                disabled
                        >
                    </div>

                    <div class="campo">
                        <label>URL</label>
                        <input
                                type="text"
                                value="<?= htmlspecialchars($viacaoSelecionada['url'] ?? '—') ?>"
                                readonly
                                disabled
                        >
                    </div>

                    <div class="campo">
                        <label>Cidade</label>
                        <input
                                type="text"
                                value="<?= htmlspecialchars($viacaoSelecionada['cidade'] ?? '—') ?>"
                                readonly
                                disabled
                        >
                    </div>

                    <div class="campo">
                        <label>Status</label>
                        <input
                                type="text"
                                value="<?= htmlspecialchars(ucfirst($viacaoSelecionada['status'] ?? '—')) ?>"
                                readonly
                                disabled
                                style="padding:11px;"
                        >
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>
    <section class="header-actions" style="margin-top:13px;width: 1300px;">
        <div class="header-buttons">
            <a href="/admin/viacoes" class="btn btn-secondary">Voltar para a lista</a>
        </div>

        <form method="GET" action="/admin/historico" class="form-busca">

            <?php if (!empty($viacoes)): ?>
                <select name="viacao_id" onchange="this.form.submit()">
                    <option value="">Todas as viações</option>
                    <?php foreach ($viacoes as $v): ?>
                        <option value="<?= (int) $v['id'] ?>"
                                <?= ((int)($filtros['viacao_id'] ?? 0) === (int)$v['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <select name="acao">
                <option value="">Todas as ações</option>
                <option value="CREATE" <?= ($filtros['acao'] ?? '') === 'CREATE' ? 'selected' : '' ?>>Criado</option>
                <option value="UPDATE" <?= ($filtros['acao'] ?? '') === 'UPDATE' ? 'selected' : '' ?>>Editado</option>
                <option value="DELETE" <?= ($filtros['acao'] ?? '') === 'DELETE' ? 'selected' : '' ?>>Excluído</option>
            </select>

            <input type="date" name="data_ini" value="<?= htmlspecialchars($filtros['data_ini'] ?? '') ?>" title="Data inicial">
            <input type="date" name="data_fim" value="<?= htmlspecialchars($filtros['data_fim'] ?? '') ?>" title="Data final">

            <button type="submit" class="btn btn-search">Filtrar</button>

            <?php if (array_filter($filtros)): ?>
                <a href="/admin/historico" class="btn-limpar">Limpar</a>
            <?php endif; ?>

        </form>
    </section>


    <div class="table-wrapper">

        <?php if (empty($historico)): ?>

            <table class="table">
                <tbody>
                <tr><td class="empty-row">Nenhuma ação registrada ainda.</td></tr>
                </tbody>
            </table>

        <?php else: ?>

            <table class="table">
                <thead>
                <tr>
                    <th>Data/Hora</th>
                    <th>Viação</th>
                    <th>Viação ID</th>
                    <th>Usuário</th>
                    <th>Ação</th>
                    <th>Detalhes</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($historico as $log): ?>
                    <tr>
                        <td><?= $log->data ? (new DateTime($log->data))->format('d/m/Y H:i') : '—' ?></td>

                        <td><strong><?= htmlspecialchars($log->viacaoNome ?? '—') ?></strong></td>

                        <td><strong><?= htmlspecialchars($log->viacaoId ?? '—') ?></strong></td>

                        <td><?= htmlspecialchars($log->usuarioNome ?? '—') ?></td>

                        <td>
                            <?php
                            $classe = match($log->acao) {
                                'CREATE' => 'criou',
                                'UPDATE' => 'editou',
                                'DELETE' => 'excluiu',
                                default  => '',
                            };
                            $label = match($log->acao) {
                                'CREATE' => 'Criado',
                                'UPDATE' => 'Editado',
                                'DELETE' => 'Excluído',
                                default  => htmlspecialchars($log->acao),
                            };
                            ?>
                            <span class="<?= $classe ?>"><?= $label ?></span>
                        </td>

                        <td class="detalhes-col">
                            <?php if ($log->detalhes !== null): ?>
                                <?php foreach (($log->detalhesArray() ?? []) as $chave => $valor): ?>
                                    <div>
                                        <strong><?= htmlspecialchars((string) $chave) ?>:</strong>
                                        <?= is_array($valor) ? htmlspecialchars(json_encode($valor, JSON_UNESCAPED_UNICODE)) : htmlspecialchars((string) $valor) ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>
    </div>

</div>
<script src="/script.js"></script>
</body>
</html>