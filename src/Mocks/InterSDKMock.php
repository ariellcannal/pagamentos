<?php

namespace CANNALPagamentos\Mocks;

/**
 * Classe de simulação para o SDK do Banco Inter.
 * O objetivo é demonstrar a arquitetura sem dependência real.
 */
class InterSDKMock
{
    public function emitirCobranca(array $requestData): array
    {
        // Simula a resposta da API do Inter para emissão de cobrança (Boleto/Pix)
        return [
            'codigoSolicitacao' => 'INT' . time(),
            'seuNumero' => $requestData['seuNumero'],
            'status' => 'EM_PROCESSAMENTO',
            'valorNominal' => $requestData['valorNominal'],
            'dataVencimento' => $requestData['dataVencimento'],
            // Dados simulados para Pix/Boleto
            'qrCode' => 'BASE64_QRCODE_INTER',
            'linhaDigitavel' => '12345678901234567890123456789012345678901234',
        ];
    }
}
