<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração de Viações</title>
    <link rel="stylesheet" href="/styles.css">
</head>

<body class="admin-page">

<?php
use App\Core\View;

$uploadBase = dirname(__DIR__, 4) . '/src/public/uploads/logos/';
$flash      = View::pullFlash();
?>

<div class="page-container">

    <header class="page-header">
        <div>
            <h1 class="page-title">Administração de Viações</h1>
            <p class="page-subtitle">Gerencie empresas cadastradas na plataforma</p>
        </div>
        <div class="theme-toggle">
            <div class="trilho" id="theme-toggle">
                <div class="indicador"></div>
            </div>
        </div>
    </header>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <section class="header-actions">

        <div class="header-buttons">
            <a href="/admin/viacoes/create" class="btn btn-primary">
                + Nova Viação
            </a>
            <a href="/admin/historico" class="btn btn-secondary">
                Ver Histórico Geral
            </a>
            <a href="/admin/usuarios" class="btn btn-secondary">
                Gerenciar Usuários
            </a>
        </div>

        <form method="GET" action="/admin/viacoes" class="form-busca">

            <div class="campo-busca" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">

                <input
                        type="text"
                        name="busca"
                        value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>"
                        placeholder="Buscar por nome ou cidade..."
                        autocomplete="off"
                >

                <select name="status" onchange="this.form.submit()">
                    <option value="" <?= ($filtros['status'] ?? '') === '' ? 'selected' : '' ?>>Todas as Viações</option>
                    <option value="ativo" <?= ($filtros['status'] ?? '') === 'ativo' ? 'selected' : '' ?>>Apenas Ativos</option>
                    <option value="inativo" <?= ($filtros['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>Apenas Inativos</option>
                    <option value="deletados" <?= ($filtros['status'] ?? '') === 'deletados' ? 'selected' : '' ?>>Deletados</option>
                </select>

                <button type="submit" class="btn btn-search">
                    Pesquisar
                </button>

                <?php if (!empty($filtros['busca']) || !empty($filtros['status'])): ?>
                    <a href="/admin/viacoes" class="btn-limpar">
                        Limpar
                    </a>
                <?php endif; ?>

            </div>

        </form>

    </section>

    <div class="table-wrapper">
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Viação</th>
                <th>URL</th>
                <th>Cidade</th>
                <th>Status</th>
                <th class="text-center">Ações</th>
            </tr>
            </thead>
            <tbody>

            <?php if (empty($viacoes)): ?>
                <tr>
                    <td colspan="6" class="empty-row">Nenhuma viação encontrada.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($viacoes as $v): ?>
                    <tr>
                        <td>#<?= (int) $v->id ?></td>

                        <td>
                            <div class="viacao-info">
                                <?php if (!empty($v->logo) && file_exists($uploadBase . $v->logo)): ?>
                                    <img src="/uploads/logos/<?= htmlspecialchars($v->logo) ?>"
                                         alt="Logo <?= htmlspecialchars($v->nome) ?>"
                                         class="logo-avatar">
                                <?php else: ?>
                                    <div class="logo-placeholder">
                                        <?= strtoupper(mb_substr($v->nome, 0, 1, 'UTF-8')) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="viacao-meta">
                                    <strong><?= htmlspecialchars($v->nome) ?></strong>
                                </div>
                            </div>
                        </td>

                        <td>
                            <a href="<?= htmlspecialchars($v->url) ?>"
                               target="_blank" rel="noopener noreferrer"
                               class="url-link">Visitar site</a>
                        </td>

                        <td><?= htmlspecialchars($v->cidade ?: '—') ?></td>

                        <td>
                <span class="status-badge status-<?= htmlspecialchars($v->status) ?>">
                  <?= ucfirst(htmlspecialchars($v->status)) ?>
                </span>
                        </td>

                        <td>
                            <div class="table-actions">
                                <a href="/admin/viacoes/<?= (int) $v->id ?>/edit"
                                   class="btn-action btn-edit">Editar</a>
                                <a href="/admin/viacoes/<?= (int) $v->id ?>/historico-viacao"
                                   class="btn-action btn-edit">Historico</a>

                                <form method="POST"
                                      action="/admin/viacoes/<?= (int) $v->id ?>"
                                      onsubmit="return confirm('Deseja realmente excluir esta viação?')">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn-action btn-delete">Excluir</button>
                                </form>
                                <form method="POST"
                                      action="/admin/viacoes/<?= (int) $v->id ?>/restore"
                                      onsubmit="return confirm('Deseja reativar esta viação?')">
                                    <input type="hidden" name="_method" value="POST">
                                    <button type="submit" class="btn-action btn-restaurar">Restaurar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            </tbody>
        </table>
    </div>

    <a href="/logout" class="btn btn-secondary">Sair</a>

</div>

<script src="/script.js"></script>
</body>
</html>