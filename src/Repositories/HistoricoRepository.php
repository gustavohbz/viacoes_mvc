<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Historico;
use PDO;

final class HistoricoRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \getPdo();
    }

    // =========================================================
    // Consulta com filtros
    // =========================================================

    /**
     * Retorna registros de auditoria com filtros opcionais.
     *
     * @param array{
     * viacao_id?: int|string,
     * user_id?:   int|string,
     * acao?:      string,
     * data_ini?:  string,
     * data_fim?:  string,
     * } $filtros
     *
     * @return Historico[]
     */
    public function all(array $filtros = []): array
    {
        $sql = "
            SELECT
                h.id,
                h.viacao_id,
                h.user_id,
                h.acao,
                h.antes,
                h.depois,
                h.criado_em,
                v.nome  AS viacao_nome,
                u.nome  AS usuario_nome,
                u.email AS usuario_email
            FROM  viacoes_historico h
            LEFT  JOIN viacoes v ON v.id = h.viacao_id
            LEFT  JOIN users   u ON u.id = h.user_id
            WHERE 1=1
        ";

        $params = [];

        // Filtro: viação
        if (!empty($filtros['viacao_id'])) {
            $sql           .= ' AND h.viacao_id = :viacao_id';
            $params['viacao_id'] = (int) $filtros['viacao_id'];
        }

        // Filtro: usuário
        if (!empty($filtros['user_id'])) {
            $sql           .= ' AND h.user_id = :user_id';
            $params['user_id'] = (int) $filtros['user_id'];
        }

        // Filtro: tipo de ação (CREATE | UPDATE | DELETE)
        $acoesValidas = ['CREATE', 'UPDATE', 'DELETE'];
        $acao = strtoupper(trim($filtros['acao'] ?? ''));

        if ($acao !== '' && in_array($acao, $acoesValidas, true)) {
            $sql           .= ' AND h.acao = :acao';
            $params['acao'] = $acao;
        }

        // Filtro: data inicial (yyyy-mm-dd) - AJUSTADO: Usando h.criado_em
        if (!empty($filtros['data_ini'])) {
            $sql              .= ' AND DATE(h.criado_em) >= :data_ini';
            $params['data_ini'] = $filtros['data_ini'];
        }

        // Filtro: data final (yyyy-mm-dd) - AJUSTADO: Usando h.criado_em
        if (!empty($filtros['data_fim'])) {
            $sql              .= ' AND DATE(h.criado_em) <= :data_fim';
            $params['data_fim'] = $filtros['data_fim'];
        }

        // AJUSTADO: Ordenando por h.criado_em
        $sql .= ' ORDER BY h.criado_em DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return array_map(
            fn(array $row) => Historico::fromRow($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    // =========================================================
    // Gravação de auditoria
    // =========================================================

    /**
     * Registra uma ação de auditoria.
     *
     * @param int         $viacaoId  ID da viação afetada
     * @param int         $userId    ID do usuário que executou a ação
     * @param string      $acao      'CREATE' | 'UPDATE' | 'DELETE'
     * @param array|null  $antes     Snapshot do estado anterior (será serializado em JSON)
     * @param array|null  $depois    Snapshot do estado posterior (será serializado em JSON)
     */
    public function log(
        int     $viacaoId,
        int     $userId,
        string  $acao,
        ?array  $antes  = null,
        ?array  $depois = null
    ): void {
        $stmt = $this->pdo->prepare('
            INSERT INTO viacoes_historico
                (viacao_id, user_id, acao, antes, depois)
            VALUES
                (:viacao_id, :user_id, :acao, :antes, :depois)
        ');

        $stmt->execute([
            ':viacao_id' => $viacaoId,
            ':user_id'   => $userId,
            ':acao'      => strtoupper($acao), // ADAPTADO: Mantendo salvamento em maiúsculo
            ':antes'     => $antes  !== null ? json_encode($antes,  JSON_UNESCAPED_UNICODE) : null,
            ':depois'    => $depois !== null ? json_encode($depois, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }

    // =========================================================
    // Helpers para os selects de filtro
    // =========================================================

    /**
     * Lista distinta de viações que possuem registro de histórico.
     * Usado para popular o <select> de filtro na view.
     */
    public function listViacoes(): array
    {
        $stmt = $this->pdo->query('
            SELECT DISTINCT v.id, v.nome
            FROM   viacoes_historico h
            JOIN   viacoes v ON v.id = h.viacao_id
            ORDER  BY v.nome ASC
        ');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista distinta de usuários que possuem registro de histórico.
     */
    public function listUsuarios(): array
    {
        $stmt = $this->pdo->query('
            SELECT DISTINCT u.id, u.nome, u.email
            FROM   viacoes_historico h
            JOIN   users u ON u.id = h.user_id
            ORDER  BY u.nome ASC
        ');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}