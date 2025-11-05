<?php

namespace App\Controllers;

use App\Models\Charge;
use App\Models\UserConfiguration;
use CANNALPagamentos\Entities\Cliente;
use CANNALPagamentos\Entities\Pedido;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controller para gerenciar cobranças.
 */
class Charges extends Controller
{
    protected $chargeModel;
    protected $userConfigModel;
    protected $session;

    public function __construct()
    {
        $this->chargeModel = new Charge();
        $this->userConfigModel = new UserConfiguration();
        $this->session = session();
    }

    /**
     * Lista as cobranças do usuário.
     *
     * @return string
     */
    public function index()
    {
        $userId = $this->session->get('user_id');
        $page = $this->request->getVar('page') ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $charges = $this->chargeModel->getByUserId($userId, $limit, $offset);
        $totalCharges = $this->chargeModel->where('user_id', $userId)->countAllResults();

        $data = [
            'title' => 'Cobranças',
            'charges' => $charges,
            'total_charges' => $totalCharges,
            'current_page' => $page,
            'total_pages' => ceil($totalCharges / $limit),
        ];

        return view('charges/index', $data);
    }

    /**
     * Exibe o formulário de criação de cobrança.
     *
     * @return string
     */
    public function create()
    {
        if ($this->request->getMethod() === 'post') {
            return $this->processCreate();
        }

        $data = [
            'title' => 'Criar Cobrança',
            'banks' => ['pagarme' => 'Pagar.me', 'inter' => 'Banco Inter', 'c6' => 'C6 Bank'],
            'charge_types' => [
                'boleto' => 'Boleto',
                'pix' => 'Pix',
                'credit_card' => 'Cartão de Crédito',
                'debit_card' => 'Cartão de Débito',
                'payment_link' => 'Link de Pagamento',
            ],
        ];

        return view('charges/create', $data);
    }

    /**
     * Processa a criação de uma cobrança.
     *
     * @return ResponseInterface
     */
    private function processCreate()
    {
        $userId = $this->session->get('user_id');

        // Validação dos dados
        $rules = [
            'bank_type' => 'required|in_list[pagarme,inter,c6]',
            'charge_type' => 'required|in_list[boleto,pix,credit_card,debit_card,payment_link]',
            'amount' => 'required|numeric|greater_than[0]',
            'customer_name' => 'required|string|max_length[255]',
            'customer_email' => 'required|valid_email',
            'customer_document' => 'required|string|max_length[20]',
            'due_date' => 'required|valid_date',
            'description' => 'permit_empty|string',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $data['user_id'] = $userId;
        $data['status'] = 'pending';
        $data['origin'] = 'manual';

        // Instanciar o gateway de pagamento
        $gateway = $this->getGateway($data['bank_type'], $userId);

        if (!$gateway) {
            return redirect()->back()->with('error', 'Configuração de banco não encontrada.');
        }

        try {
            // Criar cliente
            $cliente = new Cliente();
            $cliente->setNome($data['customer_name'])
                    ->setEmail($data['customer_email'])
                    ->setDocumento($data['customer_document']);

            // Criar pedido
            $pedido = new Pedido();
            $pedido->setId($data['customer_document'])
                   ->setValor($data['amount'])
                   ->setNomeDoItem($data['description'] ?? 'Cobrança')
                   ->setDataVencimento(new \DateTime($data['due_date']));

            // Chamar o método apropriado do gateway
            $methodName = $data['charge_type'];
            if (method_exists($gateway, $methodName)) {
                $transacao = $gateway->$methodName($cliente, $pedido);

                // Salvar a cobrança no banco de dados
                $data['bank_charge_id'] = $transacao->getOperadoraID();
                $data['pix_qr_code'] = $transacao->getPixQrCode();
                $data['boleto_barcode'] = $transacao->getPixQrCode(); // Usar o mesmo campo para boleto
                $data['bank_response'] = json_encode($transacao);

                $this->chargeModel->insert($data);

                return redirect()->to('/charges')->with('success', 'Cobrança criada com sucesso!');
            } else {
                return redirect()->back()->with('error', 'Método de cobrança não suportado pelo gateway.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao criar cobrança: ' . $e->getMessage());
        }
    }

    /**
     * Obtém a instância do gateway de pagamento.
     *
     * @param string $bankType
     * @param int $userId
     * @return object|null
     */
    private function getGateway(string $bankType, int $userId)
    {
        $config = $this->userConfigModel->getByUserId($userId);

        if (!$config) {
            return null;
        }

        try {
            switch ($bankType) {
                case 'pagarme':
                    return new \CANNALPagamentos\Interfaces\Pagarme(
                        $config['pagarme_api_key'],
                        null
                    );
                case 'inter':
                    return new \CANNALPagamentos\Interfaces\Inter(
                        $config['inter_client_id'],
                        $config['inter_client_secret'],
                        $config['inter_certificate_path'],
                        $config['inter_certificate_password']
                    );
                case 'c6':
                    return new \CANNALPagamentos\Interfaces\C6(
                        $config['c6_api_key']
                    );
                default:
                    return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Exibe os detalhes de uma cobrança.
     *
     * @param int $chargeId
     * @return string|ResponseInterface
     */
    public function view(int $chargeId)
    {
        $userId = $this->session->get('user_id');
        $charge = $this->chargeModel->getChargeById($chargeId, $userId);

        if (!$charge) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cobrança não encontrada.');
        }

        $data = [
            'title' => 'Detalhes da Cobrança',
            'charge' => $charge,
        ];

        return view('charges/view', $data);
    }
}

