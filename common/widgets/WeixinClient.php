<?php

namespace common\widgets;

use yii\authclient\OAuth2;
use Yii;

/**
 * Weixin(Wechat) allows authentication via Weixin(Wechat) OAuth.
 *
 * In order to use Weixin(Wechat) OAuth you must register your application at <https://open.weixin.qq.com/> or <https://mp.weixin.qq.com/>.
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'weixin' => [   // for account of https://open.weixin.qq.com/
 *                 'class' => 'yujiandong\authclient\Weixin',
 *                 'clientId' => 'weixin_appid',
 *                 'clientSecret' => 'weixin_appkey',
 *             ],
 *             'weixinmp' => [  // for account of https://mp.weixin.qq.com/
 *                 'class' => 'yujiandong\authclient\Weixin',
 *                 'type' => 'mp',
 *                 'clientId' => 'weixin_appid',
 *                 'clientSecret' => 'weixin_appkey',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see https://open.weixin.qq.com/
 * @see https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&lang=zh_CN
 * @see https://mp.weixin.qq.com/
 * @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140842&token=&lang=zh_CN
 *
 * @author Jiandong Yu <flyyjd@gmail.com>
 * @since 2.0
 */
class WeixinClient extends OAuth2 {

    /**
     * @inheritdoc
     */
    public $authUrl = 'https://open.weixin.qq.com/connect/qrconnect';
    public $authUrlMp = 'https://open.weixin.qq.com/connect/oauth2/authorize';

    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://api.weixin.qq.com';
    public $type = null;

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
        if ($this->scope === null) {
            $this->scope = implode(',', [
                'snsapi_userinfo',
            ]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap() {
        return [
            'id' => 'openid',
            'username' => 'nickname',
        ];
    }

    /**
     * @inheritdoc
     */
    public function buildAuthUrl(array $params = []) {
        $authState = $this->generateAuthState();
        $this->setState('authState', $authState);
        $defaultParams = [
            'appid' => $this->clientId,
            'redirect_uri' => $this->getReturnUrl(),
            'response_type' => 'code',
        ];
        if (!empty($this->scope)) {
            $defaultParams['scope'] = $this->scope;
        }
        $defaultParams['state'] = $authState;

        $url = $this->composeUrl($this->type == 'mp' ? $this->authUrlMp : $this->authUrl, array_merge($defaultParams, $params));
        return $url;
    }

    public function fetchAccessToken($authCode, array $params = []) {
        $defaultParams = [
            'appid' => $this->clientId,
            'secret' => $this->clientSecret,
            'code' => $authCode,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->getReturnUrl(),
        ];
        $response = $this->sendRequest('GET', $this->tokenUrl, array_merge($defaultParams, $params));
        $token = null;
        if (isset($response['access_token'])) {
            $arr['oauth_token_secret'] = $this->clientSecret;
            $token = $this->createToken(['params' => array_merge($arr, $response), 'class' => WeChatToken::className()]);
            $this->setAccessToken($token);
        }
        return $token;
    }

    /**
     * @inheritdoc
     */
    protected function apiInternal($accessToken, $url, $method, array $params, array $headers) {
        $params['access_token'] = $accessToken->getToken();
        $params['openid'] = $accessToken->getParam('openid');
        $params['lang'] = 'zh_CN';
        return $this->sendRequest($method, $url, $params, $headers);
    }

    /**
     * @inheritdoc
     */
    protected function initUserAttributes() {
        return $this->api('sns/userinfo');
//        $userAttributes['id'] = $userAttributes['unionid'];
//        return $userAttributes;
    }

    /**
     * @inheritdoc
     */
    protected function defaultReturnUrl() {
        $params = $_GET;
        unset($params['code']);
        unset($params['state']);
        $params[0] = Yii::$app->controller->getRoute();
        return Yii::$app->getUrlManager()->createAbsoluteUrl($params);
    }

    /**
     * Generates the auth state value.
     * @return string auth state value.
     */
    protected function generateAuthState() {
        return sha1(uniqid(get_class($this), true));
    }

    /**
     * @inheritdoc
     */
    protected function defaultName() {
        return 'weixin';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle() {
        return '微信';
    }

    /**
     * @inheritdoc
     */
    protected function defaultViewOptions() {
        return [
            'popupWidth' => 800,
            'popupHeight' => 500,
        ];
    }

}
