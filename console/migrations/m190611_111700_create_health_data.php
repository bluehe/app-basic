<?php

use yii\db\Migration;

/**
 * Handles the creation of table `health_data`.
 */
class m190611_111700_create_health_data extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%health_data}}';
        $logTable = '{{%health_log}}';   
        $corporationTable = '{{%corporation}}';
        $groupTable = '{{%group}}';
        $userTable = '{{%user}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="健康数据表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'log_id' => $this->integer(), 
            'group_id' => $this->integer()->comment('项目'),
            'corporation_id' => $this->integer()->notNull(),
            'bd_id' => $this->integer(),
            'statistics_time' => $this->integer()->notNull(),
            'activity_day' => $this->integer()->notNull()->defaultValue(0),   
            'activity_week' => $this->integer()->notNull()->defaultValue(0),            
            'activity_month' => $this->integer()->notNull()->defaultValue(0),
            'level' => $this->integer()->notNull()->defaultValue(0),
            'H' => $this->float()->notNull()->defaultValue(0),           
            'C' => $this->float()->notNull()->defaultValue(0),
            'I' => $this->float()->notNull()->defaultValue(0),
            'A' => $this->float()->notNull()->defaultValue(0),
            'R' => $this->float()->notNull()->defaultValue(0),
            'V' => $this->float()->notNull()->defaultValue(0),
            'D' => $this->float()->notNull()->defaultValue(0),
            'act_trend' => $this->integer()->notNull()->defaultValue(0),
            'health_trend' => $this->integer()->notNull()->defaultValue(0),
            'is_allocate' => $this->integer()->notNull()->defaultValue(0)->comment('是否下拨'),
            "FOREIGN KEY ([[log_id]]) REFERENCES {$logTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE", 
            "FOREIGN KEY ([[corporation_id]]) REFERENCES {$corporationTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
            "FOREIGN KEY ([[bd_id]]) REFERENCES {$userTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
            "FOREIGN KEY ([[group_id]]) REFERENCES {$groupTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
                ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%health_data}}');
    }

}
