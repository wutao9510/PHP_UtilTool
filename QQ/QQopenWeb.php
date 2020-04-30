<?php
namespace Chenmu\QQ;

use Chenmu\Sys\{Curl, Log};

class QQopenWeb
{
	# 对象实例
	private static $instance = null;

	# 主体配置
	private $config = [];

	# 网关
	protected static $qqApiUrl = 'https://graph.qq.com';

	private function __clone(){}

	private function __construct(string $appId, string $appKey)
	{
		$this->config['app_id'] = $appId;
		$this->config['app_key'] = $appKey;
	}

    /**
     * 单例出口
     * @param string $appId
     * @param string $appKey
     * @return QQopenWeb|null
     */
	public static function instance(string $appId, string $appKey)
	{
		if (self::$instance && self::$instance instanceof self) {
			return self::$instance;
		}
		if (empty($appId) || empty($appKey)) {
			exit('请把appid、appkey填写完整！');
		}
		self::$instance = new self($appId, $appKey);
		return self::$instance;
    }
    
    /**
     * 通过code获取access_token
     * @param  string $code
     * @param  string $redirectUri
     * @return 
     */
    public function getAccessToken(string $code, string $redirectUri)
    {
        try {
            if (empty(trim($code)) || empty(trim($redirectUri))) {
                throw new \Exception('缺少参数code或redirect_uri！');
            }
            $params['client_id'] = $this->config['app_id'];
            $params['client_secret'] = $this->config['app_key'];
            $params['code'] = trim($code);
            $params['redirect_uri'] = urlencode($redirectUri);
            $params['grant_type'] = 'authorization_code';

            $result = Curl::instance()->get(self::$qqApiUrl.'/oauth2.0/token', $params);
            if ($result) {
                parse_str(urldecode($result), $arr);
                return $arr;
            }else {
                throw new \Exception('请求失败！');
            }
        } catch (\Exception $e) {
            Log::instance()->write($e->getMessage());
            return false;
        }
    }

    /**
     * 刷新access_token有效期
     * @param  string $refreshToken
     * @return 
     */
    public function refreshToken(string $refreshToken)
    {
        try {
            if (empty(trim($refreshToken))) {
                throw new \Exception('缺少参数refresh_token！');
            }
            $params['client_id'] = $this->config['app_id'];
            $params['client_secret'] = $this->config['app_key'];
            $params['refresh_token'] = trim($refreshToken);
            $params['grant_type'] = 'refresh_token';
            $result = Curl::instance()->get(self::$qqApiUrl.'/oauth2.0/token', $params);
            if ($result) {
                parse_str(urldecode($result), $arr);
                return $arr;
            }else {
                throw new \Exception('请求失败！');
            }
        } catch (\Exception $e) {
            Log::instance()->write($e->getMessage());
            return false;
        }
    }

    /**
     * 获取用户个人信息（UnionID机制）
     * @param  string $accessToken
     * @param  string $openId
     * @return
     */
    public function getUserInfo(string $accessToken, string $openId)
    {
        try {
            if (empty(trim($accessToken)) || empty(trim($openId))) {
                throw new \Exception('请access_token或openid填写完整！');
            }
            $params['access_token'] = trim($accessToken);
            $params['openid'] = trim($openId);
            $params['oauth_consumer_key'] = $this->config['app_id'];
            $result = Curl::instance()->get(self::$qqApiUrl.'/user/get_user_info', $params);
            if ($result) {
                return json_decode($result, true);
            }else {
                throw new \Exception('请求失败！');
            }
        } catch (\Exception $e) {
            Log::instance()->write($e->getMessage());
            return false;
        }
    }
}
