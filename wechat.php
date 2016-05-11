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
}