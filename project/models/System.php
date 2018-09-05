<?php

namespace project\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "system".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $code
 * @property string $type
 * @property string $store_range
 * @property string $hint
 * @property string $value
 * @property integer $sort_order
 */
class System extends ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%system}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['parent_id', 'sort_order'], 'integer'],
            [['value'], 'string'],
            [['code'], 'string', 'max' => 30],
            [['code'], 'unique'],
            [['tag'], 'string', 'max' => 20],
            [['type'], 'string', 'max' => 10],
            [['store_range', 'hint'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'parent_id' => '父ID',
            'code' => '代码',
            'tag' => '标签',
            'type' => '类型',
            'store_range' => '范围',
            'hint' => '提示',
            'value' => '值',
            'sort_order' => '顺序',
        ];
    }
    
    //得到具体值
    public static function getValue($code = '') {
        $config = static::findOne(['code' => $code]);
        return $config['value'];
    }
    
    //查找值是否在code中
    public static function existValue($code = '', $value = '') {
        return static::find()->where(['code' => $code])->andWhere('FIND_IN_SET(:value, value)')->addParams([':value' => $value])->one();
    }
    
    //得到code下集合值-返回键值方式数组
    public static function getChildrenValue($code = '') {
        $parent = static::find()->where(['code' => $code])->select('id')->one();
        $infos = static::find()->where(['parent_id' => $parent['id']])->orderBy('sort_order ASC')->all();
        $value = array();
        foreach ($infos as $info) {
            $value[$info['code']] = $info['value'];
        }
        return $value;
    }
    
    

    //得到code下集合值，返回数据集合
    public static function getChildren($code = '') {
        $parent = static::find()->where(['code' => $code])->select('id')->one();
        return static::find()->where(['parent_id' => $parent['id']])->orderBy('sort_order ASC')->all();
    }

    //设定具体值
    public static function setValue($code = '', $value = '') {
        $config = static::findOne(['code' => $code]);
        if ($value != $config['value']) {
            $config['value'] = $value;
            return $config->save();
        } else {
            return 0;
        }
    }
    
    public static function setSystem($system) {
        $res = 0;
        foreach ($system as $key => $value) {
            $r = self::setValue($key, $value);
            if ($r) {
                $res++;
            } elseif ($r === false) {
                $res = false;
                break;
            }
        }
        return $res;
    }

   

}
