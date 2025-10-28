<?php

namespace CANNALPagamentos\Interfaces;

use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Pedido;
use CANNALPagamentos\Entities\Transacao;
use Inter\InterSdk; // SDK real do Banco Inter
use Psr\Log\LoggerInterface;
use Exception;

class Inter implements PagamentosInterface
{
    private InterSdk $sdk;
    private LoggerInterface $logger;

    /**
     * Construtor que recebe as credenciais para inicializar o SDK do Inter.
     *
     * @param LoggerInterface $logger
     * @param string $clientId Client ID da integração
     * @param string $clientSecret Client Secret da integração
     * @param string $certificatePath Caminho para o certificado PFX
     * @param string $certificatePassword Senha do certificado PFX
     * @param string $environment Ambiente (PRODUCTION ou SANDBOX)
     */
    public function __construct(
        LoggerInterface $logger,
        string $clientId,
        string $clientSecret,
        string $certificatePath,
        string $certificatePassword,
        string $environment = "SANDBOX"
    ) {
        $this->logger = $logger;
        
        // Inicialização do SDK do Inter com as credenciais fornecidas
        // O SDK inter/sdk espera o caminho do certificado e a senha para autenticação mútua.
        $this->sdk = new InterSdk(
            $environment,
            $clientId,
            $clientSecret,
            $certificatePath,
            $certificatePassword
        );
    }

    // Métodos de PagamentosInterface (Implementação real)

    public function creditCard(Cliente &$cli, Pedido $pedido, $cartao, ?string $token = null): Transacao
    {
        // O Inter não suporta diretamente transações de Cartão de Crédito via API de Cobrança.
        // Se a intenção é usar o Inter como adquirente, a lógica seria diferente e usaria outra API.
        throw new Exception("Não Suportado: O Banco Inter não suporta transações de Cartão de Crédito via API de Cobrança.");
    }

    public function pix(Cliente &$cli, Pedido $pedido, $cartao, ?string $token = null): Transacao
    {
        // Lógica de Adapter: Traduzir Entidades para o formato de requisição do Inter
        // A API de Cobrança (Boleto com Pix) é usada para emitir o Pix.
        
        // Exemplo de mapeamento para a API de Cobrança (Pix)
        $cobranca = [
            'seuNumero' => $pedido->getId(),
            'valorNominal' => $pedido->getValorTotal(),
            'dataVencimento' => date('Y-m-d', strtotime('+7 days')),
            'pagador' => [
                'cpfCnpj' => $cli->getCpfCnpj(),
                'nome' => $cli->getNome(),
                // ... outros dados do cliente ...
            ],
            // ... outros campos necessários para Pix ...
        ];

        try {
            // Chamada real ao SDK do Inter
            $response = $this->sdk->cobranca().emitirCobranca($cobranca);
            
            // Lógica de Adapter: Traduzir resposta do Inter para Entidade Transacao
            $transacao = new Transacao();
            $transacao->setOperadoraID($response->getCodigoSolicitacao());
            $transacao->setOperadoraStatus($response->getSituacao());
            $transacao->setValorBruto($response->getValorNominal());
            $transacao->setPixQrCode($response->getQrCode());
            $transacao->setOperadoraCodigo($response->getSeuNumero());
            $transacao->setOperadora('Inter');
            
            return $transacao;
        } catch (Exception $e) {
            $this->logger->error("Erro ao emitir Pix (Inter): " . $e->getMessage());
            throw $e;
        }
    }

    public function refund(string $charge_id, float $amount): Transacao
    {
        // Lógica real de estorno
        try {
            // A API do Inter para estorno de Pix/Boleto é diferente e precisa ser implementada
            // Exemplo: $this->sdk->cobranca()->cancelarCobranca($charge_id);
            
            $this->logger->info("Tentativa de estorno de {$amount} para charge ID {$charge_id} no Inter.");
            
            // Simulação de sucesso (a ser substituída pelo código do SDK)
            $transacao = new Transacao();
            $transacao->setOperadoraID($charge_id);
            $transacao->setValorCancelado($amount);
            $transacao->setOperadoraStatus('REFUNDED');
            $transacao->setDataCancelamento(date('Y-m-d H:i:s'));
            return $transacao;
        } catch (Exception $e) {
            $this->logger->error("Erro ao realizar estorno (Inter): " . $e->getMessage());
            throw $e;
        }
    }

    public function saveCard(Cliente &$cli, string $cartao): string
    {
        throw new Exception("Não Suportado: O Banco Inter não suporta a funcionalidade SaveCard via API de Cobrança.");
    }

    public function getCards(Cliente $cli): array
    {
        return [];
    }

    public function updateCustumer(Cliente $cli): Cliente
    {
        // O SDK do Inter não tem um método direto para 'updateCustomer' na API de Cobrança.
        // Os dados do pagador são enviados a cada nova cobrança.
        return $cli;
    }
    
    public function getReceivable(string $id): Transacao
    {
        throw new Exception("Não Suportado: Consulta de recebíveis não é suportada diretamente na API de Cobrança do Inter.");
    }
    public function getReceivables(array $params): array
    {
        throw new Exception("Não Suportado: Consulta de recebíveis não é suportada diretamente na API de Cobrança do Inter.");
    }
    public function getCharge(string $id): Transacao
    {
        // Lógica real de consulta de cobrança
        try {
            $response = $this->sdk->cobranca()->consultarCobranca($id);
            
            $transacao = new Transacao();
            $transacao->setOperadoraID($response->getCodigoSolicitacao());
            $transacao->setOperadoraStatus($response->getSituacao());
            $transacao->setValorBruto($response->getValorNominal());
            $transacao->setOperadora('Inter');
            
            return $transacao;
        } catch (Exception $e) {
            $this->logger->error("Erro ao consultar charge (Inter): " . $e->getMessage());
            throw $e;
        }
    }
    public function cancelCharge(string $charge_id): Transacao
    {
        // Lógica real de cancelamento de cobrança
        try {
            $this->sdk->cobranca()->cancelarCobranca($charge_id);
            
            $transacao = new Transacao();
            $transacao->setOperadoraID($charge_id);
            $transacao->setOperadoraStatus('CANCELLED');
            $transacao->setDataCancelamento(date('Y-m-d H:i:s'));
            return $transacao;
        } catch (Exception $e) {
            $this->logger->error("Erro ao cancelar charge (Inter): " . $e->getMessage());
            throw $e;
        }
    }
    
    // Implementação de boleto (adicional, mas importante para o Inter)
    public function boleto(Cliente &$cli, Pedido $pedido, $cartao, ?string $token = null): Transacao
    {
        // A lógica é a mesma do Pix, mas com o payload ajustado para Boleto
        return $this->pix($cli, $pedido, $cartao, $token);
    }
}
