<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Cache;
use think\Request;
use app\home\Util\Wechat;

class Message extends Controller
{
 	public function index()
 	{
 		$wechat = new Wechat();
 		$access_token = $wechat->getAccessToken();
 		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";

 		$touser = 'ovMfqvsRZhlwlD3i53Nj26tf8G-M';
 		$template_id ='VwytmOOTU4AtzN7LpIISxkv2MQ3RuGI6anFYtq3elE0';
 		$first['value'] = '您好，您收到新的任务';
 		$first['color'] = '#173177';
 		$keyword1['value'] = 'T00000001';
 		$keyword1['color'] = '#173177';
 		$keyword2['value'] = '楼板';
 		$keyword2['color'] = '#173177';
 		$keyword3['value'] = '陈新颖';
 		$keyword3['color'] = '#173177';
 		$keyword4['value'] = '彭雪莲';
 		$keyword4['color'] = '#173177';
 		$keyword5['value'] = '2017年12月25日';
 		$keyword5['color'] = '#173177';
 		$remark['value'] = '请在xxxx前完成任务';
 		$remark['color'] = '#173177';

 		$data['first'] = $first;
 		$data['keyword1'] = $keyword1;
 		$data['keyword2'] = $keyword2;
 		$data['keyword3'] = $keyword3;
 		$data['keyword4'] = $keyword4;
 		$data['keyword5'] = $keyword5;
 		$data['remark'] = $remark;

 		$msg['touser'] = $touser;
 		$msg['template_id'] = $template_id;
 		$msg['data'] = $data;

 		$wechat->post($url, json_encode($msg));
 	}
    //通知检查组以及设计组的模长
    public function PushMessage($data){
        $task_name=$data['task_name'];
        $filestate=$data['filestate'];
        $user=$data['user'];
        $subproject_name=$data['subproject_name'];
        $subproject_id=$data['subproject_id'];
        $lists=Db::query("
          select a.openid from ipm_inst_user_role as a LEFT JOIN ipm_inst_subproject_user as b on a.openid=b.openid
           where ((a.role_id=7 and a.master=1) || b.role_id='6') and b.subproject_id='$subproject_id' group by a.openid");

        if(!empty($lists)){
            foreach($lists as $k=>$v){
                $openid=$lists[$k]['openid'];
                $wechat = new Wechat();
                $access_token = $wechat->getAccessToken();
                $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";
                $template_id ='eff2JP9-JikQSx9S74U5Mi5awQChd2zp5Z2EKToa95s';
                $first['value'] = $user.'上传了成果('.$subproject_name.')';
                $first['color'] = '#173177';
                $keyword1['value'] =$task_name;
                $keyword1['color'] = '#173177';
                $keyword2['value'] = $filestate;
                $keyword2['color'] = '#173177';
                $keyword3['value'] = date("Y-m-d H:i:s");
                $keyword3['color'] = '#173177';
                $data['first'] = $first;
                $data['keyword1'] = $keyword1;
                $data['keyword2'] = $keyword2;
                $data['keyword3'] = $keyword3;
                $msg['touser'] = $openid;
                $msg['template_id'] = $template_id;
                $msg['data'] = $data;
                $wechat->post($url, json_encode($msg));
            }
        }
    }
    //通知这个参与这个项目并且是检查组的人
    public function PushMessage1($data){
        $task_name=$data['task_name'];
        $filestate=$data['filestate'];
        $user=$data['user'];
        $subproject_id=$data['subproject_id'];
        $subproject_name=$data['subproject_name'];
        $roleTable=Db::table('ipm_inst_subproject_user')->where("role_id=6 and subproject_id=$subproject_id")->select();
        if(!empty($roleTable)){
            foreach($roleTable as $k=>$v){
                $openid=$roleTable[$k]['openid'];
                $wechat = new Wechat();
                $access_token = $wechat->getAccessToken();
                $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";
                $template_id ='eff2JP9-JikQSx9S74U5Mi5awQChd2zp5Z2EKToa95s';
                $first['value'] = $user.'上传了成果('.$subproject_name.')';
                $first['color'] = '#173177';
                $keyword1['value'] =$task_name;
                $keyword1['color'] = '#173177';
                $keyword2['value'] = $filestate;
                $keyword2['color'] = '#173177';
                $keyword3['value'] = date("Y-m-d H:i:s");
                $keyword3['color'] = '#173177';
                $data['first'] = $first;
                $data['keyword1'] = $keyword1;
                $data['keyword2'] = $keyword2;
                $data['keyword3'] = $keyword3;
                $msg['touser'] = $openid;
                $msg['template_id'] = $template_id;
                $msg['data'] = $data;
                $wechat->post($url, json_encode($msg));
            }
        }
    }
    //上传终版底图任务，通知检查组或者设计签收的人
    public function PushMessage2($data){
        $openid=$data['openid'];
        $user=$data['user'];
        $task_name=$data['task_name'];
        $filestate=$data['filestate'];
        $subproject_name=$data['subproject_name'];
        $subproject_id=$data['subproject_id'];
        $roleTable=Db::table('ipm_inst_subproject_user')->where("role_id=6 and subproject_id=$subproject_id")->select();

        if(!empty($roleTable)){
            foreach($roleTable as $k=>$v) {
                $users[] = $v['openid'];
            }

            //把 $openid 加到$users数组里面，组成新的数组
            array_push($users, "$openid");
            //去除重复的值
            $aa=array_unique($users);
            foreach($aa as $kk=>$vv){
                $touser=$vv;
                $wechat = new Wechat();
                $access_token = $wechat->getAccessToken();
                $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";
                $template_id ='eff2JP9-JikQSx9S74U5Mi5awQChd2zp5Z2EKToa95s';
                $first['value'] = $user.'上传了成果('.$subproject_name.')';
                $first['color'] = '#173177';
                $keyword1['value'] =$task_name;
                $keyword1['color'] = '#173177';
                $keyword2['value'] = $filestate;
                $keyword2['color'] = '#173177';
                $keyword3['value'] = date("Y-m-d H:i:s");
                $keyword3['color'] = '#173177';
                $data['first'] = $first;
                $data['keyword1'] = $keyword1;
                $data['keyword2'] = $keyword2;
                $data['keyword3'] = $keyword3;
                $msg['touser'] = $touser;
                $msg['template_id'] = $template_id;
                $msg['data'] = $data;
                $wechat->post($url, json_encode($msg));
            }
        }
    }
    public function PushMessageTaskStatus($arr){
       // $task_name=$arr['task_name'];
        $openid=$arr['openid'];
        $subprj_name=$arr['subprj_name'];
        $subprj_id=$arr['subprj_id'];
        $roleTable=Db::table('ipm_inst_subproject_user')->where( 'subproject_id',$subprj_id)->select();
        $state=Db::table('ipm_inst_subproject')->where( 'id',$subprj_id)->value('state');
        if($state==1) {
            $state='项目已立项,底图待深化';
        }elseif($state==2) {
            $state='底图已深化,底图深化待审核';
        }elseif($state==3){
            $state='底图深化已审核，待标准层下单';
        }elseif($state==4){
            $state='标准层已下单,待变化层下单';
        }elseif($state==5){
            $state='变化层已下单，待归档';
        }elseif($state==6){
            $state='项目已归档';
        }
        if(!empty($roleTable)) {
            foreach ($roleTable as $k => $v) {
                $users[] = $v['openid'];
            }
            array_push($users, "$openid");
            //去除重复的值
            $aa=array_unique($users);
            foreach($aa as $kk=>$vv) {
                $touser = $vv;
                $wechat = new Wechat();
                $access_token = $wechat->getAccessToken();
                $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";
                $template_id = 'xMS4moszdXP3_nD2P38djA6b8rIJ2tOc9lGa1ieW8jU';
                $first['value'] = '底图签收完成提醒';
                $first['color'] = '#173177';
                $keyword1['value'] = $subprj_id;
                $keyword1['color'] = '#173177';
                $keyword2['value'] = $subprj_name;
                $keyword2['color'] = '#173177';
                $keyword3['value'] =$state;
                $keyword3['color'] = '#173177';
                $remark['value'] = '任务已经完成';
                $remark['color'] = '#173177';
                $data['first'] = $first;
                $data['keyword1'] = $keyword1;
                $data['keyword2'] = $keyword2;
                $data['keyword3'] = $keyword3;
                $data['remark'] = $remark;
                $msg['touser'] = $touser;
                $msg['template_id'] = $template_id;
                $msg['data'] = $data;
                $wechat->post($url, json_encode($msg));
            }
        }
    }


}