<?php

use yii\db\Migration;

/**
 * Handles the creation of table `meal`.
 */
class m180821_092800_create_meal_table extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%meal}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="套餐表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),          
            'name' => $this->string(32)->notNull()->comment('规格'),
            'region' => $this->string(32)->notNull()->comment('地区'),
            'devcloud_count'=>$this->integer()->notNull()->comment('软开云人数'),
            'devcloud_amount'=>$this->decimal(10,2)->notNull()->comment('软开云金额（元）'),
            'cloud_amount'=>$this->decimal(10,2)->notNull()->comment('公有云金额（元）'),
            'amount'=>$this->decimal(10,2)->notNull()->comment('总金额（元）'),            
            'content' => $this->text()->notNull()->comment('内容'),            
            'order_sort' =>  $this->smallInteger()->notNull()->defaultValue(10)->comment('排序'),
            'stat' => $this->smallInteger()->notNull()->defaultValue(1)->comment('状态'),
                ], $tableOptions); 
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%meal}}');
    }

}
