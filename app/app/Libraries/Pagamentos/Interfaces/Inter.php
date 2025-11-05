<?php

namespace App\Libraries\Pagamentos\Interfaces;

use App\Libraries\Pagamentos\PagamentosInterface;
use App\Libraries\Pagamentos\Entities\Cliente;
use App\Libraries\Pagamentos\Entities\Pedido;
use App\Libraries\Pagamentos\Entities\Transacao;
use App\Libraries\Pagamentos\Entities\Recebivel;
use Psr\Log\LoggerInterface;
use Inter\InterSdk;
use Inter\Model\Cobranca;
use Inter\Model\FiltroBuscarCobrancas;
use Inter\Model\Item;
use Inter\Model\Pessoa;
use Inter\Model\Endereco;

class Inter implements PagamentosInterface
{
    private InterSdk $sdk;
    private LoggerInterface $logger;

    public function __construct(string $clientId, string $clientSecret, string $certificatePath, string $certificatePassword, LoggerInterface $logger)
    {
        $this->logger = $logger;
        
        // Inicialização do SDK do Inter
        $this->sdk = new InterSdk(
            $clientId,
            $clientSecret,
            $certificatePath,
            $certificatePassword
        );
    }

    private function mapClienteToPessoa(Cliente $cli): Pessoa
    {
        $pessoa = new Pessoa();
        $pessoa->setCpfCnpj($cli->getCPF());
        $pessoa->setNome($cli->getNome());
        $pessoa->setEmail($cli->getEmail());
        $pessoa->setTelefone($cli->getTelefone());
        
        // Mapeamento de Endereço (simplificado)
        $endereco = new Endereco();
        $endereco->setCep($cli->getEndereco()->getCep());
        $endereco->setCidade($cli->getEndereco()->getCidade());
        $endereco->setUf($cli->getEndereco()->getEstado());
        $endereco->setBairro($cli->getEndereco()->getBairro());
        $endereco->setLogradouro($cli->getEndereco()->getRua());
        $endereco->setNumero($cli->getEndereco()->getNumero());
        
        $pessoa->setEndereco($endereco);
        
        return $pessoa;
    }

    public function creditCard(Cliente &$cli, Pedido $pedido): Transacao
    {
        // A API de Cobrança do Inter não suporta diretamente cartão de crédito.
        // O método deve lançar uma exceção informativa.
        throw new \Exception("Método 'creditCard' não suportado pela API de Cobrança do Banco Inter. Use Pix ou Boleto.");
    }

    public function boleto(Cliente &$cli, Pedido $pedido): Transacao
    {
        $this->logger->info("Inter: Criando cobrança (Boleto) para o cliente {$cli->getNome()}");

        $cobranca = new Cobranca();
        $cobranca->setValorNominal($pedido->getValor());
        $cobranca->setDataVencimento($pedido->getDataVencimento());
        $cobranca->setSeuNumero($pedido->getId()); // ID do pedido na sua aplicação
        $cobranca->setNumDiasAgenda(0); // Não agendar

        // Mapeamento do cliente
        $cobranca->setPessoa($this->mapClienteToPessoa($cli));

        // Adicionar item (simplificado)
        $item = new Item();
        $item->setDescricao("Cobrança: {$pedido->getDescricao()}");
        $item->setQuantidade(1);
        $item->setValor(number_format($pedido->getValor(), 2, '.', ''));
        $cobranca->addItem($item);

        try {
            $response = $this->sdk->cobranca()->criar($cobranca);
            
            $transacao = new Transacao();
            $transacao->setOperadoraID($response->getCodigoSolicitacao());
            $transacao->setOperadoraStatus($response->getSituacao());
            $transacao->setValorBruto($response->getValorNominal());
            $transacao->setOperadoraResposta($response->toArray());
            $transacao->setOperadoraCodigo($response->getSeuNumero()); // ID do pedido
            
            return $transacao;

        } catch (\Exception $e) {
            $this->logger->error("Inter: Erro ao criar boleto: " . $e->getMessage());
            throw $e;
        }
    }

    public function pix(Cliente &$cli, Pedido $pedido): Transacao
    {
        $this->logger->info("Inter: Criando cobrança (Pix) para o cliente {$cli->getNome()}");

        // A lógica é a mesma do boleto, mas o SDK do Inter trata a forma de pagamento
        // com base na configuração da conta. Aqui, assumimos que a API de Cobrança
        // pode gerar Pix ou Boleto dependendo da configuração.
        // Para Pix, o Inter usa a API de Cobrança.

        $cobranca = new Cobranca();
        $cobranca->setValorNominal($pedido->getValor());
        $cobranca->setDataVencimento($pedido->getDataVencimento());
        $cobranca->setSeuNumero($pedido->getId()); // ID do pedido na sua aplicação
        $cobranca->setNumDiasAgenda(0); // Não agendar

        // Mapeamento do cliente
        $cobranca->setPessoa($this->mapClienteToPessoa($cli));

        // Adicionar item (simplificado)
        $item = new Item();
        $item->setDescricao("Cobrança: {$pedido->getDescricao()}");
        $item->setQuantidade(1);
        $item->setValor(number_format($pedido->getValor(), 2, '.', ''));
        $cobranca->addItem($item);

        try {
            $response = $this->sdk->cobranca()->criar($cobranca);
            
            $transacao = new Transacao();
            $transacao->setOperadoraID($response->getCodigoSolicitacao());
            $transacao->setOperadoraStatus($response->getSituacao());
            $transacao->setValorBruto($response->getValorNominal());
            $transacao->setOperadoraResposta($response->toArray());
            $transacao->setOperadoraCodigo($response->getSeuNumero()); // ID do pedido
            
            return $transacao;

        } catch (\Exception $e) {
            $this->logger->error("Inter: Erro ao criar Pix: " . $e->getMessage());
            throw $e;
        }
    }

    public function refund(string $chargeId, float $amount): Transacao
    {
        // A API de Cobrança do Inter não suporta estorno direto via SDK.
        // Seria necessário usar a API de Pagamentos ou Transferências.
        throw new \Exception("Método 'refund' não implementado para o Banco Inter via API de Cobrança.");
    }

    public function saveCard(Cliente &$cli, string $token): string
    {
        // A API de Cobrança do Inter não suporta tokenização de cartão.
        throw new \Exception("Método 'saveCard' não suportado pelo Banco Inter.");
    }

    public function getCards(Cliente $cli): array
    {
        // A API de Cobrança do Inter não suporta gestão de cartões.
        return [];
    }

    public function updateCustumer(Cliente $cli): Cliente
    {
        // A API de Cobrança do Inter não suporta atualização de cliente.
        return $cli;
    }

    public function getReceivable(string $receivableId): Recebivel
    {
        // A API de Cobrança do Inter não suporta recebíveis.
        throw new \Exception("Método 'getReceivable' não suportado pelo Banco Inter.");
    }

    public function getReceivables(array $filters): array
    {
        // A API de Cobrança do Inter não suporta recebíveis.
        return [];
    }

    public function getCharge(string $chargeId): Transacao
    {
        $this->logger->info("Inter: Buscando cobrança {$chargeId}");

        try {
            $filtro = new FiltroBuscarCobrancas();
            $filtro->setCodigoSolicitacao($chargeId);
            
            $response = $this->sdk->cobranca()->buscar($filtro);
            
            if (empty($response->getContent())) {
                throw new \Exception("Cobrança {$chargeId} não encontrada no Banco Inter.");
            }
            
            $cobranca = $response->getContent()[0];
            
            $transacao = new Transacao();
            $transacao->setOperadoraID($cobranca->getCodigoSolicitacao());
            $transacao->setOperadoraStatus($cobranca->getSituacao());
            $transacao->setValorBruto($cobranca->getValorNominal());
            $transacao->setOperadoraResposta($cobranca->toArray());
            $transacao->setOperadoraCodigo($cobranca->getSeuNumero());
            
            return $transacao;

        } catch (\Exception $e) {
            $this->logger->error("Inter: Erro ao buscar cobrança: " . $e->getMessage());
            throw $e;
        }
    }

    public function cancelCharge(string $chargeId): Transacao
    {
        $this->logger->info("Inter: Cancelando cobrança {$chargeId}");

        try {
            $this->sdk->cobranca()->cancelar($chargeId);
            
            $transacao = new Transacao();
            $transacao->setOperadoraID($chargeId);
            $transacao->setOperadoraStatus('CANCELADA');
            
            return $transacao;

        } catch (\Exception $e) {
            $this->logger->error("Inter: Erro ao cancelar cobrança: " . $e->getMessage());
            throw $e;
        }
    }
}

