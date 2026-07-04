<?php

declare(strict_types=1);

namespace App\Controllers;

require_once __DIR__ . '/BaseController.php';

use App\Support\Storage;
use CANNALPagamentos\CannalPagamentos;
use CANNALPagamentos\Entities\Cartao;
use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Pedido;
use CANNALPagamentos\DTO\CredenciaisAutenticacao;

class TransacoesController extends BaseController
{
    public function criar()
    {
        $input = $this->request->getJSON(true) ?: $this->request->getPost();

        $banco = strtolower((string) ($input['banco'] ?? getenv('BANCO_ATIVO') ?: 'pagarme'));
        $tipo = strtolower((string) ($input['tipo'] ?? 'pix'));

        $credenciais = CredenciaisAutenticacao::fromEnv($banco);
        $cannal = new CannalPagamentos();
        $gateway = $cannal->registrarBanco($credenciais)->usar($banco);

        $cliente = (new Cliente())
            ->setId((int) ($input['cliente_id'] ?? 1))
            ->setNome((string) ($input['nome'] ?? 'Cliente Sandbox'))
            ->setCpf((string) ($input['cpf'] ?? '00000000000'))
            ->setEmail((string) ($input['email'] ?? 'sandbox@local.test'));

        $pedido = new Pedido(
            id: (string) ($input['pedido_id'] ?? uniqid('ped_', true)),
            valor: (string) ($input['valor'] ?? '10.00'),
            parcelas: (int) ($input['parcelas'] ?? 1),
            descricaoFatura: (string) ($input['descricao'] ?? 'Teste sandbox'),
            nomeDoItem: (string) ($input['item'] ?? 'Item sandbox')
        );

        if ($tipo === 'credit_card') {
            $cartao = (new Cartao())
                ->setNome((string) ($input['cartao_nome'] ?? 'CLIENTE SANDBOX'))
                ->setNumero((string) ($input['cartao_numero'] ?? '4111111111111111'))
                ->setVencimentoMes((int) ($input['cartao_mes'] ?? 12))
                ->setVencimentoAno((int) ($input['cartao_ano'] ?? 2030))
                ->setCodigo((string) ($input['cartao_cvv'] ?? '123'));

            $response = $gateway->creditCard($cliente, $pedido, $cartao);
        } elseif ($tipo === 'boleto') {
            $response = $gateway->boleto($cliente, $pedido);
        } else {
            $response = $gateway->pix($cliente, $pedido);
        }

        $json = [
            'ok' => true,
            'status' => $response->getStatus(),
            'statusDescricao' => $response->getStatusDescricao(),
            'idOperadora' => $response->getIdOperadora(),
            'banco' => $response->getBanco(),
        ];

        Storage::insertTransacao([
            'banco' => $banco,
            'operadora_id' => $response->getIdOperadora(),
            'status' => $response->getStatus(),
            'forma' => $tipo,
            'valor' => (float) ($input['valor'] ?? 0),
            'payload_request_json' => json_encode($input, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'payload_response_json' => json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return $this->response->setJSON($json);
    }

    public function reprocessar()
    {
        $input = $this->request->getJSON(true) ?: $this->request->getPost();

        $banco = strtolower((string) ($input['banco'] ?? getenv('BANCO_ATIVO') ?: 'pagarme'));
        $credenciais = CredenciaisAutenticacao::fromEnv($banco);

        $cannal = new CannalPagamentos();
        $gateway = $cannal->registrarBanco($credenciais)->usar($banco);

        $cliente = (new Cliente())
            ->setId((int) ($input['cliente_id'] ?? 1))
            ->setNome((string) ($input['nome'] ?? 'Cliente Sandbox'))
            ->setCpf((string) ($input['cpf'] ?? '00000000000'))
            ->setEmail((string) ($input['email'] ?? 'sandbox@local.test'));

        $pedido = new Pedido(
            id: (string) ($input['pedido_id'] ?? uniqid('ped_', true)),
            valor: (string) ($input['valor'] ?? '10.00'),
            parcelas: (int) ($input['parcelas'] ?? 1),
            descricaoFatura: (string) ($input['descricao'] ?? 'Teste reprocessamento'),
            nomeDoItem: (string) ($input['item'] ?? 'Item sandbox')
        );

        $cartao = (new Cartao())
            ->setNome((string) ($input['cartao_nome'] ?? 'CLIENTE SANDBOX'))
            ->setNumero((string) ($input['cartao_numero'] ?? '4111111111111111'))
            ->setVencimentoMes((int) ($input['cartao_mes'] ?? 12))
            ->setVencimentoAno((int) ($input['cartao_ano'] ?? 2030))
            ->setCodigo((string) ($input['cartao_cvv'] ?? '123'));

        $response = $gateway->reprocessarTransacaoRecusada($cliente, $pedido, $cartao);

        $json = [
            'ok' => true,
            'status' => $response->getStatus(),
            'statusDescricao' => $response->getStatusDescricao(),
            'idOperadora' => $response->getIdOperadora(),
            'banco' => $response->getBanco(),
        ];

        Storage::insertTransacao([
            'banco' => $banco,
            'operadora_id' => $response->getIdOperadora(),
            'status' => $response->getStatus(),
            'forma' => 'reprocessamento',
            'valor' => (float) ($input['valor'] ?? 0),
            'payload_request_json' => json_encode($input, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'payload_response_json' => json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return $this->response->setJSON($json);
    }
}
