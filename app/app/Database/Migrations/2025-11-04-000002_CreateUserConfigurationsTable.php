<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserConfigurationsTable extends Migration
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
                'null' => true,
            ],
            'pagarme_api_key' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'inter_client_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'inter_client_secret' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'inter_certificate_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'inter_certificate_password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'c6_api_key' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'bling_api_key' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'bling_webhook_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'boleto_instructions' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'email_webhook_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->createTable('user_configurations');
    }

    public function down()
    {
        $this->forge->dropTable('user_configurations');
    }
}

