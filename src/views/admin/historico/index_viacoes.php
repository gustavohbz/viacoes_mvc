<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Histórico</title>
  <link rel="stylesheet" href="/styles.css">
</head>

<body class="admin-page">
<div class="page-container">

  <!-- HEADER -->
  <header class="page-header">
    <div>
      <h1 class="page-title"><?= htmlspecialchars((string) $filtros['nome']) ?></h1>
      <p class="page-subtitle">Registro de todas as ações referentes a <?= htmlspecialchars((string) $filtros['nome']) ?> </p>
    </div>
  </header>

  <!-- FILTROS -->
  <section class="header-actions">
    <div class="header-buttons">
      <a href="/admin/viacoes" class="btn btn-secondary">Voltar para a lista</a>
    </div>

      <form method="GET" action="/admin/viacoes/<?= (int) $filtros['viacao_id'] ?>/historico-viacao" class="form-busca">

          <?php if (!empty($usuarios)): ?>
              <select name="user_id">
                  <option value="">Todos os Usuários</option>
                  <?php foreach ($usuarios as $u): ?>
                      <option value="<?= (int) $u['id'] ?>"
                              <?= ((string)$filtros['user_id'] === (string)$u['id']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($u['nome']) ?>
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

          <?php if (!empty($_GET['user_id']) || !empty($_GET['acao']) || !empty($_GET['data_ini']) || !empty($_GET['data_fim'])): ?>
              <a href="/admin/viacoes/<?= (int) $filtros['viacao_id'] ?>/historico-viacao" class="btn-limpar">Limpar</a>
          <?php endif; ?>

      </form>

  <!-- TABELA -->
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
            <th>ID</th>
            <th>Data da Modificação</th>
            <th>Usuário</th>
            <th>Ação</th>
            <th>Antes</th>
            <th>Depois</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($historico as $item): ?>
          <tr>
            <td><?= $item->id ?></td>
            <!-- DATA/HORA -->
            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($item->criadoEm))) ?></td>

            <!-- USUÁRIO -->
            <td><?= htmlspecialchars($item->usuarioNome ?? '—') ?></td>

            <!-- AÇÃO -->
            <td>
              <?php
              $classe = match($item->acao) {
                'CREATE' => 'criou',
                'UPDATE' => 'editou',
                'DELETE' => 'excluiu',
                default  => '',
              };
              $label = match($item->acao) {
                'CREATE' => 'Criado',
                'UPDATE' => 'Editado',
                'DELETE' => 'Excluído',
                default  => htmlspecialchars($item->acao),
              };
              ?>
              <span class="<?= $classe ?>"><?= $label ?></span>
            </td>

            <!-- ANTES -->
            <!-- CORREÇÃO: antes/depois agora são JSON decodificados corretamente -->
            <td class="detalhes-col">
              <?php if ($item->antes !== null): ?>
                <?php foreach (($item->antesArray() ?? []) as $chave => $valor): ?>
                  <div><strong><?= htmlspecialchars((string) $chave) ?>:</strong> <?= htmlspecialchars((string) $valor) ?></div>
                <?php endforeach; ?>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>

            <!-- DEPOIS -->
            <td class="detalhes-col">
              <?php if ($item->depois !== null): ?>
                <?php foreach (($item->depoisArray() ?? []) as $chave => $valor): ?>
                  <div><strong><?= htmlspecialchars((string) $chave) ?>:</strong> <?= htmlspecialchars((string) $valor) ?></div>
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
