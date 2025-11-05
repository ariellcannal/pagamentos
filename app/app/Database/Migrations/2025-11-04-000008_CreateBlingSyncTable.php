<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBlingSyncTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'charge_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'bling_nfe_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'bling_order_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'sync_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'sync_status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'pending',
            ],
            'sync_data' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'last_sync_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
                'on_update' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('charge_id');
        $this->forge->addKey('sync_status');
        $this->forge->createTable('bling_sync');
    }

    public function down()
    {
        $this->forge->dropTable('bling_sync');
    }
}

