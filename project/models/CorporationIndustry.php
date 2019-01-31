<?php

namespace project\models;

use Yii;

/**
 * This is the model class for table "{{%corporation_industry}}".
 *
 * @property int $corporation_id
 * @property int $industry_id
 *
 * @property Corporation $corporation
 * @property Industry $industry
 */
class CorporationIndustry extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%corporation_industry}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['corporation_id', 'industry_id'], 'required'],
            [['corporation_id', 'industry_id'], 'integer'],
            [['corporation_id', 'industry_id'], 'unique', 'targetAttribute' => ['corporation_id', 'industry_id']],
            [['corporation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Corporation::className(), 'targetAttribute' => ['corporation_id' => 'id']],
            [['industry_id'], 'exist', 'skipOnError' => true, 'targetClass' => Industry::className(), 'targetAttribute' => ['industry_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'corporation_id' => '公司ID',
            'industry_id' => '行业ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorporation()
    {
        return $this->hasOne(Corporation::className(), ['id' => 'corporation_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIndustry()
    {
        return $this->hasOne(Industry::className(), ['id' => 'industry_id']);
    }
    
    public static function get_corporation_industryid($id) {
        return self::find()->where(['corporation_id' => $id])->select(['industry_id'])->column();
    }
    
    public static function get_industry_total($annual=null,$group_id=null) {
        $query =  static::find();
        if($annual){
            $corporation= CorporationMeal::get_corporation_by_annual($annual);
            $query->andFilterWhere(['corporation_id'=>$corporation]);
        }
        if(!$group_id){
            $group_id=UserGroup::get_user_groupid(Yii::$app->user->identity->id);
        }
        $corporation= CorporationMeal::get_corporation_by_group($group_id);
        $query->andWhere(['corporation_id'=>$corporation]);
        $data=$query->select(['num'=>'count(corporation_id)','industry_id'])->orderBy(['num'=>SORT_DESC])->groupBy(['industry_id'])->indexBy('industry_id')->column();
         return $data;
     }
}
