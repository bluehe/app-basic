<?php

use yii\db\Migration;

/**
 * Handles the creation of table `crontab_list`.
 */
class m190530_150500_create_crontab_list extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%crontab_list}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="仓库执行表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->comment('定时任务名称'),
            'route' => $this->string(50)->comment('任务路由'),
            'crontab_str' => $this->string(50)->comment('crontab格式'),
            'switch' => $this->smallInteger()->notNull()->defaultValue(0)->comment('任务开关'),
            'status' => $this->smallInteger()->notNull()->defaultValue(0)->comment('任务运行状态'),
            'last_rundate' => $this->dateTime()->comment('上次运行时'),
            'next_rundate' => $this->dateTime()->comment('下次运行时间'),
            'execmemory' => $this->decimal(9,2)->notNull()->defaultValue(1)->comment('任务执行消耗内存(单位/byte)'),
            'exectime' => $this->decimal(9,2)->notNull()->defaultValue(1)->comment('任务执行消耗时间'),
                ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%crontab_list}}');
    }

}
