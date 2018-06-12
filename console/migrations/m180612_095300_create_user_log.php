<?php

use yii\db\Migration;

class m180612_095300_create_user_log extends Migration {

    public function up() {
        $table = '{{%user_log}}';
        $userTable = '{{%user}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="登录记录表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'uid' => $this->integer()->notNull(),
            'ip' => $this->string(32)->notNull(),
            'country' => $this->string(32),
            'area' => $this->string(32),
            'region' => $this->string(32),
            'city' => $this->string(32),
            'county' => $this->string(32),
            'isp' => $this->string(32),
            'country_id' => $this->string(12),
            'area_id' => $this->string(12),
            'region_id' => $this->string(12),
            'city_id' => $this->string(12),
            'county_id' => $this->string(12),
            'isp_id' => $this->string(12),
            'created_at' => $this->integer()->notNull(),
            "FOREIGN KEY ([[uid]]) REFERENCES {$userTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
                ], $tableOptions);
    }

    public function down() {
        $this->dropTable('{{%user_log}}');
    }

}
