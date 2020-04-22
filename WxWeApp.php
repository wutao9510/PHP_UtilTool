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

	private function __construct(string $appId, string $appSecret)
	{
		$this->wxConfig['app_id'] = $appId;
		$this->wxConfig['app_secret'] = $appSecret;
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

	public function FunctionName($value='')
	{
		# code...
	}
}