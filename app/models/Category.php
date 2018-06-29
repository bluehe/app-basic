<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%category}}".
 *
 * @property integer $id
 * @property integer $uid
 * @property integer $cid
 * @property string $title
 * @property integer $sort_order
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $stat
 *
 * @property User $u
 * @property Category $c
 * @property Category[] $categories
 * @property Website[] $websites
 */
class Category extends \yii\db\ActiveRecord {

    const STAT_OPEN = 1;
    const STAT_CLOSE = 2;
    const ISOPEN_OPEN = 1;
    const ISOPEN_CLOSE = 2;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%category}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['sort_order', 'collect_num', 'created_at', 'updated_at', 'stat'], 'integer'],
            [['title', 'is_open'], 'required'],
            [['title'], 'string', 'max' => 8],
            ['title', 'unique', 'targetAttribute' => ['title', 'uid'],'message'=>'{attribute}已经存在'],
            [['uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['uid' => 'id']],
            [['cid'], 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['cid' => 'id']],
            [['collect_num'], 'default', 'value' => 0],
            [['is_open'], 'default', 'value' => self::ISOPEN_OPEN],
            [['stat'], 'default', 'value' => self::STAT_OPEN],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'uid' => '用户',
            'cid' => '上级分类',
            'title' => '名称',
            'collect_num' => '收藏次数',
            'sort_order' => '排序',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'is_open' => '是否公开',
            'stat' => '状态',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getU() {
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getC() {
        return $this->hasOne(Category::className(), ['id' => 'cid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories() {
        return $this->hasMany(Category::className(), ['cid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWebsites() {
        return $this->hasMany(Website::className(), ['cid' => 'id']);
    }

    public static $List = [
        'stat' => [
            self::STAT_OPEN => "启用",
            self::STAT_CLOSE => "关闭"
        ],
        'is_open' => [
            self::ISOPEN_OPEN => "开放",
            self::ISOPEN_CLOSE => "关闭"
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

    public static function findTitle($id) {
        return static::find()->where(['id' => $id])->select('title')->scalar();
    }

    public static function findMaxSort($uid, $stat = '') {
        $query = static::find()->where(['uid' => $uid]);
        if ($stat) {
            $query->andWhere(['stat' => $stat]);
        }
        return $query->max('sort_order');
    }

    public static function get_category_sql($uid = NULL, $cid = NULL, $stat = self::STAT_OPEN, $is_open = '') {
        $query = static::find()->where(['uid' => $uid, 'cid' => $cid,])->andFilterWhere(['stat' => $stat, 'is_open' => $is_open])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
        return $query;
    }

    public static function get_category($limit = '', $uid = NULL, $cid = NULL, $stat = self::STAT_OPEN, $is_open = '') {
        $query = static::find()->where(['uid' => $uid, 'cid' => $cid])->andFilterWhere(['stat' => $stat, 'is_open' => $is_open])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
        if ($limit) {
            $query->limit($limit);
        }
        $category = $query->indexBy('id')->asArray()->all();
        return $category;
    }

    public static function get_user_category($uid = NULL, $is = false,$stat=self::STAT_OPEN) {
        $query = static::find()->where(['uid' => $uid])->andFilterWhere(['stat' => $stat]);
        if ($is) {
            //达到或超过10个网址的不列入
            $cids = Website::find()->joinWith('c')->select([Website::tableName() . '.cid'])->where(['uid' => $uid, Website::tableName() . '.stat' => Website::STAT_OPEN])->groupBy([Website::tableName() . '.cid'])->having(['>=', 'count(*)', 10])->column();
            $query->andWhere(['not', ['id' => $cids]]);
        }
        $category = $query->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
        return ArrayHelper::map($category, 'id', 'title');
    }

    public static function get_category_num($uid = NULL, $is = '', $stat = '') {
        $query = static::find();
        if ($is == 'not') {
            $query->where(['not', ['uid' => $uid]]);
        } else {
            $query->where(['uid' => $uid]);
        }
        if ($stat) {
            $query->andWhere(['stat' => $stat]);
        }
        $num = $query->count();
        return $num;
    }

    //统计-分类每日增加数
    public static function get_day_total($a = 'created_at', $start = '', $end = '', $uid = NULL, $is = '') {
         $query = static::find()->where(['stat' => self::STAT_OPEN])->andFilterWhere(['>=', $a, $start])->andFilterWhere(['<=', $a, $end]);
        if ($is == 'not') {
            $query->andWhere(['not', ['uid' => $uid]]);
        } else {
            $query->andWhere(['uid' => $uid]);
        }
       
        return $query->groupBy(["FROM_UNIXTIME($a, '%Y-%m-%d')"])->select(['count(*)', "FROM_UNIXTIME($a,'%Y-%m-%d')"])->indexBy("FROM_UNIXTIME($a,'%Y-%m-%d')")->column();
    }

}
