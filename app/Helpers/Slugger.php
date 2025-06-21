<?php

namespace App\Helpers;

use Core\Database;

class Slugger
{
    /**
     * Gera um slug único para um texto, verificando duplicatas em uma tabela.
     *
     * @param string $text O texto base para o slug.
     * @param string $table A tabela onde verificar a unicidade do slug.
     * @param ?int $ignoreId O ID do registro a ser ignorado na verificação (útil em updates).
     * @return string O slug único gerado.
     */
    public static function generate(string $text, string $table, ?int $ignoreId = null): string
    {
        // 1. Normalização básica do slug
        $slug = strtolower($text);
        // Remove acentos e caracteres especiais
        $slug = preg_replace('/[áàâãäå]/u', 'a', $slug);
        $slug = preg_replace('/[éèêë]/u', 'e', $slug);
        $slug = preg_replace('/[íìîï]/u', 'i', $slug);
        $slug = preg_replace('/[óòôõö]/u', 'o', $slug);
        $slug = preg_replace('/[úùûü]/u', 'u', $slug);
        $slug = preg_replace('/[ç]/u', 'c', $slug);
        // Substitui tudo que não for letra, número ou hífen por um hífen
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        // Remove hífens duplicados
        $slug = preg_replace('/-+/', '-', $slug);
        // Remove hífens do início e do fim
        $slug = trim($slug, '-');

        // 2. Verificação de unicidade
        $pdo = Database::getInstance();
        $originalSlug = $slug;
        $counter = 1;

        $sql = "SELECT COUNT(*) FROM {$table} WHERE slug = :slug";
        if ($ignoreId !== null) {
            $sql .= " AND id != :id";
        }
        $stmt = $pdo->prepare($sql);
        
        if ($ignoreId !== null) {
            $stmt->bindValue(':id', $ignoreId);
        }

        while (true) {
            $stmt->bindValue(':slug', $slug);
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                break; // Encontrou um slug único
            }
            // Se não for único, anexa um número e tenta novamente
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }
}