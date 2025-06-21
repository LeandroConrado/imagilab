<?php

namespace App\Helpers;

use Exception;

class Uploader
{
    /**
     * Lida com o upload de um arquivo.
     *
     * @param array $file O array do arquivo vindo de $_FILES.
     * @param string $destinationFolder A pasta de destino dentro de 'public/uploads/'.
     * @return string|null O caminho do arquivo salvo ou null em caso de falha.
     * @throws Exception
     */
    public static function upload(array $file, string $destinationFolder): ?string
    {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Ocorreu um erro durante o upload do arquivo.');
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Tipo de arquivo não permitido.');
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = uniqid('', true) . '.' . $extension;

        $destinationPath = __DIR__ . '/../../public/uploads/' . trim($destinationFolder, '/');

        // *** MELHORIA ADICIONADA AQUI ***
        // Verifica se a pasta de destino existe, se não, tenta criar recursivamente.
        if (!is_dir($destinationPath)) {
            if (!mkdir($destinationPath, 0775, true)) {
                // Se não conseguir criar a pasta, lança uma exceção.
                throw new Exception("Falha ao criar a pasta de destino: {$destinationPath}");
            }
        }
        
        $uploadFile = $destinationPath . '/' . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $uploadFile)) {
            throw new Exception('Não foi possível mover o arquivo para a pasta de destino.');
        }

        return '/uploads/' . trim($destinationFolder, '/') . '/' . $newFileName;
    }

    /**
     * Deleta um arquivo.
     *
     * @param string|null $filePath O caminho do arquivo a partir da pasta public (ex: /uploads/categorias/arquivo.jpg)
     */
    public static function delete(?string $filePath): void
    {
        if ($filePath && file_exists(__DIR__ . '/../../public' . $filePath)) {
            unlink(__DIR__ . '/../../public' . $filePath);
        }
    }
}