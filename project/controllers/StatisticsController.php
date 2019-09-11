<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use project\models\User;
use project\models\UserLog;
use project\models\ActivityChange;
use project\models\Corporation;
use project\models\CorporationMeal;
use project\models\CorporationIndustry;
use project\models\Industry;
use project\models\Train;
use project\models\CloudSubsidy;
use project\models\UserGroup;
use yii\web\JsExpression;
use project\models\HealthData;
use project\models\ActivityData;

class StatisticsController extends Controller {

    public function actionUser() {
        
        $chart=Yii::$app->siteConfig->business_charts;

        $start = strtotime('-30 days');
        $end = strtotime('today') + 86399;

        if (Yii::$app->request->get('range')) {
            $range = explode('~', Yii::$app->request->get('range'));
            $start = isset($range[0]) ? strtotime($range[0]) : $start;
            $end = isset($range[1]) && (strtotime($range[1]) < $end) ? strtotime($range[1]) + 86399 : $end;
        }
        $series = [];

        //用户趋势
        $day_signup = User::get_day_total('created_at', $start, $end);
        $day_log = UserLog::get_day_total('created_at', $start, $end);
      

        $data_signup = [];
        $data_log = [];
       
       
        for ($i = $start; $i < $end; $i = $i + 86400) {
            $j = date('Y-m-d', $i);
            $y_signup=isset($day_signup[$j]) ? (int) $day_signup[$j] : 0;
            $y_log=isset($day_log[$j]) ? (int) $day_log[$j] : 0;
            $data_signup[] = ['name' => $j, 'y' =>$y_signup , 'value' => [$j,$y_signup]];
            $data_log[] = ['name' => $j, 'y' => $y_log, 'value' => [$j,$y_log]];

        }
    
        if($chart==1){
            $series['day'][] = ['type' => 'line', 'name' => '注册', 'data' => $data_signup];
            $series['day'][] = ['type' => 'line', 'name' => '登录', 'data' => $data_log];            
        }else{
            $series['day'][] = ['type' => 'line', 'name' => '注册','symbol'=>'circle','label'=>['show'=>true,'color'=>'#000'],'symbolSize'=>8, 'data' => $data_signup];
            $series['day'][] = ['type' => 'line', 'name' => '登录', 'symbol'=>'diamond','label'=>['show'=>true,'color'=>'#000'],'symbolSize'=>8,'data' => $data_log];           
        }        
       
        return $this->render('user', ['chart'=>$chart,'series' => $series, 'start' => $start, 'end' => $end]);
    }
    
    public function actionCorporation() {
        
        $chart=Yii::$app->siteConfig->business_charts;
         
        $annual=Yii::$app->request->get('annual',null);
        $group=Yii::$app->request->get('group',null);
        
        //行业
        $series['industry'] = [];
        $drilldown['industry']=[];
        
        $industry_num= CorporationIndustry::get_industry_total($annual,$group);
        $industrys= Industry::find()->where(['id'=> array_keys($industry_num)])->indexBy('id')->all();
        $parent=$sum=$e_chart=[];
        
        foreach($industry_num as $key=>$num){
            if($industrys[$key]['parent_id']){
                $parent[$industrys[$key]['parent_id']][]=[$industrys[$key]['name'],(int)$num];
                $e_chart[$industrys[$key]['parent_id']][]=['name'=>$industrys[$key]['name'],'value'=>(int)$num];
                $sum[$industrys[$key]['parent_id']]=isset($sum[$industrys[$key]['parent_id']])?$sum[$industrys[$key]['parent_id']]+$num:(int)$num;
            }else{
                //$parent[$industrys[$key]['id']][]=[$industrys[$key]['name'],(int)$num];
                $sum[$industrys[$key]['id']]=isset($sum[$industrys[$key]['id']])?$sum[$industrys[$key]['id']]+$num:(int)$num;
            }
        }
        
        $parents= Industry::find()->where(['id'=> array_keys($sum)])->indexBy('id')->all();
        arsort($sum);
        $serie_data=$drilldown_data=$e_parent=$e_child=[];
        foreach($sum as $k=>$s){
            if(isset($parent[$k])){
                $serie_data[]=['name'=>$parents[$k]['name'],'y'=>$s,'drilldown'=>$parents[$k]['name']];
                $drilldown_data[]=['name'=>$parents[$k]['name'],'id'=>$parents[$k]['name'],'data'=>$parent[$k]];
                $e_parent[]=['name'=>$parents[$k]['name'],'value'=>$s];
                $e_child= array_merge($e_child,$e_chart[$k]);
            }else{
                $serie_data[]=['name'=>$parents[$k]['name'],'y'=>$s,'drilldown'=>false];
                $e_parent[]=$e_child[]=['name'=>$parents[$k]['name'],'value'=>$s];                
            }
        }
        if($chart==1){
            $series['industry'][]=['name'=>'一级分类','colorByPoint'=>true,'data'=>$serie_data];
            $drilldown['industry']=['series'=>$drilldown_data];
        }else{
            $series['industry'][]=['type' => 'pie','name'=>'行业分布','radius'=>[0,'30%'],'selectedMode'=>'single', 'label'=>['normal'=>['position'=>'inner']], 'data' => $e_parent];
            $series['industry'][]=['type' => 'pie','name'=>'行业分布','radius'=>['40%','55%'], 'data' =>$e_child,'label'=>['formatter'=>"{b},{c},{d}%"]];
        }
        
        //注册资金
        $series['capital']=[];       
        $data_capital=[];     
        $capitals= Corporation::get_capital_total($annual,$group);
        foreach($capitals as $capital){
            $data_capital[] = ['name' =>  $capital['title'], 'y' => (int) $capital['num'],'value'=>(int) $capital['num']];
        }
        
        if($chart==1){
            $series['capital'][] = ['type' => 'pie','innerSize'=>'50%', 'name' => '数量', 'data' => $data_capital];
        }else{
            $series['capital'][] = ['type' => 'pie','radius'=>['25%','50%'], 'name' => '数量', 'data' => $data_capital,'label'=>['formatter'=>"{d}%",'color'=>'#000']];
        }
        
        //研发规模
        $series['scale']=[];       
        $data_scale=[];     
        $scales= Corporation::get_scale_total($annual,$group);
        foreach($scales as $scale){
            $data_scale[] = ['name' =>  $scale['title'], 'y' => (int) $scale['num'],'value'=>(int) $scale['num']];
        }
        if($chart==1){
            $series['scale'][] = ['type' => 'pie','innerSize'=>'50%', 'name' => '数量', 'data' => $data_scale];
        }else{
            $series['scale'][] = ['type' => 'pie','radius'=>['25%','50%'], 'name' => '数量', 'data' => $data_scale,'label'=>['formatter'=>"{d}%",'color'=>'#000']];
        }
        
        
        //下拨额
        $series['amount'] = [];
        $end = strtotime('today');
        $start = strtotime('-1 year',$end);
        $sum=Yii::$app->request->get('sum',1);//1-天；2-周；3-月
        

        if (Yii::$app->request->get('range')) {
            $range = explode('~', Yii::$app->request->get('range'));
            $start = isset($range[0]) ? strtotime($range[0]) : $start;
            $end = isset($range[1]) && (strtotime($range[1]) < $end) ? strtotime($range[1]) : $end;
        }

        $allocate_total= CorporationMeal::get_amount_total($start,$end,$sum,0,$annual,$group);
        $base_amount= (float)CorporationMeal::get_amount_base($start,$annual,$group);
               
        $cloud_total= CloudSubsidy::get_amount_total($start,$end,$sum,$annual,$group);
        $base_cloud=$base_cloud_cost=(float)CloudSubsidy::get_amount_base($start,$annual,$group);
        
        $cache = Yii::$app->cache;
        if(!$group){
            $group_id= implode(',',UserGroup::get_user_groupid(Yii::$app->user->identity->id));
        }else{
            $group_id=$group;
        }
        $cost_total = $cache->get('allocate_cost_'.$annual.'_'.$group_id);
        if ($cost_total === false) {
            $cost_total=[];
        }

        $data_allocate_amount = [];
        $data_allocate_num=[];
        $data_cloud_amount = [];
        $data_cloud_num = [];
        if($sum==1){                
            //天

            $amount_num_start=($allocate_total?strtotime(key($allocate_total)):$start)-86400;
            $cloud_num_start=($cloud_total?strtotime(key($cloud_total)):($allocate_total?strtotime(key($allocate_total)):$start))-86400;
            if($amount_num_start<=$cloud_num_start){                               
                if($allocate_total||$base_amount){
                    for ($i = $amount_num_start; $i <= $end; $i = $i + 86400) {
                        $k=date('Y-m-d', $i);
                        $j = $end-$amount_num_start>=365*86400?date('Y.n.j', $i):date('n.j', $i);
                        $base_amount=isset($allocate_total[$k]['amount']) ? (float) $allocate_total[$k]['amount']+$base_amount : $base_amount;
                        $y_allocate_amount=$base_amount/10000;
                        $data_allocate_amount[] = ['name' => $j, 'y' => $y_allocate_amount,'value' =>[$j,$y_allocate_amount]]; 
                    }
                    if($chart==1){
                        $series['amount'][] = ['type' => 'area','zIndex'=>1, 'name' => '累计下拨额','color'=>'#7CB5EC', 'data' => $data_allocate_amount];
                    }else{
                        $series['amount'][] = ['type' => 'line','areaStyle'=>[], 'z'=>1,'name' => '累计下拨额','data' => $data_allocate_amount]; 
                    }
                }
                if($cloud_total||$base_cloud){
                    for ($i = $cloud_num_start; $i <= $end; $i = $i + 86400) {
                        $k=date('Y-m-d', $i);
                        $j = $end-$amount_num_start>=365*86400?date('Y.n.j', $i):date('n.j', $i);                  
                        $base_cloud=isset($cloud_total[$k]['amount']) ? (float) $cloud_total[$k]['amount']+$base_cloud : $base_cloud;
                        $y_cloud_amount=$base_cloud/10000;
                        $data_cloud_amount[] = ['name' => $j, 'y' =>$y_cloud_amount, 'value' =>[$j,$y_cloud_amount]];
                    }
                    if($chart==1){
                        $series['amount'][] = ['type' => 'area','zIndex'=>3, 'name' => '累计公有云补贴','color'=>'#F7A35C', 'data' => $data_cloud_amount];
                    }else{
                        $series['amount'][] = ['type' => 'line','areaStyle'=>[], 'z'=>3, 'name' => '累计公有云补贴', 'data' => $data_cloud_amount];          
                    }
                }
                              
            }else{
                
                if($cloud_total||$base_cloud){
                    for ($i = $cloud_num_start; $i <= $end; $i = $i + 86400) {
                        $k=date('Y-m-d', $i);
                        $j = $end-$cloud_num_start>=365*86400?date('Y.n.j', $i):date('n.j', $i);                  
                        $base_cloud=isset($cloud_total[$k]['amount']) ? (float) $cloud_total[$k]['amount']+$base_cloud : $base_cloud;
                        $y_cloud_amount=$base_cloud/10000;
                        $data_cloud_amount[] = ['name' => $j, 'y' =>$y_cloud_amount, 'value' =>[$j,$y_cloud_amount]];
                    }
                    if($chart==1){
                        $series['amount'][] = ['type' => 'area','zIndex'=>3, 'name' => '累计公有云补贴','color'=>'#F7A35C', 'data' => $data_cloud_amount];                            
                    }else{
                        $series['amount'][] = ['type' => 'line','areaStyle'=>[], 'z'=>3, 'name' => '累计公有云补贴','data' => $data_cloud_amount];          
                    }
                }

                if($allocate_total||$base_amount){
                    for ($i = $amount_num_start; $i <= $end; $i = $i + 86400) {
                        $k=date('Y-m-d', $i);
                        $j = $end-$cloud_num_start>=365*86400?date('Y.n.j', $i):date('n.j', $i);
                        $base_amount=isset($allocate_total[$k]['amount']) ? (float) $allocate_total[$k]['amount']+$base_amount : $base_amount;
                        $y_allocate_amount=$base_amount/10000;
                        $data_allocate_amount[] = ['name' => $j, 'y' => $y_allocate_amount,'value' =>[$j,$y_allocate_amount]]; 
                    }
                    if($chart==1){
                        $series['amount'][] = ['type' => 'area','zIndex'=>1, 'name' => '累计下拨额','color'=>'#7CB5EC', 'data' => $data_allocate_amount];
                    }else{
                        $series['amount'][] = ['type' => 'line','areaStyle'=>[], 'z'=>1,'name' => '累计下拨额', 'data' => $data_allocate_amount]; 
                    }
                }
           
            }

            $old_cost_num= count($cost_total);            
            for ($i = $amount_num_start<=$cloud_num_start?$amount_num_start:$cloud_num_start; $i <= $end; $i = $i + 86400){                  
                $j = $end-($amount_num_start<=$cloud_num_start?$amount_num_start:$cloud_num_start)>=365*86400?date('Y.n.j', $i):date('n.j', $i);                    
                if(isset($cost_total[$i])){
                    $cost=$cost_total[$i];
                }else{
                    $k=date('Y-m-d', $i);
                    $base_cloud_cost=isset($cloud_total[$k]['amount']) ? (float) $cloud_total[$k]['amount']+$base_cloud_cost : $base_cloud_cost;
                    $cost= sprintf("%.0f", (float) CorporationMeal::get_cost_total($i,$annual,$group))+$base_cloud_cost;
                    $cost_total[$i]=$cost;
                }
                $data_amount_cost[] = ['name' => $j, 'y' => round($cost/10000,2), 'value' =>[$j, round($cost/10000,2)]];
            }
            if($chart==1){
                $series['amount'][] = ['type' => 'areaspline','zIndex'=>2, 'name' => '累计消耗额','color'=>'#90EE7E', 'data' => $data_amount_cost];
            }else{
                $series['amount'][] = ['type' => 'line','areaStyle'=>[],'z'=>2,'smooth'=>true, 'name' => '累计消耗额', 'data' => $data_amount_cost];
            }
            if($old_cost_num!=count($cost_total)){
                $query = CorporationMeal::find()->select(['SUM(amount)'])->andWhere(['group_id'=> explode(',', $group_id)])->andFilterWhere(['annual'=>$annual])->createCommand()->getRawSql();
                $dependency = new \yii\caching\DbDependency(['sql' => $query]);
                $cache->set('allocate_cost_'.$annual.'_'.$group_id, $cost_total, null, $dependency);
            }

        }elseif($sum==2){
            //周
            $amount_num_start=($allocate_total?strtotime(key($allocate_total)):strtotime(strftime("%Y-W%W",$start)));
            $cloud_num_start=($cloud_total?strtotime(key($cloud_total)):($allocate_total?strtotime(key($allocate_total)):strtotime(strftime("%Y-W%W",$start))));
            if($amount_num_start<=$cloud_num_start){
              
                if($allocate_total||$base_amount){
                    for ($i = $amount_num_start; $i <= $end; $i = $i + 86400*7) {
                        $k=strftime("%Y-W%W",$i);
                        $l=($i + 86400*6)<$end?($i + 86400*6):$end;
                        $j = $end-$amount_num_start>=365*86400?date('Y.n.j', $i).'-'.date('Y.n.j', $l):date('n.j', $i).'-'.date('n.j', $l);
                        //$base_amount=isset($amount_num[$k]) ? (float) $amount_num[$k]['num']+$base_amount : $base_amount;
                        $y_allocate_amount=isset($allocate_total[$k]['amount']) ? (float) $allocate_total[$k]['amount']/10000 :0;
                        $y_allocate_num=isset($allocate_total[$k]['num']) ? (float) $allocate_total[$k]['num'] :0;
                        $data_allocate_amount[] = ['name' => $j, 'y' =>$y_allocate_amount,'value' =>[$j,$y_allocate_amount]];                            
                        $data_allocate_num[] = ['name' => $j, 'y' =>$y_allocate_num,'value' =>[$j, $y_allocate_num]];
                    }
                    if($chart==1){
                        $series['amount'][] = ['type' => 'line','zIndex'=>3, 'name' => '当期下拨额', 'data' => $data_allocate_amount];
                        $series['amount'][] = ['type' => 'column','zIndex'=>1, 'name' => '当期下拨数', 'data' => $data_allocate_num,'yAxis'=>1,];
                    }else{
                        $series['amount'][] = ['type' => 'line','z'=>3, 'name' => '当期下拨额','data' => $data_allocate_amount];
                        $series['amount'][] = ['type' => 'bar','z'=>1, 'name' => '当期下拨数','label'=>['show'=>true,'color'=>'#000','position'=>'top','formatter'=>new JsExpression("function(params) {if (params.value[1] > 0) {return params.value[1];} else {return ''}}")], 'data' => $data_allocate_num,'yAxisIndex'=>1,];
                    }
                }

                if($cloud_total||$base_cloud){
                    for ($i = $cloud_num_start; $i <= $end; $i = $i + 86400*7) {
                        $k=strftime("%Y-W%W",$i);
                        $l=($i + 86400*6)<$end?($i + 86400*6):$end;
                        $j = $end-$amount_num_start>=365*86400?date('Y.n.j', $i).'-'.date('Y.n.j', $l):date('n.j', $i).'-'.date('n.j', $l);
                        //$base_amount=isset($amount_num[$k]) ? (float) $amount_num[$k]['num']+$base_amount : $base_amount;
                        $y_cloud_amount=isset($cloud_total[$k]['amount']) ? (float) $cloud_total[$k]['amount']/10000 :0;
                        $y_cloud_num=isset($cloud_total[$k]['num']) ? (float) $cloud_total[$k]['num'] :0;
                        $data_cloud_amount[] = ['name' => $j, 'y' =>$y_cloud_amount, 'value' => [$j,$y_cloud_amount]];
                        $data_cloud_num[] = ['name' => $j, 'y' =>$y_cloud_num, 'value' => [$j,$y_cloud_num]];
                    }
                    if($chart==1){
                        $series['amount'][] = ['type' => 'line','zIndex'=>4, 'name' => '当期公有云补贴额', 'data' => $data_cloud_amount];
                        $series['amount'][] = ['type' => 'column','zIndex'=>2, 'name' => '当期公有云补贴数', 'data' => $data_cloud_num,'yAxis'=>1,];
                    }else{
                        $series['amount'][] = ['type' => 'line','z'=>4, 'name' => '当期公有云补贴额', 'data' => $data_cloud_amount];
                        $series['amount'][] = ['type' => 'bar','z'=>2, 'name' => '当期公有云补贴数','label'=>['show'=>true,'color'=>'#000','position'=>'top','formatter'=>new JsExpression("function(params) {if (params.value[1] > 0) {return params.value[1];} else {return ''}}")],  'data' => $data_cloud_num,'yAxisIndex'=>1,];
                     }
                }

            }else{
               
                if($cloud_total||$base_cloud){
                    for ($i = $cloud_num_start; $i <= $end; $i = $i + 86400*7) {
                        $k=strftime("%Y-W%W",$i);
                        $l=($i + 86400*6)<$end?($i + 86400*6):$end;
                        $j = $end-$cloud_num_start>=365*86400?date('Y.n.j', $i).'-'.date('Y.n.j', $l):date('n.j', $i).'-'.date('n.j', $l);
                        //$base_amount=isset($amount_num[$k]) ? (float) $amount_num[$k]['num']+$base_amount : $base_amount;
                        $y_cloud_amount=isset($cloud_total[$k]['amount']) ? (float) $cloud_total[$k]['amount']/10000 :0;
                        $y_cloud_num=isset($cloud_total[$k]['num']) ? (float) $cloud_total[$k]['num'] :0;
                        $data_cloud_amount[] = ['name' => $j, 'y' =>$y_cloud_amount, 'value' => [$j,$y_cloud_amount]];
                        $data_cloud_num[] = ['name' => $j, 'y' =>$y_cloud_num, 'value' => [$j,$y_cloud_num]];
                    }
                    if($chart==1){
                        $series['amount'][] = ['type' => 'line','zIndex'=>3, 'name' => '当期公有云补贴额', 'data' => $data_cloud_amount];
                        $series['amount'][] = ['type' => 'column','zIndex'=>1, 'name' => '当期公有云补贴数', 'data' => $data_cloud_num,'yAxis'=>1,];
                    }else{
                        $series['amount'][] = ['type' => 'line','z'=>3, 'name' => '当期公有云补贴额', 'data' => $data_cloud_amount];
                        $series['amount'][] = ['type' => 'bar','z'=>1, 'name' => '当期公有云补贴数','label'=>['show'=>true,'color'=>'#000','position'=>'top','formatter'=>new JsExpression("function(params) {if (params.value[1] > 0) {return params.value[1];} else {return ''}}")],  'data' => $data_cloud_num,'yAxisIndex'=>1,];
                    }
                }
                if($allocate_total||$base_amount){
                    for ($i = $amount_num_start; $i <= $end; $i = $i + 86400*7) {
                        $k=strftime("%Y-W%W",$i);
                        $l=($i + 86400*6)<$end?($i + 86400*6):$end;
                        $j = $end-$cloud_num_start>=365*86400?date('Y.n.j', $i).'-'.date('Y.n.j', $l):date('n.j', $i).'-'.date('n.j', $l);
                        //$base_amount=isset($amount_num[$k]) ? (float) $amount_num[$k]['num']+$base_amount : $base_amount;              
                        $y_allocate_amount=isset($allocate_total[$k]['amount']) ? (float) $allocate_total[$k]['amount']/10000 :0;
                        $y_allocate_num=isset($allocate_total[$k]['num']) ? (float) $allocate_total[$k]['num'] :0;
                        $data_allocate_amount[] = ['name' => $j, 'y' =>$y_allocate_amount,'value' =>[$j,$y_allocate_amount]];                            
                        $data_allocate_num[] = ['name' => $j, 'y' =>$y_allocate_num,'value' =>[$j, $y_allocate_num]];
                    }
                    if($chart==1){
                        $series['amount'][] = ['type' => 'line','zIndex'=>4, 'name' => '当期下拨额', 'data' => $data_allocate_amount];
                        $series['amount'][] = ['type' => 'column','zIndex'=>2, 'name' => '当期下拨数', 'data' => $data_allocate_num,'yAxis'=>1,];
                    }else{
                        $series['amount'][] = ['type' => 'line','z'=>4, 'name' => '当期下拨额','data' => $data_allocate_amount];
                        $series['amount'][] = ['type' => 'bar','z'=>2, 'name' => '当期下拨数','label'=>['show'=>true,'color'=>'#000','position'=>'top','formatter'=>new JsExpression("function(params) {if (params.value[1] > 0) {return params.value[1];} else {return ''}}")], 'data' => $data_allocate_num,'yAxisIndex'=>1,];
                    }
                }
          
            }

            $old_cost_num= count($cost_total);
            for ($i = $amount_num_start<=$cloud_num_start?$amount_num_start:$cloud_num_start; $i <= $end; $i = $i + 86400*7){
                $l=($i + 86400*6)<$end?($i + 86400*6):$end;
                $j = $end-($amount_num_start<=$cloud_num_start?$amount_num_start:$cloud_num_start)>=365*86400?date('Y.n.j', $i).'-'.date('Y.n.j', $l):date('n.j', $i).'-'.date('n.j', $l);

                if(!isset($cost_total[$i])){                       
                    $cost_total[$i]= sprintf("%.0f", (float) CorporationMeal::get_cost_total($i,$annual,$group))+(float)CloudSubsidy::get_amount_base($i,$annual,$group);
                }
                if(!isset($cost_total[$l+86400])){
                   $cost_total[$l+86400]= sprintf("%.0f", (float) CorporationMeal::get_cost_total($l+86400,$annual,$group))+(float)CloudSubsidy::get_amount_base($l+86400,$annual,$group);
                }


                $cost=$cost_total[$l+86400]-$cost_total[$i];
                $data_amount_cost[] = ['name' => $j, 'y' => round($cost/10000,2),'value'=>[$j,round($cost/10000,2)]];

            }
            if($chart==1){
                $series['amount'][] = ['type' => 'spline','zIndex'=>5, 'name' => '当期消耗额', 'data' => $data_amount_cost];
            }else{
                $series['amount'][] = ['type' => 'line','smooth'=>true,'z'=>5, 'name' => '当期消耗额', 'data' => $data_amount_cost];
            }
            if($old_cost_num!=count($cost_total)){
                $query = CorporationMeal::find()->select(['SUM(amount)'])->andWhere(['group_id'=> explode(',', $group_id)])->andFilterWhere(['annual'=>$annual])->createCommand()->getRawSql();
                $dependency = new \yii\caching\DbDependency(['sql' => $query]);
                $cache->set('allocate_cost_'.$annual.'_'.$group_id, $cost_total, null, $dependency);
            }
        }else{
            //月
            $amount_num_start=($allocate_total?strtotime(key($allocate_total)):strtotime(date("Y-m",$start)));
            $cloud_num_start=($cloud_total?strtotime(key($cloud_total)):($allocate_total?strtotime(key($allocate_total)):strtotime(date("Y-m",$start))));

            if($amount_num_start<=$cloud_num_start){
                if($allocate_total||$base_amount){
                    for ($i = $amount_num_start; $i <= $end; $i= strtotime('+1 months',$i)) {
                        $k=date("Y-m",$i);
                        $j = date('Y.n', $i);
    //                      $base_amount=isset($amount_num[$k]) ? (float) $amount_num[$k]['num']+$base_amount : $base_amount;
                        $y_allocate_amount=isset($allocate_total[$k]['amount']) ? (float) $allocate_total[$k]['amount']/10000 :0;
                        $y_allocate_num=isset($allocate_total[$k]['num']) ? (float) $allocate_total[$k]['num'] :0;
                        $data_allocate_amount[] = ['name' => $j, 'y' =>$y_allocate_amount,'value' =>[$j,$y_allocate_amount]];                            
                        $data_allocate_num[] = ['name' => $j, 'y' =>$y_allocate_num,'value' =>[$j, $y_allocate_num]];
                    }
                    if($chart==1){
                        $series['amount'][] = ['type' => 'line','zIndex'=>3, 'name' => '当期下拨额', 'data' => $data_allocate_amount];
                        $series['amount'][] = ['type' => 'column','zIndex'=>1, 'name' => '当期下拨数', 'data' => $data_allocate_num,'yAxis'=>1,];
                    }else{
                        $series['amount'][] = ['type' => 'line','z'=>3, 'name' => '当期下拨额','data' => $data_allocate_amount];
                        $series['amount'][] = ['type' => 'bar','z'=>1, 'name' => '当期下拨数','label'=>['show'=>true,'color'=>'#000','position'=>'top','formatter'=>new JsExpression("function(params) {if (params.value[1] > 0) {return params.value[1];} else {return ''}}")], 'data' => $data_allocate_num,'yAxisIndex'=>1,];
                    }
                }

                if($cloud_total||$base_cloud){
                    for ($i = $cloud_num_start; $i <= $end; $i= strtotime('+1 months',$i)) {
                        $k=date("Y-m",$i);
                        $j = date('Y.n', $i);
//                      $base_amount=isset($amount_num[$k]) ? (float) $amount_num[$k]['num']+$base_amount : $base_amount;
                        $y_cloud_amount=isset($cloud_total[$k]['amount']) ? (float) $cloud_total[$k]['amount']/10000 :0;
                        $y_cloud_num=isset($cloud_total[$k]['num']) ? (float) $cloud_total[$k]['num'] :0;
                        $data_cloud_amount[] = ['name' => $j, 'y' =>$y_cloud_amount, 'value' => [$j,$y_cloud_amount]];
                        $data_cloud_num[] = ['name' => $j, 'y' =>$y_cloud_num, 'value' => [$j,$y_cloud_num]];
                    }
                    if($chart==1){
                        $series['amount'][] = ['type' => 'line','zIndex'=>4, 'name' => '当期公有云补贴额', 'data' => $data_cloud_amount];
                        $series['amount'][] = ['type' => 'column','zIndex'=>2, 'name' => '当期公有云补贴数', 'data' => $data_cloud_num,'yAxis'=>1,];
                    }else{
                        $series['amount'][] = ['type' => 'line','z'=>4, 'name' => '当期公有云补贴额', 'data' => $data_cloud_amount];
                        $series['amount'][] = ['type' => 'bar','z'=>2, 'name' => '当期公有云补贴数','label'=>['show'=>true,'color'=>'#000','position'=>'top','formatter'=>new JsExpression("function(params) {if (params.value[1] > 0) {return params.value[1];} else {return ''}}")],  'data' => $data_cloud_num,'yAxisIndex'=>1,];
                    }
                }
            }else{
                if($cloud_total||$base_cloud){
                    for ($i = $cloud_num_start; $i <= $end; $i= strtotime('+1 months',$i)) {
                        $k=date("Y-m",$i);
                        $j = date('Y.n', $i);
//                      $base_amount=isset($amount_num[$k]) ? (float) $amount_num[$k]['num']+$base_amount : $base_amount;
                        $y_cloud_amount=isset($cloud_total[$k]['amount']) ? (float) $cloud_total[$k]['amount']/10000 :0;
                        $y_cloud_num=isset($cloud_total[$k]['num']) ? (float) $cloud_total[$k]['num'] :0;
                        $data_cloud_amount[] = ['name' => $j, 'y' =>$y_cloud_amount, 'value' => [$j,$y_cloud_amount]];
                        $data_cloud_num[] = ['name' => $j, 'y' =>$y_cloud_num, 'value' => [$j,$y_cloud_num]];
                    }
                    if($chart==1){
                        $series['amount'][] = ['type' => 'line','zIndex'=>3, 'name' => '当期公有云补贴额', 'data' => $data_cloud_amount];
                        $series['amount'][] = ['type' => 'column','zIndex'=>1, 'name' => '当期公有云补贴数', 'data' => $data_cloud_num,'yAxis'=>1,];
                    }else{
                        $series['amount'][] = ['type' => 'line','z'=>3, 'name' => '当期公有云补贴额', 'data' => $data_cloud_amount];
                        $series['amount'][] = ['type' => 'bar','z'=>1, 'name' => '当期公有云补贴数','label'=>['show'=>true,'color'=>'#000','position'=>'top','formatter'=>new JsExpression("function(params) {if (params.value[1] > 0) {return params.value[1];} else {return ''}}")],  'data' => $data_cloud_num,'yAxisIndex'=>1,];
                    }
                }
                if($allocate_total||$base_amount){
                    for ($i = $amount_num_start; $i <= $end; $i= strtotime('+1 months',$i)) {
                        $k=date("Y-m",$i);
                        $j = date('Y.n', $i);
//                      $base_amount=isset($amount_num[$k]) ? (float) $amount_num[$k]['num']+$base_amount : $base_amount;
                        $y_allocate_amount=isset($allocate_total[$k]['amount']) ? (float) $allocate_total[$k]['amount']/10000 :0;
                        $y_allocate_num=isset($allocate_total[$k]['num']) ? (float) $allocate_total[$k]['num'] :0;
                        $data_allocate_amount[] = ['name' => $j, 'y' =>$y_allocate_amount,'value' =>[$j,$y_allocate_amount]];                            
                        $data_allocate_num[] = ['name' => $j, 'y' =>$y_allocate_num,'value' =>[$j, $y_allocate_num]];
                    }
                    if($chart==1){
                        $series['amount'][] = ['type' => 'line','zIndex'=>4, 'name' => '当期下拨额', 'data' => $data_allocate_amount];
                        $series['amount'][] = ['type' => 'column','zIndex'=>2, 'name' => '当期下拨数', 'data' => $data_allocate_num,'yAxis'=>1,];
                    }else{
                        $series['amount'][] = ['type' => 'line','z'=>4, 'name' => '当期下拨额','data' => $data_allocate_amount];
                        $series['amount'][] = ['type' => 'bar','z'=>2, 'name' => '当期下拨数','label'=>['show'=>true,'color'=>'#000','position'=>'top','formatter'=>new JsExpression("function(params) {if (params.value[1] > 0) {return params.value[1];} else {return ''}}")], 'data' => $data_allocate_num,'yAxisIndex'=>1,];
                    }
                }
            }

            $old_cost_num= count($cost_total);
            for ($i = $amount_num_start<=$cloud_num_start?$amount_num_start:$cloud_num_start; $i <= $end; $i = strtotime('+1 months',$i)){
                $l=strtotime('+1 months',$i)-86400<$end?strtotime('+1 months',$i)-86400:$end;
                $j = date('Y.n', $i);                                      
                if(!isset($cost_total[$i])){                       
                    $cost_total[$i]= sprintf("%.0f", (float) CorporationMeal::get_cost_total($i,$annual,$group))+(float)CloudSubsidy::get_amount_base($i,$annual,$group);
                }
                if(!isset($cost_total[$l+86400])){
                   $cost_total[$l+86400]= sprintf("%.0f", (float) CorporationMeal::get_cost_total($l+86400,$annual,$group))+(float)CloudSubsidy::get_amount_base($l+86400,$annual,$group);
                }

                $cost=$cost_total[$l+86400]-$cost_total[$i];
                $data_amount_cost[] = ['name' => $j, 'y' => round($cost/10000,2),'value'=>[$j,round($cost/10000,2)]];

            }
            if($chart==1){
                $series['amount'][] = ['type' => 'spline','zIndex'=>5, 'name' => '当期消耗额', 'data' => $data_amount_cost];
            }else{
                $series['amount'][] = ['type' => 'line','smooth'=>true,'z'=>5, 'name' => '当期消耗额', 'data' => $data_amount_cost];
            }
            if($old_cost_num!=count($cost_total)){
                $query = CorporationMeal::find()->select(['SUM(amount)'])->andWhere(['group_id'=> explode(',', $group_id)])->andFilterWhere(['annual'=>$annual])->createCommand()->getRawSql();
                $dependency = new \yii\caching\DbDependency(['sql' => $query]);
                $cache->set('allocate_cost_'.$annual.'_'.$group_id, $cost_total, null, $dependency);
            }
        }
            
        //下拨金额百分比
        $series['allocate_num']=[]; 
        $data_allocate=[];     
        $allocate_num= CorporationMeal::get_allocate_num($start,$end,$annual,$group);
        foreach($allocate_num as $allocate){
            $data_allocate[] = ['name' =>floatval($allocate['amount']/10000).'万', 'y' => (int) $allocate['num'],'value'=>(int) $allocate['num']];
        }
        if($chart==1){
            $series['allocate_num'][] = ['type' => 'pie','innerSize'=>'50%', 'name' => '数量', 'data' => $data_allocate];
        }else{
            $series['allocate_num'][] = ['type' => 'pie','radius'=>['25%','50%'], 'name' => '数量', 'data' => $data_allocate,'label'=>['formatter'=>"{c}家,{d}%",'color'=>'#000']];
        }
        
        //BD下拨金额
        $series['allocate_bd']=[];       
        $data_allocate_bd=[]; 
        $changes=[];
        $bds=[];
        $allocate_bd= CorporationMeal::get_amount_total($start,$end,3,1,$annual,$group);
        
        $groups = User::get_bd_color();
            
        foreach($allocate_bd as $allocate){
            $allocate['bd']=$allocate['bd']?$allocate['bd']:0;               
            $changes[$allocate['time']][$allocate['bd']]=(float) $allocate['amount']; 
            $bds[$allocate['bd']]=(isset($bds[$allocate['bd']])?$bds[$allocate['bd']]:0)+(float) $allocate['amount'];
        }
        ksort($changes);
        arsort($bds);
        
  
        foreach($changes as $key=>$change){
            foreach($bds as $b=>$bd){
                $y_allocate_bd=isset($change[$b])?$change[$b]/10000:0;
                $data_allocate_bd[$key][] = ['name' =>$b?$groups[$b]['name']:'未分配', 'y' =>$y_allocate_bd,'value'=>[$y_allocate_bd,$b?$groups[$b]['name']:'未分配']];
            }
        
        }
        if($chart==1){
            foreach($data_allocate_bd as $k=>$allocate){
                $series['allocate_bd'][] = ['type' => 'bar', 'name' => $k, 'data' => $allocate];
            }
        }else{
            foreach($data_allocate_bd as $k=>$allocate){
                $series['allocate_bd'][] = ['type' => 'bar', 'name' => $k,'stack'=>$allocate[0]['name'], 'data' => array_reverse($allocate)];

            }
        }
        
     
        return $this->render('corporation', ['chart'=>$chart,'series' => $series,'drilldown'=>$drilldown, 'start' => $start, 'end' => $end,'sum'=>$sum,'annual'=>$annual,'group'=>$group]);
        
    }
    
    public function actionActivity() {

        $chart=Yii::$app->siteConfig->business_charts;
        $end = strtotime('today');
        $start = strtotime('-1 months +1 days',$end);
        $sum=Yii::$app->request->get('sum',1);
        $total=Yii::$app->request->get('total',1);
        $annual=Yii::$app->request->get('annual');
        $group=Yii::$app->request->get('group',null);
        $allocate=Yii::$app->request->get('allocate',ActivityChange::ALLOCATE_Y)?ActivityChange::ALLOCATE_Y:null;
        if(!$group){
            $group_id=UserGroup::get_user_groupid(Yii::$app->user->identity->id);
            if(count($group_id)>0){
                $group=$group_id[0];
            }
        }

        if (Yii::$app->request->get('range')) {
            $range = explode('~', Yii::$app->request->get('range'));
            $start = isset($range[0]) ? strtotime($range[0]) : $start;
            $end = isset($range[1]) && (strtotime($range[1]) < $end) ? strtotime($range[1]): $end;
        }
        $series['activity'] = [];
        
        //活跃数
        $activity_total = ActivityChange::get_activity_total($start-86400, $end,$sum,$total,$annual,false,$group,$allocate);
        $activity_change = ActivityChange::get_activity_total($start-86400, $end,$sum,$total,$annual,true,$group,$allocate);
        if($total==1){
           
            $data_total = [];
            $data_change = [];
            $data_per = [];
            foreach($activity_change as $change){
                $changes[date('Y.n.j',$change['start_time']+86400).'-'.date('Y.n.j',$change['end_time'])]=(int) $change['num'];                
            }
            foreach($activity_total as $row){
                $key=date('Y.n.j',$row['start_time']+86400).'-'.date('Y.n.j',$row['end_time']);
                $j = $end-$start>=365*86400?date('Y.n.j', $row['start_time']+86400).'-'.date('Y.n.j', $row['end_time']):date('n.j', $row['start_time']+86400).'-'.date('n.j', $row['end_time']);
                $data_total[]=['name' =>$j , 'y' =>  (int) $row['num'], 'value' =>  [$j,(int) $row['num']]];
                $data_change[]=['name' => $j, 'y' =>  isset($changes[$key])?$changes[$key]:0, 'value' =>  [$j,isset($changes[$key])?$changes[$key]:0]];
                $data_per[]=['name' => $j, 'y' => isset($changes[$key])?round($changes[$key]/(int)$row['num']*100,2):0,'value' => [$j,isset($changes[$key])?round($changes[$key]/(int)$row['num']*100,2):0]];               
            }
            
            if($chart==1){
                $series['activity'][] = ['type' => 'column', 'name' => '下拨企业数', 'data' => $data_total,'grouping'=>false,'borderWidth'=>0,'shadow'=>false];
                $series['activity'][] = ['type' => 'column', 'name' => '活跃企业数', 'data' => $data_change,'grouping'=>false,'borderWidth'=>0,'shadow'=>false,'dataLabels'=>['inside'=>true]];
                $series['activity'][] = ['type' => 'spline', 'name' => '活跃率', 'data' => $data_per,'tooltip'=>['valueSuffix'=>'%'],'yAxis'=>1, 'dataLabels'=>['allowOverlap'=>true]];   
            }else{
                $series['activity'][] = ['type' => 'bar', 'name' => '下拨企业数', 'data' => $data_total,'label'=>['show'=>true,'position'=>'top']];
                $series['activity'][] = ['type' => 'bar', 'name' => '活跃企业数', 'data' => $data_change,'label'=>['show'=>true],'barGap'=>'-100%'];
                $series['activity'][] = ['type' => 'line','smooth'=>true, 'name' => '活跃率', 'data' => $data_per,'yAxisIndex'=>1,'label'=>['show'=>true,'formatter'=>"{@[1]}%",'color'=>'#000']];   
            }
        }else{
            
            $data_change=[];
            $data_per = [];
            $groups = User::get_bd_color();
            
            foreach($activity_change as $change){
                $change['bd_id']=$change['bd_id']?$change['bd_id']:0;
                $start_time=$change['start_time'];
                $end_time=$change['end_time'];
//                $start_time=date('n.j',$change['start_time']+86400);
//                $end_time=date('n.j',$change['end_time']);
                if($sum){
                    $et=date('Y.n.j',$change['end_time']);//次
                }else{
                    $et=date('Y.n',$change['end_time']);//月
                }
                $changes[$et][$change['bd_id']]=(int) $change['num'];
                $changes[$et]['start_time']=!isset($changes[$et]['start_time'])||$start_time<$changes[$et]['start_time']?$start_time:$changes[$et]['start_time'];
                $changes[$et]['end_time']=!isset($changes[$et]['end_time'])||$end_time>$changes[$et]['end_time']?$end_time:$changes[$et]['end_time'];
            }
            

            foreach($activity_total as $row){
                if($sum){
                    $et2=date('Y.n.j',$row['end_time']);
                }else{
                    $et2=date('Y.n',$row['end_time']);
                }
               
                $key=$end-$start>=365*86400?date('Y.n.j',$changes[$et2]['start_time']+86400).'-'.date('Y.n.j',$changes[$et2]['end_time']):date('n.j',$changes[$et2]['start_time']+86400).'-'.date('n.j',$changes[$et2]['end_time']);
//              $key=$changes[$et2]['start_time'].'-'.$changes[$et2]['end_time'];                
                $row['bd_id']=$row['bd_id']?$row['bd_id']:0;
                $y_change=isset($changes[$et2][$row['bd_id']])?$changes[$et2][$row['bd_id']]:0;
                $y_per=isset($changes[$et2][$row['bd_id']])?round($changes[$et2][$row['bd_id']]/(int)$row['num']*100,2):0;
                $data_change[$row['bd_id']][]=['name' => $key, 'y' => $y_change ,'value'=>[$key,$y_change]];
                $data_per[$row['bd_id']][]=['name' => $key, 'y' =>$y_per,'value'=>[$key,$y_per]];               
            }
            
            if($chart==1){
                foreach ($data_change as $gid=>$data){
                    $series['activity'][] = ['type' => 'column', 'name' => $gid&&isset($groups[$gid]['name'])?$groups[$gid]['name']:'未分配', 'data' => $data,'color'=>$gid&&isset($groups[$gid]['color'])?'#'.$groups[$gid]['color']:'#FF0000'];

                }
                foreach ($data_per as $gid=>$per){
                     $series['activity'][] = ['type' => 'spline', 'name' => $gid&&isset($groups[$gid]['name'])?$groups[$gid]['name']:'未分配', 'data' => $per,'tooltip'=>['valueSuffix'=>'%'],'yAxis'=>1,'color'=>$gid&&isset($groups[$gid]['color'])?'#'.$groups[$gid]['color']:'#FF0000'];
                }
            }else{
                foreach ($data_change as $gid=>$data){
                    $series['activity'][] = ['type' => 'bar', 'name' => $gid&&isset($groups[$gid]['name'])?$groups[$gid]['name']:'未分配', 'data' => $data,'color'=>$gid&&isset($groups[$gid]['color'])?'#'.$groups[$gid]['color']:'#FF0000','label'=>['show'=>true]];

                }
                foreach ($data_per as $gid=>$per){
                     $series['activity'][] = ['type' => 'line','smooth'=>true, 'name' => $gid&&isset($groups[$gid]['name'])?$groups[$gid]['name']:'未分配', 'data' => $per,'yAxisIndex'=>1,'color'=>$gid&&isset($groups[$gid]['color'])?'#'.$groups[$gid]['color']:'#FF0000'];
                }
            }
                               
        }
        
        
        //用户数
        $activity_user= ActivityData::get_user_total($start, $end, $total, $annual, $group, $allocate);
        foreach($activity_user as $row){
            $j=date('Y.n.j', $row['statistics_time']);
            $data_total_num[]=['name' =>$j , 'y' =>  (int) $row['total_num'], 'value' =>  [$j,(int) $row['total_num']]];
            $data_user_num[]=['name' => $j, 'y' =>  (int) $row['user_num'], 'value' =>  [$j,(int) $row['user_num']]];
            $data_user_per[]=['name' => $j, 'y' => isset($row['user_num'])&&isset($row['total_num'])&&(int)$row['total_num']>0?round((int)$row['user_num']/(int)$row['total_num']*100,2):0,'value' => [$j,isset($row['user_num'])&&isset($row['total_num'])&&(int)$row['total_num']>0?round((int)$row['user_num']/(int)$row['total_num']*100,2):0]];
        }
        
        if($chart==1){
            $series['user'][] = ['type' => 'column', 'name' => '下拨用户数', 'data' => $data_total_num,'grouping'=>false,'borderWidth'=>0,'shadow'=>false];
            $series['user'][] = ['type' => 'column', 'name' => '实际用户数', 'data' => $data_user_num,'grouping'=>false,'borderWidth'=>0,'shadow'=>false,'dataLabels'=>['inside'=>true]];
            $series['user'][] = ['type' => 'spline', 'name' => '用户占比', 'data' => $data_user_per,'tooltip'=>['valueSuffix'=>'%'],'yAxis'=>1, 'dataLabels'=>['allowOverlap'=>true]];   
        }else{
            $series['user'][] = ['type' => 'bar', 'name' => '下拨用户数', 'data' => $data_total_num,'label'=>['show'=>true,'position'=>'top']];
            $series['user'][] = ['type' => 'bar', 'name' => '实际用户数', 'data' => $data_user_num,'label'=>['show'=>true],'barGap'=>'-100%'];
            $series['user'][] = ['type' => 'line','smooth'=>true, 'name' => '用户占比', 'data' => $data_user_per,'yAxisIndex'=>1,'label'=>['show'=>true,'formatter'=>"{@[1]}%",'color'=>'#000']];   
        }
        
        //活跃项目
        $series['item']=[];       
        $data_item=[]; 
        
        $items=[];       
        $items['项目管理']=(int) ActivityChange::get_activity_item($start-86400, $end,['projectman_projectcount','projectman_membercount','projectman_issuecount','projectman_wiki','projectman_docman'],$annual,true,$group,$allocate);
        $items['代码仓库']=(int) ActivityChange::get_activity_item($start-86400, $end,['codehub_repositorycount','codehub_commitcount','codehub_repositorysize'],$annual,true,$group,$allocate);
        $items['流水线']=(int) ActivityChange::get_activity_item($start-86400, $end,['pipeline_assignmentscount','pipeline_elapse_time'],$annual,true,$group,$allocate);
        $items['代码检查']=(int) ActivityChange::get_activity_item($start-86400, $end,['codecheck_taskcount','codecheck_codelinecount','codecheck_execount'],$annual,true,$group,$allocate);
        $items['编译构建']=(int) ActivityChange::get_activity_item($start-86400, $end,['codeci_buildcount','codeci_buildtotaltime'],$annual,true,$group,$allocate);
        $items['测试']=(int) ActivityChange::get_activity_item($start-86400, $end,['testman_casecount','testman_execasecount'],$annual,true,$group,$allocate);
        $items['部署']=(int) ActivityChange::get_activity_item($start-86400, $end,['deploy_envcount','deploy_execount'],$annual,true,$group,$allocate); 
        $items['发布']=(int) ActivityChange::get_activity_item($start-86400, $end,['releaseman_uploadcount','releaseman_downloadcount'],$annual,true,$group,$allocate); 
        $items['沉默企业']=(int) ActivityChange::get_activity_item($start-86400, $end,null,$annual,false,$group,$allocate);
        
        arsort($items);
//        $fv= reset($items);
//        $fk= key($items);
//        unset($items[$fk]);
//        $items[$fk]=$fv;
        
        foreach ($items as $key=>$item){
            $data_item[] = ['name' =>  $key, 'y' =>$item,'value'=>$item];
        }
        if($chart==1){
            $series['item'][] = ['type' => 'pie','name' => '数量', 'data' => $data_item];
        }else{
            $series['item'][] = ['type' => 'pie','radius'=>[0,'60%'],'selectedMode'=>'single','name' => '数量', 'data' => $data_item,'label'=>['formatter'=>"{b}:{c}家,{d}%",'color'=>'#000']];
        }
    
        
        return $this->render('activity', ['chart'=>$chart,'series' => $series, 'start' => $start, 'end' => $end,'sum'=>$sum,'total'=>$total,'annual'=>$annual,'group'=>$group,'allocate'=>$allocate]);
    }
    
//    public function actionHealth() {
//        $chart=Yii::$app->siteConfig->business_charts;
//        $end = strtotime('today');
//        $start = strtotime('-1 months +1 days',$end);
//        $group=Yii::$app->request->get('group',null);
//        $allocate=Yii::$app->request->get('allocate',ActivityChange::ALLOCATE_Y)?ActivityChange::ALLOCATE_Y:null;
//        if(!$group){
//            $group_id=UserGroup::get_user_groupid(Yii::$app->user->identity->id);
//            if(count($group_id)>0){
//                $group=$group_id[0];
//            }
//        }
//
//        if (Yii::$app->request->get('range')) {
//            $range = explode('~', Yii::$app->request->get('range'));
//            $start = isset($range[0]) ? strtotime($range[0]) : $start;
//            $end = isset($range[1]) && (strtotime($range[1]) < $end) ? strtotime($range[1]): $end;
//        }
//        
//        //健康度
//        $series['health']=[]; 
//        $data_health=$health_value=$health_key=[];
//        $health_total= ActivityChange::get_health($start-86400, $end,$group,$allocate);      
//        
//        foreach($health_total as $total){
//            $key=$end-$start>=365*86400?date('Y.n.j',$total['start_time']+86400).'-'.date('Y.n.j',$total['end_time']):date('n.j',$total['start_time']+86400).'-'.date('n.j',$total['end_time']);
//            $health_value[$key][$total['health']]= (int) $total['num'];
//            if(!in_array($total['health'], $health_key)){
//                $health_key[]=$total['health'];
//            }
//        }
//        asort($health_key);
//        foreach($health_value as $date=>$value){
//            foreach($health_key as $key){
//                $y_health=isset($health_value[$date][$key])?$health_value[$date][$key]:0;
//                $data_health[$key][]=['name' =>$date , 'y' => $y_health,'value'=>[$date,$y_health]];
//            }
//        }
//       
//        if($chart==1){
//            foreach($data_health as $k=>$v){
//                $series['health'][] = ['type' => 'column', 'name' => ActivityChange::$List['health'][$k], 'data' => $v,'color'=> ActivityChange::$List['health_color'][$k]];
//            }
//        }else{
//            foreach($data_health as $k=>$v){
//                $series['health'][] = ['type' => 'bar', 'name' => ActivityChange::$List['health'][$k],'stack'=>'健康度', 'data' => $v,'color'=> ActivityChange::$List['health_color'][$k]];
//            }
//        }
//        
//        return $this->render('health', ['chart'=>$chart,'series' => $series, 'start' => $start, 'end' => $end,'group'=>$group,'allocate'=>$allocate]);
//    
//    }
    
     public function actionHealth() {
        $chart=Yii::$app->siteConfig->business_charts;
        $end = strtotime('today');
        $start = strtotime('-1 months +1 days',$end);
        $group=Yii::$app->request->get('group',null);
        $total_get=Yii::$app->request->get('total',1);
        $allocate=Yii::$app->request->get('allocate', HealthData::ALLOCATE_Y)?HealthData::ALLOCATE_Y:null;
        if(!$group){
            $group_id=UserGroup::get_user_groupid(Yii::$app->user->identity->id);
            if(count($group_id)>0){
                $group=$group_id[0];
            }
        }

        if (Yii::$app->request->get('range')) {
            $range = explode('~', Yii::$app->request->get('range'));
            $start = isset($range[0]) ? strtotime($range[0]) : $start;
            $end = isset($range[1]) && (strtotime($range[1]) < $end) ? strtotime($range[1]): $end;
        }
        
               
        //健康度
        $series['health']=[]; 
        $data_health=$data_per=$health_value=$health_key=[];
        $data_activity=$data_per_day=$data_per_week=$data_per_month=$activity=$activity_value=[];
        $health_total= HealthData::get_health($start-86400, $end,$group,$allocate,$total_get);
        $activity_day= HealthData::get_activity($start-86400, $end, $group, $allocate, $total_get, 'activity_day');
        $activity_week= HealthData::get_activity($start-86400, $end, $group, $allocate, $total_get, 'activity_week');
        $activity_month= HealthData::get_activity($start-86400, $end, $group, $allocate, $total_get, 'activity_month');      

        if($total_get==1){
            foreach($activity_day as $one){
                $activity[$one['statistics_time']]['day']=$one['num'];
            }
            foreach($activity_week as $one){
                $activity[$one['statistics_time']]['week']=$one['num'];
            }
            foreach($activity_month as $one){
                $activity[$one['statistics_time']]['month']=$one['num'];
            }
            foreach($health_total as $total){
                $key=$end-$start>=365*86400?date('Y.n.j',$total['statistics_time']):date('n.j',$total['statistics_time']);
                $health_value[$key][$total['health']]= (int) $total['num'];
                if(!in_array($total['health'], $health_key)){
                    $health_key[]=$total['health'];
                }
                $activity_value[$key]['day']=isset($activity[$total['statistics_time']]['day'])?(int)$activity[$total['statistics_time']]['day']:0;
                $activity_value[$key]['week']=isset($activity[$total['statistics_time']]['week'])?(int)$activity[$total['statistics_time']]['week']:0;
                $activity_value[$key]['month']=isset($activity[$total['statistics_time']]['month'])?(int)$activity[$total['statistics_time']]['month']:0;
            }
            asort($health_key);
            foreach($health_value as $date=>$value){
                $sum=0;
                foreach($health_key as $key){
                    $y_health=isset($health_value[$date][$key])?$health_value[$date][$key]:0;
                    $sum+=$y_health;
                    $data_health[$key][]=['name' =>$date , 'y' => $y_health,'value'=>[$date,$y_health]];
                }
                $health_5=isset($health_value[$date][HealthData::HEALTH_H5])?$health_value[$date][HealthData::HEALTH_H5]:0;
                $health_4=isset($health_value[$date][HealthData::HEALTH_H4])?$health_value[$date][HealthData::HEALTH_H4]:0;
                $data_per[]=['name' => $date, 'y' => $sum==0?0: round(($health_5)*100/$sum,2),'value' => [$date,$sum==0?0: round(($health_5)*100/$sum,2)]]; 
                $data_activity['day'][]=['name' =>$date , 'y' => $activity_value[$date]['day'],'value'=>[$date,$activity_value[$date]['day']]];
                $data_activity['week'][]=['name' =>$date , 'y' => $activity_value[$date]['week'],'value'=>[$date,$activity_value[$date]['week']]];
                $data_activity['month'][]=['name' =>$date , 'y' => $activity_value[$date]['month'],'value'=>[$date,$activity_value[$date]['month']]];
                $data_activity['total'][]=['name' =>$date , 'y' => $sum,'value'=>[$date,$sum]];
                $data_per_day[]=['name'=>$date,'y' => $sum==0?0: round($activity_value[$date]['day']*100/$sum,2),'value' => [$date,$sum==0?0: round($activity_value[$date]['day']*100/$sum,2)]];
                $data_per_week[]=['name'=>$date,'y' => $sum==0?0: round($activity_value[$date]['week']*100/$sum,2),'value' => [$date,$sum==0?0: round($activity_value[$date]['week']*100/$sum,2)]];
                $data_per_month[]=['name'=>$date,'y' => $sum==0?0: round($activity_value[$date]['month']*100/$sum,2),'value' => [$date,$sum==0?0: round($activity_value[$date]['month']*100/$sum,2)]];
            }

            if($chart==1){
                foreach($data_health as $k=>$v){
                    $series['health'][] = ['type' => 'column', 'name' => HealthData::$List['health'][$k], 'data' => $v,'color'=> HealthData::$List['health_color'][$k]];
                }              
                $series['health'][] = ['type' => 'spline', 'name' => '健康度', 'data' => $data_per,'tooltip'=>['valueSuffix'=>'%'],'yAxis'=>1, 'dataLabels'=>['allowOverlap'=>true]];
                
                $series['activity'][] = ['type' => 'column', 'name' => '总企业数', 'data' => $data_activity['total'],'grouping'=>false,'borderWidth'=>0,'shadow'=>false];
                $series['activity'][] = ['type' => 'column', 'name' => '月活跃企业数', 'data' => $data_activity['month'],'grouping'=>false,'borderWidth'=>0,'shadow'=>false];
                $series['activity'][] = ['type' => 'column', 'name' => '周活跃企业数', 'data' => $data_activity['week'],'grouping'=>false,'borderWidth'=>0,'shadow'=>false,'dataLabels'=>['inside'=>true]];
                $series['activity'][] = ['type' => 'column', 'name' => '日活跃企业数', 'data' => $data_activity['day'],'grouping'=>false,'borderWidth'=>0,'shadow'=>false,'dataLabels'=>['inside'=>true]];
                
                $series['activity'][] = ['type' => 'spline', 'name' => '日活跃率', 'data' => $data_per_day,'tooltip'=>['valueSuffix'=>'%'],'yAxis'=>1, 'dataLabels'=>['allowOverlap'=>true]];
                $series['activity'][] = ['type' => 'spline', 'name' => '周活跃率', 'data' => $data_per_week,'tooltip'=>['valueSuffix'=>'%'],'yAxis'=>1, 'dataLabels'=>['allowOverlap'=>true]];
                $series['activity'][] = ['type' => 'spline', 'name' => '月活跃率', 'data' => $data_per_month,'tooltip'=>['valueSuffix'=>'%'],'yAxis'=>1, 'dataLabels'=>['allowOverlap'=>true]];
                
                
            }else{
                foreach($data_health as $k=>$v){
                    $series['health'][] = ['type' => 'bar', 'name' => HealthData::$List['health'][$k],'stack'=>'健康度', 'data' => $v,'color'=> HealthData::$List['health_color'][$k],'label'=>['show'=>true]];
                }
                $series['health'][] = ['type' => 'line','smooth'=>true, 'name' => '健康度', 'data' => $data_per,'yAxisIndex'=>1,'label'=>['show'=>true,'formatter'=>"{@[1]}%",'color'=>'#000']];  
                
                $series['activity'][] = ['type' => 'bar', 'name' => '总企业数', 'data' => $data_activity['total'],'label'=>['show'=>true,'position'=>'top']];
                $series['activity'][] = ['type' => 'bar', 'name' => '月活跃企业数', 'data' => $data_activity['month'],'label'=>['show'=>true],'barGap'=>'-100%'];
                $series['activity'][] = ['type' => 'bar', 'name' => '周活跃企业数', 'data' => $data_activity['week'],'label'=>['show'=>true],'barGap'=>'-100%'];
                $series['activity'][] = ['type' => 'bar', 'name' => '日活跃企业数', 'data' => $data_activity['day'],'label'=>['show'=>true],'barGap'=>'-100%'];
                
                $series['activity'][] = ['type' => 'line','smooth'=>true, 'name' => '日活跃率', 'data' => $data_per_day,'yAxisIndex'=>1,'label'=>['show'=>true,'formatter'=>"{@[1]}%",'color'=>'#000']];
                $series['activity'][] = ['type' => 'line','smooth'=>true, 'name' => '周活跃率', 'data' => $data_per_week,'yAxisIndex'=>1,'label'=>['show'=>true,'formatter'=>"{@[1]}%",'color'=>'#000']];
                $series['activity'][] = ['type' => 'line','smooth'=>true, 'name' => '月活跃率', 'data' => $data_per_month,'yAxisIndex'=>1,'label'=>['show'=>true,'formatter'=>"{@[1]}%",'color'=>'#000']];
            }
        }else{
            
            $data_per = [];
            $groups = User::get_bd_color();
            
            foreach($health_total as $total){
                $total['bd_id']=$total['bd_id']?$total['bd_id']:0;
                $key=$end-$start>=365*86400?date('Y.n.j',$total['statistics_time']):date('n.j',$total['statistics_time']);
                $health_value[$key][$total['bd_id']][$total['health']]= (int) $total['num'];
                if(!in_array($total['bd_id'], $health_key)){
                    $health_key[]=$total['bd_id'];
                }
            }
            asort($health_key);
            foreach($health_value as $date=>$value){
                foreach($health_key as $key){
                    $health_5=isset($health_value[$date][$key][HealthData::HEALTH_H5])?$health_value[$date][$key][HealthData::HEALTH_H5]:0;
                    $health_4=isset($health_value[$date][$key][HealthData::HEALTH_H4])?$health_value[$date][$key][HealthData::HEALTH_H4]:0;
                    $sum= isset($health_value[$date][$key])?array_sum($health_value[$date][$key]):0;
                    $y_health=$health_5;//$health_4+$health_5;
                    $y_per=$sum?round($y_health/$sum*100,2):0;
                    $data_health[$key][]=['name' => $date, 'y' => $y_health ,'value'=>[$date,$y_health]];
                    $data_per[$key][]=['name' => $date, 'y' =>$y_per,'value'=>[$date,$y_per]];
                }
            }          
            
            if($chart==1){
                foreach ($data_health as $gid=>$data){
                    $series['health'][] = ['type' => 'column', 'name' => $gid&&isset($groups[$gid]['name'])?$groups[$gid]['name']:'未分配', 'data' => $data,'color'=>$gid&&isset($groups[$gid]['color'])?'#'.$groups[$gid]['color']:'#FF0000','stacking'=>false];

                }
                foreach ($data_per as $gid=>$per){
                     $series['health'][] = ['type' => 'spline', 'name' => $gid&&isset($groups[$gid]['name'])?$groups[$gid]['name']:'未分配', 'data' => $per,'tooltip'=>['valueSuffix'=>'%'],'yAxis'=>1,'color'=>$gid&&isset($groups[$gid]['color'])?'#'.$groups[$gid]['color']:'#FF0000'];
                }
                $series['activity'][] =[];
            }else{
                foreach ($data_health as $gid=>$data){
                    $series['health'][] = ['type' => 'bar', 'name' => $gid&&isset($groups[$gid]['name'])?$groups[$gid]['name']:'未分配', 'data' => $data,'color'=>$gid&&isset($groups[$gid]['color'])?'#'.$groups[$gid]['color']:'#FF0000','label'=>['show'=>true]];

                }
                foreach ($data_per as $gid=>$per){
                    $series['health'][] = ['type' => 'line','smooth'=>true, 'name' => $gid&&isset($groups[$gid]['name'])?$groups[$gid]['name']:'未分配', 'data' => $per,'yAxisIndex'=>1,'color'=>$gid&&isset($groups[$gid]['color'])?'#'.$groups[$gid]['color']:'#FF0000'];
                }
                $series['activity'][] =[];
            }
            
        }
        
        return $this->render('health', ['chart'=>$chart,'series' => $series, 'start' => $start, 'end' => $end,'group'=>$group,'total'=>$total_get,'allocate'=>$allocate]);
    
    }
    
    public function actionTrain() {
        $chart=Yii::$app->siteConfig->business_charts;
        $end = strtotime('today')+ 86399;
        $start = strtotime('-30 days',$end);
        $sum=Yii::$app->request->get('sum',1);//1-天；2-周；3-月
        $total=Yii::$app->request->get('total',1);//1-总；0-个人
        $group=Yii::$app->request->get('group',null);

        if (Yii::$app->request->get('range')) {
            $range = explode('~', Yii::$app->request->get('range'));
            $start = isset($range[0]) ? strtotime($range[0]) : $start;
            $end = isset($range[1]) && (strtotime($range[1]) < $end) ? strtotime($range[1]) + 86399 : $end;
        }
        
        $series['num']=[];
        $series['type']=[];

        //趋势
        $train_num = Train::get_train_num($start, $end, Train::STAT_END,$sum,$total,$group);
        $train_type = Train::get_train_type($start, $end, Train::STAT_END,$total,$group);
               
        if($total==1){
            $data_train_num = [];            
            if($sum==1){
                //天
                for ($i = ($train_num?strtotime(key($train_num))-86400:$start); $i < $end; $i = $i + 86400) {
                    $k=date('Y-m-d', $i);
                    $j = $end-($train_num?strtotime(key($train_num))-86400:$start)>=365*86400?date('Y.n.j', $i):date('n.j', $i);
                    $data_train_num[] = ['name' => $j, 'y' => isset($train_num[$k]) ? (int) $train_num[$k]['num'] : 0, 'value' => [$j,isset($train_num[$k]) ? (int) $train_num[$k]['num'] : 0]];          
                }
            }elseif($sum==2){
                //周
                
                $start_w1=$start-((date('w',$start)==0?7:date('w',$start))-1)*86400;//获取周一
                
                for ($i = ($train_num?strtotime(key($train_num)):$start_w1); $i < $end; $i = $i + 86400*7) {
                    
                    $k= strtotime(strftime("%Y-W%W",$i))==$i?strftime("%Y-W%W",$i):strftime("%Y-W%W",$i+86400*7);//strftime函数可能会相差一周，此处进行调整
                    $s=$i<$start?$start:$i;//优化开始日期显示，日期显示更精确
                    $e=$i+86400*7>$end?$end:$i+86400*7-1;
                    $j = $end-($train_num?strtotime(key($train_num)):$start_w1)>=365*86400?date('Y.n.j', $s).'-'.date('Y.n.j', $e):date('n.j', $s).'-'.date('n.j', $e);
                    $data_train_num[] = ['name' => $j, 'y' => isset($train_num[$k]) ? (int) $train_num[$k]['num'] : 0, 'value' =>[$j, isset($train_num[$k]) ? (int) $train_num[$k]['num'] : 0]];          
                }
            }else{
                //月
                for ($i = ($train_num?strtotime(key($train_num)):strtotime(date("Y-m",$start))); $i < $end; $i= strtotime('+1 months',$i)) {
                    $k=date("Y-m",$i);
                    $j = date('Y.n', $i);
                    $data_train_num[] = ['name' => $j, 'y' => isset($train_num[$k]) ? (int) $train_num[$k]['num'] : 0,'value' =>[$j,isset($train_num[$k]) ? (int) $train_num[$k]['num'] : 0]];         
                }
            }
            $series['num'][] = ['type' => 'line', 'name' => '次数', 'data' => $data_train_num,'showInLegend'=>false];

            //类型
            $data_train_type = [];
            foreach($train_type as $type){
                 $data_train_type[] = ['name' => Train::$List['train_type'][$type['train_type']], 'y' => (int) $type['num'],'value'=>[Train::$List['train_type'][$type['train_type']],(int) $type['num']]];
                 //$series['type'][] = ['type' => 'column', 'name' => Train::$List['train_type'][$type['train_type']], 'data'=>[(int) $type['num']]];
            }
            if($chart==1){          
                $series['type'][] = ['type' => 'column', 'name' => '次数', 'data' => $data_train_type,'showInLegend'=>false,'colorByPoint'=>false];  
            }else{
                $series['type'][] = ['type' => 'bar', 'name' => '次数', 'data' => $data_train_type,'label'=>['show'=>true]];
            }
        }else{
                     
            $groups = User::get_user_color();
            
            //次数
            $users_num=[];
            $data_num_total=[];
            $data_train_num = [];
            
            
            foreach ($train_num as $num){
                if(!in_array($num['user_id'], $users_num)){
                    $users_num[]=$num['user_id'];
                }              
                $data_num_total[$num['time']][$num['user_id']]=$num['num'];
            }
            if($sum==1){
                for ($i = ($data_num_total?strtotime(key($data_num_total)):$start); $i < $end; $i = $i + 86400) {
                    $k=date('Y-m-d', $i);
                    $j = $end-($data_num_total?strtotime(key($data_num_total)):$start)>=365*86400?date('Y.n.j', $i):date('n.j', $i);
                    foreach($users_num as $user){
                        $y_train_num=isset($data_num_total[$k][$user]) ? (int) $data_num_total[$k][$user] : 0;
                        $data_train_num[$user][] = ['name' => $j, 'y' => $y_train_num,'value'=>[$j,$y_train_num]];
                    }
                }
            }elseif($sum==2){
                $start_w1=$start-((date('w',$start)==0?7:date('w',$start))-1)*86400;//获取周一
                for ($i = ($data_num_total?strtotime(key($data_num_total)):$start_w1); $i < $end; $i = $i + 86400*7) {
                    $k=strtotime(strftime("%Y-W%W",$i))==$i?strftime("%Y-W%W",$i):strftime("%Y-W%W",$i+86400*7);//strftime函数可能会相差一周，此处进行调整
                    $s=$i<$start?$start:$i;//优化开始日期显示，日期显示更精确
                    $e=$i+86400*7>$end?$end:$i+86400*7-1;
                    $j = $end-($data_num_total?strtotime(key($data_num_total)):$start_w1)>=365*86400?date('Y.n.j', $s).'-'.date('Y.n.j', $e):date('n.j', $s).'-'.date('n.j', $e);
                    foreach($users_num as $user){
                        $y_train_num=isset($data_num_total[$k][$user]) ? (int) $data_num_total[$k][$user] : 0;
                        $data_train_num[$user][] = ['name' => $j, 'y' => $y_train_num,'value'=>[$j,$y_train_num]];                    
                    }      
                }
            }else{
                for ($i = ($data_num_total?strtotime(key($data_num_total)):strtotime(date("Y-m",$start))); $i < $end; $i= strtotime('+1 months',$i)) {
                    $k=date("Y-m",$i);
                    $j = date('Y.n', $i);
                    foreach($users_num as $user){
                        $y_train_num=isset($data_num_total[$k][$user]) ? (int) $data_num_total[$k][$user] : 0;
                        $data_train_num[$user][] = ['name' => $j, 'y' => $y_train_num,'value'=>[$j,$y_train_num]];                   
                    }         
                }
            }
            
            foreach ($data_train_num as $gid=>$data){
                $series['num'][] = ['type' => 'line', 'name' => $gid?$groups[$gid]['name']:'未分配', 'data' => $data,'color'=>$gid&&$groups[$gid]['color']?'#'.$groups[$gid]['color']:''];              
            }
            
            //类型
            $users_type=[];
            $data_type_total=[];
            $data_train_type = [];
           
            foreach ($train_type as $t){
                if(!in_array($t['user_id'], $users_type)){
                    $users_type[]=$t['user_id'];
                }              
                $data_type_total[$t['train_type']][$t['user_id']]=$t['num'];
            }
            
            foreach($data_type_total as $k=>$type){
                foreach($users_type as $user){
                    $y_train_type=isset($data_type_total[$k][$user]) ? (int) $data_type_total[$k][$user] : 0;
                    $data_train_type[$user][] = ['name' => Train::$List['train_type'][$k], 'y' =>$y_train_type,'value'=>[Train::$List['train_type'][$k],$y_train_type] ];                   
                }      
            }
            if($chart==1){          
                foreach ($data_train_type as $gid=>$data){
                    $series['type'][] = ['type' => 'column', 'name' => $gid?$groups[$gid]['name']:'未分配', 'data' => $data,'color'=>$gid&&$groups[$gid]['color']?'#'.$groups[$gid]['color']:''];              
                }     
            }else{
                foreach ($data_train_type as $gid=>$data){
                    $series['type'][] = ['type' => 'bar', 'name' => $gid?$groups[$gid]['name']:'未分配', 'data' => $data,'color'=>$gid&&$groups[$gid]['color']?'#'.$groups[$gid]['color']:''];              
                }
            }
            
            
        }
        return $this->render('train', ['chart'=>$chart,'series' => $series, 'start' => $start, 'end' => $end,'sum'=>$sum,'total'=>$total,'group'=>$group]);
    }

}
