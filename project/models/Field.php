<?php

namespace project\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%field}}".
 *
 * @property int $id
 * @property int $parent
 * @property string $code
 * @property string $name
 * @property int $type
 *
 * @property Field $parent0
 * @property Field[] $fields
 */
class Field extends \yii\db\ActiveRecord
{
    
    const TYPE_ALL = 1;
    const TYPE_ADD = 2;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%field}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent', 'type'], 'integer'],
            [['name'], 'required'],
            [['code', 'name'], 'string', 'max' => 32],
            [['name'], 'unique'],
            [['parent'], 'exist', 'skipOnError' => true, 'targetClass' => Field::className(), 'targetAttribute' => ['parent' => 'id']],
            [['type'],'default','value'=> self::TYPE_ALL],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent' => '上级',
            'code' => '代码',
            'name' => '名称',
            'type' => '类型',
        ];
    }
    
    public static $List = [
        'type' => [
            self::TYPE_ALL => "全量",
            self::TYPE_ADD => "增量"
        ],
    ];

    public function getType() {
        $type = isset(self::$List['type'][$this->type]) ? self::$List['type'][$this->type] : null;
        return $type;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent0()
    {
        return $this->hasOne(Field::className(), ['id' => 'parent']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFields()
    {
        return $this->hasMany(Field::className(), ['parent' => 'id']);
    }
    
    //得到一级项目ID-name 键值数组
    public static function get_parents_id($id=0) {
        $items = self::find()->where(['parent'=>NULL])->andFilterWhere(['not',['id'=>$id]])->orderBy('id')->all();
        return ArrayHelper::map($items, 'id', 'name');
    }
    
//    //无代码项目，返回数组
//    public static function get_item_notcode($ids=array()) {
//        $items=self::find()->where(['or',['code'=>NULL],['code'=>'']])->andFilterWhere(['id'=>$ids])->all();
//        return ArrayHelper::map($items, 'name', 'id');
//    }
//    
//    public static function get_code_by_id($ids=array()) {
//        $items=self::find()->filterWhere(['id'=>$ids])->all();
//        return ArrayHelper::map($items, 'id', 'code');
//    }
}
