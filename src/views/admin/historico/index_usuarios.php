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

    <section class="header-actions">
        <div class="header-buttons">
            <a href="/admin/usuarios" class="btn btn-secondary">← Voltar para Usuários</a>
        </div>

        <form method="GET" action="/admin/usuarios/<?= (int)$filtros['user_id'] ?>/historico_usuarios" class="form-busca">
            <select name="acao">
                <option value="">Todas as Ações</option>
                <option value="CREATE" <?= ($filtros['acao'] ?? '') === 'CREATE' ? 'selected' : '' ?>>Criação</option>
                <option value="UPDATE" <?= ($filtros['acao'] ?? '') === 'UPDATE' ? 'selected' : '' ?>>Edição</option>
                <option value="DELETE" <?= ($filtros['acao'] ?? '') === 'DELETE' ? 'selected' : '' ?>>Exclusão</option>
            </select>

            <button type="submit" class="btn btn-search">Filtrar</button>
        </form>
    </section>

    <div class="table-wrapper">

        <?php if (empty($historico)): ?>
            <table class="table">
                <tbody>
                <tr>
                    <td colspan="7" class="empty-row">
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

<!--                        <td class="detalhes-col">-->
<!--                            --><?php
//                            // Tenta executar o metodo do modelo para pegar o array do JSON
//                            $antes = method_exists($item, 'antesArray') ? $item->antesArray() : ($item->antes ?? null);
//                            ?>
<!--                            --><?php //if (!empty($antes) && is_array($antes)): ?>
<!--                                --><?php //foreach ($antes as $chave => $valor): ?>
<!--                                    <div><strong>--><?php //= htmlspecialchars((string) $chave) ?><!--:</strong> --><?php //= htmlspecialchars((string) $valor) ?><!--</div>-->
<!--                                --><?php //endforeach; ?>
<!--                            --><?php //else: ?>
<!--                                <span style="color: var(--text-muted);">—</span>-->
<!--                            --><?php //endif; ?>
<!--                        </td>-->

                        <td class="detalhes-col">
                            <?php
                            // Tenta executar o metodo do modelo para pegar o array do JSON
                            $depois = method_exists($item, 'detalhes') ? $item->detalhesArray() : ($item->depois ?? null);
                            ?>
                            <?php if (!empty($depois) && is_array($depois)): ?>
                                <?php foreach ($depois as $chave => $valor): ?>
                                    <div><strong><?= htmlspecialchars((string) $chave) ?>:</strong> <?= htmlspecialchars((string) $valor) ?></div>
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

    </div>

    <div style="margin-top: 24px;">
        <a href="/logout" class="btn btn-secondary">Sair</a>
    </div>

</div>
<script src="/script.js"></script>
</body>
</html>