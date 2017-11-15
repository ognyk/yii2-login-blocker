<?php

use yii\db\Migration;

/**
 * Class m171115_162009_create_table_login_blocker
 */
class m171115_162009_create_table_login_blocker extends Migration
{
    public function up()
    {
        $this->createTable('Login_blocker', [
            'id'                => $this->primaryKey(),
            'ip'                => $this->string(50),
            'created_datetime'  => $this->dateTime(),
//            'username'          => $this->string(127),
        ], 'Engine="InnoDb"');
    }
    
    public function down()
    {
        $this->dropTable('Login_blocker');
    }
}
