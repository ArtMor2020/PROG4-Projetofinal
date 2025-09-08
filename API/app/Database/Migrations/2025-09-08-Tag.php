<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class User extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'id_owner'    => ['type' => 'INT', 'unsigned' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255],
            'color'       => ['type' => 'VARCHAR', 'constraint' => 255]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tag');
    }

    public function down(){
                $this->forge->dropTable('tag');
    }
}