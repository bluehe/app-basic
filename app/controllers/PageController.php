<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\models\Category;
use app\models\Website;

/**
 * Page controller
 */
class PageController extends Controller {
    
        /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['user'],
                'rules' => [
                    [
                        'actions' => ['user'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];
    }
    
    public function actionAll() {
        $cache = Yii::$app->cache;
        $cates = $cache->get('page_all');
        if ($cates === false) {
            $cates = Category::get_category_sql()->asArray()->all();
            foreach ($cates as $key => $cate) {
                $websites = Website::get_website(null, $cate['id']);
                $cates[$key]['website'] = $websites;
            }
            $cache->set('page_all', $cates, 360);
        }

        $this->layout = '//main-page';
        return $this->render('all', ['cates' => $cates]);
    }
    
    public function actionUser() {       
        $cates = Category::get_category_sql(Yii::$app->user->identity->id)->asArray()->all();
        if (count($cates) > 0) {
            foreach ($cates as $key => $cate) {
                $websites = Website::get_website(NULL, $cate['id']);
                $cates[$key]['website'] = $websites;
            }
        }
        $common = Website::get_website_order(10, Yii::$app->user->identity->id);
        
        $this->layout = '//main-page';
        return $this->render('user', ['cates' => $cates, 'common' => $common]);
    }
}
