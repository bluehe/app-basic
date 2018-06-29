<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Html;
use yii\helpers\Url;
//use app\components\Aliyunoss;

/**
 * This is the model class for table "{{%website}}".
 *
 * @property integer $id
 * @property integer $cid
 * @property string $title
 * @property string $url
 * @property integer $sort_order
 * @property integer $click_num
 * @property integer $is_open
 * @property integer $share_status
 * @property integer $share_cid
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $stat
 * *
 * @property Category $c
 * @property User $u
 *  @property Category $shareC
 */
class Website extends \yii\db\ActiveRecord {

    const STAT_OPEN = 1;
    const STAT_CLOSE = 2;
    const STAT_BLACK = -1;
    const STAT_DELETE = -2;
    const ISOPEN_OPEN = 1;
    const ISOPEN_CLOSE = 2;
    const SHARE_DEFAULT = 0;
    const SHARE_WAIT = 1;
    const SHARE_ACTIVE = 2;
    const SHARE_COLLECT = -1;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%website}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['title','url'],'trim'],
            [['cid', 'title', 'url', 'sort_order'], 'required'],
            [['cid', 'sort_order', 'click_num', 'collect_num', 'is_open', 'created_at', 'updated_at', 'stat'], 'integer'],
            [['title', 'url', 'host', 'note'], 'string', 'max' => 255],
            ['url', 'url', 'defaultScheme' => 'http'],
            [['cid'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['cid' => 'id']],
            [['share_id'], 'exist', 'skipOnError' => true, 'targetClass' => Website::className(), 'targetAttribute' => ['share_id' => 'id']],
            [['share_status'], 'default', 'value' => self::SHARE_DEFAULT],
            [['collect_num', 'click_num'], 'default', 'value' => 0],
            [['is_open'], 'default', 'value' => self::ISOPEN_OPEN],
            [['stat'], 'default', 'value' => self::STAT_OPEN],
            [['note'], 'default', 'value' => ''],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            TimestampBehavior::className(),
        ];
    }

    public function beforeSave($insert) {
        // 注意，重载之后要调用父类同名函数
        if (parent::beforeSave($insert)) {
            $host = strtolower(parse_url($this->url, PHP_URL_HOST));
            $this->host = preg_replace("/^(www\.)?/is", "", $host);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'cid' => '分类',
            'title' => '名称',
            'url' => '网址',
            'host' => '域名',
            'note' => '备注',
            'sort_order' => '排序',
            'click_num' => '点击数',
            'share_status' => '关联状态',
            'share_id' => '关联ID',
            'collect_num' => '收藏次数',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'is_open' => '是否公开',
            'stat' => '状态',
        ];
    }

    public static $List = [
        'stat' => [
            self::STAT_OPEN => "启用",
            self::STAT_CLOSE => "关闭",
            self::STAT_BLACK => "拉黑",
        ],
        'is_open' => [
            self::ISOPEN_OPEN => "公开",
            self::ISOPEN_CLOSE => "私有"
        ],
        'share_status' => [
            self::SHARE_DEFAULT => "未分享",
            self::SHARE_WAIT => "待审核",
            self::SHARE_ACTIVE => "分享中",
            self::SHARE_COLLECT => "收藏"
        ]
    ];

    public function getStat() {

        $stat = isset(self::$List['stat'][$this->stat]) ? self::$List['stat'][$this->stat] : null;
        return $stat;
    }

    public function getIsOpen() {

        $is_open = isset(self::$List['is_open'][$this->is_open]) ? self::$List['is_open'][$this->is_open] : null;
        return $is_open;
    }

    public function getShareStatus() {

        $result = isset(self::$List['share_status'][$this->share_status]) ? self::$List['share_status'][$this->share_status] : null;
        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getC() {
        return $this->hasOne(Category::className(), ['id' => 'cid']);
    }

    public function getShareC() {
        return $this->hasOne(Website::className(), ['id' => 'share_id']);
    }

    public function getU() {
        return $this->hasOne(User::className(), ['id' => 'uid'])->via('c');
    }

    public static function findMaxSort($cid, $stat = '') {
        $query = static::find()->where(['cid' => $cid]);
        if ($stat) {
            $query->andWhere(['stat' => $stat]);
        }
        return $query->max('sort_order');
    }

    public static function get_website($limit = '', $cid = NULL, $stat = self::STAT_OPEN, $is_open = '') {
        $query = static::find()->andFilterWhere(['cid' => $cid, 'stat' => $stat, 'is_open' => $is_open])->orderBy(['sort_order' => SORT_ASC]);
        if ($limit) {
            $query->limit($limit);
        }
        $websites = $query->indexBy('id')->asArray()->all();
        return $websites;
    }

    public static function get_website_order($limit = '', $uid = NULL, $is = '', $stat = self::STAT_OPEN, $is_open = '') {
        $query = static::find()->joinWith('c')->andFilterWhere([self::tableName() . '.stat' => $stat, self::tableName() . '.is_open' => $is_open])->orderBy(['click_num' => SORT_DESC]);
        if ($limit) {
            $query->limit($limit);
        }
        if ($is == 'not') {
            $query->andWhere(['not', ['uid' => $uid]]);
        } else {
            $query->andWhere(['uid' => $uid]);
        }
        $websites = $query->asArray()->all();
        return $websites;
    }

    public static function get_website_num($uid = NULL, $is = '', $stat = '', $is_open = '') {
        $query = static::find()->joinWith('c')->andFilterWhere([self::tableName() . '.stat' => $stat, self::tableName() . '.is_open' => $is_open]);
        if ($is == 'not') {
            $query->andWhere(['not', ['uid' => $uid]]);
        } else {
            $query->andWhere(['uid' => $uid]);
        }

        $num = $query->count();
        return $num;
    }

//    分类网址数
    public static function get_category_website_num($cid, $stat = '') {
        $num = static::find()->andFilterWhere(['cid' => $cid, 'stat' => $stat])->count();
        return $num;
    }

//    public static function get_tab_addlist($num = '') {
//        $query = static::find()->joinWith('c')->where(['not', ['uid' => NULL]])->andWhere([self::tableName() . '.stat' => self::STAT_OPEN, self::tableName() . '.is_open' => self::ISOPEN_OPEN])->andWhere(['not', ['share_status' => self::SHARE_COLLECT]])->orderBy([self::tableName() . '.created_at' => SORT_DESC]);
//        if ($num) {
//            $query->limit($num);
//        }
//        $data = [];
//        foreach ($query->each() as $website) {
//            $data[] = ['id' => $website->id, 'url' => $website->url, 'img' => Html::img(Aliyunoss::getfav($website->host)?Aliyunoss::getfav($website->host):Url::to(Yii::$app->params['img_url'] . '/api/getfav?url=' . $website->host)), 'title' => $website->title, 'label' => Yii::$app->formatter->asRelativeTime($website->created_at)];
//        }
//        return $data;
//    }
//
//    public static function get_tab_addorder($num = '') {
//        $query = static::find()->select(['id', 'url', 'host', 'title', 'num' => 'SUM(collect_num)'])->andWhere(['is_open' => self::ISOPEN_OPEN])->groupBy(['host'])->orderBy(['num' => SORT_DESC]);
//        if ($num) {
//            $query->limit($num);
//        }
//        $websites = $query->asArray()->all();
//        $data = [];
//        foreach ($websites as $website) {
//            $data[] = ['id' => $website['id'], 'url' => $website['url'], 'img' => Html::img(Aliyunoss::getfav($website['host'])?Aliyunoss::getfav($website['host']):Url::to(Yii::$app->params['img_url'] . '/api/getfav?url=' . $website['host'])), 'title' => $website['title'], 'label' => $website['num']];
//        }
//        return $data;
//    }
//
//    public static function get_tab_clickorder($num = '') {
//        $query = static::find()->select(['id', 'url', 'host', 'title', 'num' => 'SUM(click_num)'])->andWhere(['is_open' => self::ISOPEN_OPEN])->groupBy(['host'])->orderBy(['num' => SORT_DESC]);
//        if ($num) {
//            $query->limit($num);
//        }
//        $websites = $query->asArray()->all();
//        $data = [];
//        foreach ($websites as $website) {
//            $data[] = ['id' => $website['id'], 'url' => $website['url'], 'img' => Html::img(Aliyunoss::getfav($website['host'])?Aliyunoss::getfav($website['host']):Url::to(Yii::$app->params['img_url'] . '/api/getfav?url=' . $website['host'])), 'title' => $website['title'], 'label' => $website['num']];
//        }
//        return $data;
//    }

    //统计-网址每日增加数
    public static function get_day_total($a = 'created_at', $start = '', $end = '', $uid = NULL, $is = '') {
        $query = static::find()->joinWith('c')->where([self::tableName() . '.stat' => self::STAT_OPEN])->andFilterWhere(['>=', self::tableName() . '.' . $a, $start])->andFilterWhere(['<=', self::tableName() . '.' . $a, $end]);
        if ($is == 'not') {
            $query->andWhere(['not', ['uid' => $uid]]);
        } else {
            $query->andWhere(['uid' => $uid]);
        }

        return $query->groupBy(["FROM_UNIXTIME(" . self::tableName() . "." . $a . ", '%Y-%m-%d')"])->select(['count(*)', 'wday' => "FROM_UNIXTIME(" . self::tableName() . "." . $a . ",'%Y-%m-%d')"])->indexBy('wday')->column();
    }

}
