<?php

use yii\db\Migration;

/**
 * Handles the creation of table `system`.
 */
class m180606_150800_create_system_table extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%system}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="系统表"';
        }

        $this->createTable($table, [
            'id' => $this->primaryKey()->comment('ID'),
            'parent_id' => $this->integer()->comment('父ID'),
            'code' => $this->string(30)->notNull()->unique()->comment('代码'),
            'tag' => $this->string(20)->notNull()->comment('标签'),
            'type' => $this->string(10)->notNull()->comment('类型'),
            'store_range' => $this->string()->notNull()->comment('范围'),
            'store_dir' => $this->string()->notNull()->comment('目录'),
            'value' => $this->text()->notNull()->comment('值'),
            'sort_order' => $this->smallInteger(3)->notNull()->defaultValue(1)->comment('排序'),
            "FOREIGN KEY ([[parent_id]]) REFERENCES {$table}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
                ], $tableOptions);
        $this->createIndex('parent_id', $table, 'parent_id');

        //插入数据
        $this->batchInsert($table, ['id', 'parent_id', 'code', 'tag', 'type', 'store_range', 'store_dir', 'value', 'sort_order'], [
            [1, NULL, 'system', '系统信息', 'group', '', '', '', 1],
            [2, NULL, 'smtp', '邮件设置', 'group', '', '', '', 2],
            [3, NULL, 'captcha', '验证码设置', 'group', '', '', '', 3],
            [4, NULL, 'sms', '短信设置', 'group', '', '', '', 4],
            [101, 1, 'system_name', '网站名称', 'text', '', '', '', 1],
            [102, 1, 'system_title', '网站标题', 'text', '', '', '', 2],
            [103, 1, 'system_keywords', '关键字', 'textarea', '3', '', '', 3],
            [104, 1, 'system_desc', '网站描述', 'textarea', '3', '', '', 4],
            [105, 1, 'system_icp', '备案信息', 'text', '', '', '', 5],
            [106, 1, 'system_statcode', '第三方统计', 'textarea', '3', '', '', 6],
            [201, 2, 'smtp_service', '自定义邮件', 'radio', '{"0":"否","1":"是"}', '', '0', 1],
            [202, 2, 'smtp_ssl', '加密连接(SSL)', 'radio', '{"0":"否","1":"是"}', '', '0', 2],
            [203, 2, 'smtp_host', 'SMTP服务器', 'text', '', '', '', 3],
            [204, 2, 'smtp_port', 'SMTP端口', 'text', '', '', '', 4],
            [205, 2, 'smtp_from', '发件人地址', 'text', '', '', '', 5],
            [206, 2, 'smtp_username', 'SMTP用户名', 'text', '', '', '', 6],
            [207, 2, 'smtp_password', 'SMTP密码', 'password', '', '', '', 7],
            [208, 2, 'smtp_charset', '邮件编码', 'radio', '{"1":"UTF-8","2":"GB2312"}', '', '1', 8],
            [301, 3, 'captcha_open', '启用验证码', 'checkbox', '{"1":"新用户注册","2":"用户登录","3":"找回密码"}', '', '', 1],
            [302, 3, 'captcha_loginfail', '登录失败显示', 'radio', '{"0":"否","1":"是"}', '', '0', 2],
            [303, 3, 'captcha_length', '验证码长度', 'text', '', '', '6', 3],
            [401, 4, 'sms_service', '短信平台', 'radio', '{"aliyun":"阿里云"', '', 'aliyun', 1],
            [402, 4, 'sms_key', 'Key', 'text', '', '', '', 2],
            [403, 4, 'sms_secret', 'Secret', 'password', '', '', '', 3],
            [404, 4, 'sms_sign', '短信签名', 'text', '', '', '', 4],
            [405, 4, 'sms_captcha', '验证码模板', 'text', '', '', '', 5],
           
        ]);
    }
   
    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%system}}');
    }

}
