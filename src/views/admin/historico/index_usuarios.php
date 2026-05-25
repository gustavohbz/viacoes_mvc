<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Historico do Usuário</title>
  <link rel="stylesheet" href="/styles.css">
</head>

<body class="admin-page">
<div class="page-container">

  <!-- HEADER -->
  <header class="page-header">
    <div>
        <h1 class="page-title">Usuário <?= (int) $filtros['user_id'] ?></h1>
      <p class="page-subtitle">Cadastre, edite e gerencie os usuários do sistema</p>
    </div>
  </header>

  <!-- FLASH MESSAGE -->
  <!-- CORREÇÃO: flash não era exibido na listagem — o controller agora passa $flash -->
  <?php if (!empty($flash)): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
      <?= htmlspecialchars($flash['message']) ?>
    </div>
  <?php endif; ?>

  <!-- AÇÕES + FILTRO -->
  <section class="header-actions">

    <div class="header-buttons">
      <a href="/admin/viacoes" class="btn btn-secondary">← Viações</a>
      <!-- CORREÇÃO: btn sem btn-primary ficava sem cor -->
      <a href="/admin/usuarios/create" class="btn btn-primary">+ Novo Usuário</a>
    </div>

    <form method="GET" action="/admin/usuarios" class="form-busca">
      <input
        type="text"
        name="busca"
        value="<?= htmlspecialchars($filtros['busca'] ?? '') ?>"
        placeholder="Buscar por nome ou e-mail..."
        autocomplete="off"
      >
      <button type="submit" class="btn btn-search">Filtrar</button>

      <?php if (!empty($filtros['busca'])): ?>
        <a href="/admin/usuarios" class="btn-limpar">Limpar</a>
      <?php endif; ?>
    </form>

  </section>

  <!-- TABELA -->
  <div class="table-wrapper">

    <?php if (empty($usuarios)): ?>

      <table class="table">
        <tbody>
        <tr>
          <td colspan="5" class="empty-row">
            <?= !empty($filtros['busca'])
              ? 'Nenhum usuário encontrado para "' . htmlspecialchars($filtros['busca']) . '".'
              : 'Nenhum usuário cadastrado ainda.' ?>
          </td>
        </tr>
        </tbody>
      </table>

    <?php else: ?>

      <table class="table">
        <thead>
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>E-mail</th>
          <th>Cadastrado em</th>
          <th class="text-center">Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // ID do usuário logado — para desabilitar o botão de excluir a si mesmo
        $userLogadoId = (int) ($_SESSION['user_id'] ?? 0);
        ?>

        <?php foreach ($usuarios as $usuario): ?>
          <tr>
            <td>#<?= (int) $usuario->id ?></td>

            <td>
              <div class="viacao-info">
                <!-- Avatar com inicial do nome, padrão do projeto -->
                <div class="logo-placeholder">
                  <?= strtoupper(mb_substr($usuario->nome, 0, 1, 'UTF-8')) ?>
                </div>
                <div class="viacao-meta">
                  <strong><?= htmlspecialchars($usuario->nome) ?></strong>
                  <?php if ($usuario->id === $userLogadoId): ?>
                    <small style="color: var(--text-muted); font-size: 11px;">Você</small>
                  <?php endif; ?>
                </div>
              </div>
            </td>

            <td><?= htmlspecialchars($usuario->email) ?></td>

            <td>
              <?= !empty($usuario->criadoEm)
                ? date('d/m/Y', strtotime($usuario->criadoEm))
                : '—' ?>
            </td>

            <td>
              <div class="table-actions">
                <a href="/admin/usuarios/<?= (int) $usuario->id ?>/edit"
                   class="btn-action btn-edit">
                  Editar
                </a>
                  <a href="/admin/usuarios/<?= (int) $usuario->id ?>/historico_usuarios"
                     class="btn-action btn-edit">
                      Editar
                  </a>

                <?php if ($usuario->id !== $userLogadoId): ?>
                  <form
                    method="POST"
                    action="/admin/usuarios/<?= (int) $usuario->id ?>"
                    onsubmit="return confirm('Tem certeza que deseja excluir o usuário <?= htmlspecialchars(addslashes($usuario->nome)) ?>?')"
                  >
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn-action btn-delete">Excluir</button>
                  </form>
                <?php else: ?>
                  <!-- Usuário logado não pode se excluir — botão desabilitado visualmente -->
                  <span style="color: var(--text-muted); font-size: 13px; font-weight: 600;">
                      Sua conta
                    </span>
                <?php endif; ?>
              </div>
            </td>

          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>

    <?php endif; ?>

  </div>

  <!-- Link de volta, padrão das outras páginas admin -->
  <div style="margin-top: 24px;">
    <a href="/logout" class="btn btn-secondary">Sair</a>
  </div>

</div>
<script src="/script.js"></script>
</body>
</html>
