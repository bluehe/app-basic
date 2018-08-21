<?php

use yii\db\Migration;

/**
 * Handles the creation of table `company_new`.
 */
class m180821_092700_create_parameter_table extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%parameter}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="参数表"';
        }
        $this->createTable($table, [
            'type' => $this->string(32)->notNull()->comment('类型'),
            'code' => $this->smallInteger()->notNull()->comment('代码'),
            'title' => $this->string(32)->comment('内容'),
            'description' => $this->string()->comment('描述'),
            'sort_p' => $this->integer()->notNull()->defaultValue(10)->comment('排序'),
            'PRIMARY KEY (type, code)',            
                ], $tableOptions);  
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%parameter}}');
    }

}
