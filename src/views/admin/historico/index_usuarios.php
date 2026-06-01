<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico do Usuário</title>
    <link rel="stylesheet" href="/styles.css">
</head>

<body class="admin-page">
<div class="page-container">

    <header class="page-header">
        <div>
            <h1 class="page-title">Histórico de <?= htmlspecialchars((string) $filtros['nome']) ?></h1>
            <p class="page-subtitle">Informações de edição e manipulações realizadas no sistema</p>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($usuarioSelecionado)): ?>


        <div class="form-card">

            <form onsubmit="return false;">

                <div class="form-grid">

                    <div class="campo">
                        <label>
                            Nome
                        </label>
                        <input
                                type="text"
                                value="<?= htmlspecialchars($usuarioSelecionado['nome'] ?? '—') ?>"
                                readonly
                                disabled
                        >
                    </div>

                    <div class="campo">
                        <label>
                            E-mail
                        </label>
                        <input
                                type="text"
                                value="<?= htmlspecialchars($usuarioSelecionado['email'] ?? '—') ?>"
                                readonly
                                disabled
                        >
                    </div>

                    <div class="campo">
                        <label>
                            Função
                        </label>
                        <input
                                type="text"
                                value="<?= htmlspecialchars(ucfirst($usuarioSelecionado['role'] ?? '—')) ?>"
                                readonly
                                disabled
                        >
                    </div>

                    <div class="campo">
                        <label>
                            Status
                        </label>
                        <select disabled >
                            <option selected>
                                <?= htmlspecialchars(ucfirst($usuarioSelecionado['status'] ?? '—')) ?>
                            </option>
                        </select>
                    </div>

                </div>

            </form>
        </div>
    <?php endif; ?>

    <section class="header-actions" style="margin-top: 13px">
        <div class="header-buttons">
            <a href="/admin/usuarios" class="btn btn-secondary">← Voltar para Usuários</a>
        </div>

        <form method="GET" action="/admin/usuarios/<?= (int)$filtros['user_id'] ?>/historico-usuarios" class="form-busca">

            <select name="acao" onchange="this.form.submit()">
                <option value="">Todas as ações</option>
                <option value="CREATE" <?= ($filtros['acao'] ?? '') === 'CREATE' ? 'selected' : '' ?>>Criado</option>
                <option value="UPDATE" <?= ($filtros['acao'] ?? '') === 'UPDATE' ? 'selected' : '' ?>>Editado</option>
                <option value="DELETE" <?= ($filtros['acao'] ?? '') === 'DELETE' ? 'selected' : '' ?>>Excluído</option>
                <option value="RESTORE" <?= ($filtros['acao'] ?? '') === 'RESTORE' ? 'selected' : '' ?>>Restaurado</option>
            </select>
        </form>
    </section>


    <div class="table-wrapper">

        <?php if (empty($historico)): ?>
            <table class="table">
                <tbody>
                <tr>
                    <td colspan="6" class="empty-row">
                        Nenhum registro de auditoria encontrado para este usuário.
                    </td>
                </tr>
                </tbody>
            </table>
        <?php else: ?>

            <table class="table">
                <thead>
                <tr>
                    <th>ID Log</th>
                    <th>Viação Afetada</th>
                    <th>ID da Viação</th>
                    <th>Detalhes</th>
                    <th>Data/Hora</th>
                    <th>Ação Executada</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($historico as $item): ?>
                    <tr>
                        <td>#<?= (int) $item->id ?></td>

                        <td>
                            <div class="viacao-info">
                                <div class="logo-placeholder">
                                    <?= strtoupper(mb_substr($item->viacaoNome ?? 'V', 0, 1, 'UTF-8')) ?>
                                </div>
                                <div class="viacao-meta">
                                    <strong><?= htmlspecialchars($item->viacaoNome ?? 'Viação Removida') ?></strong>
                                </div>
                            </div>
                        </td>

                        <td><?= (int) ($item->viacaoId ?? 0) ?></td>

                        <td class="detalhes-col">
                            <?php
                            $depois = method_exists($item, 'detalhesArray') ? $item->detalhesArray() : ($item->depois ?? null);
                            ?>
                            <?php if (!empty($depois) && is_array($depois)): ?>
                                <?php foreach ($depois as $chave => $valor): ?>
                                    <div>
                                        <strong><?= htmlspecialchars((string) $chave) ?>:</strong>
                                        <?= is_array($valor) ? htmlspecialchars(json_encode($valor, JSON_UNESCAPED_UNICODE)) : htmlspecialchars((string) $valor) ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">—</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= !empty($item->data)
                                    ? date('d/m/Y H:i', strtotime($item->data))
                                    : '—' ?>
                        </td>

                        <td>
                            <span class="badge badge-<?= strtolower($item->acao ?? '') ?>">
                                <?= htmlspecialchars($item->acao ?? '') ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>
        <!-- aqui e o bloco da paginação,ele e o mesmo do viações e do logs---------------------------------------->>
        <?php if (($paginaAtual ?? 1) > 1): ?>
            <?php $filtrosAnterior = array_merge($_GET, ['pagina' => $paginaAtual - 1]); ?>
            <a href="/admin/usuarios/<?= (int)$filtros['user_id'] ?>/historico-usuarios?<?= http_build_query($filtrosAnterior) ?>" style="text-decoration: none; margin-right: 10px; font-weight: bold; color: #007bff;">
                <?= $paginaAtual - 1 ?>
            </a>
        <?php endif; ?>

        <span style="font-weight: bold; margin-right: 10px; color: #333;"><?= (int)($paginaAtual ?? 1) ?></span>

        <?php if (count($historico) === 10): ?>
            <?php $filtrosProximo = array_merge($_GET, ['pagina' => $paginaAtual + 1]); ?>
            <a href="/admin/usuarios/<?= (int)$filtros['user_id'] ?>/historico-usuarios?<?= http_build_query($filtrosProximo) ?>" style="text-decoration: none; font-weight: bold; color: #007bff;">
                <?= $paginaAtual + 1 ?>
            </a>
        <?php endif; ?>
        <!----------------------------------------------------------------------------------->

    </div>

    <div style="margin-top: 24px;">
        <a href="/logout" class="btn btn-secondary">Sair</a>
    </div>

</div>
<script src="/script.js"></script>
</body>
</html>
<!-- teste1
teste@teste.com
123456