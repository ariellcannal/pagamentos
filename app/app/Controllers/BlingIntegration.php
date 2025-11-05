<?php

namespace App\Controllers;

use App\Models\Charge;
use App\Models\User;
use App\Models\UserConfiguration;
use App\Models\BlingSync;
use App\Services\BlingService;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controller para gerenciar a integração com Bling API.
 */
class BlingIntegration extends Controller
{
    protected $chargeModel;
    protected $userModel;
    protected $userConfigModel;
    protected $blingSyncModel;
    protected $session;

    public function __construct()
    {
        $this->chargeModel = new Charge();
        $this->userModel = new User();
        $this->userConfigModel = new UserConfiguration();
        $this->blingSyncModel = new BlingSync();
        $this->session = session();
    }

    /**
     * Exibe o painel de integração com Bling.
     *
     * @return string
     */
    public function index()
    {
        $userId = $this->session->get('user_id');
        $config = $this->userConfigModel->getByUserId($userId);

        $stats = [
            'total_syncs' => $this->blingSyncModel->where('user_id', $userId)->countAllResults(),
            'pending_syncs' => $this->blingSyncModel->countByStatus($userId, 'pending'),
            'successful_syncs' => $this->blingSyncModel->countByStatus($userId, 'success'),
            'failed_syncs' => $this->blingSyncModel->countByStatus($userId, 'failed'),
        ];

        $recentSyncs = $this->blingSyncModel->getSyncHistory($userId, 10, 0);

        $data = [
            'title' => 'Integração com Bling',
            'config' => $config,
            'stats' => $stats,
            'recent_syncs' => $recentSyncs,
            'is_configured' => $config && !empty($config['bling_api_key']),
        ];

        return view('bling_integration/index', $data);
    }

    /**
     * Testa a conexão com Bling.
     *
     * @return ResponseInterface
     */
    public function testConnection()
    {
        $userId = $this->session->get('user_id');
        $config = $this->userConfigModel->getByUserId($userId);

        if (!$config || empty($config['bling_api_key'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Chave de API do Bling não configurada']);
        }

        try {
            $blingService = new BlingService($config['bling_api_key']);

            if ($blingService->testConnection()) {
                return $this->response->setStatusCode(200)->setJSON(['success' => true, 'message' => 'Conexão com Bling bem-sucedida!']);
            } else {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Falha na conexão com Bling']);
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Sincroniza uma cobrança com Bling.
     *
     * @param int $chargeId
     * @return ResponseInterface
     */
    public function syncCharge(int $chargeId)
    {
        $userId = $this->session->get('user_id');
        $charge = $this->chargeModel->getChargeById($chargeId, $userId);

        if (!$charge) {
            return redirect()->back()->with('error', 'Cobrança não encontrada.');
        }

        $config = $this->userConfigModel->getByUserId($userId);

        if (!$config || empty($config['bling_api_key'])) {
            return redirect()->back()->with('error', 'Bling não está configurado.');
        }

        try {
            $blingService = new BlingService($config['bling_api_key']);

            // Mapear cobrança para formato do Bling
            $receivableData = $blingService->mapChargeToReceivable($charge, [
                'name' => $charge['customer_name'],
                'email' => $charge['customer_email'],
                'document' => $charge['customer_document'],
            ]);

            // Criar conta a receber no Bling
            $response = $blingService->createReceivable($receivableData);

            // Registrar sincronização
            $this->blingSyncModel->recordSync(
                $userId,
                $chargeId,
                'charge_to_bling',
                $receivableData,
                'success',
                null
            );

            // Atualizar cobrança com ID do Bling
            $this->chargeModel->update($chargeId, [
                'bling_receivable_id' => $response['data']['id'] ?? null,
            ]);

            return redirect()->back()->with('success', 'Cobrança sincronizada com Bling com sucesso!');
        } catch (\Exception $e) {
            // Registrar erro de sincronização
            $this->blingSyncModel->recordSync(
                $userId,
                $chargeId,
                'charge_to_bling',
                [],
                'failed',
                $e->getMessage()
            );

            return redirect()->back()->with('error', 'Erro ao sincronizar com Bling: ' . $e->getMessage());
        }
    }

    /**
     * Sincroniza todas as cobranças pendentes com Bling.
     *
     * @return ResponseInterface
     */
    public function syncAllCharges()
    {
        $userId = $this->session->get('user_id');
        $config = $this->userConfigModel->getByUserId($userId);

        if (!$config || empty($config['bling_api_key'])) {
            return redirect()->back()->with('error', 'Bling não está configurado.');
        }

        try {
            $blingService = new BlingService($config['bling_api_key']);

            // Obter cobranças não sincronizadas
            $charges = $this->chargeModel->where('user_id', $userId)
                                        ->whereNull('bling_receivable_id')
                                        ->findAll();

            $successCount = 0;
            $errorCount = 0;

            foreach ($charges as $charge) {
                try {
                    $receivableData = $blingService->mapChargeToReceivable($charge, [
                        'name' => $charge['customer_name'],
                        'email' => $charge['customer_email'],
                        'document' => $charge['customer_document'],
                    ]);

                    $response = $blingService->createReceivable($receivableData);

                    $this->blingSyncModel->recordSync(
                        $userId,
                        $charge['id'],
                        'charge_to_bling',
                        $receivableData,
                        'success'
                    );

                    $this->chargeModel->update($charge['id'], [
                        'bling_receivable_id' => $response['data']['id'] ?? null,
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $this->blingSyncModel->recordSync(
                        $userId,
                        $charge['id'],
                        'charge_to_bling',
                        [],
                        'failed',
                        $e->getMessage()
                    );

                    $errorCount++;
                }
            }

            $message = "Sincronização concluída: $successCount sucesso(s), $errorCount erro(s).";
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao sincronizar: ' . $e->getMessage());
        }
    }

    /**
     * Importa contas a receber do Bling.
     *
     * @return ResponseInterface
     */
    public function importReceivables()
    {
        $userId = $this->session->get('user_id');
        $config = $this->userConfigModel->getByUserId($userId);

        if (!$config || empty($config['bling_api_key'])) {
            return redirect()->back()->with('error', 'Bling não está configurado.');
        }

        try {
            $blingService = new BlingService($config['bling_api_key']);

            // Listar contas a receber do Bling
            $receivables = $blingService->listReceivables(['limit' => 100]);

            $importedCount = 0;
            $skippedCount = 0;

            if (isset($receivables['data']) && is_array($receivables['data'])) {
                foreach ($receivables['data'] as $receivable) {
                    // Verificar se já existe
                    $existing = $this->chargeModel->where('bling_receivable_id', $receivable['id'])
                                                   ->where('user_id', $userId)
                                                   ->first();

                    if (!$existing) {
                        // Criar cobrança a partir do Bling
                        $chargeData = [
                            'user_id' => $userId,
                            'bank_type' => 'bling',
                            'charge_type' => 'imported',
                            'origin' => 'bling_import',
                            'bling_receivable_id' => $receivable['id'],
                            'amount' => $receivable['valor'] ?? 0,
                            'description' => $receivable['descricao'] ?? 'Importado do Bling',
                            'customer_name' => $receivable['contato']['nome'] ?? 'Cliente Bling',
                            'customer_email' => $receivable['contato']['email'] ?? '',
                            'customer_document' => $receivable['contato']['documento'] ?? '',
                            'due_date' => $receivable['dataVencimento'] ?? date('Y-m-d'),
                            'status' => $blingService->mapBlingStatusToCharge($receivable['situacao'] ?? 'aberto'),
                            'bank_response' => json_encode($receivable),
                        ];

                        $this->chargeModel->insert($chargeData);

                        $this->blingSyncModel->recordSync(
                            $userId,
                            0,
                            'bling_to_charge',
                            $chargeData,
                            'success'
                        );

                        $importedCount++;
                    } else {
                        $skippedCount++;
                    }
                }
            }

            $message = "Importação concluída: $importedCount importado(s), $skippedCount ignorado(s).";
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao importar: ' . $e->getMessage());
        }
    }

    /**
     * Exibe o histórico de sincronizações.
     *
     * @return string
     */
    public function syncHistory()
    {
        $userId = $this->session->get('user_id');
        $page = $this->request->getVar('page') ?? 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $syncs = $this->blingSyncModel->getSyncHistory($userId, $limit, $offset);
        $totalSyncs = $this->blingSyncModel->where('user_id', $userId)->countAllResults();

        $data = [
            'title' => 'Histórico de Sincronizações',
            'syncs' => $syncs,
            'total_syncs' => $totalSyncs,
            'current_page' => $page,
            'total_pages' => ceil($totalSyncs / $limit),
        ];

        return view('bling_integration/sync_history', $data);
    }
}

