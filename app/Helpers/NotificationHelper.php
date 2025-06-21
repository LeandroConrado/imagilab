<?php

namespace App\Helpers;

class NotificationHelper
{
    /**
     * Gera um link "Clique para Conversar" do WhatsApp com um resumo do pedido.
     * @param array $pedido Dados completos do pedido.
     * @return string O link completo.
     */
    public static function generateWhatsAppLink(array $pedido): string
    {
        if (empty($pedido['cliente_telefone'])) {
            return '#';
        }

        $telefoneCliente = '55' . preg_replace('/\D/', '', $pedido['cliente_telefone']);

        // --- PASSO 1: Montar a mensagem inteira em um array de linhas ---
        
        $linhasDaMensagem = [];
        
        $linhasDaMensagem[] = "Olá, *{$pedido['cliente_nome']}*! 👋";
        $linhasDaMensagem[] = ""; // Linha em branco
        $linhasDaMensagem[] = "Agradecemos pela sua compra! ❤️";
        $linhasDaMensagem[] = "";
        $linhasDaMensagem[] = "Segue o resumo do seu pedido *#{$pedido['codigo_pedido']}*:";
        $linhasDaMensagem[] = "";

        // Itens
        foreach ($pedido['itens'] as $item) {
            $linhasDaMensagem[] = "🛍️ {$item['quantidade']}x {$item['produto_nome']}";
        }
        
        $linhasDaMensagem[] = "";
        $linhasDaMensagem[] = "------------------";
        $linhasDaMensagem[] = "*Subtotal:* R$ " . number_format($pedido['subtotal'], 2, ',', '.');
        $linhasDaMensagem[] = "*Frete:* R$ " . number_format($pedido['valor_frete'], 2, ',', '.');
        $linhasDaMensagem[] = "*Desconto:* - R$ " . number_format($pedido['desconto'], 2, ',', '.');
        $linhasDaMensagem[] = "------------------";
        $linhasDaMensagem[] = "*Total:* R$ " . number_format($pedido['valor_total'], 2, ',', '.');
        $linhasDaMensagem[] = "";
        
        // Status
        $linhasDaMensagem[] = "*Status atual:* {$pedido['status_nome']}";

        // Junta todas as linhas com o caractere de quebra de linha "\n"
        $mensagemFinal = implode("\n", $linhasDaMensagem);

        // --- PASSO 2: Codificar a mensagem inteira de uma só vez para a URL ---
        return "https://wa.me/{$telefoneCliente}?text=" . rawurlencode($mensagemFinal);
    }
}