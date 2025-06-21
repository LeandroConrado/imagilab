<?php
namespace App\Models;
use Core\Database;
use PDO;

class Configuracao
{
    private PDO $pdo;
    private static ?array $settings = null;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    /**
     * Busca todas as configurações e as armazena em cache estático para a requisição.
     * @return array
     */
    public function getAll(): array
    {
        if (self::$settings === null) {
            $stmt = $this->pdo->query("SELECT chave, valor FROM configuracoes");
            self::$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        }
        return self::$settings;
    }

    /**
     * Atualiza um valor de configuração ou cria um novo.
     * @param string $chave
     * @param ?string $valor
     * @return bool
     */
    public function update(string $chave, ?string $valor): bool
    {
        // CORREÇÃO: A consulta agora usa a sintaxe VALUES(valor) que é mais robusta
        // para o PDO e evita a duplicação de placeholders.
        $sql = "INSERT INTO configuracoes (chave, valor) VALUES (:chave, :valor) 
                ON DUPLICATE KEY UPDATE valor = VALUES(valor)";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':chave', $chave);
        $stmt->bindValue(':valor', $valor);
        
        return $stmt->execute();
    }
}
