<?php
namespace Chenmu\Wechat;

use Chenmu\Sys\{Curl, Log};

class WxOpenWeb
{
	# 对象实例
	private static $instance = null;

	# 主体配置
	private $config = [];

	# 网关
	protected static $wxApiUrl = 'https://api.weixin.qq.com';

	private function __clone(){}

	private function __construct(string $appId, string $appSecret)
	{
		$this->config['app_id'] = $appId;
		$this->config['app_secret'] = $appSecret;
	}

    /**
     * 单例出口
     * @param string $appId
     * @param string $appSecret
     * @return WxOpenWeb|null
     */
	public static function instance(string $appId, string $appSecret)
	{
		if (self::$instance && self::$instance instanceof self) {
			return self::$instance;
		}
		if (empty($appId) || empty($appSecret)) {
			exit('请把appid、appsecret填写完整！');
		}
		self::$instance = new self($appId, $appSecret);
		return self::$instance;
    }
    
    /**
     * 通过code获取access_token
     * @param  string $code
     * @return 
     */
    public function getAccessToken(string $code)
    {
        if (empty(trim($code))) {
            throw new \Exception('缺少参数code！');
        }
        $params['appid'] = $this->config['app_id'];
        $params['secret'] = $this->config['secret'];
        $params['code'] = trim($code);
        $params['grant_type'] = 'authorization_code';

        try {
            $result = Curl::instance()->get(self::$wxApiUrl.'/sns/oauth2/access_token', $params);
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

    /**
     * 刷新access_token有效期
     * @param  string $refreshToken
     * @return 
     */
    public function refreshToken(string $refreshToken)
    {
        if (empty(trim($refreshToken))) {
            throw new \Exception('缺少参数refresh_token！');
        }
        $params['appid'] = $this->config['app_id'];
        $params['refresh_token'] = trim($refreshToken);
        $params['grant_type'] = 'refresh_token';
        try {
            $result = Curl::instance()->get(self::$wxApiUrl.'/sns/oauth2/refresh_token', $params);
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

    /**
     * 检验授权凭证（access_token）是否有效
     * @param  string $accessToken
     * @param  string $openId
     * @return 
     */
    public function checkAuth(string $accessToken, string $openId)
    {
        if (empty(trim($accessToken)) || empty(trim($openId))) {
            throw new \Exception('请access_token或openid填写完整！');
        }
        $params['access_token'] = trim($accessToken);
        $params['openid'] = trim($openId);
        try {
            $result = Curl::instance()->get(self::$wxApiUrl.'/sns/oauth', $params);
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

    /**
     * 获取用户个人信息（UnionID机制）
     * @param  string $accessToken
     * @param  string $openId
     * @return
     */
    public function getUserInfo(string $accessToken, string $openId)
    {
        if (empty(trim($accessToken)) || empty(trim($openId))) {
            throw new \Exception('请access_token或openid填写完整！');
        }
        $params['access_token'] = trim($accessToken);
        $params['openid'] = trim($openId);
        $params['lang'] = 'zh_CN';
        try {
            $result = Curl::instance()->get(self::$wxApiUrl.'/sns/userinfo', $params);
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
