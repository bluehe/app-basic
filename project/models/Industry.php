<?php

namespace project\models;

use Yii;
use yii\helpers\ArrayHelper;
use common\helpers\FamilyTree;

/**
 * This is the model class for table "{{%industry}}".
 *
 * @property int $id
 * @property int $parent_id
 * @property string $name
 * @property int $industry_sort
 *
 * @property CompanyIndustry[] $companyIndustries
 * @property Company[] $companies
 * @property Industry $parent
 * @property Industry[] $industries
 */
class Industry extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%industry}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'industry_sort'], 'integer'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 32],
            [['name'],'unique','targetAttribute' => ['parent_id', 'name'],'message' => '{attribute}已经存在'],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Industry::className(), 'targetAttribute' => ['parent_id' => 'id']],
            ['industry_sort', 'default', 'value' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => '上级',
            'name' => '名称',
            'industry_sort' => '排序',
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Industry::className(), ['id' => 'parent_id']);
    }
    
    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    protected static function _getIndustries()
    {
        return self::find()->orderBy(['parent_id'=>SORT_ASC,'industry_sort'=>SORT_ASC,'id'=>SORT_ASC])->asArray()->all();
    }

    /**
     * @return array
     */
    public static function getIndustries()
    {
        $industries = self::_getIndustries();
        $familyTree = new FamilyTree($industries);
        $array = $familyTree->getDescendants(0);
        return ArrayHelper::index($array, 'id');
    }
    
    /**
     * @return array
     */
    public static function getIndustriesName()
    {
        $industries = array_values(self::getIndustries());
        $data = [];
        foreach ($industries as $k => $industry){
            if($industry['level']==1){
                $name = $industry['name'];
            }elseif( isset($industries[$k+1]['level']) && $industries[$k+1]['level'] == $industry['level'] ){
                $name = ' ├'.$industry['name'];
            }else{
                $name = ' └'.$industry['name'];
            }
       
            $data[$industry['id']] = $name;
        }
        return $data;
    }
    
        /**
     * @return array
     */
    public static function getIndustryName($id)
    {
        $industries = static::find()->where(['id'=>$id])->asArray()->all();
        $data = [];
        if($industries!=null){
            $parent_ids= static::find()->where(['id'=>$id])->select(['parent_id'])->distinct()->column();
            $parent_name= static::find()->where(['id'=>$parent_ids])->select(['name','id'])->indexBy('id')->column();
            foreach ($industries as $k => $industry){
                if($industry['parent_id']){
                    $name = $parent_name[$industry['parent_id']].' - '.$industry['name'];          
                }else{
                    $name =$industry['name'];
                }

                $data[$industry['id']] = $name;
            }
        }
        
        
        return $data;
    }
    
    //得到一级项目ID-name 键值数组
    public static function get_parents_id($id=0) {
        $parent= static::find()->where(['not',['parent_id'=>NULL]])->select(['parent_id'])->distinct()->column();
        if(in_array($id, $parent)){
            return [];
        }else{
        $items = self::find()->where(['parent_id'=>NULL])->andFilterWhere(['not',['id'=>$id]])->orderBy(['industry_sort'=>SORT_ASC,'parent_id'=>SORT_ASC])->all();
        return ArrayHelper::map($items, 'id', 'name');
        
        }
    }
    
    public static function get_industry_id() {
       
        $parent_id= static::find()->andWhere(['not',['parent_id'=>NULL]])->select(['parent_id'])->distinct()->column();//一级ID
        
        //$data= static::find()->andWhere(['parent_id'=>NULL])->andWhere(['not',['id'=>$parent_id]])->select(['name','id'])->indexBy('id')->asArray()->column();//不存在下级的一级
        $data=[];
        $parents= static::find()->andWhere(['id'=>$parent_id])->select(['id','name'])->all();
        foreach($parents as $parent){
            $data[$parent['name']]=static::find()->andWhere(['parent_id'=>$parent['id']])->orderBy(['industry_sort'=>SORT_ASC,'id'=>SORT_ASC])->select(['name','id'])->indexBy('id')->asArray()->column();
        }
        return $data;
    }
    
//    public static function get_industry_children() {
//        $items= static::find()->where(['not',['parent_id'=>NULL]])->orderBy(['parent_id'=>SORT_ASC,'industry_sort'=>SORT_ASC,'id'=>SORT_ASC])->all();      
//        return ArrayHelper::map($items, 'id', 'name'); 
//    }
}
