<?php
namespace app\home\controller;
use think\Controller;
use app\home\model\Users;
use app\home\model\Userinst;
use think\Db;
use think\Cache;
use think\Request;

class User extends Controller
{
    public  function selectUser(){
        if(request()->isGet()) {
            $openid= input('openid');
            if(empty($openid)){
                return json ();
            }
            $userTbale=new Users();
            $res=$userTbale->select_users($openid);
            if($res)
            {
                $sql = "select a.company_id,c.name as company_name,a.status,
                b.real_name as nickname 
                from ipm_inst_user as a
                left join ipm_user as b on b.openid=a.openid
                left join ipm_inst_company as c on a.company_id=c.id
                where a.openid = '$openid'";

                $read=Db::query($sql);
                if(!isset($read) || empty($read))
                  return json ();
                $result['company_id'] = $read[0]['company_id'];
                $result['company_name'] = $read[0]['company_name'];
                $result['status'] = $read[0]['status'];
                $result['nickname'] = $read[0]['nickname'];
                return json ($result);
            }
            else
            {
                $res['company_id'] = -1;
                $res['company_name'] = "";
                $res['status'] = -1;
                $res['nickname'] = "";
                return json ($res);
            }
        }
    }

    private function checkRequestData()
    {
        $json = file_get_contents("php://input");
        if (empty($json)) {
            $res['success'] = false;
            $res['message'] = 'Empty RequestData';
            return json ($res);

        }

        $read = json_decode($json,true);
        if (is_null($read)) {
            $res['success'] = false;
            $res['message'] = "json_decode_error";
            return json ($res);

        }
        return $read;
    }
}