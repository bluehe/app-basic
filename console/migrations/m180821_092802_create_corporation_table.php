<?php

use yii\db\Migration;
use mdm\admin\components\Configs;

/**
 * Handles the creation of table `company_new`.
 */
class m180821_092802_create_corporation_table extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%corporation}}';
        $userTable = Configs::instance()->userTable;
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="公司表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey()->comment('ID'),
            'base_company_name' => $this->string(32)->comment('公司名称'),
//            'base_bd' => $this->integer()->comment('客户经理'),
            //'base_industry' => $this->string()->comment('行业'),
            'base_company_scale' => $this->integer()->comment('企业规模'),
            'base_registered_capital'=>$this->decimal(10,4)->comment('注册资金'),
            'base_registered_time'=>$this->integer()->comment('注册日期'),
            'base_main_business'=>$this->text()->comment('主营业务'),
            'base_last_income'=>$this->decimal(10,4)->comment('近一年营业收入'),
            'stat' => $this->smallInteger()->notNull()->defaultValue(1)->comment('状态'),
            'intent_set'=>$this->integer()->comment('意向套餐'),
            //'intent_amount'=>$this->decimal(10,4)->comment('意向金额'),
            'huawei_account' => $this->string(32)->comment('华为云账号'),
//            'allocate_set'=>$this->integer()->comment('下拨套餐'),
//            'allocate_amount'=>$this->decimal(10,4)->comment('下拨金额'),
//            'allocate_time'=>$this->integer()->comment('下拨日期'),
            'note' => $this->text()->comment('备注'),
            
            'contact_park'=>$this->integer()->comment('所属园区'),
            'contact_address'=>$this->string(128)->comment('实际地址'),
            'contact_location'=>$this->string(64)->comment('坐标'),
            'contact_business_name'=>$this->string(16)->comment('商业联系人'),
            'contact_business_job'=>$this->string(16)->comment('商业联系人职务'),
            'contact_business_tel'=>$this->string(32)->comment('商业联系人电话'),
            'contact_technology_name'=>$this->string(16)->comment('技术联系人'),
            'contact_technology_job'=>$this->string(16)->comment('技术联系人职务'),
            'contact_technology_tel'=>$this->string(32)->comment('技术联系人电话'),
            
            'develop_scale' => $this->integer()->comment('研发规模'),
            'develop_pattern' => $this->string()->comment('开发模式'),
            'develop_scenario' => $this->string()->comment('开发场景'),
            'develop_science' => $this->string()->comment('开发环境'),
            'develop_language' => $this->string()->comment('开发语言'),
            'develop_IDE' => $this->string()->comment('开发IDE'),
            'develop_current_situation' => $this->text()->comment('研发工具现状'),
            'develop_weakness' => $this->text()->comment('研发痛点'),

            'created_at' => $this->integer()->comment('创建时间'),
            'updated_at' => $this->integer()->comment('更新时间'),
            
//            "FOREIGN KEY ([[base_bd]]) REFERENCES {$userTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
                ], $tableOptions);  
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%corporation}}');
    }

}
