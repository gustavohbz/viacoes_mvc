<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Viacao;
use PDO;

final class ViacaoRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {

        $this->pdo = $pdo ?? \getPdo();
    }

    /**
     * Busca filtrada e ordenada.
     */
    public function all(string $busca, string $status, string $ordem, string $dir): array
    {
        //alteração em que aparece somente onde deleted_at é null
        $sql    = 'SELECT * FROM viacoes WHERE 1=1'; //aqui eu usei 1=1,modificando o anterior
        $params = [];

        if ($busca !== '') {
            $sql .= ' AND (nome LIKE :busca OR cidade LIKE :busca)';
            $params['busca'] = "%{$busca}%";
        }

        // separação entre ativos e inativos / deletados,aqui foi apenas uma implementação,a busca esta mais parruda
        if ($status === 'deletados') {
            $sql .= ' AND deleted_at IS NOT NULL';
        } else {
            $sql .= ' AND deleted_at IS NULL';

            if (in_array($status, ['ativo', 'inativo'], true)) {
                $sql .= ' AND status = :status';
                $params['status'] = $status;
            }
        }

        // Whitelist de segurança para evitar SQL Injection via ORDER BY
        $colunas = ['id', 'nome', 'cidade', 'criado_em', 'alterado_em'];
        $ordem   = in_array($ordem, $colunas, true) ? $ordem : 'nome';
        $dir     = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';

        $sql .= " ORDER BY {$ordem} {$dir}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return array_map(
            static fn(array $r): Viacao => Viacao::fromRow($r),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function find(int $id): ?Viacao
    {
        //adição do final no stmt para que ele selecione a maneira correta
        $stmt = $this->pdo->prepare('SELECT * FROM viacoes WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? Viacao::fromRow($row) : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO viacoes (nome, url, cidade, status, logo)
             VALUES (:nome, :url, :cidade, :status, :logo)'
        );
        $stmt->execute($data);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sql = 'UPDATE viacoes SET nome = :nome, url = :url, cidade = :cidade, status = :status';

        if (array_key_exists('logo', $data)) {
            $sql .= ', logo = :logo';
        } else {
            unset($data['logo']);
        }

        $sql .= ' WHERE id = :id';

        $data['id'] = $id;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }

//  public function delete(int $id): void
//  {
//    $stmt = $this->pdo->prepare('DELETE FROM viacoes WHERE id = :id');
//    $stmt->execute(['id' => $id]);
//  }
    public function delete(int $id): void
    {
        // update logico com tiragem do delete,esta comentado acima o codigo original

        $stmt = $this->pdo->prepare('UPDATE viacoes SET deleted_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}