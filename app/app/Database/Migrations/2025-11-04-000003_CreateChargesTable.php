<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChargesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'bank_type' => [
                'type' => 'ENUM',
                'constraint' => ['pagarme', 'inter', 'c6'],
            ],
            'charge_type' => [
                'type' => 'ENUM',
                'constraint' => ['boleto', 'pix', 'credit_card', 'debit_card', 'payment_link'],
            ],
            'origin' => [
                'type' => 'ENUM',
                'constraint' => ['bling', 'api', 'manual'],
                'default' => 'manual',
            ],
            'external_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'bank_charge_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'customer_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'customer_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'customer_document' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'due_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'paid', 'canceled', 'overdue', 'failed'],
                'default' => 'pending',
            ],
            'pix_qr_code' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'pix_qr_code_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'boleto_barcode' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'boleto_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'payment_link_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'bank_response' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', false, true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('charges');
    }

    public function down()
    {
        $this->forge->dropTable('charges');
    }
}

