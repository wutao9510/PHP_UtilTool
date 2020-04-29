<?php
namespace Chenmu\Wechat;

class WxpayEnum
{
    private function __construct(){}

    # 支付错误码
    protected static $errInfo = [
        # Common
        'SYSTEMERROR' => '系统错误，请重试！',
        'XML_FORMAT_ERROR' => 'XML格式错误！',
        'REQUIRE_POST_METHOD' => '未使用post传递参数!',
        'SIGNERROR' => '参数签名结果不正确!',
        'APPID_NOT_EXIST' => '参数中缺少appId！',
        'MCHID_NOT_EXIST' => '参数中缺少mchId！',
        'NOT_UTF8' => '编码格式错误!',

        # 统一下单错误码
        'UNIORDER_INVALID_REQUEST' => '参数错误或者未按规则上传!',
        'UNIORDER_NOAUTH' => '商户未开通此接口权限!',
        'UNIORDER_NOTENOUGH' => '用户帐号余额不足!',
        'UNIORDER_ORDERPAID' => '订单已支付，无需重复操作!',
        'UNIORDER_ORDERCLOSED' => '订单已关闭!',
        'UNIORDER_APPID_MCHID_NOT_MATCH' => 'appid和mch_id不匹配!',
        'UNIORDER_LACK_PARAMS' => '缺少参数!',
        'UNIORDER_OUT_TRADE_NO_USED' => '同一笔交易不能多次提交!',
        'UNIORDER_POST_DATA_EMPTY' => 'post数据为空!',

        # 查询订单错误码
        'QUERYORDER_ORDERNOTEXIST' => '不存在此交易订单号!',

        # 关闭订单错误码
        'CLSORDER_ORDERPAID' => '订单已支付，不能发起关单!',
        'CLSORDER_ORDERCLOSED' => '订单已关闭，无法重复关闭!',

        # 退款错误码
        'REFUND_BIZERR_NEED_RETRY' => '退款业务流程错误，请重试！',
        'REFUND_TRADE_OVERDUE' => '订单已经超过退款期限！',
        'REFUND_ERROR' => '业务错误！',
        'REFUND_USER_ACCOUNT_ABNORMAL' => '用户帐号注销！',
        'REFUND_INVALID_REQ_TOO_MUCH' => '连续错误请求数过多被系统短暂屏蔽，请在1分钟后重试！',
        'REFUND_NOTENOUGH' => '可用退款余额不足！',
        'REFUND_INVALID_TRANSACTIONID' => '无效transaction_id！',
        'REFUND_PARAM_ERROR' => '参数错误！',
        'REFUND_FREQUENCY_LIMITED' => '2个月之前的订单申请退款有频率限制！',
        'REFUND_NOAUTH' => '请求ip异常！',
        'REFUND_CERT_ERROR' => '请检查证书是否正确，证书是否过期或作废！',
        'REFUND_FEE_MISMATCH' => '订单金额或退款金额与之前请求不一致！',
        'REFUND_INVALID_REQUEST' => '请求参数符合参数格式，但不符合业务规则！',
        'REFUND_ORDER_NOT_READY' => '订单处理中，暂时无法退款，请稍后再试 ！'
    ];

    /**
     * 获取错误码信息
     * @param  string $errCode
     * @return string
     */
    public static function getErrInfo(string $errCode): string
    {
        return empty($errCode) ? '' : self::$errInfo[trim($errCode)];
    }
}
