<?php

namespace App\Controllers;

use App\Models\EmailTemplate;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controller para gerenciar templates de e-mail.
 */
class EmailTemplates extends Controller
{
    protected $emailTemplateModel;
    protected $session;

    public function __construct()
    {
        $this->emailTemplateModel = new EmailTemplate();
        $this->session = session();
    }

    /**
     * Lista os templates de e-mail do usuário.
     *
     * @return string
     */
    public function index()
    {
        $userId = $this->session->get('user_id');
        $templates = $this->emailTemplateModel->getByUserId($userId);

        $data = [
            'title' => 'Templates de E-mail',
            'templates' => $templates,
            'template_types' => [
                'charge_created' => 'Cobrança Criada',
                'charge_paid' => 'Cobrança Paga',
                'charge_overdue' => 'Cobrança Vencida',
                'charge_reminder' => 'Lembrete de Cobrança',
            ],
        ];

        return view('email_templates/index', $data);
    }

    /**
     * Exibe o formulário de edição de um template.
     *
     * @param int $templateId
     * @return string|ResponseInterface
     */
    public function edit(int $templateId)
    {
        $userId = $this->session->get('user_id');
        $template = $this->emailTemplateModel->find($templateId);

        if (!$template || $template['user_id'] !== $userId) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Template não encontrado.');
        }

        $data = [
            'title' => 'Editar Template de E-mail',
            'template' => $template,
            'variables' => [
                '{customer_name}' => 'Nome do cliente',
                '{customer_email}' => 'E-mail do cliente',
                '{charge_id}' => 'ID da cobrança',
                '{charge_amount}' => 'Valor da cobrança',
                '{charge_due_date}' => 'Data de vencimento',
                '{charge_description}' => 'Descrição da cobrança',
                '{pix_qr_code}' => 'QR Code do Pix',
                '{boleto_barcode}' => 'Código de barras do boleto',
                '{payment_link}' => 'Link de pagamento',
                '{company_name}' => 'Nome da empresa',
                '{current_date}' => 'Data atual',
            ],
        ];

        return view('email_templates/edit', $data);
    }

    /**
     * Processa a atualização de um template.
     *
     * @param int $templateId
     * @return ResponseInterface
     */
    public function update(int $templateId)
    {
        $userId = $this->session->get('user_id');
        $template = $this->emailTemplateModel->find($templateId);

        if (!$template || $template['user_id'] !== $userId) {
            return redirect()->back()->with('error', 'Template não encontrado.');
        }

        // Validação dos dados
        $rules = [
            'subject' => 'required|string|max_length[255]',
            'html_content' => 'required|string',
            'is_enabled' => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'subject' => $this->request->getPost('subject'),
            'html_content' => $this->request->getPost('html_content'),
            'is_enabled' => $this->request->getPost('is_enabled') ? true : false,
        ];

        if ($this->emailTemplateModel->update($templateId, $data)) {
            return redirect()->to('/email-templates')->with('success', 'Template atualizado com sucesso!');
        }

        return redirect()->back()->with('error', 'Erro ao atualizar template.');
    }

    /**
     * Cria um novo template.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        $userId = $this->session->get('user_id');

        // Validação dos dados
        $rules = [
            'template_type' => 'required|in_list[charge_created,charge_paid,charge_overdue,charge_reminder]',
            'subject' => 'required|string|max_length[255]',
            'html_content' => 'required|string',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'user_id' => $userId,
            'template_type' => $this->request->getPost('template_type'),
            'subject' => $this->request->getPost('subject'),
            'html_content' => $this->request->getPost('html_content'),
            'is_enabled' => true,
        ];

        if ($this->emailTemplateModel->insert($data)) {
            return redirect()->to('/email-templates')->with('success', 'Template criado com sucesso!');
        }

        return redirect()->back()->with('error', 'Erro ao criar template.');
    }

    /**
     * Ativa ou desativa um template.
     *
     * @param int $templateId
     * @return ResponseInterface
     */
    public function toggle(int $templateId)
    {
        $userId = $this->session->get('user_id');
        $template = $this->emailTemplateModel->find($templateId);

        if (!$template || $template['user_id'] !== $userId) {
            return redirect()->back()->with('error', 'Template não encontrado.');
        }

        $newStatus = !$template['is_enabled'];

        if ($this->emailTemplateModel->toggleTemplate($templateId, $userId, $newStatus)) {
            $message = $newStatus ? 'Template ativado com sucesso!' : 'Template desativado com sucesso!';
            return redirect()->to('/email-templates')->with('success', $message);
        }

        return redirect()->back()->with('error', 'Erro ao atualizar template.');
    }

    /**
     * Envia um e-mail de teste.
     *
     * @param int $templateId
     * @return ResponseInterface
     */
    public function sendTest(int $templateId)
    {
        $userId = $this->session->get('user_id');
        $template = $this->emailTemplateModel->find($templateId);

        if (!$template || $template['user_id'] !== $userId) {
            return redirect()->back()->with('error', 'Template não encontrado.');
        }

        $testEmail = $this->request->getPost('test_email');

        if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'E-mail de teste inválido.');
        }

        try {
            // Dados de teste
            $testData = [
                '{customer_name}' => 'Cliente Teste',
                '{customer_email}' => $testEmail,
                '{charge_id}' => '12345',
                '{charge_amount}' => 'R$ 100,00',
                '{charge_due_date}' => date('d/m/Y'),
                '{charge_description}' => 'Descrição de Teste',
                '{pix_qr_code}' => '[QR Code do Pix]',
                '{boleto_barcode}' => '12345.67890 12345.678901 12345.678901 1 12345678901234',
                '{payment_link}' => base_url('/payment/12345'),
                '{company_name}' => 'Minha Empresa',
                '{current_date}' => date('d/m/Y H:i'),
            ];

            // Substituir variáveis
            $subject = str_replace(array_keys($testData), array_values($testData), $template['subject']);
            $htmlContent = str_replace(array_keys($testData), array_values($testData), $template['html_content']);

            // Enviar e-mail
            $email = \Config\Services::email();
            $email->setFrom(getenv('MAIL_FROM_ADDRESS'), getenv('MAIL_FROM_NAME'));
            $email->setTo($testEmail);
            $email->setSubject($subject);
            $email->setMessage($htmlContent);

            if ($email->send()) {
                return redirect()->back()->with('success', 'E-mail de teste enviado com sucesso!');
            } else {
                return redirect()->back()->with('error', 'Erro ao enviar e-mail de teste: ' . $email->printDebugger());
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao enviar e-mail: ' . $e->getMessage());
        }
    }
}

