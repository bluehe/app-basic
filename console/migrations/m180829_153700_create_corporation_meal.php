<?php

use yii\db\Migration;

/**
 * Handles the creation of table `corporation_bd`.
 */
class m180829_153700_create_corporation_meal extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%corporation_meal}}';
        $corporationTable = '{{%corporation}}';
        $mealTable = '{{%meal}}';
        $userTable = '{{%user}}';
        $groupTable = '{{%group}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="企业下拨表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'corporation_id' => $this->integer()->notNull(),
            'group_id' => $this->integer()->comment('项目'),
            'meal_id'=>$this->integer(),
            'start_time' => $this->integer()->notNull(),
            'end_time' => $this->integer()->notNull(),
            'number'=>$this->integer()->notNull()->defaultValue(1),
            'devcloud_count'=>$this->integer(),
            'devcloud_amount'=>$this->decimal(10,2),
            'cloud_amount'=>$this->decimal(10,2),
            'amount'=>$this->decimal(10,2),
            'annual'=>$this->string()->comment('下拨年度'),
            'huawei_account' => $this->string(32)->comment('华为云账号'),
            'bd' => $this->integer(),
            'user_id' => $this->integer(),
            'created_at' => $this->integer(),
            'stat' => $this->smallInteger()->notNull()->defaultValue(1)->comment('类型'),
            "FOREIGN KEY ([[corporation_id]]) REFERENCES {$corporationTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
            "FOREIGN KEY ([[group_id]]) REFERENCES {$groupTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
            "FOREIGN KEY ([[meal_id]]) REFERENCES {$mealTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
            "FOREIGN KEY ([[user_id]]) REFERENCES {$userTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
            "FOREIGN KEY ([[bd]]) REFERENCES {$userTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
                ], $tableOptions);

       
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%corporation_meal}}');
    }

}
