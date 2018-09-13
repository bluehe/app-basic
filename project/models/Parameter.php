<?php

namespace project\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%parameter}}".
 *
 * @property string $type 类型
 * @property string $code 代码
 * @property string $title 内容
 * @property string $description 描述
 * @property int $sort_p 排序
 */
class Parameter extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%parameter}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type','code'], 'required'],
            [['sort_p', 'code'], 'integer'],
            [['type', 'title'], 'string', 'max' => 32],
            [['description'], 'string', 'max' => 255],
            [['code'], 'unique', 'targetAttribute' => ['type', 'code'],'message' => '{attribute}已经存在'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'type' => '类型',
            'code' => '代码',
            'title' => '内容',
            'description' => '描述',
            'sort_p' => '排序',
        ];
    }
    
    
    public static function get_type($type='') {
        $p = static::find()->andFilterWhere(['type'=>$type])->orderBy(['type'=>SORT_ASC,'sort_p'=>SORT_ASC])->all();
        return ArrayHelper::map($p, 'code', 'title');
    }
    
        
    public static function get_para_value($type,$code) {
        return static::find()->where(['type'=>$type,'code'=>$code])->orderBy(['sort_p'=>SORT_ASC])->select(['title'])->column();
    }
    
    public static function add_type($type,$title,$code=null) {
        
        $p= static::findOne(['type'=>$type,'title'=>$title]);
        if($p==null){
            if(!$code){
                $max= static::find()->where(['type'=>$type])->orderBy(['code'=>SORT_DESC])->select('code')->scalar();
                $code=$max+1;
            }

            $model=new Parameter();
            $model->type=$type;
            $model->title=$title;
            $model->code=$code;
            $model->sort_p=10;
            if($model->save()){
                return $code;
            }else{
                return false;
            }
        
        }else{
            return $p->code;
        }
        
    }
}
