<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use project\models\Group;
use project\models\HealthData;
use project\models\Corporation;
use project\models\CloudSubsidy;
use project\models\ActivityChange;
use project\models\CorporationMeal;
use yii\web\JsExpression;


class DataViewController extends Controller
{

    //获取补贴数据
    public function actionCloud()
    {
        $info = ['code' => 200, 'message' => '', 'data' => []];
        //补贴企业数
        $meal_c_id = CorporationMeal::find()->select(['corporation_id'])->column();
        $subside_c_id = CloudSubsidy::find()->select(['corporation_id'])->column();
        $cloud_num = (int) Corporation::find()->where(['group_id' => 1])->andWhere(['id' => array_unique(array_merge($meal_c_id, $subside_c_id))])->count();
        $meal_amount = CorporationMeal::find()->where(['group_id' => 1])->sum('amount');
        $subside_amount = CloudSubsidy::find()->where(['group_id' => 1])->sum('subsidy_amount');
        $cloud_amount = round(($meal_amount + $subside_amount) / 10000, 2);

        $info['data'] = ['cloud_num' => $cloud_num, 'cloud_amount' => $cloud_amount];

        return json_encode($info);
    }

    //获取健康度数据
    public function actionHealth()
    {
        $info = ['code' => 200, 'message' => '', 'data' => []];
        //健康度
        $end = strtotime('today');
        $month_start = strtotime('-1 months +1 days', $end);
        $group = 1;
        $is_allocate = HealthData::ALLOCATE_Y;
        $total_get = 1;
        $series = $health_key = $data_health = $data_per = [];
        $health_total = HealthData::get_health($month_start - 86400, $end, $group, $is_allocate, $total_get);

        foreach ($health_total as $total) {
            $key = $end - $month_start >= 365 * 86400 ? date('Y.n.j', $total['statistics_time']) : date('n.j', $total['statistics_time']);
            $health_value[$key][$total['health']] = (int) $total['num'];
            if (!in_array($total['health'], $health_key)) {
                $health_key[] = $total['health'];
            }
        }
        asort($health_key);
        foreach ($health_value as $date => $value) {
            $sum = 0;
            foreach ($health_key as $key) {
                $y_health = isset($health_value[$date][$key]) ? $health_value[$date][$key] : 0;
                $sum += $y_health;
                $data_health[$key][] = ['value' => [$date, $y_health]];
            }
            $health_5 = isset($health_value[$date][HealthData::HEALTH_H5]) ? $health_value[$date][HealthData::HEALTH_H5] : 0;
            $data_per[] = ['value' => [$date, $sum == 0 ? 0 : round(($health_5) * 100 / $sum, 2)]];
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

        $info['data'] = $series['health'];
        return json_encode($info);
    }

    //获取活跃度数据
    public function actionActivity()
    {
        $info = ['code' => 200, 'message' => '', 'data' => []];
        //活跃度
        $end = strtotime('today');
        $month_start = strtotime('-1 months +1 days', $end);
        $group = 1;
        $is_allocate = HealthData::ALLOCATE_Y;
        $total_get = 1;
        $series  = $activity = $activity_value = $data_activity = $data_per_month = [];

        $activity_month = HealthData::get_activity($month_start - 86400, $end, $group, $is_allocate, $total_get, 'activity_month');
        $activity_total = HealthData::get_activity($month_start - 86400, $end, $group, $is_allocate, $total_get, null);

        foreach ($activity_month as $one) {
            $activity[$one['statistics_time']]['month'] = $one['num'];
        }
        foreach ($activity_total as $total) {
            $key = $end - $month_start >= 365 * 86400 ? date('Y.n.j', $total['statistics_time']) : date('n.j', $total['statistics_time']);
            $activity_value[$key]['total'] = $total['num'];
            $activity_value[$key]['month'] = isset($activity[$total['statistics_time']]['month']) ? (int) $activity[$total['statistics_time']]['month'] : 0;
        }

        foreach ($activity_value as $date => $value) {
            $sum = $value['total'];
            $data_activity['month'][] = ['value' => [$date, $activity_value[$date]['month']]];
            $data_activity['total'][] = ['value' => [$date, $sum]];
            $data_per_month[] = ['value' => [$date, $sum == 0 ? 0 : round($activity_value[$date]['month'] * 100 / $sum, 2)]];
        }

        $series['activity'][] = [
            'type' => 'bar', 'name' => '总企业', 'barWidth' => "35%",
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
            'type' => 'bar', 'name' => '月活跃', 'barWidth' => "35%",
            'itemStyle' => [
                'normal' => [
                    'color' => 'rgb(247, 163, 92)',
                    'opacity' => 1,
                    'barBorderRadius' => 5
                ]
            ],
            'data' => isset($data_activity['month']) ? $data_activity['month'] : [], 'label' => ['show' => true, 'color' => '#fff'], 'barGap' => '-100%'
        ];
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

        $info['data'] = $series['activity'];
        // $this->layout = '//main-kanban';
        // return $this->render('/site/kanban');
        return json_encode($info);
    }

    //获取活跃项目
    public function actionItem()
    {
        $info = ['code' => 200, 'message' => '', 'data' => []];

        //活跃项目
        $end = strtotime('today');
        $start = strtotime('-1 year', $end);
        $annual = '';
        $group = 1;
        $is_allocate = HealthData::ALLOCATE_Y;

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

        foreach ($items as $key => $item) {
            $data_item[] = ['name' => $key, 'value' => $item];
        }

        $series['item'][] = ['type' => 'pie', 'radius' => [0, '60%'], 'selectedMode' => 'single', 'name' => '数量', 'minAngle' => 10, 'data' => $data_item, 'label' => ['color' => '#fff'], 'color' => ["#0184d5", "rgb(255, 188, 117)", "rgb(144, 238, 126)", "rgb(119, 152, 191)", "#aaeeee",  "#06c8ab", "#06dcab", "#06f0ab", "#DF5353", "#ff0066"]];

        $info['data'] = $series['item'];
        return json_encode($info);
    }

    //获取用户数
    public function actionUser()
    {
        $info = ['code' => 200, 'message' => '', 'data' => []];

        $end = strtotime('today');
        $month_start = strtotime('-1 months +1 days', $end);
        $total_get = 1;
        $is_allocate = HealthData::ALLOCATE_Y;
        $group = 1;

        //用户数
        $data_total_num = $data_user_num = $data_user_per = [];
        $activity_user = HealthData::get_user_total($month_start, $end, $group, $is_allocate, $total_get, null);

        foreach ($activity_user as $row) {
            $j = date('n.j', $row['statistics_time']);
            $data_total_num[] = ['value' => [$j, (int) $row['total_num']]];
            $data_user_num[] = ['value' => [$j, (int) $row['user_num']]];
            $data_user_per[] = ['value' => [$j, isset($row['user_num']) && isset($row['total_num']) && (int) $row['total_num'] > 0 ? round((int) $row['user_num'] / (int) $row['total_num'] * 100, 2) : 0]];
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
        $info['data'] = $series['user'];
        return json_encode($info);
    }

    //获取企业补贴
    public function actionSubsidy()
    {
        $info = ['code' => 200, 'message' => '', 'data' => []];

        $end = strtotime('today');
        $start = strtotime('-1 year', $end);
        $group = 1;
        $annual = '';
        $sum = 1;

        //补贴
        $series['amount'] = [];

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

            $data_amount[] = ['value' => [$j, $y_amount]];
            $data_num[] = ['value' => [$j, $y_num]];
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
        $info['data'] = $series['amount'];
        return json_encode($info);
    }

    //获取企业下拨
    public function actionAllocate()
    {
        $info = ['code' => 200, 'message' => '', 'data' => []];

        $end = strtotime('today');
        $start = strtotime('-1 year', $end);
        $group = 1;
        $annual = '';
        $sum = 3;

        //下拨
        $series['amount'] = [];

        $allocate_total = CorporationMeal::get_amount_total($start, $end, $sum, 0, $annual, $group);

        $data_amount = [];

        //月
        $allocate_start = $allocate_total ? strtotime(key($allocate_total)) : $start; //下拨最早日期

        for ($i = $allocate_start; $i <= $end; $i = strtotime('+1 months', $i)) {
            $k = date('Y-m', $i);
            $j = date('y.n', $i);
            //下拨
            $y_allocate_amount = isset($allocate_total[$k]['amount']) ? (float) $allocate_total[$k]['amount'] / 10000 : 0;
            $y_allocate_num = isset($allocate_total[$k]['num']) ? (float) $allocate_total[$k]['num'] : 0;

            $data_amount[] = ['value' => [$j, $y_allocate_amount]];
            $data_num[] = ['value' => [$j, $y_allocate_num]];
        }

        // $series['amount'][] = ['type' => 'line', 'z' => 3, 'name' => '当期下拨额', 'data' => $data_allocate_amount];
        // $series['amount'][] = ['type' => 'bar', 'z' => 1, 'name' => '当期下拨数', 'label' => ['show' => true, 'color' => '#000', 'position' => 'top', 'formatter' => new JsExpression("function(params) {if (params.value[1] > 0) {return params.value[1];} else {return ''}}")], 'data' => $data_allocate_num, 'yAxisIndex' => 1,];

        $series['amount'][] = [
            'type' => 'bar', 'name' => '当期下拨数', 'data' => $data_num, 'label' => ['show' => true],
            'itemStyle' => [
                'normal' => [
                    'color' => '#0184d5',
                    'opacity' => 1,
                    'barBorderRadius' => 5,
                ]
            ],
            'label' => ['show' => true, 'color' => '#fff']
        ];
        $series['amount'][] = [
            'type' => 'line', 'name' => '当期下拨额', 'data' => $data_amount, 'yAxisIndex' => 1,
            'itemStyle' => [
                'normal' => [
                    'color' => "#00d887",
                    'borderColor' => "rgba(221, 220, 107, .1)",
                    'borderWidth' => 12
                ]
            ],
            'label' => ['show' => true, 'formatter' => "{@[1]}万", 'color' => '#FFF']
        ];

        $info['data'] = $series['amount'];
        return json_encode($info);
    }

    //获取下拨套餐百分比
    public function actionMeal()
    {
        $info = ['code' => 200, 'message' => '', 'data' => []];

        $end = strtotime('today');
        $start = strtotime('-1 year', $end);
        $annual = '';
        $group = 1;

        //下拨套餐百分比
        $series['meal'] = [];
        $data_allocate = [];
        $allocate_num = CorporationMeal::get_allocate_num($start, $end, $annual, $group);
        foreach ($allocate_num as $allocate) {
            $data_allocate[] = ['name' => floatval($allocate['amount'] / 10000) . '万', 'value' => (int) $allocate['num']];
        }

        $series['meal'][] = ['type' => 'pie', 'radius' => ['25%', '50%'], 'selectedMode' => 'single', 'name' => '数量', 'minAngle' => 10, 'data' => $data_allocate, 'label' => ['formatter' => "{b}\n{c}家,{d}%", 'color' => '#FFF'], 'color' => ["#0184d5", "rgb(255, 188, 117)", "rgb(144, 238, 126)", "rgb(119, 152, 191)", "#06a0ab", "#06b4ab", "#06c8ab", "#06dcab", "#06f0ab"]];

        $info['data'] = $series['meal'];
        return json_encode($info);
    }

    //获取地图
    public function actionMap()
    {
        $info = ['code' => 200, 'message' => '', 'data' => []];


        //地图
        $series['map'] = $location = $lines = [];
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

        $series['map'] = [
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

        $info['data'] = $series['map'];
        return json_encode($info);
    }
}
