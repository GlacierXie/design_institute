<?php
namespace app\home\controller;
use think\Controller;
use app\home\Util\Wechat;
use app\home\model\Users;
use think\Db;
//场景1的二维码
class Qrcode extends Controller {
    public function index(){

        //场景id
        $scene_id = file_get_contents(APP_PATH."Home/wechat_scene_max_id.txt");

        if($scene_id >= 4294967295)
            $scene_id = 2;
        file_put_contents(APP_PATH."Home/wechat_scene_max_id.txt", $scene_id+1);

        // access_token
        //import("@.Util.Wechat");
        //include EXTEND_PATH.'Util/Wechat.php';
        //$wechatUtil = new \Wechat;
        $wechatUtil = new Wechat();
        $access_token = $wechatUtil->getAccessToken();
        // ticket
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$access_token";
        $data = '{"expire_seconds":604800, "action_name": "QR_SCENE", "action_info":{"scene":{"scene_id":'. $scene_id .'}}}';
        $json = $wechatUtil->post($url, $data);
        $array = json_decode($json, true);
        $ticket = $array["ticket"];
        // qrcode
        $url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=$ticket";
        echo '{"scene_id":'.$scene_id.',"url":"'.$url.'"}';
    }

    //场景1扫码的时候，不断轮询该方法, 返回用户信息
    public function pollScan(){
        $scene_id = $_GET['scene_id'];

//        $Login = M('Login');
//        $result = $Login->where("scene_id=$scene_id")->find();
        $result=Db::table('ipm_login')->where('scene_id',$scene_id)->find();

        if($result)
        {
            $openid=$result['openid'];
            $userTbale=new Users();
            $res=$userTbale->select_users($openid);
            if($res){
                $userInfo = Db::table('ipm_inst_user')->where('openid',$openid)->find();
                if($userInfo)
                echo json_encode($userInfo, JSON_UNESCAPED_UNICODE);
                else
                echo "";

            }
        }
        else
        {
            echo "";
        }
        
    }
}