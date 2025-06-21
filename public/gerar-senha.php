<?php

// A senha que queremos usar
$senhaPlana = 'senha123';

// Gera a versão criptografada (hash) da senha
$hashDaSenha = password_hash($senhaPlana, PASSWORD_DEFAULT);

// Exibe o resultado na tela de forma clara
echo "<h1>Gerador de Senha Segura</h1>";
echo "<p>Use o valor abaixo para atualizar a coluna 'senha' no seu banco de dados para o usuário admin.</p>";
echo "<hr>";
echo "<p><strong>Senha Plana:</strong> {$senhaPlana}</p>";
echo "<p><strong>Hash Gerado (copie este valor completo):</strong></p>";
echo "<pre style='background-color: #f0f0f0; padding: 10px; border: 1px solid #ccc; font-family: monospace; font-size: 1.2em;'>";
echo htmlspecialchars($hashDaSenha);
echo "</pre>";

?>
