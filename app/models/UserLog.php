<?php

namespace app\models;


//use common\models\User;

/**
 * This is the model class for table "{{%user_log}}".
 *
 * @property integer $id
 * @property integer $uid
 * @property string $ip
 * @property string $country
 * @property string $area
 * @property string $region
 * @property string $city
 * @property string $county
 * @property string $isp
 * @property string $country_id
 * @property string $area_id
 * @property string $region_id
 * @property string $city_id
 * @property string $county_id
 * @property string $isp_id
 * @property integer $created_at
 *
 * @property User $u
 */
class UserLog extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%user_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['uid', 'ip', 'created_at'], 'required'],
            [['uid', 'created_at'], 'integer'],
            [['ip', 'country', 'area', 'region', 'city', 'county', 'isp'], 'string', 'max' => 32],
            [['country_id', 'area_id', 'region_id', 'city_id', 'county_id', 'isp_id'], 'string', 'max' => 12],
            [['uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['uid' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'uid' => '用户',
            'ip' => 'IP',
            'country' => '国家',
            'area' => '地区',
            'region' => '省',
            'city' => '市',
            'county' => '县',
            'isp' => '运营商',
            'country_id' => '国家代码',
            'area_id' => '地区代码',
            'region_id' => '省代码',
            'city_id' => '市代码',
            'county_id' => '县代码',
            'isp_id' => '运营商代码',
            'created_at' => '创建时间',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getU() {
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }

    public static function get_day_total($a = 'created_at', $start = '', $end = '') {

        $query = static::find()->andFilterWhere(['>=', $a, $start])->andFilterWhere(['<=', $a, $end]);
        return $query->select(['count(distinct uid)', "FROM_UNIXTIME($a, '%Y-%m-%d')"])->groupBy(["FROM_UNIXTIME($a, '%Y-%m-%d')"])->indexBy("FROM_UNIXTIME($a, '%Y-%m-%d')")->column();
    }

}
