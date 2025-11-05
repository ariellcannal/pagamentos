<?php

namespace App\Controllers;

use App\Models\Charge;
use App\Models\UserConfiguration;
use CodeIgniter\Controller;

/**
 * Controller para o Dashboard administrativo.
 */
class Dashboard extends Controller
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
     * Exibe o dashboard com estatísticas e cobranças recentes.
     *
     * @return string
     */
    public function index()
    {
        $userId = $this->session->get('user_id');

        // Estatísticas
        $stats = [
            'total_charges' => $this->chargeModel->where('user_id', $userId)->countAllResults(),
            'pending_charges' => $this->chargeModel->countByStatus($userId, 'pending'),
            'paid_charges' => $this->chargeModel->countByStatus($userId, 'paid'),
            'overdue_charges' => $this->chargeModel->countByStatus($userId, 'overdue'),
        ];

        // Cobranças recentes
        $recentCharges = $this->chargeModel->getByUserId($userId, 10, 0);

        // Configurações do usuário
        $config = $this->userConfigModel->getByUserId($userId);

        $data = [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recent_charges' => $recentCharges,
            'config' => $config,
        ];

        return view('dashboard/index', $data);
    }
}

