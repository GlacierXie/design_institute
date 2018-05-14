<?php
namespace app\home\Util;
class Wechat
{
	public function post($url, $data = null)
	{
	   	$curl = curl_init();
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	    if (!empty($data))
	    {
	        curl_setopt($curl, CURLOPT_POST, 1);
	        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	    }

	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	    $output = curl_exec($curl);
	    curl_close($curl);
	    return $output;
	}

	public function getAccessToken()
	{
		//$appid = C('WECHAT_APPID');
      	//$appsecret = C('WECHAT_APPSECRET');
		
		//获取access_token
      	$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx39535e8f079a2b4c&secret=ce16533145b9e285e67a134c169e9df6";
      	$json =  $this->post($url);
      	$array = json_decode($json, true);
      	$array['time'] = time();
      	file_put_contents(APP_PATH."home/access_token.txt", json_encode($array));
      	return $array["access_token"];
	}
}
