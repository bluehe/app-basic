<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use project\models\User;
use yii\web\JsExpression;
use project\models\System;
use yii\filters\VerbFilter;
use project\models\UserAuth;
use project\models\LoginForm;
use project\models\UserGroup;
use project\models\HealthData;
use project\models\SignupForm;
use yii\filters\AccessControl;
use project\models\Corporation;
use project\models\CloudSubsidy;
use project\models\ActivityChange;
use project\models\CorporationMeal;
use project\models\Group;
use yii\base\InvalidParamException;
use project\models\PasswordFindForm;
use project\models\PasswordResetForm;
use project\models\ResetPasswordForm;
use project\models\PasswordResetRequestForm;

/**
 * Site controller
 */
class SiteController extends Controller
{

    public $attempts = 3; // allowed 3 attempts
    public $counter;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'index'],
                'rules' => [
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'successCallback'],
                //                'cancelCallback' => [$this, 'cancelCallback'],
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'maxLength' => $captcha_length = System::getValue('captcha_length'), //最大显示个数
                'minLength' => $captcha_length, //最少显示个数
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        //补贴企业数
        $meal_c_id = CorporationMeal::find()->select(['corporation_id'])->column();
        $subside_c_id = CloudSubsidy::find()->select(['corporation_id'])->column();
        $allocate_num = (int) Corporation::find()->where(['group_id' => 1])->andWhere(['id' => array_unique(array_merge($meal_c_id, $subside_c_id))])->count();
        $meal_amount = CorporationMeal::find()->where(['group_id' => 1])->sum('amount');
        $subside_amount = CloudSubsidy::find()->where(['group_id' => 1])->sum('subsidy_amount');
        $allocate_amount = round(($meal_amount + $subside_amount) / 10000, 2);


        //下拨额
        $series['amount'] = [];
        $annual = ''; //Yii::$app->request->get('annual', null);
        $group = ''; //Yii::$app->request->get('group', null);
        $chart = 2;

        $end = strtotime('today');
        $start = strtotime('-1 year', $end);
        $sum = 1; //Yii::$app->request->get('sum', 1);//1-天；2-周；3-月

        $allocate_total = CorporationMeal::get_amount_total($start, $end, $sum, 0, $annual, $group);
        $base_allocate = (float) CorporationMeal::get_amount_base($start, $annual, $group);
        $num_allocate = (int) CorporationMeal::get_num_base($start, $annual, $group);

        $cloud_total = CloudSubsidy::get_amount_total($start, $end, $sum, $annual, $group);
        $base_cloud = (float) CloudSubsidy::get_amount_base($start, $annual, $group);
        $num_cloud = (int) CloudSubsidy::get_num_base($start, $annual, $group);


        $data_amount = [];

        //天
        $allocate_start = $allocate_total ? strtotime(key($allocate_total)) : $start; //下拨最早日期
        $cloud_start = $cloud_total ? strtotime(key($cloud_total)) : $allocate_start; //公有云补贴最早日期
        $amount_start = ($allocate_start < $cloud_start ? $allocate_start : $cloud_start) - 86400; //补贴最早日期

        for ($i = $amount_start; $i <= $end; $i = $i + 86400) {
            $k = date('Y-m-d', $i);
            $j = $end - $amount_start >= 365 * 86400 ? date('Y.n.j', $i) : date('n.j', $i);
            //下拨
            $base_allocate = isset($allocate_total[$k]['amount']) ? (float) $allocate_total[$k]['amount'] + $base_allocate : $base_allocate;
            $num_allocate = isset($allocate_total[$k]['num']) ? (float) $allocate_total[$k]['num'] + $num_allocate : $num_allocate;
            //$y_allocate_amount = $base_amount / 10000;
            //公有云
            $base_cloud = isset($cloud_total[$k]['amount']) ? (float) $cloud_total[$k]['amount'] + $base_cloud : $base_cloud;
            $num_cloud = isset($cloud_total[$k]['num']) ? (float) $cloud_total[$k]['num'] + $num_cloud : $num_cloud;
            //$y_cloud_amount = $base_cloud / 10000;
            $y_amount = ($base_allocate + $base_cloud) / 10000;
            $y_num = $num_allocate + $num_cloud;

            $data_amount[] = ['name' => $j, 'y' => $y_amount, 'value' => [$j, $y_amount]];
            $data_num[] = ['name' => $j, 'y' => $y_num, 'value' => [$j, $y_num]];
        }

        $series['amount'][] = [
            'name' => "累计补贴数",
            'type' => "line",
            'smooth' => true,
            'symbol' => "circle",
            'symbolSize' => 5,
            'showSymbol' => false,
            'lineStyle' => [
                'normal' => [
                    'color' => "#00d887",
                    'width' => 2
                ]
            ],
            'areaStyle' => [
                'normal' => [
                    'color' => "rgba(0, 216, 135, 0.4)",
                    'shadowColor' => "rgba(0, 0, 0, 0.1)",
                ]
            ],
            'itemStyle' => [
                'normal' => [
                    'color' => "#00d887",
                    'borderColor' => "rgba(221, 220, 107, .1)",
                    'borderWidth' => 12
                ]
            ],
            'label' => [
                'show' => true
            ],
            'data' => $data_num
        ];

        $series['amount'][] = [
            'name' => "累计补贴额",
            'type' => "line",
            'smooth' => true,
            'symbol' => "circle",
            'symbolSize' => 5,
            'showSymbol' => false,
            'lineStyle' => [
                'normal' => [
                    'color' => "#0184d5",
                    'width' => 2
                ]
            ],
            'areaStyle' => [
                'normal' => [
                    'color' => "rgba(1, 132, 213, 0.4)",
                    'shadowColor' => "rgba(0, 0, 0, 0.1)",
                ]
            ],
            'itemStyle' => [
                'normal' => [
                    'color' => "#0184d5",
                    'borderColor' => "rgba(221, 220, 107, .1)",
                    'borderWidth' => 12
                ]
            ],
            'label' => [
                'show' => true
            ],
            'yAxisIndex' => 1,
            'data' => $data_amount
        ];

        //下拨套餐百分比
        $series['allocate_num'] = [];
        $data_allocate = [];
        $allocate_cnum = CorporationMeal::get_allocate_num($start, $end, $annual, $group);
        foreach ($allocate_cnum as $allocate) {
            $data_allocate[] = ['name' => floatval($allocate['amount'] / 10000) . '万', 'y' => (int) $allocate['num'], 'value' => (int) $allocate['num']];
        }

        $series['allocate_num'][] = ['type' => 'pie', 'radius' => ['25%', '50%'], 'name' => '数量', 'minAngle' => 10, 'data' => $data_allocate, 'label' => ['formatter' => "{c}家,{d}%", 'color' => '#FFF'], 'color' => ["#0184d5", "rgb(255, 188, 117)", "rgb(144, 238, 126)", "rgb(119, 152, 191)", "#06a0ab", "#06b4ab", "#06c8ab", "#06dcab", "#06f0ab"]];



        $month_start = strtotime('-1 months +1 days', $end);
        $total_get = 1; //Yii::$app->request->get('total', 1); //全体或个人
        $is_allocate = HealthData::ALLOCATE_Y; //Yii::$app->request->get('allocate', HealthData::ALLOCATE_Y) ? HealthData::ALLOCATE_Y : null;
        if (!$group) {
            $group_id = UserGroup::get_user_groupid(Yii::$app->user->identity->id);
            if (count($group_id) > 0) {
                $group = $group_id[0];
            }
        }

        if (Yii::$app->request->get('range')) {
            $range = explode('~', Yii::$app->request->get('range'));
            $month_start = isset($range[0]) ? strtotime($range[0]) : $month_start;
            $end = isset($range[1]) && (strtotime($range[1]) < $end) ? strtotime($range[1]) : $end;
        }

        //健康度
        $series['health'] = [];
        $data_health = $data_per = $health_value = $health_key = [];
        $data_activity = $data_per_day = $data_per_week = $data_per_month = $activity = $activity_value = [];
        $health_total = HealthData::get_health($month_start - 86400, $end, $group, $is_allocate, $total_get);
        $activity_day = HealthData::get_activity($month_start - 86400, $end, $group, $is_allocate, $total_get, 'activity_day');
        $activity_week = HealthData::get_activity($month_start - 86400, $end, $group, $is_allocate, $total_get, 'activity_week');
        $activity_month = HealthData::get_activity($month_start - 86400, $end, $group, $is_allocate, $total_get, 'activity_month');

        // var_dump($health_total);

        foreach ($activity_day as $one) {
            $activity[$one['statistics_time']]['day'] = $one['num'];
        }
        foreach ($activity_week as $one) {
            $activity[$one['statistics_time']]['week'] = $one['num'];
        }
        foreach ($activity_month as $one) {
            $activity[$one['statistics_time']]['month'] = $one['num'];
        }
        foreach ($health_total as $total) {
            $key = $end - $month_start >= 365 * 86400 ? date('Y.n.j', $total['statistics_time']) : date('n.j', $total['statistics_time']);
            $health_value[$key][$total['health']] = (int) $total['num'];
            if (!in_array($total['health'], $health_key)) {
                $health_key[] = $total['health'];
            }
            $activity_value[$key]['day'] = isset($activity[$total['statistics_time']]['day']) ? (int) $activity[$total['statistics_time']]['day'] : 0;
            $activity_value[$key]['week'] = isset($activity[$total['statistics_time']]['week']) ? (int) $activity[$total['statistics_time']]['week'] : 0;
            $activity_value[$key]['month'] = isset($activity[$total['statistics_time']]['month']) ? (int) $activity[$total['statistics_time']]['month'] : 0;
        }
        asort($health_key);
        foreach ($health_value as $date => $value) {
            $sum = 0;
            foreach ($health_key as $key) {
                $y_health = isset($health_value[$date][$key]) ? $health_value[$date][$key] : 0;
                $sum += $y_health;
                $data_health[$key][] = ['name' => $date, 'y' => $y_health, 'value' => [$date, $y_health]];
            }
            $health_5 = isset($health_value[$date][HealthData::HEALTH_H5]) ? $health_value[$date][HealthData::HEALTH_H5] : 0;
            //$health_4 = isset($health_value[$date][HealthData::HEALTH_H4]) ? $health_value[$date][HealthData::HEALTH_H4] : 0;
            $data_per[] = ['name' => $date, 'y' => $sum == 0 ? 0 : round(($health_5) * 100 / $sum, 2), 'value' => [$date, $sum == 0 ? 0 : round(($health_5) * 100 / $sum, 2)]];
            $data_activity['day'][] = ['name' => $date, 'y' => $activity_value[$date]['day'], 'value' => [$date, $activity_value[$date]['day']]];
            $data_activity['week'][] = ['name' => $date, 'y' => $activity_value[$date]['week'], 'value' => [$date, $activity_value[$date]['week']]];
            $data_activity['month'][] = ['name' => $date, 'y' => $activity_value[$date]['month'], 'value' => [$date, $activity_value[$date]['month']]];
            $data_activity['total'][] = ['name' => $date, 'y' => $sum, 'value' => [$date, $sum]];
            $data_per_day[] = ['name' => $date, 'y' => $sum == 0 ? 0 : round($activity_value[$date]['day'] * 100 / $sum, 2), 'value' => [$date, $sum == 0 ? 0 : round($activity_value[$date]['day'] * 100 / $sum, 2)]];
            $data_per_week[] = ['name' => $date, 'y' => $sum == 0 ? 0 : round($activity_value[$date]['week'] * 100 / $sum, 2), 'value' => [$date, $sum == 0 ? 0 : round($activity_value[$date]['week'] * 100 / $sum, 2)]];
            $data_per_month[] = ['name' => $date, 'y' => $sum == 0 ? 0 : round($activity_value[$date]['month'] * 100 / $sum, 2), 'value' => [$date, $sum == 0 ? 0 : round($activity_value[$date]['month'] * 100 / $sum, 2)]];
        }


        foreach ($data_health as $k => $v) {
            $series['health'][] = [
                'type' => 'bar', 'name' => HealthData::$List['health'][$k],

                'data' => $v,
                'itemStyle' => [
                    'normal' => [
                        'color' => HealthData::$List['health_color'][$k],
                        'opacity' => 1,
                        'barBorderRadius' => 5
                    ]
                ],
                //'stack' => '健康度',
                'label' => ['show' => true, 'position' => 'top']
            ];
        }
        $series['health'][] = [
            'type' => 'line', 'smooth' => true, 'name' => '健康度', 'data' => $data_per, 'yAxisIndex' => 1,
            'itemStyle' => [
                'normal' => [
                    'color' => "#0184d5",
                    'borderColor' => "rgba(221, 220, 107, .1)",
                    'borderWidth' => 12
                ]
            ],
            'label' => ['show' => true, 'formatter' => "{@[1]}%", 'color' => '#fff']
        ];

        $series['activity'][] = [
            'type' => 'bar', 'name' => '总企业数', 'barWidth' => "35%",
            'itemStyle' => [
                'normal' => [
                    'color' => '#0184d5',
                    'opacity' => 1,
                    'barBorderRadius' => 5
                ]
            ],
            'data' => isset($data_activity['total']) ? $data_activity['total'] : [], 'label' => ['show' => true, 'position' => 'top', 'color' => '#fff']
        ];
        $series['activity'][] = [
            'type' => 'bar', 'name' => '月活跃企业数', 'barWidth' => "35%",
            'itemStyle' => [
                'normal' => [
                    'color' => 'rgb(247, 163, 92)',
                    'opacity' => 1,
                    'barBorderRadius' => 5
                ]
            ],
            'data' => isset($data_activity['month']) ? $data_activity['month'] : [], 'label' => ['show' => true], 'barGap' => '-100%'
        ];
        // $series['activity'][] = [
        //     'type' => 'bar', 'name' => '周活跃企业数', 'barWidth' => "35%",
        //     'itemStyle' => [
        //         'normal' => [
        //             'color' => '#00d887',
        //             'opacity' => 1,
        //             'barBorderRadius' => 5
        //         ]
        //     ],
        //     'data' => isset($data_activity['week']) ? $data_activity['week'] : [], 'label' => ['show' => true], 'barGap' => '-100%'
        // ];
        // $series['activity'][] = [
        //     'type' => 'bar', 'name' => '日活跃企业数', 'barWidth' => "35%",
        //     'itemStyle' => [
        //         'normal' => [
        //             'color' => 'rgb(119, 152, 191)',
        //             'opacity' => 1,
        //             'barBorderRadius' => 5
        //         ]
        //     ],
        //     'data' => isset($data_activity['day']) ? $data_activity['day'] : [], 'label' => ['show' => true], 'barGap' => '-100%'
        // ];

        // $series['activity'][] = ['type' => 'line', 'smooth' => true, 'name' => '日活跃率', 'data' => $data_per_day, 'yAxisIndex' => 1, 'label' => ['show' => true, 'formatter' => "{@[1]}%", 'color' => '#000']];
        // $series['activity'][] = ['type' => 'line', 'smooth' => true, 'name' => '周活跃率', 'data' => $data_per_week, 'yAxisIndex' => 1, 'label' => ['show' => true, 'formatter' => "{@[1]}%", 'color' => '#000']];
        $series['activity'][] = [
            'type' => 'line', 'smooth' => true, 'name' => '月活跃率', 'data' => $data_per_month, 'yAxisIndex' => 1,
            'itemStyle' => [
                'normal' => [
                    'color' => "#dd4b39",
                    'borderColor' => "rgba(221, 220, 107, .1)",
                    'borderWidth' => 12
                ]
            ],
            'label' => ['show' => true, 'formatter' => "{@[1]}%", 'color' => '#fff']
        ];


        //用户数
        $data_total_num = $data_user_num = $data_user_per = [];
        $activity_user = HealthData::get_user_total($month_start, $end, $group, $is_allocate, $total_get, null);

        foreach ($activity_user as $row) {
            $j = date('n.j', $row['statistics_time']);
            $data_total_num[] = ['name' => $j, 'y' => (int) $row['total_num'], 'value' => [$j, (int) $row['total_num']]];
            $data_user_num[] = ['name' => $j, 'y' => (int) $row['user_num'], 'value' => [$j, (int) $row['user_num']]];
            $data_user_per[] = ['name' => $j, 'y' => isset($row['user_num']) && isset($row['total_num']) && (int) $row['total_num'] > 0 ? round((int) $row['user_num'] / (int) $row['total_num'] * 100, 2) : 0, 'value' => [$j, isset($row['user_num']) && isset($row['total_num']) && (int) $row['total_num'] > 0 ? round((int) $row['user_num'] / (int) $row['total_num'] * 100, 2) : 0]];
        }

        $series['user'][] = [
            'type' => 'bar', 'name' => '下拨用户数', 'data' => $data_total_num,
            'label' => ['show' => true, 'position' => 'top', 'color' => '#FFF'],
            'barWidth' => "35%",
            'itemStyle' => [
                'normal' => [
                    'color' => '#0184d5',
                    'opacity' => 1,
                    'barBorderRadius' => 5
                ]
            ],
        ];
        $series['user'][] = [
            'type' => 'bar', 'name' => '实际用户数', 'data' => $data_user_num, 'label' => ['show' => true], 'barGap' => '-100%',
            'barWidth' => "35%",
            'itemStyle' => [
                'normal' => [
                    'color' => '#00d887',
                    'opacity' => 1,
                    'barBorderRadius' => 5,
                ]
            ],
            'label' => ['show' => true, 'color' => '#fff']
        ];
        $series['user'][] = [
            'type' => 'line', 'smooth' => true, 'name' => '用户占比', 'data' => $data_user_per, 'yAxisIndex' => 1,
            'itemStyle' => [
                'normal' => [
                    'color' => "#dd4b39",
                    'borderColor' => "rgba(221, 220, 107, .1)",
                    'borderWidth' => 12
                ]
            ],
            'label' => ['show' => false, 'formatter' => "{@[1]}%", 'color' => '#FFF']
        ];

        //活跃项目
        $series['item'] = [];
        $data_item = [];

        $items = [];
        $items['项目管理'] = (int) ActivityChange::get_activity_item($start - 86400, $end, ['projectman_projectcount', 'projectman_membercount', 'projectman_issuecount', 'projectman_wiki', 'projectman_docman'], $annual, true, $group, $is_allocate);
        $items['代码仓库'] = (int) ActivityChange::get_activity_item($start - 86400, $end, ['codehub_repositorycount', 'codehub_commitcount', 'codehub_repositorysize'], $annual, true, $group, $is_allocate);
        $items['流水线'] = (int) ActivityChange::get_activity_item($start - 86400, $end, ['pipeline_assignmentscount', 'pipeline_elapse_time'], $annual, true, $group, $is_allocate);
        $items['代码检查'] = (int) ActivityChange::get_activity_item($start - 86400, $end, ['codecheck_taskcount', 'codecheck_codelinecount', 'codecheck_execount'], $annual, true, $group, $is_allocate);
        $items['编译构建'] = (int) ActivityChange::get_activity_item($start - 86400, $end, ['codeci_buildcount', 'codeci_buildtotaltime'], $annual, true, $group, $is_allocate);
        $items['测试'] = (int) ActivityChange::get_activity_item($start - 86400, $end, ['testman_casecount', 'testman_execasecount'], $annual, true, $group, $is_allocate);
        $items['部署'] = (int) ActivityChange::get_activity_item($start - 86400, $end, ['deploy_envcount', 'deploy_execount'], $annual, true, $group, $is_allocate);
        $items['发布'] = (int) ActivityChange::get_activity_item($start - 86400, $end, ['releaseman_uploadcount', 'releaseman_downloadcount'], $annual, true, $group, $is_allocate);
        $items['沉默企业'] = (int) ActivityChange::get_activity_item($start - 86400, $end, null, $annual, false, $group, $is_allocate);

        arsort($items);
        //        $fv= reset($items);
        //        $fk= key($items);
        //        unset($items[$fk]);
        //        $items[$fk]=$fv;

        foreach ($items as $key => $item) {
            $data_item[] = ['name' => $key, 'y' => $item, 'value' => $item];
        }

        $series['item'][] = ['type' => 'pie', 'radius' => [0, '60%'], 'selectedMode' => 'single', 'name' => '数量', 'minAngle' => 10, 'data' => $data_item, 'label' => ['color' => '#fff'], 'color' => ["#0184d5", "rgb(255, 188, 117)", "rgb(144, 238, 126)", "rgb(119, 152, 191)", "#aaeeee",  "#06c8ab", "#06dcab", "#06f0ab", "#DF5353", "#ff0066"]];


        //地图
        $series['geo'] = $location = $lines = [];
        $groups = Group::find()->select(['id', 'title', 'location'])->all();
        foreach ($groups as $one) {
            $group_num = (int) Corporation::find()->where(['group_id' => $one->id])->count();
            $fromCoord = $l = $one->location ? explode(',', $one->location) : [];
            array_push($l, $group_num);
            $location[] = ['name' => $one->title, 'value' => $l];
            foreach ($groups as $two) {
                $toCoord = $two->location ? explode(',', $two->location) : [];
                //$lines[] = ['coords' => [$fromCoord, $toCoord]];
                $lines[] = [['coord' => $fromCoord], ['coord' => $toCoord]];
            }
        }
        $planePath = 'path://M.6,1318.313v-89.254l-319.9-221.799l0.073-208.063c0.521-84.662-26.629-121.796-63.961-121.491c-37.332-0.305-64.482,36.829-63.961,121.491l0.073,208.063l-319.9,221.799v89.254l330.343-157.288l12.238,241.308l-134.449,92.931l0.531,42.034l175.125-42.917l175.125,42.917l0.531-42.034l-134.449-92.931l12.238-241.308L1705';
        //$planePath = 'path://M1705.06,1318.313v-89.254l-319.9-221.799l0.073-208.063c0.521-84.662-26.629-121.796-63.961-121.491c-37.332-0.305-64.482,36.829-63.961,121.491l0.073,208.063l-319.9,221.799v89.254l330.343-157.288l12.238,241.308l-134.449,92.931l0.531,42.034l175.125-42.917l175.125,42.917l0.531-42.034l-134.449-92.931l12.238-241.308L1705.06,1318.313z';//飞机

        $series['geo'] = [
            [
                'name' => '联动',
                'type' => 'lines',
                'zlevel' => 1,
                'effect' => [
                    'show' => true,
                    'period' => 6,
                    'trailLength' => 0.7,
                    'color' => '#fff',
                    'symbolSize' => 3
                ],
                'lineStyle' => [
                    'normal' => [
                        'color' => '#ffeb7b',
                        'width' => 0,
                        'curveness' => 0.2
                    ]
                ],
                'data' => $lines
            ],
            [
                'name' => '联动',
                'type' => 'lines',
                'zlevel' => 2,
                'effect' => [
                    'show' => true,
                    'period' => 6,
                    'trailLength' => 0,
                    'symbol' => $planePath,
                    'symbolSize' => 15
                ],
                'label' => ['show' => false],
                'lineStyle' => [
                    'normal' => [
                        'color' => '#ffeb7b',
                        'width' => 1,
                        'opacity' => 0.4,
                        'curveness' => 0.2
                    ]
                ],
                'data' => $lines
            ],
            [
                'name' => "联创中心",
                'type' => "effectScatter",
                'coordinateSystem' => "geo",
                'rippleEffect' => [
                    'brushType' => 'stroke'
                ],
                'symbolSize' => 15,
                'label' => [
                    'normal' => [
                        'formatter' => "{b}",
                        'position' => "right",
                        'show' => true
                    ],
                    'emphasis' => [
                        'show' => true
                    ]
                ],
                'itemStyle' => [
                    'normal' => [
                        'color' => "#ffeb7b"
                    ]
                ],
                'data' => $location
            ]
        ];
        // $corporations = Corporation::find()->select(['base_company_name', 'contact_location'])->all();
        // foreach ($corporations as $one) {
        //     $l = $one->contact_location ? explode(',', $one->contact_location) : [];
        //     array_push($l, 1);
        //     $corporation_location[] = ['name' => $one->base_company_name, 'value' => $l];
        // }
        // $series['geo'][] = [
        //     'name' => "补贴企业",
        //     'type' => "scatter",
        //     'coordinateSystem' => "geo",
        //     'symbolSize' => 5,
        //     'label' => [
        //         'normal' => [
        //             'formatter' => "{b}",
        //             'position' => "right",
        //             'show' => false
        //         ],
        //         'emphasis' => [
        //             'show' => true
        //         ]
        //     ],
        //     'itemStyle' => [
        //         'normal' => [
        //             'color' => "#ffeb7b"
        //         ]
        //     ],
        //     'data' => $corporation_location
        // ];

        return $this->render('index', ['allocate_num' => $allocate_num, 'allocate_amount' => $allocate_amount, 'series' => $series,]);
    }

    public function actionKanban()
    {
        $this->layout = '//main-kanban';
        return $this->render('kanban');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (System::getValue('system_stat') == '0') {
            $notice = System::getValue('system_close');
            Yii::$app->session->setFlash('warning', $notice ? $notice : '管理员临时关闭本站');
            if (!Yii::$app->user->isGuest) {
                Yii::$app->user->logout();
            }
        }

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->login()) {
                Yii::$app->session->remove('loginCaptchaRequired');
                return $this->goBack();
            } else {
                $this->counter = Yii::$app->session->get('loginCaptchaRequired') + 1;
                Yii::$app->session->set('loginCaptchaRequired', $this->counter);
            }
        }
        $this->counter = Yii::$app->session->get('loginCaptchaRequired') + 1;
        $captcha_loginfail = System::getValue('captcha_loginfail');
        if ((($this->counter > $this->attempts && $captcha_loginfail == '1') || $captcha_loginfail != '1') && System::existValue('captcha_open', '2')) {
            $model->setScenario("captchaRequired");
        }

        $this->layout = '//main-login';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        if (!System::getValue('system_register')) {
            Yii::$app->session->setFlash('warning', '本站未开放注册权限。');
            return $this->goHome();
        }
        $model = new SignupForm();
        $model->agreement = 1;
        if ($model->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\bootstrap\ActiveForm::validate($model);
            }
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        } else {
            if (System::existValue('captcha_open', '1')) {
                $model->setScenario("captchaRequired");
            }
        }
        $this->layout = '//main-login';
        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    public function actionAgreement($code)
    {
        $model = System::getValue('agreement_' . $code);
        if ($model == null) {
            return false;
        } else {
            return $this->renderAjax('agreement', [
                'model' => $model,
            ]);
        }
    }

    //发送邮件方式
    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        $model->load(Yii::$app->request->post());
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\bootstrap\ActiveForm::validate($model);
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', '邮件已经发送，请检查你的邮件并进一步操作。');
                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', '对不起，我们不能通过你提供的邮箱进行密码重置。');
            }
        } else {
            if (System::existValue('captcha_open', '3')) {
                $model->setScenario("captchaRequired");
            }
        }
        $this->layout = '//main-login';
        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            Yii::$app->session->setFlash('danger', '链接已过期，请重新操作。');

            return $this->goHome();
            //throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', '新密码已经被保存。');

            return $this->goHome();
        }
        $this->layout = '//main-login';
        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    //验证码方式
    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionPasswordReset()
    {
        $model = new PasswordResetForm();
        if ($model->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\bootstrap\ActiveForm::validate($model);
            }
            if ($model->validate() && $model->setSession()) {
                return $this->redirect(['password-find']);
            }
        }
        if (System::existValue('captcha_open', '3')) {
            $model->setScenario("captchaRequired");
        }
        $this->layout = '//main-login';
        return $this->render('passwordReset', [
            'model' => $model,
        ]);
    }

    public function actionPasswordFind()
    {
        $token = Yii::$app->session->get('find_password_token');
        try {
            $model = new PasswordFindForm($token);
            $model->type = Yii::$app->request->get('type', 'email');
        } catch (InvalidParamException $e) {
            Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(['password-reset']);
        }
        if ($model->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\bootstrap\ActiveForm::validate($model);
            }
            if ($model->validate() && $model->resetPassword()) {
                Yii::$app->session->setFlash('success', '新密码已经被保存。');
                return $this->goHome();
            }
        }
        $this->layout = '//main-login';
        return $this->render('passwordFind', [
            'model' => $model,
        ]);
    }

    //第三方回调
    public function successCallback($client)
    {
        $type = $client->getId(); // qq | weibo | github
        $attributes = $client->getUserAttributes(); // basic info

        $auth = UserAuth::find()->where(['type' => $type, 'open_id' => $attributes['id']])->one();
        switch ($type) {
            case 'github':
                $avatar = $attributes['avatar_url'];
                $nickname = $attributes['name'];
                $gender = '';
                break;
            case 'weibo':
                $avatar = $attributes['profile_image_url'];
                $nickname = $attributes['name'];
                $gender = $attributes['gender']; //m
                break;
            case 'qq':
                $avatar = $attributes['figureurl_qq_2'];
                $nickname = $attributes['nickname'];
                $gender = $attributes['gender']; //男
                break;
            default:
                $avatar = '';
                $nickname = '';
                $gender = '';
                break;
        }
        if ($auth) {
            //存在
            if (Yii::$app->user->login($auth->user)) {
                if (!$auth->user->avatar) {
                    $auth->user->avatar = $avatar;
                    $auth->user->save();
                }
                if (!$auth->user->nickname && (mb_strlen($nickname, "UTF8") >= 5) && !User::exist_nickname($nickname)) {
                    $auth->user->nickname = $nickname;
                    $auth->user->save();
                }
                return $this->goHome();
            }
        } else {
            //不存在，注册
            if (!System::getValue('system_register')) {
                Yii::$app->session->setFlash('warning', '本站未开放注册权限。');
                return $this->goHome();
            }
            Yii::$app->session->set('auth_type', $type);
            Yii::$app->session->set('auth_openid', $attributes['id']);
            Yii::$app->session->set('auth_avatar', $avatar);
            Yii::$app->session->set('auth_nickname', $nickname);
            return $this->redirect('complete');
        }


        // user login or signup comes here
    }

    //    public function cancelCallback($client) {
    //        $type = $client->getId(); // qq | weibo | github |weixin
    //        $attributes = $client->getUserAttributes(); // basic info
    //
    //        $auth = UserAuth::find()->where(['type' => $type, 'open_id' => $attributes['id']])->one();
    //        if($auth!==null){
    //            $auth->delete();
    //        }
    //        return $this->goHome();
    //    }

    public function actionComplete()
    {
        if (!Yii::$app->user->isGuest) {
            //创建第三方记录
            $auth = new UserAuth();
            $auth->type = Yii::$app->session->get('auth_type');
            $auth->open_id = Yii::$app->session->get('auth_openid');
            $auth->uid = Yii::$app->user->identity->id;
            $auth->created_at = time();
            if ($auth->save()) {
                if (!$auth->user->avatar) {
                    $auth->user->avatar = Yii::$app->session->get('auth_avatar');
                    $auth->user->save();
                }
                $nickname = Yii::$app->session->get('auth_nickname');
                if (!$auth->user->nickname && (mb_strlen($nickname, "UTF8") >= 5) && !User::exist_nickname($nickname)) {
                    $auth->user->nickname = $nickname;
                    $auth->user->save();
                }
                Yii::$app->session->remove('auth_type');
                Yii::$app->session->remove('auth_openid');
                Yii::$app->session->remove('auth_avatar');
                Yii::$app->session->remove('auth_nickname');
                //return $this->goHome();
            }
            return $this->goHome();
        }
        $model_l = new LoginForm();
        $model_s = new SignupForm();
        $model_s->agreement = 1;
        if (Yii::$app->request->isPost) {

            if (Yii::$app->request->post('type') === 'bind') {
                //登录
                if ($model_l->load(Yii::$app->request->post()) && $model_l->login()) {
                    Yii::$app->session->remove('loginCaptchaRequired');
                    //创建第三方记录
                    $auth = new UserAuth();
                    $auth->type = Yii::$app->session->get('auth_type');
                    $auth->open_id = Yii::$app->session->get('auth_openid');
                    $auth->uid = Yii::$app->user->identity->id;
                    $auth->created_at = time();
                    if ($auth->save()) {
                        if (!$auth->user->avatar) {
                            $auth->user->avatar = Yii::$app->session->get('auth_avatar');
                            $auth->user->save();
                        }
                        $nickname = Yii::$app->session->get('auth_nickname');
                        if (!$auth->user->nickname && (mb_strlen($nickname, "UTF8") >= 5) && !User::exist_nickname($nickname)) {
                            $auth->user->nickname = $nickname;
                            $auth->user->save();
                        }
                        Yii::$app->session->remove('auth_type');
                        Yii::$app->session->remove('auth_openid');
                        Yii::$app->session->remove('auth_avatar');
                        Yii::$app->session->remove('auth_nickname');
                        return $this->goHome();
                    }
                } else {
                    $this->counter = Yii::$app->session->get('loginCaptchaRequired') + 1;
                    Yii::$app->session->set('loginCaptchaRequired', $this->counter);
                }
            } else {
                if ($model_s->load(Yii::$app->request->post())) {
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                        return \yii\bootstrap\ActiveForm::validate($model_s);
                    }
                    //创建用户
                    if ($user = $model_s->signup()) {
                        //登录
                        if (Yii::$app->getUser()->login($user)) {
                            //创建第三方记录
                            $auth = new UserAuth();
                            $auth->type = Yii::$app->session->get('auth_type');
                            $auth->open_id = Yii::$app->session->get('auth_openid');
                            $auth->uid = Yii::$app->user->identity->id;
                            $auth->created_at = time();
                            if ($auth->save()) {
                                if (!$auth->user->avatar) {
                                    $auth->user->avatar = Yii::$app->session->get('auth_avatar');
                                    $auth->user->save();
                                }
                                $nickname = Yii::$app->session->get('auth_nickname');
                                if (!$auth->user->nickname && (mb_strlen($nickname, "UTF8") >= 5) && !User::exist_nickname($nickname)) {
                                    $auth->user->nickname = $nickname;
                                    $auth->user->save();
                                }
                                Yii::$app->session->remove('auth_type');
                                Yii::$app->session->remove('auth_openid');
                                Yii::$app->session->remove('auth_avatar');
                                Yii::$app->session->remove('auth_nickname');
                                return $this->goHome();
                            }
                        }
                    }
                }
            }
        }
        $this->counter = Yii::$app->session->get('loginCaptchaRequired') + 1;
        $captcha_loginfail = System::getValue('captcha_loginfail');
        if ((($this->counter > $this->attempts && $captcha_loginfail == '1') || $captcha_loginfail != '1') && System::existValue('captcha_open', '2')) {
            $model_l->setScenario("captchaRequired");
        }
        if (System::existValue('captcha_open', '1')) {
            $model_s->setScenario("captchaRequired");
        }


        $this->layout = '//main-login';
        return $this->render('complete', ['model_l' => $model_l, 'model_s' => $model_s,]);
    }
}
