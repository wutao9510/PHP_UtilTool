<?php 
namespace Chenmu\Wechat;

use Chenmu\Sys\Curl;

/**
 * 小程序服务端类
 * Class WxWeApp
 * @package Chenmu\Wechat
 */
class WxWeApp
{
	# 对象实例
	private static $instance = null;

	# 主体配置
	private $wxConfig = [];

	# 网关
	protected static $wxApiUrl = 'https://api.weixin.qq.com';

	private function __clone(){}

	private function __construct(string $appId, string $appSecret, string $mchId = null)
	{
		$this->wxConfig['app_id'] = $appId;
		$this->wxConfig['app_secret'] = $appSecret;
		$this->wxConfig['mch_id'] = $mchId;
	}

    /**
     * 单例出口
     * @param string $appId
     * @param string $appSecret
     * @return WxWeApp|null
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
	 * @return array
	 */
	public function login(string $jsCode)
	{
		try {
			if (empty($jsCode)) {
				throw new \Exception("请传入jscode！", 1);
			}
			$result = Curl::instance()->get(self::$wxApiUrl.'/sns/jscode2session', [
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
	 * @return array
	 */
	public function getAccessToken()
	{
		try {
			$result = Curl::instance()->get(self::$wxApiUrl.'/cgi-bin/token', [
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
     * @param string $accessToken
     * @param string $openId
     * @param array $orderInfo
     * @return mixed
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
			$result = Curl::instance()->get(self::$wxApiUrl.'/wxa/getpaidunionid', $param);
			return json_decode($result, true);
		} catch (\Exception $e) {
			exit($e->getMessage());
		}
	}

    /**
     * 获取用户访问小程序日留存 post
     * 限定查询1天数据，允许设置的最大值为昨日,格式为yyyymmdd！
     * @param string $accessToken
     * @param string $begin
     * @param string $end
     * @return mixed
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
			$result = Curl::instance()->postRawData(self::$wxApiUrl.'/datacube/getweanalysisappiddailyretaininfo?access_token='.$accessToken, [
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
     * @param string $accessToken
     * @param string $begin
     * @param string $end
     * @return mixed
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
			$result = Curl::instance()->postRawData(self::$wxApiUrl.'/datacube/getweanalysisappidmonthlyretaininfo?access_token='.$accessToken, [
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
     * @param string $accessToken
     * @param string $begin
     * @param string $end
     * @return mixed
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
			$result = Curl::instance()->postRawData(self::$wxApiUrl.'/datacube/getweanalysisappidweeklyretaininfo?access_token='.$accessToken, [
				'begin_date'=> $begin,
				'end_date'=> $end
			]);
			return json_decode($result, true);
		} catch (\Exception $e) {
			exit($e->getMessage());
		}
	}

    /**
     * 获取用户访问小程序数据概况 post
     * 限定查询1天数据，允许设置的最大值为昨日。格式为yyyymmdd
     * @param string $accessToken
     * @param string $begin
     * @param string $end
     * @return mixed
     */
	public function getDailySummary(string $accessToken, string $begin, string $end)
	{
		try {
			if (empty($accessToken)) {
				throw new \Exception("请传入access_token！", 1);
			}
			if ($begin != $end || strtotime($begin) > time()) {
				throw new \Exception("限定查询1天数据，允许设置的最大值为昨日,格式为yyyymmdd！", 1);
			}
			$result = Curl::instance()->postRawData(self::$wxApiUrl.'/datacube/getweanalysisappiddailysummarytrend?access_token='.$accessToken, [
				'begin_date'=> $begin,
				'end_date'=> $end
			]);
			return json_decode($result, true);
		} catch (\Exception $e) {
			exit($e->getMessage());
		}
	}

    /**
     * 获取客服消息内的临时素材。即下载临时的多媒体文件。目前仅支持下载图片文件。
     * @param string $accessToken
     * @param string $mediaId
     * @return bool|mixed|string
     */
	public function getTempMedia(string $accessToken, string $mediaId)
	{
		try {
			if (empty($accessToken)) {
				throw new \Exception("请传入access_token！", 1);
			}
			$result = Curl::instance()->get(self::$wxApiUrl.'/cgi-bin/media/get', [
				'access_token'=> $accessToken,
				'media_id'=> $mediaId
			]);
			if (is_array(json_decode($result, true))) {
				return json_decode($result, true);
			}else{
				return $result;
			}
		} catch (\Exception $e) {
			exit($e->getMessage());
		}
	}

    /**
     * 发送客服消息给用户。
     * @param string $accessToken
     * @param array $msg
     * @return mixed
     */
	public function serviceMsgSend(string $accessToken, array $msg)
	{
		try {
			if (empty($accessToken)) {
				throw new \Exception("请传入access_token！", 1);
			}
			if (empty($msg)) {
				throw new \Exception("限定查询1天数据，允许设置的最大值为昨日,格式为yyyymmdd！", 1);
			}
			$result = Curl::instance()->postRawData(self::$wxApiUrl.'/cgi-bin/message/custom/send?access_token='.$accessToken, $msg);
			return json_decode($result, true);
		} catch (\Exception $e) {
			exit($e->getMessage());
		}
	}

    /**
     * 下发客服当前输入状态给用户
     * 对用户下发|取消"正在输入"状态
     * @param string $accessToken
     * @param string $openId
     * @param bool $isTying
     * @return mixed
     */
	public function setTyping(string $accessToken, string $openId, bool $isTying)
	{
		try {
			if (empty($accessToken)) {
				throw new \Exception("请传入access_token！", 1);
			}
			$result = Curl::instance()->postRawData(self::$wxApiUrl.'/cgi-bin/message/custom/typing?access_token='.$accessToken, [
				'touser'=> $openId,
				'command'=> $isTying ? 'Typing' : 'CancelTyping'
			]);
			return json_decode($result, true);
		} catch (\Exception $e) {
			exit($e->getMessage());
		}
	}

}