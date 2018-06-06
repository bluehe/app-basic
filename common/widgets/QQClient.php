<?php

namespace common\widgets;

use yii\authclient\OAuth2;

/**
 * QQ allows authentication via QQ OAuth.
 *
 * In order to use QQ OAuth you must register your application at <http://connect.qq.com/>.
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'qq' => [
 *                 'class' => 'common\widgets\QQClient',
 *                 'clientId' => 'qq_appid',
 *                 'clientSecret' => 'qq_appkey',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see http://connect.qq.com/
 * @see http://wiki.connect.qq.com/
 *
 */
class QQClient extends OAuth2 {

    /**
     * @inheritdoc
     */
    public $authUrl = 'https://graph.qq.com/oauth2.0/authorize';

    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://graph.qq.com/oauth2.0/token';

    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://graph.qq.com';

    /**
     * @inheritdoc
     */
    protected function initUserAttributes() {

        $userAttributes = $this->api('user/get_user_info', 'GET', ['oauth_consumer_key' => $this->user->client_id, 'openid' => $this->user->openid]);

        $userAttributes['id'] = $this->user->openid;
        return $userAttributes;
    }

    /**
     * @inheritdoc
     */
    protected function getUser() {
        $str = file_get_contents('https://graph.qq.com/oauth2.0/me?access_token=' . $this->accessToken->token);

        if (strpos($str, "callback") !== false) {
            $lpos = strpos($str, "(");
            $rpos = strrpos($str, ")");
            $str = substr($str, $lpos + 1, $rpos - $lpos - 1);
        }

        return json_decode($str);
    }

    /**
     * @inheritdoc
     */
    protected function defaultName() {
        return 'qq';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle() {
        return 'QQ';
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
