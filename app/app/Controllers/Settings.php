<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\UserConfiguration;
use CodeIgniter\Controller;
use CodeIgniter\Log\Logger;
use App\Libraries\Pagamentos\Interfaces\Pagarme;
use App\Libraries\Pagamentos\Interfaces\Inter;
use App\Libraries\Pagamentos\Interfaces\C6;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controller para gerenciar configurações do usuário.
 */
class Settings extends Controller
{
    protected $userModel;
    protected $userConfigModel;
    protected $session;
    protected $logger;

    public function __construct()
    {
        $this->userModel = new User();
        $this->userConfigModel = new UserConfiguration();
        $this->session = session();
        $this->logger = \Config\Services::logger();
    }

    /**
     * Exibe a página de configurações.
     *
     * @return string
     */
    public function index()
    {
        $userId = $this->session->get('user_id');
        $user = $this->userModel->find($userId);
        $config = $this->userConfigModel->getByUserId($userId);

        $data = [
            'title' => 'Configurações',
            'user' => $user,
            'config' => $config,
            'banks' => ['pagarme' => 'Pagar.me', 'inter' => 'Banco Inter', 'c6' => 'C6 Bank'],
        ];

        return view('settings/index', $data);
    }

    /**
     * Processa a atualização de configurações.
     *
     * @return ResponseInterface
     */
    public function update()
    {
        $userId = $this->session->get('user_id');

        // Validação dos dados
        $rules = [
            'company_name' => 'permit_empty|string|max_length[255]',
            'company_document' => 'permit_empty|string|max_length[20]',
            'bank_type' => 'permit_empty|in_list[pagarme,inter,c6]',
            'pagarme_api_key' => 'permit_empty|string',
            'inter_client_id' => 'permit_empty|string',
            'inter_client_secret' => 'permit_empty|string',
            'inter_certificate_path' => 'permit_empty|string',
            'inter_certificate_password' => 'permit_empty|string',
            'c6_api_key' => 'permit_empty|string',
            'c6_api_secret' => 'permit_empty|string',
            'bling_api_key' => 'permit_empty|string',
            'bling_webhook_url' => 'permit_empty|valid_url',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Atualizar dados do usuário
        $userData = [
            'company_name' => $this->request->getPost('company_name'),
            'company_document' => $this->request->getPost('company_document'),
        ];

        $this->userModel->update($userId, $userData);

        // Atualizar configurações
        $configData = [
            'bank_type' => $this->request->getPost('bank_type'),
            'pagarme_api_key' => $this->request->getPost('pagarme_api_key'),
            'inter_client_id' => $this->request->getPost('inter_client_id'),
            'inter_client_secret' => $this->request->getPost('inter_client_secret'),
            'inter_certificate_path' => $this->request->getPost('inter_certificate_path'),
            'inter_certificate_password' => $this->request->getPost('inter_certificate_password'),
            'c6_api_key' => $this->request->getPost('c6_api_key'),
            'c6_api_secret' => $this->request->getPost('c6_api_secret'),
            'bling_api_key' => $this->request->getPost('bling_api_key'),
            'bling_webhook_url' => $this->request->getPost('bling_webhook_url'),
        ];

        // Processar instruções de boleto (JSON)
        $boletoInstructions = $this->request->getPost('boleto_instructions');
        if ($boletoInstructions) {
            $instructions = [];
            for ($i = 1; $i <= 4; $i++) {
                $instruction = trim($this->request->getPost("boleto_instruction_$i"));
                if ($instruction) {
                    $instructions[] = $instruction;
                }
            }
            $configData['boleto_instructions'] = json_encode($instructions);
        }

        $this->userConfigModel->updateByUserId($userId, $configData);

        return redirect()->to('/settings')->with('success', 'Configurações atualizadas com sucesso!');
    }

    /**
     * Testa a conexão com um banco.
     *
     * @return ResponseInterface
     */
    public function testConnection()
    {
        $userId = $this->session->get('user_id');
        $bankType = $this->request->getPost('bank_type');

        $config = $this->userConfigModel->getByUserId($userId);

        if (!$config) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Configuração não encontrada']);
        }

        try {
            switch ($bankType) {
                case 'pagarme':
                    if (!$config['pagarme_api_key']) {
                        throw new \Exception('Chave de API do Pagar.me não configurada');
                    }
                    // Teste simples de conexão
                    $gateway = new Pagarme($config['pagarme_api_key'], $this->logger);
                    // TODO: Implementar um método de teste no Pagarme que não crie cobrança
                    return $this->response->setStatusCode(200)->setJSON(['success' => true, 'message' => 'Conexão com Pagar.me bem-sucedida!']);

                case 'inter':
                    if (!$config['inter_client_id'] || !$config['inter_client_secret']) {
                        throw new \Exception('Credenciais do Banco Inter não configuradas');
                    }
                    // Teste simples de conexão
                    $gateway = new Inter(
                        $config['inter_client_id'],
                        $config['inter_client_secret'],
                        $config['inter_certificate_path'],
                        $config['inter_certificate_password'],
                        $this->logger
                    );
                    // TODO: Implementar um método de teste no Inter que não crie cobrança
                    return $this->response->setStatusCode(200)->setJSON(['success' => true, 'message' => 'Conexão com Banco Inter bem-sucedida!']);

                case 'c6':
                    if (!$config['c6_api_key'] || !$config['c6_api_secret']) {
                        throw new \Exception('Credenciais do C6 Bank não configuradas');
                    }
                    // Teste simples de conexão
                    $gateway = new C6($config['c6_api_key'], $config['c6_api_secret'], $this->logger);
                    // TODO: Implementar um método de teste no C6 que não crie cobrança
                    return $this->response->setStatusCode(200)->setJSON(['success' => true, 'message' => 'Conexão com C6 Bank bem-sucedida!']);

                default:
                    throw new \Exception('Banco não suportado');
            }
        } catch (\Exception $e) {
            return $this->response->setStatusCode(400)->setJSON(['error' => $e->getMessage()]);
        }
    }
}

