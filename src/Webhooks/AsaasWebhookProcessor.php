<?php

namespace CANNALPagamentos\Webhooks;

use CANNALPagamentos\Interfaces\WebhookProcessorInterface;
use CANNALPagamentos\Entities\Transacao;
use Psr\Log\LoggerInterface;
use Exception;

class AsaasWebhookProcessor implements WebhookProcessorInterface
{
    private LoggerInterface $logger;

    private ?string $authToken = null;

    public function __construct(LoggerInterface $logger, ?string $authToken = null)
    {
        $this->logger = $logger;
        $this->authToken = $authToken;
    }

    /**
     * Processa o payload bruto do Webhook de um Gateway.
     *
     * @param array $payload O payload JSON/Array recebido do Asaas.
     * @param array $headers Os headers HTTP para validação de segurança.
     * @return Transacao A entidade de Transacao padronizada.
     * @throws Exception Em caso de falha na validação ou processamento.
     */
    public function process(array $payload, array $headers): Transacao
    {
        // 1. Validação de Segurança
        if ($this->authToken) {
            $asaasToken = $headers['asaas-access-token'] ?? $headers['HTTP_ASAAS_ACCESS_TOKEN'] ?? null;
            
            if (!$asaasToken) {
                throw new Exception("Webhook do Asaas sem token de autenticação.");
            }

            if ($asaasToken !== $this->authToken) {
                throw new Exception("Token de autenticação inválido do webhook Asaas.");
            }
        }

        // 2. Extração e Validação
        $event = $payload['event'] ?? null;
        $data = $payload['data'] ?? null;

        if (!$event || !$data) {
            throw new Exception("Payload do Asaas inválido: event ou data não encontrado.");
        }

        // Log do evento
        $this->logger->info("Webhook Asaas recebido: Evento=$event, ID={$data['id']}");

        // 3. Filtra apenas eventos de pagamento
        $paymentEvents = [
            'PAYMENT_CREATED',
            'PAYMENT_UPDATED',
            'PAYMENT_CONFIRMED',
            'PAYMENT_RECEIVED',
            'PAYMENT_OVERDUE',
            'PAYMENT_DELETED',
            'PAYMENT_RESTORED',
            'PAYMENT_REFUNDED',
            'PAYMENT_CANCELLED',
            'PAYMENT_CHARGEBACK_INITIATED',
            'PAYMENT_CHARGEBACK_RESOLVED',
        ];

        if (!in_array($event, $paymentEvents)) {
            throw new Exception("Evento do Asaas não suportado para Transação: $event");
        }

        // 4. Mapeamento para Transacao
        return $this->mapPaymentToTransacao($data, $event);
    }

    /**
     * Mapeia os dados do pagamento do Asaas para a entidade Transacao.
     *
     * @param array $paymentData Os dados do pagamento do webhook.
     * @param string $event O tipo de evento.
     * @return Transacao
     */
    private function mapPaymentToTransacao(array $paymentData, string $event): Transacao
    {
        $transacao = new Transacao();

        // Dados básicos
        $transacao->setOperadoraID($paymentData['id'] ?? null);
        $transacao->setOperadoraStatus($paymentData['status'] ?? null);
        $transacao->setOperadora('Asaas');
        $transacao->setOperadoraResposta(json_encode($paymentData));
        $transacao->setOperadoraCodigo($paymentData['externalReference'] ?? null);

        // Valores
        $transacao->setValorBruto($paymentData['value'] ?? 0);
        $transacao->setValorLiquido($paymentData['netValue'] ?? 0);
        
        // Caso seja um reembolso, mapear o valor cancelado
        if (in_array($event, ['PAYMENT_REFUNDED', 'PAYMENT_CANCELLED'])) {
            $transacao->setValorCancelado($paymentData['value'] ?? 0);
            $transacao->setDataCancelamento($paymentData['updatedAt'] ?? null);
        }

        // Datas
        $transacao->setDataTransacao($paymentData['createdAt'] ?? null);
        $transacao->setDataExpiracao($paymentData['dueDate'] ?? null);

        // Mapeamento de tipo de pagamento (forma)
        $billingType = $paymentData['billingType'] ?? '';
        switch ($billingType) {
            case 'BOLETO':
                $transacao->setForma('boleto');
                break;
            case 'CREDIT_CARD':
                $transacao->setForma('creditcard');
                $transacao->setCartao($paymentData['creditCard']['number'] ?? null);
                break;
            case 'PIX':
                $transacao->setForma('pix');
                if ($paymentData['pixQrCodeUrl'] ?? null) {
                    $transacao->setPixQrCodeUrl($paymentData['pixQrCodeUrl']);
                }
                break;
            case 'DEBIT_ACCOUNT':
                $transacao->setForma('debit');
                break;
        }

        // Determinar se está confirmada baseado no status
        $confirmedStatuses = ['CONFIRMED', 'RECEIVED', 'OVERDUE'];
        $transacao->setConfirmada(in_array($paymentData['status'] ?? '', $confirmedStatuses));

        // Lógica específica por tipo de evento
        switch ($event) {
            case 'PAYMENT_REFUNDED':
                $transacao->setConfirmada(false);
                break;

            case 'PAYMENT_CANCELLED':
                $transacao->setConfirmada(false);
                break;

            case 'PAYMENT_CHARGEBACK_INITIATED':
                // Marcar como contestada
                if (method_exists($transacao, 'setDisputada')) {
                    $transacao->setDisputada(true);
                }
                break;

            case 'PAYMENT_CHARGEBACK_RESOLVED':
                // Resolver disputa
                if (method_exists($transacao, 'setDisputada')) {
                    $transacao->setDisputada(false);
                }
                break;
        }

        return $transacao;
    }
}
