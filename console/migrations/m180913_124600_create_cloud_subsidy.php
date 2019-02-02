<?php

use yii\db\Migration;

class m180913_124600_create_cloud_subsidy extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%cloud_subsidy}}';
        $corporationTable = '{{%corporation}}';
        $userTable = '{{%user}}';
        $groupTable = '{{%group}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="公有云补贴表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'group_id' => $this->integer()->comment('项目'),
            'corporation_id' => $this->integer()->comment('补贴企业'),
            'corporation_name' => $this->string()->comment('企业名称'),
            'subsidy_bd' => $this->integer()->comment('客户经理'),
            'annual'=>$this->string()->comment('补贴年度'),
            'subsidy_time' => $this->integer()->comment('补贴时间'),
            'subsidy_amount' => $this->decimal(10,2)->comment('补贴金额'),
            'subsidy_note'=>$this->text()->comment('备注'),
            "FOREIGN KEY ([[group_id]]) REFERENCES {$groupTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
            "FOREIGN KEY ([[corporation_id]]) REFERENCES {$corporationTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
            "FOREIGN KEY ([[subsidy_bd]]) REFERENCES {$userTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
                ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%cloud_subsidy}}');
    }

}
