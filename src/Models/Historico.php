<?php
declare(strict_types=1);

namespace App\Models;

use DateTime;

final class Historico
{
  public function __construct(
    public readonly int     $id,
    public readonly ?int    $usuarioId,
    public readonly ?string   $tabela,
    public readonly ?int  $viacaoId,
    public readonly ?string $acao,
    public readonly string  $detalhes,
    public readonly ?string $data,
    // JOINs opcionais — podem ser null se o registro foi deletado
    public readonly ?string $viacaoNome,
    public readonly ?string $usuarioNome,
    public readonly ?string $usuarioEmail,
  ) {}

  public static function fromRow(array $row): self
  {
    return new self(
      id:            (int) $row['id'],
      usuarioId:      isset($row['usuario_id'])    ? (int) $row['usuario_id']    : null,
      tabela:        isset($row['tabela'])       ? (string) $row['tabela']      : null,
      viacaoId:    isset($row['viacao_id'])   ? (int) $row['viacao_id']   : null,
      acao:          (string) ($row['acao']       ?? ''),
     detalhes:      (string) ($row['detalhes']    ?? ''),
      data:      (string) ($row['data'] ?? ''),
      viacaoNome:    $row['viacao_nome']          ?? null,
      usuarioNome:   $row['usuario_nome']         ?? null,
      usuarioEmail:  $row['usuario_email']        ?? null,
    );
  }

  /**
   * Decodifica o snapshot JSON do campo 'antes' ou 'depois'
   * e retorna um array associativo (ou null se vazio/inválido).
   */
  public function detalhesArray(): ?array
  {
    return $this->detalhes !== null ? json_decode($this->detalhes, true) : null;
  }
}
