<?php

use mdm\admin\components\Configs;

/**
 * Migration table of table_menu
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class m180606_154100_create_menu_table extends \yii\db\Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $menuTable = Configs::instance()->menuTable;
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB COMMENT="菜单表"';
        }

        $this->createTable($menuTable, [
            'id' => $this->primaryKey(),
            'name' => $this->string(128)->notNull(),
            'parent' => $this->integer(),
            'route' => $this->string(),
            'order' => $this->integer(),
            'data' => $this->binary(),
            "FOREIGN KEY ([[parent]]) REFERENCES {$menuTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
                ], $tableOptions);

        //插入数据
        $this->batchInsert($menuTable, ['id', 'name', 'parent', 'route', 'order', 'data'], [
            [10, '系统设置', NULL, '/system/index', 1, '{"icon":"fa fa-cogs"}'],
            [11, '系统信息', 10, '/system/index', 1, '{"icon":"fa fa-cog"}'],
            [12, '邮件设置', 10, '/system/smtp', 2, '{"icon":"fa fa-envelope-o"}'],
            [13, '短信设置', 10, '/system/sms', 3, '{"icon":"fa fa-mobile"}'],
            [14, 'CDN设置', 10, '/system/cdn', 4, '{"icon":"fa fa-share-alt"}'],
            [15, '验证码设置', 10, '/system/captcha', 5, '{"icon":"fa fa-key"}'],
            [16, '协议设置', 10, '/system/agreement', 6, '{"icon":"fa fa-balance-scale"}'],
            [17, '计划任务', 10, '/system/crontab', 7, '{"icon":"fa fa-clock-o", "multi-action":[ "crontab", "crontab-create", "crontab-update"]}'],

            [20, '账号信息', NULL, '/account/index', 2, '{"icon":"fa fa-list-alt"}'],
            [21, '注册信息', 20, '/account/index', 1, '{"icon":"fa fa-pencil-square-o"}'],
            [22, '修改密码', 20, '/account/change-password', 2, '{"icon":"fa fa-unlock-alt"}'],
            [23, '头像设置', 20, '/account/thumb', 3, '{"icon":"fa fa-camera-retro"}'],
            
            [30, '业务中心', NULL, '/user/user-list', 3, '{"icon":"fa  fa-briefcase"}'],
            [31, '用户管理', 30, '/user/user-list', 1, '{"icon":"fa fa-user", "multi-action":["user-list", "user-update"]}'], 
            [32, '参数管理', 30, '/parameter/parameter-list', 2, '{"icon":"fa fa-code"}'], 
            [33, '套餐管理', 30, '/meal/meal-list', 3, '{"icon":"fa fa-gift", "multi-action":["meal-list", "meal-create", "meal-update"]}'], 
            [34, '行业管理', 30, '/industry/industry-list',4, '{"icon":"fa fa-flag-checkered"}'],
            [35, '企业管理', 30, '/corporation/corporation-list', 5, '{"icon":"fa fa-newspaper-o"}'],
            [36, '下拨管理', 30, '/allocate/allocate-list', 6, '{"icon":"fa fa-trophy"}'],
            [37, '补贴管理', 30, '/subsidy/subsidy-list', 7, '{"icon":"fa fa-tint"}'],


        ]);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable(Configs::instance()->menuTable);
    }

}
