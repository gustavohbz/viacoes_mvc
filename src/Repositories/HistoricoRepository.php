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
     * usuario_id?:   int|string,
     * tabela?: string,
     * acao?:      string,
     * data_ini?:  string,
     * data_fim?:  string,
     * status?:    string,
     * } $filtros
     *
     * @return Historico[]
     */
    public function all(array $filtros = []): array
    {
        $sql = "
            SELECT
                h.id,
                h.usuario_id,
                h.tabela,
                h.viacao_id,
                h.acao,
                h.detalhes,
                h.data_hora AS data,
                v.nome  AS viacao_nome,
                u.nome  AS usuario_nome,
                u.email AS usuario_email,
                v.deleted_at AS viacao_deleted_at 
            FROM  historico_geral h
            LEFT  JOIN viacoes v ON v.id = h.viacao_id
            LEFT  JOIN users   u ON u.id = h.usuario_id
            WHERE 1=1
        ";

        $params = [];

        // Filtro: viação
        if (!empty($filtros['viacao_id'])) {
            $sql           .= ' AND h.viacao_id = :viacao_id';
            $params['viacao_id'] = (int) $filtros['viacao_id'];
        }

        // Filtro: usuário
        if (!empty($filtros['usuario_id'])) {
            $sql           .= ' AND h.usuario_id = :usuario_id';
            $params['usuario_id'] = (int) $filtros['usuario_id'];
        }

        // Filtro: tipo de ação (CREATE | UPDATE | DELETE)
        $acoesValidas = ['CREATE', 'UPDATE', 'DELETE'];
        $acao = strtoupper(trim($filtros['acao'] ?? ''));

        if ($acao !== '' && in_array($acao, $acoesValidas, true)) {
            $sql           .= ' AND h.acao = :acao';
            $params['acao'] = $acao;
        }

        // Filtro: data inicial (yyyy-mm-dd)
        if (!empty($filtros['data_ini'])) {
            $sql              .= ' AND DATE(h.data) >= :data_ini';
            $params['data_ini'] = $filtros['data_ini'];
        }

        // Filtro: data final (yyyy-mm-dd)
        if (!empty($filtros['data_fim'])) {
            $sql              .= ' AND DATE(h.data_hora) <= :data_fim';
            $params['data_fim'] = $filtros['data_fim'];
        }

        //--------------Captura o status vindo do array de filtros da tela de histórico---------
        $status = $filtros['status'] ?? '';

        if ($status === 'deletados') {
            // traz so oq sofreu soft delete
            $sql .= ' AND v.deleted_at IS NOT NULL';
        } else {
            //aqui ele so puxa por padrao as viacoes que nao sao excluidas no soft delete
            $sql .= ' AND v.deleted_at IS NULL';
        }
        //--------------27/06 - Adicionei esse bloco para filtragem do soft delete ---------------
        // Ordenando por h.criado_em
        $sql .= ' ORDER BY h.data_hora DESC';

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
     * @param  string $tabela
     * @param int         $viacaoId  ID da viação afetada
     * @param int         $userId    ID do usuário que executou a ação
     * @param string      $acao      'CREATE' | 'UPDATE' | 'DELETE'
     * @param array|null  $detalhes     Snapshot do estado anterior (será serializado em JSON)
     */
    public function log(
        int     $viacaoId,
        string   $tabela,
        int     $userId,
        string  $acao,
        ?array  $detalhes  = null,
    ): void {
        $stmt = $this->pdo->prepare('
            INSERT INTO historico_geral
                (viacao_id, tabela, usuario_id, acao,detalhes)
            VALUES
                (:viacao_id,:tabela, :usuario_id, :acao, :detalhes)
        ');

        $stmt->execute([
            ':viacao_id' => $viacaoId,
            ':tabela' => $tabela,
            ':usuario_id'   => $userId,
            ':acao'      => strtoupper($acao), // ADAPTADO: Mantendo salvamento em maiúsculo
            ':detalhes'    => $detalhes !== null ? json_encode($detalhes, JSON_UNESCAPED_UNICODE) : null,
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
            FROM   historico_geral h
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
            FROM   historico_geral h
            JOIN   users u ON u.id = h.usuario_id
            ORDER  BY u.nome ASC
        ');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}