<?php

namespace CANNALPagamentos\Mocks;

/**
 * Classe de simulação para o SDK do C6 Bank.
 * O objetivo é demonstrar a arquitetura sem dependência real.
 */
class C6SDKMock
{
    public function createCharge(array $requestData): array
    {
        // Simula a resposta da API do C6 Bank para criação de cobrança (Checkout/Pix)
        return [
            'external_id' => 'C6_' . time(),
            'status' => 'CREATED',
            'amount' => $requestData['amount'],
            'payment_method' => $requestData['payment_method'],
            'partner_id' => $requestData['partner_id'],
            // Dados simulados
            'pix_code' => '00020126330014BR.GOV.BCB.PIX0111',
        ];
    }
}
