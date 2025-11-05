<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWebhookLogsTable extends Migration
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
                'constraint' => ['pagarme', 'inter', 'bling'],
            ],
            'endpoint' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'payload' => [
                'type' => 'LONGTEXT',
            ],
            'response' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'status_code' => [
                'type' => 'INT',
                'constraint' => 3,
                'null' => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', false, true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('webhook_logs');
    }

    public function down()
    {
        $this->forge->dropTable('webhook_logs');
    }
}

