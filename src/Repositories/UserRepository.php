<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UserRepository
{
  private PDO $pdo;

  public function __construct()
  {
    $this->pdo = \getPdo();
  }

  public function findByEmail(string $email): ?array
  {
    $stmt = $this->pdo->prepare(
      'SELECT id, nome, email, password
             FROM users
             WHERE email = :email
             LIMIT 1'
    );

    $stmt->bindValue(':email', trim($email));
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
  }
}
