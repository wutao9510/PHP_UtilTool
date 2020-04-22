<?php 

/**
 * 
 */
class WxWeApp
{
	private static $instance = null;

	private $wxConfig = [];

	protected $wxApiUrl = 'https://api.weixin.qq.com';

	private function __clone(){}

	private function __construct(string $appId, string $appSecret, string $mchId = null)
	{
		$this->wxConfig['app_id'] = $appId;
		$this->wxConfig['app_secret'] = $appSecret;
		$this->wxConfig['mch_id'] = $mchId;
	}

	/**
	 * 单例出口
	 * @param  string
	 * @param  string
	 * @return [instance]
	 */
	public static function instance(string $appId, string $appSecret)
	{
		if (empty($appId) || empty($appSecret)) {
			exit('请把appid、appsecret填写完整！');
		}
		if (self::$instance && self::$instance instanceof self) {
			return self::$instance;
		}
		self::$instance = new self($appId, $appSecret);
		return self::$instance;
	}

	/**
	 * 登录凭证校验，jscode2session
	 * @param  string jscode
	 * @return [array]
	 */
	public function login(string $jsCode)
	{
		if (empty($jsCode)) {
			exit('请传入jscode！');
		}
		try {
			require 'Curl.php';
			$curl = Curl::instance();
			$result = $curl->get($this->wxApiUrl . '/sns/jscode2session', [
				'appid'=> $this->wxConfig['app_id'],
				'secret'=> $this->wxConfig['app_secret'],
				'js_code'=> trim($jsCode),
				'grant_type'=> 'authorization_code'
			]);
			return json_decode($result, true);
		} catch (\Exception $e) {
			exit($e->getMessage());
		}
	}

	/**
	 * 获取小程序全局唯一后台接口调用凭据access_token,调用大多数后台接口都需access_token
	 * @return [array]
	 */
	public function getAccessToken()
	{
		try {
			require 'Curl.php';
			$curl = Curl::instance();
			$result = $curl->get($this->wxApiUrl . '/cgi-bin/token', [
				'grant_type'=> 'client_credential',
				'appid'=> $this->wxConfig['app_id'],
				'secret'=> $this->wxConfig['app_secret']
			]);
			return json_decode($result, true);
		} catch (\Exception $e) {
			exit($e->getMessage());
		}
	}

	/**
	 * 用户支付完成后，获取该用户的UnionId，无需用户授权
	 * 注意：调用前需要用户完成支付，且在支付后的五分钟内有效
	 * @param string $value [description]
	 */
	public function FunctionName(string $accessToken, string $openId, string $transactionId = '', string $outTradeNo = '')
	{
		if (empty($accessToken)) {
			exit('请传入access_token！');
		}
		try {
			require 'Curl.php';
			$curl = Curl::instance();
			$result = $curl->get($this->wxApiUrl . '/wxa/getpaidunionid', [
				'access_token' => trim($accessToken),
				'openid' => $openId
			]);
			return json_decode($result, true);
		} catch (\Exception $e) {
			exit($e->getMessage());
		}
	}
}