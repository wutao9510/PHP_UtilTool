<?php 
namespace Chenmu\Wechat;

use Chenmu\Sys\Curl;

/**
 * 小程序服务端类
 * wx weapp
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
		try {
			if (empty($jsCode)) {
				throw new \Exception("请传入jscode！", 1);
			}
			$result = Curl::instance()->get($this->wxApiUrl . '/sns/jscode2session', [
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
			$result = Curl::instance()->get($this->wxApiUrl . '/cgi-bin/token', [
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
	 * @param  string $accessToken
	 * @param  string $openId
	 * @param  array  $orderInfo
	 * @return [array]
	 */
	public function getPaidUnionId(string $accessToken, string $openId, array $orderInfo = [])
	{
		try {
			if (empty($accessToken)) {
				throw new \Exception("请传入access_token！", 1);
			}
			$param = array_merge([
				'access_token' => trim($accessToken),
				'openid' => $openId
			], $orderInfo);
			$result = Curl::instance()->get($this->wxApiUrl . '/wxa/getpaidunionid', $param);
			return json_decode($result, true);
		} catch (\Exception $e) {
			exit($e->getMessage());
		}
	}

	/**
	 * 获取用户访问小程序日留存 post
	 * 限定查询1天数据，允许设置的最大值为昨日,格式为yyyymmdd！
	 * @param  string $accessToken
	 * @param  string $begin
	 * @param  string $end
	 * @return [array]
	 */
	public function getDailyRetain(string $accessToken, string $begin, string $end)
	{
		try {
			if (empty($accessToken)) {
				throw new \Exception("请传入access_token！", 1);
			}
			if ($begin != $end || strtotime($begin) > time()) {
				throw new \Exception("限定查询1天数据，允许设置的最大值为昨日,格式为yyyymmdd！", 1);
			}
			$result = Curl::instance()->postRawData($this->wxApiUrl . '/datacube/getweanalysisappiddailyretaininfo?access_token=' . $accessToken, [
				'begin_date'=> $begin,
				'end_date'=> $end
			]);
			return json_decode($result, true);
		} catch (\Exception $e) {
			exit($e->getMessage());
		}
	}
	
	/**
	 * 获取用户访问小程序月留存 post
	 * 开始日期为自然月第一天，结束日期，为自然月最后一天，限定查询一个月数据，格式为yyyymmdd
	 * @param  string $accessToken
	 * @param  string $begin
	 * @param  string $end
	 * @return [array]
	 */
	public function getMonthlyRetain(string $accessToken, string $begin, string $end)
	{
		try {
			if (empty($accessToken)) {
				throw new \Exception("请传入access_token！", 1);
			}
			if ((int)date('m', strtotime($begin)) > (int)date('m') || (int)date('d', strtotime($begin) != 1)) {
				throw new \Exception("开始日期为自然月第一天，格式为yyyymmdd！", 1);
			}
			$result = Curl::instance()->postRawData($this->wxApiUrl . '/datacube/getweanalysisappidmonthlyretaininfo?access_token=' . $accessToken, [
				'begin_date'=> $begin,
				'end_date'=> $end
			]);
			return json_decode($result, true);
		} catch (\Exception $e) {
			exit($e->getMessage());
		}
	}

	/**
	 * 获取用户访问小程序周留存 post
	 * 开始日期为周一日期，结束日期，为周日日期，限定查询一周数据，格式为yyyymmdd
	 * @param  string $accessToken
	 * @param  string $begin
	 * @param  string $end
	 * @return [array]
	 */
	public function getWeeklyRetain(string $accessToken, string $begin, string $end)
	{
		try {
			if (empty($accessToken)) {
				throw new \Exception("请传入access_token！", 1);
			}
			if (date('w', strtotime($begin)) != 1 || date('w', strtotime($end) != 0)) {
				throw new \Exception("开始日期为周一日期，结束日期，为周日日期，限定查询一周数据，格式为yyyymmdd！", 1);
			}
			$result = Curl::instance()->postRawData($this->wxApiUrl . '/datacube/getweanalysisappidweeklyretaininfo?access_token=' . $accessToken, [
				'begin_date'=> $begin,
				'end_date'=> $end,
			]);
			return json_decode($result, true);
		} catch (\Exception $e) {
			exit($e->getMessage());
		}
	}

}