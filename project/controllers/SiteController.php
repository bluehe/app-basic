<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use project\models\User;
use project\models\System;
use yii\filters\VerbFilter;
use project\models\UserAuth;
use project\models\LoginForm;
use project\models\UserGroup;
use project\models\SignupForm;
use yii\filters\AccessControl;
use project\models\Corporation;
use project\models\CloudSubsidy;
use project\models\CorporationMeal;
use yii\base\InvalidParamException;
use project\models\PasswordFindForm;
use project\models\PasswordResetForm;
use project\models\ResetPasswordForm;
use project\models\PasswordResetRequestForm;
use yii\web\JsExpression;

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
        if ($chart == 1) {
            $series['allocate_num'][] = ['type' => 'pie', 'innerSize' => '50%', 'name' => '数量', 'data' => $data_allocate];
        } else {
            $series['allocate_num'][] = ['type' => 'pie', 'radius' => ['25%', '50%'], 'name' => '数量', 'minAngle' => 10, 'data' => $data_allocate, 'label' => ['formatter' => "{c}家,{d}%", 'color' => '#FFF'], 'color' => ["#065aab", "#066eab", "#0682ab", "#0696ab", "#06a0ab", "#06b4ab", "#06c8ab", "#06dcab", "#06f0ab"]];
        }

        return $this->render('index', ['allocate_num' => $allocate_num, 'allocate_amount' => $allocate_amount, 'series' => $series,]);
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
