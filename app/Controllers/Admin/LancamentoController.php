<?php

namespace App\Controllers\Admin;

use App\Models\Cliente;
use App\Models\Fornecedor;
use App\Models\Lancamento;
use Core\Controller;
use App\Models\CondicaoPagamento;

class LancamentoController extends Controller
{
    public function index(): void
    {
        $filtros = [
            'tipo' => $_GET['tipo'] ?? null,
            'status' => $_GET['status'] ?? null,
            'data_inicio' => $_GET['data_inicio'] ?? null,
            'data_fim' => $_GET['data_fim'] ?? null,
        ];

        $this->render('admin/financeiro/lancamentos/index.twig', [
            'titulo' => 'Lançamentos Financeiros',
            'lancamentos' => (new Lancamento())->findAll($filtros),
            'filtros' => $filtros
        ]);
    }
    
    public function create(): void
    {
        $this->render('admin/financeiro/lancamentos/create.twig', [
            'titulo' => 'Novo Lançamento',
            'clientes' => (new Cliente())->findAll(),
            'fornecedores' => (new Fornecedor())->findAll(),
            'condicoes_pagamento' => (new CondicaoPagamento())->findAll(),
        ]);
    }

    public function store(): void
    {
        $lancamentoModel = new Lancamento();
        $condicaoPagamentoModel = new CondicaoPagamento();

        $valorTotal = (float)str_replace(',', '.', str_replace('.', '', $_POST['valor'] ?? '0'));
        $condicaoId = $_POST['condicao_pagamento_id'] ?? null;
        $condicao = $condicaoId ? $condicaoPagamentoModel->findById($condicaoId) : null;
        
        $nParcelas = ($condicao && (int)$condicao['numero_parcelas'] > 1) ? (int)$condicao['numero_parcelas'] : 1;
        
        $grupoId = uniqid('lanc_');

        if ($nParcelas > 1) {
            $valorBaseParcela = floor(($valorTotal / $nParcelas) * 100) / 100;
            $valorPrimeiraParcela = $valorTotal - ($valorBaseParcela * ($nParcelas - 1));
            $diasIntervalo = explode(',', $condicao['intervalo_dias']);
            $hoje = new \DateTime();

            for ($i = 0; $i < $nParcelas; $i++) {
                $numParcela = $i + 1;
                $dias = (int)trim($diasIntervalo[$i] ?? (30 * $numParcela));
                $dataVencimento = (clone $hoje)->modify("+" . $dias . " days")->format('Y-m-d');
                $valorDaParcelaAtual = ($i == 0) ? $valorPrimeiraParcela : $valorBaseParcela;

                $data = $this->getRequestData($valorDaParcelaAtual, $dataVencimento, " (Parcela {$numParcela}/{$nParcelas})", $grupoId);
                $lancamentoModel->create($data);
            }
        } else {
            $dataVencimento = $_POST['data_vencimento'] ?? date('Y-m-d');
            $data = $this->getRequestData($valorTotal, $dataVencimento, '', $grupoId);
            $lancamentoModel->create($data);
        }

        header('Location: /admin/financeiro/lancamentos');
        exit();
    }
    
    /**
     * @param ?string $grupoId  <-- CORREÇÃO APLICADA AQUI
     */
    private function getRequestData(float $valor, string $vencimento, string $sufixoDescricao = '', ?string $grupoId = null): array
    {
        $data = [
            'descricao' => ($_POST['descricao'] ?? '') . $sufixoDescricao,
            'valor' => $valor,
            'tipo' => $_POST['tipo'] ?? 'pagar',
            'status' => $_POST['status'] ?? 'pendente',
            'data_vencimento' => $vencimento,
            'data_pagamento' => $_POST['data_pagamento'] ?: null,
            'observacoes' => $_POST['observacoes'] ?? null,
            'origem_tipo' => null,
            'origem_id' => null,
            'grupo_id' => $grupoId,
        ];

        if ($data['tipo'] === 'receber' && !empty($_POST['cliente_id'])) {
            $data['origem_tipo'] = 'cliente';
            $data['origem_id'] = $_POST['cliente_id'];
        } elseif ($data['tipo'] === 'pagar' && !empty($_POST['fornecedor_id'])) {
            $data['origem_tipo'] = 'fornecedor';
            $data['origem_id'] = $_POST['fornecedor_id'];
        }

        return $data;
    }

    public function edit(int $id): void
    {
        $this->render('admin/financeiro/lancamentos/edit.twig', [
            'titulo' => 'Editar Lançamento', 
            'lancamento' => (new Lancamento())->findById($id),
            'clientes' => (new Cliente())->findAll(),
            'fornecedores' => (new Fornecedor())->findAll()
        ]);
    }

    public function update(int $id): void
    {
        $valor = (float)str_replace(',', '.', str_replace('.', '', $_POST['valor'] ?? '0'));
        $dataVencimento = $_POST['data_vencimento'] ?? date('Y-m-d');
        // A edição de parcelamento manual é complexa, por isso editamos apenas lançamentos únicos.
        $data = $this->getRequestData($valor, $dataVencimento);

        (new Lancamento())->update($id, $data);
        header('Location: /admin/financeiro/lancamentos');
        exit();
    }

    public function destroy(int $id): void
    {
        (new Lancamento())->delete($id);
        header('Location: /admin/financeiro/lancamentos');
        exit();
    }
}
