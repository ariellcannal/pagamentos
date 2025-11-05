<?php

namespace App\Controllers;

use App\Models\User;
use CodeIgniter\Controller;

/**
 * Controller para gerenciar autenticação de usuários.
 */
class Auth extends Controller
{
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->userModel = new User();
        $this->session = session();
    }

    /**
     * Exibe a página de login.
     *
     * @return string
     */
    public function login()
    {
        if ($this->session->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    /**
     * Processa o login do usuário.
     *
     * @return mixed
     */
    public function processLogin()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (!$username || !$password) {
            return redirect()->back()->with('error', 'Usuário e senha são obrigatórios.');
        }

        $user = $this->userModel->authenticate($username, $password);

        if (!$user) {
            return redirect()->back()->with('error', 'Usuário ou senha inválidos.');
        }

        $this->session->set([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'company_name' => $user['company_name'],
        ]);

        return redirect()->to('/dashboard')->with('success', 'Login realizado com sucesso!');
    }

    /**
     * Exibe a página de registro.
     *
     * @return string
     */
    public function register()
    {
        if ($this->session->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/register');
    }

    /**
     * Processa o registro de um novo usuário.
     *
     * @return mixed
     */
    public function processRegister()
    {
        $data = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'password_confirm' => $this->request->getPost('password_confirm'),
            'company_name' => $this->request->getPost('company_name'),
            'company_document' => $this->request->getPost('company_document'),
        ];

        if ($data['password'] !== $data['password_confirm']) {
            return redirect()->back()->with('error', 'As senhas não coincidem.');
        }

        unset($data['password_confirm']);

        if (!$this->userModel->validate($data)) {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }

        if ($this->userModel->register($data)) {
            return redirect()->to('/auth/login')->with('success', 'Usuário registrado com sucesso! Faça login agora.');
        }

        return redirect()->back()->with('error', 'Erro ao registrar usuário.');
    }

    /**
     * Realiza o logout do usuário.
     *
     * @return mixed
     */
    public function logout()
    {
        $this->session->destroy();
        return redirect()->to('/auth/login')->with('success', 'Logout realizado com sucesso!');
    }
}

