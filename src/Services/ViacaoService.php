<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Viacao;
use App\Repositories\ViacaoRepository;
use App\Repositories\HistoricoRepository;
use App\Validators\ViacaoValidator;
use Exception;

final class ViacaoService
{
    private ViacaoRepository $repo;
    private ViacaoValidator $validator;
    private HistoricoRepository $historico;
    private string $uploadDir;

    public function __construct(
        ?ViacaoRepository    $repo = null,
        ?ViacaoValidator     $validator = null,
        ?HistoricoRepository $historico = null
    )
    {
        $this->repo = $repo ?? new ViacaoRepository();
        $this->validator = $validator ?? new ViacaoValidator();
        $this->historico = $historico ?? new HistoricoRepository();

        // raiz/src/public/uploads/logos/
        $this->uploadDir = dirname(__DIR__, 2) . '/src/public/uploads/logos/';
    }

    // assinatura aceita o $status do Controller,soft delete
    public function all(string $busca, string $status, string $ordem, string $dir): array
    {
        $isHomeQuery = (
            $busca === '' &&
            $status === 'ativo' &&
            $ordem === 'nome' &&
            $dir === 'ASC'
        );

        if ($isHomeQuery) {
            $cached = \getCachedData('viacoes_ativas');

            if ($cached !== null) {
                return array_map(
                    static fn(array $row): Viacao => Viacao::fromRow($row),
                    $cached
                );
            }
        }

        // aqui ele trata a parte certa do $status no repository
        $viacoes = $this->repo->all($busca, $status, $ordem, $dir);

        if ($isHomeQuery) {
            \setCachedData(
                'viacoes_ativas',
                array_map(static fn(Viacao $v): array => (array)$v, $viacoes)
            );
        }

        return $viacoes;
    }

    public function find(int $id): ?Viacao
    {
        return $this->repo->find($id);
    }

    public function create(array $data, ?array $fileLogo = null): int
    {
        $errors = $this->validator->validate($data);

        if ($errors !== []) {
            throw new Exception(implode('|', $errors));
        }

        $id = $this->repo->create([
            'nome' => trim($data['nome']),
            'url' => trim($data['url']),
            'cidade' => trim($data['cidade']),
            'status' => ($data['status'] ?? '') === 'inativo' ? 'inativo' : 'ativo',
            'logo' => $this->handleUpload($fileLogo),
        ]);

        // CORREÇÃO: user_id agora lido de $_SESSION['user_id'] (gravado no login)
        // A versão anterior tinha fallback para 1 porque leia ['user_id'] mas o
        // AuthController gravava apenas em ['user']['id'].
        $userId = (int)($_SESSION['user_id'] ?? $_SESSION['auth']['id'] ?? 1);

        $this->historico->log($id, 'viacoes', $userId, 'CREATE', ['novo' => $data]);

        \invalidateCache('viacoes_ativas');

        return $id;
    }

    public function update(int $id, array $data, ?array $fileLogo = null): void
    {
        $old = $this->repo->find($id);

        if (!$old) {
            throw new Exception('Viação não encontrada.');
        }

        $errors = $this->validator->validate($data);

        if ($errors !== []) {
            throw new Exception(implode('|', $errors));
        }

        $updateData = [
            'nome' => trim($data['nome']),
            'url' => trim($data['url']),
            'cidade' => trim($data['cidade']),
            'status' => ($data['status'] ?? '') === 'inativo' ? 'inativo' : 'ativo',
        ];

        $mudancas = [];

        if ($old->nome !== $updateData['nome']) {
            $mudancas[] = "Nome: '{$old->nome}' → '{$updateData['nome']}'";
        }
        if ($old->url !== $updateData['url']) {
            $mudancas[] = "URL: '{$old->url}' → '{$updateData['url']}'";
        }
        if ($old->cidade !== $updateData['cidade']) {
            $mudancas[] = "Cidade: '{$old->cidade}' → '{$updateData['cidade']}'";
        }
        if ($old->status !== $updateData['status']) {
            $mudancas[] = "Status: '{$old->status}' → '{$updateData['status']}'";
        }

        // CORREÇÃO: verificação de erro do upload antes de processar
        if ($fileLogo !== null && ($fileLogo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $updateData['logo'] = $this->handleUpload($fileLogo);
            $mudancas[] = 'Logo atualizada';
        }

        $this->repo->update($id, $updateData);

        if (!empty($mudancas)) {
            $userId = (int)($_SESSION['user_id'] ?? 1);

            $this->historico->log(
                $id, 'viacoes', $userId, 'UPDATE',
                ['antigo' => ['nome' => $old->nome, 'url' => $old->url, 'cidade' => $old->cidade, 'status' => $old->status, 'logo' => $old->logo],
                    'novo' => $updateData]
            );
        }

        \invalidateCache('viacoes_ativas');
    }

    public function delete(int $id): void
    {
        $viacao = $this->repo->find($id);

        if (!$viacao) {
            return;
        }

        $userId = (int)($_SESSION['user_id'] ?? 1);

        // Auditoria ANTES de deletar (garante integridade da FK)
        $this->historico->log(
            $id, 'viacoes', $userId, 'DELETE',
            ['antigo' => ['nome' => $viacao->nome, 'url' => $viacao->url, 'cidade' => $viacao->cidade, 'status' => $viacao->status, 'logo' => $viacao->logo]
            ]
        );

        $this->repo->delete($id);
        // Se apagar aqui ia quebrar,por isso comentei,como nao e mais deletado ele so é "movido" ela continua exsitindo
        /*
        if ($viacao->logo) {
          $path = $this->uploadDir . $viacao->logo;
          if (file_exists($path)) {
            unlink($path);
          }
        }
        */

        \invalidateCache('viacoes_ativas');
    }

    private function handleUpload(?array $file): ?string
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        $tmpName = $file['tmp_name'];

        if (!is_uploaded_file($tmpName)) {
            throw new Exception('Arquivo de upload inválido.');
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            throw new Exception('Arquivo muito grande. Máximo permitido: 2 MB.');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new Exception('Extensão de arquivo inválida.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpName);
        finfo_close($finfo);

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!array_key_exists($mime, $allowedMimes)) {
            throw new Exception('Apenas imagens JPG, PNG ou WEBP são permitidas.');
        }

        $nomeLogo = bin2hex(random_bytes(16)) . '.' . $allowedMimes[$mime];

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        if (!move_uploaded_file($tmpName, $this->uploadDir . $nomeLogo)) {
            throw new Exception('Falha ao salvar a imagem.');
        }

        return $nomeLogo;
    }

    // Dentro de src/Services/ViacaoService.php
    public function restore(int $id): void
    {
        // 1. Atualiza no banco primeiro
        $this->repo->restore($id);

        // 2. Busca o registro atualizado para registrar no log do histórico
        $old = $this->repo->find($id);

        $userIdLogado = (int)($_SESSION['user_id'] ?? 1);

        $this->historico->log(
            $id,
            'viacoes',
            $userIdLogado,
            'RESTORE',
            [
                'antigo' => [
                    'status' => 'inativo',
                    'deleted_at' => 'deletado'
                ],
                'novo' => [
                    'status' => 'ativo',
                    'deleted_at' => null
                ]
            ]
        );
    }
}