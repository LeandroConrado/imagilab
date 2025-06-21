<?php

namespace App\Controllers\Admin;

use App\Helpers\Mailer;
use App\Helpers\NotificationHelper;
use App\Models\Cliente;
use App\Models\CondicaoPagamento;
use App\Models\FormaPagamento;
use App\Models\Lancamento;
use App\Models\Pedido;
use App\Models\PedidoStatus;
use App\Models\Produto;
use App\Models\TipoFrete;
use Core\Controller;

class PedidoController extends Controller
{
    /**
     * Exibe a lista de todos os pedidos.
     */
    public function index(): void
    {
        $this->render('admin/pedidos/index.twig', [
            'titulo' => 'Listagem de Pedidos',
            'pedidos' => (new Pedido())->findAll()
        ]);
    }

    /**
     * Mostra os detalhes de um pedido específico.
     */
    public function show(int $id): void
    {
        $pedido = (new Pedido())->findById($id);
        if (!$pedido) {
            header('Location: /admin/pedidos');
            exit();
        }

        $pedido['whatsapp_link'] = NotificationHelper::generateWhatsAppLink($pedido);

        $this->render('admin/pedidos/show.twig', [
            'titulo' => "Detalhes do Pedido #" . $pedido['codigo_pedido'],
            'pedido' => $pedido
        ]);
    }

    /**
     * Mostra o formulário para criar um novo pedido manual.
     */
    public function create(): void
    {
        $this->render('admin/pedidos/create.twig', [
            'titulo' => 'Criar Novo Pedido Manual',
            'clientes' => (new Cliente())->findAll(),
            'produtos' => (new Produto())->findAll(),
            'formas_pagamento' => (new FormaPagamento())->findAll(),
            'pedido_status' => (new PedidoStatus())->findAll(),
            'tipos_frete' => (new TipoFrete())->findAll(),
            'condicoes_pagamento' => (new CondicaoPagamento())->findAll(),
        ]);
    }

    /**
     * Salva um novo pedido manual no banco de dados.
     */
    public function store(): void
    {
        $itensData = [];
        $subtotal = 0;
        if (isset($_POST['itens'])) {
            foreach ($_POST['itens'] as $item) {
                $itemData = [
                    'id' => $item['produto_id'],
                    'quantidade' => $item['quantidade'],
                    'preco' => $item['preco_unitario'],
                ];
                $itensData[] = $itemData;
                $subtotal += $itemData['quantidade'] * $itemData['preco'];
            }
        }
        
        $valorFrete = 0;
        $tipoFreteId = $_POST['tipo_frete_id'] ?? null;
        if ($tipoFreteId) {
            $tipoFrete = (new TipoFrete())->findById($tipoFreteId);
            if ($tipoFrete && $tipoFrete['tipo_valor'] == 'fixo') {
                $valorFrete = $tipoFrete['valor'];
            }
        }

        $desconto = str_replace(',', '.', $_POST['desconto'] ?? 0);
        $valorTotal = $subtotal + $valorFrete - $desconto;

        $pedidoData = [
            'cliente_id' => $_POST['cliente_id'],
            'endereco_entrega_id' => $_POST['endereco_entrega_id'],
            'forma_pagamento_id' => $_POST['forma_pagamento_id'],
            'condicao_pagamento_id' => $_POST['condicao_pagamento_id'],
            'pedido_status_id' => $_POST['pedido_status_id'],
            'subtotal' => $subtotal,
            'valor_frete' => $valorFrete,
            'desconto' => $desconto,
            'valor_total' => $valorTotal,
        ];

        $pedidoModel = new Pedido();
        $novoPedidoId = $pedidoModel->create($pedidoData, $itensData);

        if ($novoPedidoId) {
            $this->gerarLancamentosPorPedido($novoPedidoId);
        }

        header('Location: /admin/pedidos');
        exit();
    }

    /**
     * Mostra o formulário para editar o status/rastreio de um pedido.
     */
    public function edit(int $id): void
    {
        $pedido = (new Pedido())->findById($id);
        if (!$pedido) {
            header('Location: /admin/pedidos');
            exit();
        }
        $this->render('admin/pedidos/edit.twig', [
            'titulo' => "Atualizar Pedido #" . $pedido['codigo_pedido'],
            'pedido' => $pedido,
            'pedido_status_lista' => (new PedidoStatus())->findAll()
        ]);
    }

    /**
     * Atualiza o status/rastreio de um pedido.
     */
    public function update(int $id): void
    {
        $statusId = $_POST['pedido_status_id'] ?? null;
        $codigoRastreio = $_POST['codigo_rastreio'] ?? null;

        if ($statusId) {
            (new Pedido())->updateStatus($id, $statusId, $codigoRastreio);
        }

        header('Location: /admin/pedidos/' . $id);
        exit();
    }

    private function gerarLancamentosPorPedido(int $pedidoId): void
    {
        $pedido = (new Pedido())->findById($pedidoId);
        if (!$pedido) return;
        
        $lancamentoModel = new Lancamento();
        if ($lancamentoModel->findByOrigem('pedido', $pedidoId)) return;

        $formaPagamentoNome = $pedido['forma_pagamento_nome'];
        $statusLancamento = 'pendente';
        $dataPagamento = null;
        
        $pagamentosQuitados = ['Cartão de Crédito', 'Pix', 'Dinheiro'];
        if (in_array($formaPagamentoNome, $pagamentosQuitados)) {
            $statusLancamento = 'pago';
            $dataPagamento = date('Y-m-d');
        }

        $condicaoPagamento = (new CondicaoPagamento())->findById($pedido['condicao_pagamento_id']);
        
        if ($condicaoPagamento && (int)$condicaoPagamento['numero_parcelas'] > 1) {
            $this->criarLancamentosParcelados($lancamentoModel, $pedido, $condicaoPagamento, $statusLancamento, $dataPagamento);
        } else {
            $this->criarLancamentoUnico($lancamentoModel, $pedido, $statusLancamento, $dataPagamento);
        }
    }

    private function criarLancamentoUnico(Lancamento $lancamentoModel, array $pedido, string $status, ?string $dataPagamento): void
    {
        $data = [
            'descricao' => 'Recebimento referente ao Pedido #' . $pedido['codigo_pedido'],
            'valor' => $pedido['valor_total'],
            'tipo' => 'receber', 'status' => $status,
            'data_vencimento' => date('Y-m-d'),
            'origem_tipo' => 'pedido', 'origem_id' => $pedido['id'],
            'data_pagamento' => $dataPagamento, 'observacoes' => 'Gerado automaticamente.'
        ];
        $lancamentoModel->create($data);
    }

    private function criarLancamentosParcelados(Lancamento $lancamentoModel, array $pedido, array $condicao, string $status, ?string $dataPagamento): void
    {
        $valorTotal = (float)$pedido['valor_total'];
        $nParcelas = (int)$condicao['numero_parcelas'];
        $valorBaseParcela = floor(($valorTotal / $nParcelas) * 100) / 100;
        $valorPrimeiraParcela = $valorTotal - ($valorBaseParcela * ($nParcelas - 1));

        $diasIntervalo = explode(',', $condicao['intervalo_dias']);
        $hoje = new \DateTime();
        
        for ($i = 0; $i < $nParcelas; $i++) {
            $numParcela = $i + 1;
            $dias = (int)trim($diasIntervalo[$i] ?? (30 * $numParcela));
            
            $dataVencimento = clone $hoje;
            $dataVencimento->modify("+" . $dias . " days");
            $vencimentoFormatado = $dataVencimento->format('Y-m-d');

            $valorDaParcelaAtual = ($i == 0) ? $valorPrimeiraParcela : $valorBaseParcela;

            $data = [
                'descricao' => "Recebimento Parcela {$numParcela}/{$nParcelas} do Pedido #{$pedido['codigo_pedido']}",
                'valor' => $valorDaParcelaAtual,
                'tipo' => 'receber', 'status' => $status,
                'data_vencimento' => $vencimentoFormatado,
                'data_pagamento' => ($status == 'pago') ? $vencimentoFormatado : null,
                'origem_tipo' => 'pedido', 'origem_id' => $pedido['id'],
                'observacoes' => 'Gerado automaticamente.'
            ];
            $lancamentoModel->create($data);
        }
    }
    
    public function sendEmail(int $id): void
    {
        $pedido = (new Pedido())->findById($id);
        if ($pedido) {
            $htmlBody = $this->renderToString('emails/resumo_pedido.twig', [
                'pedido' => $pedido,
                'assunto' => "Resumo do seu pedido #" . $pedido['codigo_pedido']
            ]);

            $mailer = new \App\Helpers\Mailer();
            $mailer->send($pedido['cliente_email'], $pedido['cliente_nome'], "Resumo do seu pedido #" . $pedido['codigo_pedido'], $htmlBody);
        }
        header('Location: /admin/pedidos/' . $id);
        exit();
    }
}
