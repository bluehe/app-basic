<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use project\actions\IndexAction;
use project\actions\DeleteAction;
use project\actions\ViewAction;
use project\models\TrainSearch;
use project\models\Train;
use project\models\TrainUser;
use project\components\ExcelHelper;
use project\models\Group;


class TrainController extends Controller
{

    public function actions()
    {
        return [
            'index' => [
                'class' => IndexAction::className(),
                'data' => function () {
                    $searchModel = new TrainSearch();
                    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

                    return [
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                    ];
                }
            ],
            'view' => [
                'class' => ViewAction::className(),
                'modelClass' => Train::className(),
                'ajax' => true,
            ],
            'delete' => [
                'class' => DeleteAction::className(),
                'modelClass' => Train::className(),
            ],
        ];
    }

    public function actionCreate()
    {
        $model = new Train();
        $model->setScenario("trainStart");
        $model->loadDefaultValues();
        $group = Group::get_user_group(Yii::$app->user->identity->id);
        if (count($group) == 1) {
            $model->group_id = key($group);
        }
        $model->uid = Yii::$app->user->identity->id;
        if ($model->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\bootstrap\ActiveForm::validate($model);
            }

            $rw = Yii::$app->request->post('Train');
            $rw['sa'] = $rw['sa'] ? $rw['sa'] : [];
            $rw['other'] = $rw['other'] ? $rw['other'] : [];
            $trainuser = new TrainUser();

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->train_start = strtotime($model->train_start);
                $model->train_end = strtotime($model->train_end);

                if (Yii::$app->user->identity->role != 'sa') {
                    $model->train_stat = Train::STAT_CREATED;
                } else {
                    $model->train_stat = Train::STAT_ORDER;
                }

                $model->save(false);
                $trainuser->train_id = $model->id;

                if (count($rw['sa']) > 0) {
                    foreach ($rw['sa'] as $k => $t) {
                        $_v = clone $trainuser;
                        $_v->user_id = $t;
                        $_v->tuser_sort = $k + 1;
                        if (!$_v->save(false)) {
                            throw new \Exception("操作失败2");
                        }
                    }
                }
                if (count($rw['other']) > 0) {
                    foreach ($rw['other'] as $k => $t) {
                        $_v = clone $trainuser;
                        $_v->user_id = $t;
                        $_v->tuser_sort = $k + 1;
                        if (!$_v->save(false)) {
                            throw new \Exception("操作失败1");
                        }
                    }
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', '操作成功。');
            } catch (\Exception $e) {

                $transaction->rollBack();
                //                throw $e;
                Yii::$app->session->setFlash('error', '操作失败。' . $e);
            }
            return $this->redirect(Yii::$app->request->referrer);
        } else {

            if (Yii::$app->user->identity->role == 'sa') {
                $model->sa = $model->uid;
            } else {
                $model->other = $model->uid;
            }
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = Train::findOne($id);
        $model->setScenario("trainStart");
        $model->sa = $old_sa = TrainUser::get_userid($model->id, 'sa');
        $model->other = $old_other = TrainUser::get_userid($model->id, 'other');

        if ($model->load(Yii::$app->request->post())) {

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\bootstrap\ActiveForm::validate($model);
            }

            $rw = Yii::$app->request->post('Train');
            $rw['sa'] = $rw['sa'] ? $rw['sa'] : [];
            $rw['other'] = $rw['other'] ? $rw['other'] : [];

            $trainuser = new TrainUser();

            $transaction = Yii::$app->db->beginTransaction();
            try {

                $model->train_start = strtotime($model->train_start);
                $model->train_end = strtotime($model->train_end);

                if (Yii::$app->user->identity->role != 'sa') {
                    $model->train_stat = Train::STAT_CREATED;
                } else {
                    $model->train_stat = Train::STAT_ORDER;
                }

                $model->save(false);
                $trainuser->train_id = $model->id;

                //SA
                $sa_t1 = array_diff($rw['sa'], $old_sa); //新增
                $sa_t2 = array_diff(array_diff_assoc($rw['sa'], $old_sa), $sa_t1); //变化           
                $sa_t3 = array_diff($old_sa, $rw['sa']); //删除

                if (count($sa_t1) > 0) {
                    foreach ($sa_t1 as $k => $t) {
                        $_v = clone $trainuser;
                        $_v->user_id = $t;
                        $_v->tuser_sort = $k + 1;
                        if (!$_v->save(false)) {
                            throw new \Exception("操作失败");
                        }
                    }
                }
                if (count($sa_t2) > 0) {
                    foreach ($sa_t2 as $k => $t) {
                        $_v = TrainUser::findOne(['train_id' => $model->id, 'user_id' => $t]);
                        $_v->tuser_sort = $k + 1;
                        if (!$_v->save(false)) {
                            throw new \Exception("操作失败");
                        }
                    }
                }
                if (count($sa_t3) > 0) {
                    TrainUser::deleteAll(['train_id' => $model->id, 'user_id' => $sa_t3]);
                }

                //other
                $other_t1 = array_diff($rw['other'], $old_other); //新增
                $other_t2 = array_diff(array_diff_assoc($rw['other'], $old_other), $other_t1); //变化           
                $other_t3 = array_diff($old_other, $rw['other']); //删除

                if (count($other_t1) > 0) {
                    foreach ($other_t1 as $k => $t) {
                        $_v = clone $trainuser;
                        $_v->user_id = $t;
                        $_v->tuser_sort = $k + 1;
                        if (!$_v->save(false)) {
                            throw new \Exception("操作失败");
                        }
                    }
                }
                if (count($other_t2) > 0) {
                    foreach ($other_t2 as $k => $t) {
                        $_v = TrainUser::findOne(['train_id' => $model->id, 'user_id' => $t]);
                        $_v->tuser_sort = $k + 1;
                        if (!$_v->save(false)) {
                            throw new \Exception("操作失败");
                        }
                    }
                }
                if (count($other_t3) > 0) {
                    TrainUser::deleteAll(['train_id' => $model->id, 'user_id' => $other_t3]);
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', '操作成功。');
            } catch (\Exception $e) {

                $transaction->rollBack();
                //                throw $e;
                Yii::$app->session->setFlash('error', '操作失败。');
            }
            return $this->redirect(Yii::$app->request->referrer);
        } else {

            $model->train_start = date('Y-m-d H:i', $model->train_start);
            $model->train_end = date('Y-m-d H:i', $model->train_end);
        }
        return $this->renderAjax('update', [
            'model' => $model,
        ]);
    }

    public function actionEnd($id)
    {
        $model = Train::findOne($id);

        $model->setScenario("trainEnd");

        $model->sa = $old_sa = TrainUser::get_userid($model->id, 'sa');
        $model->other = $old_other = TrainUser::get_userid($model->id, 'other');

        if ($model->load(Yii::$app->request->post())) {

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\bootstrap\ActiveForm::validate($model);
            }

            $rw = Yii::$app->request->post('Train');
            $rw['sa'] = $rw['sa'] ? $rw['sa'] : [];
            $rw['other'] = $rw['other'] ? $rw['other'] : [];

            $trainuser = new TrainUser();

            $transaction = Yii::$app->db->beginTransaction();
            try {

                $model->train_start = strtotime($model->train_start);
                $model->train_end = strtotime($model->train_end);

                $model->train_stat = Train::STAT_END;

                $model->save(false);

                $trainuser->train_id = $model->id;

                //SA
                $sa_t1 = array_diff($rw['sa'], $old_sa); //新增
                $sa_t2 = array_diff(array_diff_assoc($rw['sa'], $old_sa), $sa_t1); //变化           
                $sa_t3 = array_diff($old_sa, $rw['sa']); //删除

                if (count($sa_t1) > 0) {
                    foreach ($sa_t1 as $k => $t) {
                        $_v = clone $trainuser;
                        $_v->user_id = $t;
                        $_v->tuser_sort = $k + 1;
                        if (!$_v->save(false)) {
                            throw new \Exception("操作失败，SA新增失败");
                        }
                    }
                }
                if (count($sa_t2) > 0) {
                    foreach ($sa_t2 as $k => $t) {
                        $_v = TrainUser::findOne(['train_id' => $model->id, 'user_id' => $t]);
                        $_v->tuser_sort = $k + 1;
                        if (!$_v->save(false)) {
                            throw new \Exception("操作失败,SA变更失败");
                        }
                    }
                }
                if (count($sa_t3) > 0) {
                    TrainUser::deleteAll(['train_id' => $model->id, 'user_id' => $sa_t3]);
                }

                //other
                $other_t1 = array_diff($rw['other'], $old_other); //新增
                $other_t2 = array_diff(array_diff_assoc($rw['other'], $old_other), $other_t1); //变化           
                $other_t3 = array_diff($old_other, $rw['other']); //删除

                if (count($other_t1) > 0) {
                    foreach ($other_t1 as $k => $t) {
                        $_v = clone $trainuser;
                        $_v->user_id = $t;
                        $_v->tuser_sort = $k + 1;
                        if (!$_v->save(false)) {
                            throw new \Exception("操作失败,人员新增失败");
                        }
                    }
                }
                if (count($other_t2) > 0) {
                    foreach ($other_t2 as $k => $t) {
                        $_v = TrainUser::findOne(['train_id' => $model->id, 'user_id' => $t]);
                        $_v->tuser_sort = $k + 1;
                        if (!$_v->save(false)) {
                            throw new \Exception("操作失败，人员变更失败");
                        }
                    }
                }
                if (count($other_t3) > 0) {
                    TrainUser::deleteAll(['train_id' => $model->id, 'user_id' => $other_t3]);
                }

                //企业地址同步
                $corporation = \project\models\Corporation::findOne($model->corporation_id);
                if ($corporation && !$corporation->contact_address) {
                    $corporation->contact_address = $model->train_address;
                    $corporation->save(false);
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', '操作成功。');
            } catch (\Exception $e) {

                $transaction->rollBack();
                //                throw $e;
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            $model->train_start = date('Y-m-d H:i', $model->train_start);
            $model->train_end = date('Y-m-d H:i', $model->train_end);

            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionCancel($id)
    {
        $model = Train::findOne($id);

        if ($model !== null && ($model->uid == Yii::$app->user->identity->id || in_array(Yii::$app->user->identity->id, TrainUser::get_userid($model->id, 'sa')))) {
            $model->train_stat = Train::STAT_CANCEL;
            $model->reply_uid = Yii::$app->user->identity->id;
            $model->reply_at = time();
            $model->save();
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionRefuse($id)
    {
        $model = Train::findOne($id);

        if ($model !== null) {
            $model->train_stat = Train::STAT_REFUSE;
            $model->reply_uid = Yii::$app->user->identity->id;
            $model->reply_at = time();
            $model->save();
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionOrder($id)
    {
        $model = Train::findOne($id);
        $model->setScenario("trainStart");
        $model->sa = $old_sa = TrainUser::get_userid($model->id, 'sa');
        if ($model->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\bootstrap\ActiveForm::validate($model);
            }

            $rw = Yii::$app->request->post('Train');
            $rw['sa'] = $rw['sa'] ? $rw['sa'] : [];


            $trainuser = new TrainUser();
            $transaction = Yii::$app->db->beginTransaction();
            try {

                $model->train_stat = Train::STAT_ORDER;
                $model->reply_uid = Yii::$app->user->identity->id;
                $model->reply_at = time();

                $model->save(false);
                $trainuser->train_id = $model->id;

                //SA
                $sa_t1 = array_diff($rw['sa'], $old_sa); //新增
                $sa_t2 = array_diff(array_diff_assoc($rw['sa'], $old_sa), $sa_t1); //变化           
                $sa_t3 = array_diff($old_sa, $rw['sa']); //删除               

                if (count($sa_t1) > 0) {
                    foreach ($sa_t1 as $k => $t) {
                        $_v = clone $trainuser;
                        $_v->user_id = $t;
                        $_v->tuser_sort = $k + 1;
                        if (!$_v->save(false)) {
                            throw new \Exception("操作失败");
                        }
                    }
                }
                if (count($sa_t2) > 0) {
                    foreach ($sa_t2 as $k => $t) {
                        $_v = TrainUser::findOne(['train_id' => $model->id, 'user_id' => $t]);
                        $_v->tuser_sort = $k + 1;
                        if (!$_v->save(false)) {
                            throw new \Exception("操作失败");
                        }
                    }
                }
                if (count($sa_t3) > 0) {
                    TrainUser::deleteAll(['train_id' => $model->id, 'user_id' => $sa_t3]);
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', '操作成功。');
            } catch (\Exception $e) {

                $transaction->rollBack();
                //                throw $e;
                Yii::$app->session->setFlash('error', '操作失败。');
            }
            return $this->redirect(Yii::$app->request->referrer);
        } else {

            return $this->renderAjax('train-order', [
                'model' => $model,
            ]);
        }
    }

    public function actionExport()
    {
        $start_time = microtime(true);
        $searchModel = new TrainSearch();
        $models = $searchModel->search(Yii::$app->request->queryParams, 1000)->getModels();

        $fileName = Yii::getAlias('@webroot') . '/excel/train_temple.xlsx';
        $format = \PHPExcel_IOFactory::identify($fileName);
        $objectreader = \PHPExcel_IOFactory::createReader($format);
        $objectPhpExcel = $objectreader->load($fileName);

        $objectPhpExcel->getActiveSheet()->setCellValue('A1', '序号')
            ->setCellValue('B1', $searchModel->getAttributeLabel('日期'))
            ->setCellValue('C1', $searchModel->getAttributeLabel('train_start'))
            ->setCellValue('D1', $searchModel->getAttributeLabel('train_end'))
            ->setCellValue('E1', $searchModel->getAttributeLabel('train_type'))
            ->setCellValue('F1', $searchModel->getAttributeLabel('train_name'))
            ->setCellValue('G1', $searchModel->getAttributeLabel('train_address'))
            ->setCellValue('H1', $searchModel->getAttributeLabel('解决方案人员'))
            ->setCellValue('I1', $searchModel->getAttributeLabel('其他人员'))
            ->setCellValue('J1', $searchModel->getAttributeLabel('train_result'))
            ->setCellValue('K1', $searchModel->getAttributeLabel('train_num'))
            ->setCellValue('L1', $searchModel->getAttributeLabel('train_stat'))
            ->setCellValue('M1', $searchModel->getAttributeLabel('note'));

        $group_count = count(Group::get_user_group(Yii::$app->user->identity->id));

        if ($group_count > 1) {
            $objectPhpExcel->getActiveSheet()->setCellValue('N1', $searchModel->getAttributeLabel('group_id'));
        }

        foreach ($models as $key => $model) {
            $k = $key + 2;
            $objectPhpExcel->getActiveSheet()->setCellValue('A' . $k, $key + 1)
                ->setCellValue('B' . $k, (date('Y-m-d', $model->train_start) == date('Y-m-d', $model->train_end) ? date('Y-m-d', $model->train_start) : date('Y-m-d', $model->train_start) . ' ~ ' . date('Y-m-d', $model->train_end)))
                ->setCellValue('C' . $k, date('H:i', $model->train_start))
                ->setCellValue('D' . $k, date('H:i', $model->train_end))
                ->setCellValue('E' . $k, $model->TrainType)
                ->setCellValue('F' . $k, $model->train_name)
                ->setCellValue('G' . $k, $model->train_address)
                ->setCellValue('H' . $k, $model->get_username($model->id, 'sa'))
                ->setCellValue('I' . $k, $model->get_username($model->id, 'other'))
                ->setCellValue('J' . $k, $model->train_result)
                ->setCellValue('K' . $k, $model->train_num)
                ->setCellValue('L' . $k, $model->TrainStat)
                ->setCellValue('M' . $k, $model->note);

            if ($group_count > 1) {
                $objectPhpExcel->getActiveSheet()->setCellValue('N' . $k, $model->group_id ? $model->group->title : $model->group_id);
            }
        }


        $end_time = microtime(true);
        if ($end_time - $start_time < 1) {
            sleep(1);
        }

        ExcelHelper::excel_set_headers($format, '培训咨询(' . date('Y-m-d', time()) . ')');

        $objectwriter = \PHPExcel_IOFactory::createWriter($objectPhpExcel, $format);
        $path = 'php://output';
        $objectwriter->save($path);
        exit();
    }
}
