<?php
namespace wechat;

/**
 * 获取微信用户code,openid,和用户信息(昵称头像)
 */
class wechat{
	private $appid;
	private $secret;
	public function __construct($appid,$secret)
	{
		$this->appid=$appid;
		$this->secret=$secret;
	}

	/**
	 * 获得code的获取地址
	 * @param  [type] $redirecturi 获取code后的跳转地址(不能带参)
	 * @return [type]              [description]
	 */
	public function getcodeurl($redirecturi)
	{
		$redirecturi=urlencode($redirecturi);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$this->appid&redirect_uri=$redirecturi&response_type=code&scope=snsapi_userinfo&state=getcode#wechat_redirect";
	}

	/**
	 * 根据code获取openid
	 * @param  [type] $code 
	 * @return [type]       [description]
	 */
	public function getopenid($code)
	{
		$url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appid&secret=$this->secret&code=$code&grant_type=authorization_code";
		return json_decode(file_get_contents($url));
	}

	/**
	 * 根据openid 和 access_token 获取用户信息
	 * @param  [type] $openid       [description]
	 * @param  [type] $access_token [description]
	 * @return object               包含用户信息的对象
	 */
	public function getuserinfo($openid,$access_token)
	{	
		$url="https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid";
		return json_decode(file_get_contents($url));
	}


	/**
	 * 签名
	 * @return [type] [description]
	 */
	public function sign($url,$timestamp,$noncestr)
	{
		// $ticket=$this->jsapi_ticket($access_token);
		$access_token=$this->gettoken();
		$ticket=$this->getticket($access_token);

		$params['jsapi_ticket']=$ticket;
		$params['url']=$url;
		$params['timestamp']=$timestamp;
		$params['noncestr']=$noncestr;
		ksort($params);

		$str='';
		foreach ($params as $key => $value)
			$str.="&$key=$value";
		$str=substr($str, 1);
		return sha1($str);
	}

	/**
	 * 获取ticket,缓存在SESSION中
	 * @param  [type] $access_token [description]
	 * @return [type]               [description]
	 */
	public function getticket($access_token)
	{
		session_start();
		if(!isset($_SESSTION['ticket']) || $_SESSTION['ticket']['expire']<time())
		{
			$ticket=$this->jsapi_ticket($access_token);
			$_SESSTION['ticket']=[
				'val'=>$ticket->ticket,
				'expire'=>time()+$ticket->expire
			];
			return $ticket->ticket;
		}else
			return $_SESSTION['ticket']['val'];
	}

	/**
	 * 根据access_token获得jsapi_ticket
	 * @param  [type] $access_token [description]
	 * @return [type]               [description]
	 */
	public function jsapi_ticket($access_token)
	{
		$url="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access_token&type=jsapi";
		return json_decode(file_get_contents($url));
	}

	/**
	 * 获得accesstoken,缓存在SESSION中
	 * @return [type] [description]
	 */
	public function gettoken()
	{
		session_start();
		if(!isset($_SESSTION['access_token']) || $_SESSTION['access_token']['expire']<time())
		{
			$url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appid&secret=$this->secret";
			$access_token=json_decode(file_get_contents($url));
			$_SESSTION['access_token']=[
				'val'=>$access_token->access_token,
				'expire'=>time()+$access_token->expire
			];
			return $access_token->access_token;
		}else
			return $_SESSTION['access_token']['val'];
	}
}