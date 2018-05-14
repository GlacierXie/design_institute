<?php
namespace app\admin\controller;
use app\home\model\Subprojects;
use think\Controller;
use app\home\model\Users;
use app\home\model\Userinst;
use app\home\model\Projects;
use think\Db;
use think\Cache;
use think\Request;
use app\home\Util\Wechat;
require_once ROOT_PATH.'application\\common\\OssCommon.php';
use app\common\OssCommon;

class User extends Controller
{
    //ipm用户列表
    /**
     * @return \think\response\Json
     */
    public function selectUser(){
        if(request()->isGet()) {
            $openid= input('openid');
            if(!isset($openid)  || empty($openid)){
                return json ();
            }
            //首先判断是不是IPM 用户
            $userTbale=new Users();
            $res=$userTbale->select_users($openid);
            if($res){
                $UserinstTbal=new Userinst();
                //然后再查是否是设计院用户
                $read=$UserinstTbal->select_users($openid);
                if($read){
                    $result['company_id'] = $read[0]['id'];
                    $result['company_name'] = $read[0]['name'];
                    $result['status'] = $read[0]['status'];
                    $result['nickname'] = $read[0]['nickname'];
                    $result['headimgurl'] = $read[0]['headimgurl'];
                    $result['real_name'] = $read[0]['real_name'];
                    $result['mobile_phone'] = $read[0]['mobile_phone'];
                    $result['qq'] = $read[0]['qq'];
                    $result['location']= $read[0]['country'].' '.$read[0]['province'].' '.$read[0]['city'];
                   return json ($result);
                }else{
                    $result['company_id'] = -1;
                    $result['company_name'] = "";
                    $result['status'] = -1;
                    return json ($result);
                }
            }else{
                $res['company_id'] = -1;
                $res['company_name'] = "";
                $res['status'] = -1;
                return json ($res);
            }
        }
    }
    //imp 用户查询
    public function ipm_user_list(){
        $arr= $this->request->param();
        $company_id=$arr['company_id'];
        $currentpage=$arr['currentpage'];
        $itemsPerPage=$arr['itemsPerPage'];
		$getType = $arr['getType'];
        $userTable=new Users();
        $UserinstTable=new Userinst();
		
		if($getType == 1)
		{
			//所有用户
			$res=$userTable->ipm_users($currentpage,$itemsPerPage);
			foreach($res as $kk=>$vv){
				$id=$res[$kk]['openid'];
				$res1=$UserinstTable->inst_userlist($company_id,$id);
				
				if($res1){
					$res[$kk]['company_id']=1;
				}else{
					$res[$kk]['company_id']=0;
				}
			}
			return json ($res);
		}
		else if($getType == 2){
			//查设计院用户
			$res = $UserinstTable->inst_user_join_users($company_id);
			return json ($res);
		}

    }
    //设计院用户查询
    public function ipm_inst_userlist(){
        $UserinstTable=new Userinst();
        $currentpage=1;
        $itemsPerPage=20;
        $res=$UserinstTable->inst_user_list($currentpage,$itemsPerPage);
        return json ($res);

    }
	//模糊查找
	public function fuzzSearch()
	{
		$arr= $this->request->param();
        $fuzz_var=$arr['fuzz_var'];
		$userTbale=new Users();
		$UserinstTable=new Userinst();
        $result = $userTbale->fuzz_search($fuzz_var);
		
		foreach($result as $kk=>$vv){
            $id=$result[$kk]['openid'];
            $res1=$UserinstTable->inst_userlist(1,$id);
			
            if($res1){
                $result[$kk]['company_id']=1;
            }else{
                $result[$kk]['company_id']=0;
            }
        }
		return json ($result);
	}
	
    //  修改用户
    public function update_inst_user(){
        $arr= $this->request->param();
        $data['telphone']=$arr['telphone'];
        $data['qq']=$arr['qq'];
        $data['nickname']=$arr['nickname'];
        $data['openid']=$arr['openid'];
        $UserTable=new Users();
        $res=$UserTable->update_ipm_users($data);
        if($res){
            $res['success'] = true;
            $res['message'] = "success";
            return json ($res);
        }else{
            $res['success'] = false;
            $res['message'] = 'error';
            return json ($res);
        }

    }
    //本人所参与的所有项目信息
    public  function user_project_list(){
        if(request()->isGet()) {
            $openid= input('openid');
            if(!isset($openid) || empty($openid)){
                return json ();
            }
            $userTbale=new Users();
            $subproject_list=$userTbale->subproject_user_list($openid);
            $project_list = array();    //总项目列表
            $ossClient = OssCommon::getOssClient(true);
            if($ossClient == null)
              return json();

            //遍历用户参与的所有子项目
            foreach ($subproject_list as $kk => $vv) {
                $project_id = $subproject_list[$kk]['project_id'];
                $list=$userTbale->project_config_users($project_id);
                $bFound = false;//是否找到总项目
                foreach($project_list as $k=>$one_project)
                {

                    if($one_project['project_id'] == $list[0]['project_id'])
                    {
                        $bFound = true;
                        $project_list[$k]['subproject_list'][] = $subproject_list[$kk];
                        break;
                    }
                }
                if(!$bFound)
                {
                    $one_project['project_id'] = $list[0]['project_id'];
                    $one_project['name'] = $list[0]['name'];
                    $one_project['creator_id'] = $list[0]['creator_id'];
                    $one_project['creator_nickname'] = $list[0]['creator_nickname'];
                    $one_project['config_id'] = $list[0]['config_id'];
                    $one_project['config_name'] = $list[0]['config_name'];
                    $fileNameArry = explode(".",  $one_project['config_name']);
                    //获取数组最后一位
                    $fileNameTitle = end($fileNameArry);
                    $one_project['config_url'] = OssCommon::downloadUrl($ossClient,$list[0]['company_id'].'/'.'configFiles/'.$list[0]['config_id'].'.'.$fileNameTitle);
                    $one_project['state'] = $list[0]['state'];
                    $one_project['start_time_plan'] = $list[0]['start_time_plan'];
                    $one_project['end_time_plan'] = $list[0]['end_time_plan'];
                    $one_project['start_time_real'] = $list[0]['start_time_real'];
                    $one_project['end_time_real'] = $list[0]['end_time_real'];
                    $one_project['subproject_list'][] =  $subproject_list[$kk];

                    $project_list[] = $one_project;
                }
            }
            return json($project_list);
        }
    }
    public function Userlist(){
        //get 接收
        if(request()->isGet()) {
            $company_id = input('company_id');
            $currentpage = 1;
            $itemsPerPage = 20;
            // $status = input('status');
            if (!isset($company_id) || empty($company_id)) {
                return json('111');
            }

            $userTbale = new Userinst();
            //根据company_id 和status来查设计管理平台用户
            $res = $userTbale->select_Liset($company_id);
        }
        return json($res);
    }
    public function UserProjectTask(){
        $arr= $this->request->param();
        if (!isset($arr['openid']) || empty($arr['openid']) ) {
            return json('openid empty');
        }
        $openid = $arr['openid'];
        $projectTable = new Projects ();
        $res = $projectTable->project_name($openid);
        foreach ($res as $kk => $vv) {
            $sub_id = $res [$kk]['sub_id'];
            //这个人所参与的子项目下面的任务名称
            $res[$kk]['task_name'] = $projectTable->task_name($sub_id,$openid);
        }
        return json($res);
    }
    // 添加用户
    public  function add_user(){
        $arr= $this->request->param();
        if (!isset($arr['openid']) || empty($arr['openid']) ) {
            return json('111');
        }
        if (!isset($arr['company_id']) || empty($arr['company_id']) || !is_numeric($arr['company_id']) || $arr['company_id']<1) {
            return json('222');
        }
        if (!isset($arr['status']) || empty($arr['status']) || !is_numeric($arr['status']) || $arr['status']<1) {
            return json('333');
        }
        $data['openid']=$arr['openid'];
        $data['company_id']=$arr['company_id'];
        $data['status']=$arr['status'];
        $data['create_time']=date("Y-m-d H:i:s");
        $data['update_time']=date("Y-m-d H:i:s");
        $userTable= new Users();
        $res=$userTable->select_users($data['openid']);
        if($res){
            $user_id=Db::table('ipm_inst_user')->insertGetId($data);
            if($user_id){
                $res['success'] = true;
                $res['message'] = "success";
                return json ($res);
            }
        }else{
            //不是IPM用户
            return json('111');
        }
    }
    //添加设计院管理平台用户
        public function add_ipminst_user(){
        $arr= $this->request->param();
        if (!isset($arr['openid']) || empty($arr['openid']) )
        {
            return json('openid为空');
        }
        if (!isset($arr['company_id'])  || !is_numeric($arr['company_id']))
        {
            return json('company_id为空');
        }
        $result=Db::table('ipm_inst_user')->where('openid',$arr['openid'])->find();
        if($result)
        {
            $res['success'] = false;
            $res['message'] = "用户存在";
        }else
        {
            $arr1 = array(
                "openid" =>$arr['openid'],
                "company_id" =>$arr['company_id'],
                "status" =>1,
                "create_time" => date("Y-m-d H:i:s"),
                "update_time" => date("Y-m-d H:i:s")
            );
            $inst_user=Db::table('ipm_inst_user')->insert($arr1);
            if ($inst_user) {
                $data = array(
                    "openid" => $arr['openid']
                );
                $result1 = Db::table('ipm_inst_user_role')->insert($data);
                if ($result1) {
                    $res['success'] = true;
                    $res['message'] = "success";
                    echo json_encode($res);
                    $openid=$arr['openid'];
                    $this->PushMessage($openid);
                    return;
                } else {
                    $res['success'] = false;
                    $res['message'] = "error";
                    echo json_encode($res);
                }
            }
        }

    }
    public function PushMessage($openid){
        $nickname= Db::table('ipm_user')->where('openid',$openid)->value('nickname');
        $wechat = new Wechat();
        $access_token = $wechat->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";
        $template_id ='Ye_U33PxTn0efrJGzsTdUFOpJLUxbs69ElyTtyjQmXo';
        $first['value'] = '添加到设计院提醒';
        $first['color'] = '#173177';
        $keyword1['value'] =$nickname;
        $keyword1['color'] = '#173177';
        $keyword2['value'] = date("Y-m-d H:i:s");
        $keyword2['color'] = '#173177';
        $remark['value'] = '您好，你已成功添加到设计院';
        $remark['color'] = '#173177';
        $data['first'] = $first;
        $data['keyword1'] = $keyword1;
        $data['keyword2'] = $keyword2;
        $data['remark'] = $remark;
        $msg['touser'] = $openid;
        $msg['template_id'] = $template_id;
        $msg['data'] = $data;
        $wechat->post($url, json_encode($msg));

    }
    // 设置权限
    public function add_ipminst_user_role(){
        $arr= $this->request->param();
        if (!isset($arr['openid']) || empty($arr['openid']) )
        {
            return json('openid为空');
        }
        if (!isset($arr['role_id'])  || empty($arr['role_id']))
        {
            return json('role_id为空');
        }
        $role_id=$arr['role_id'];
        $a=false;
        foreach($role_id as $kk=>$vv){
            if($vv==1){
                $a=true;
                break;
            }
        }
        if($a){
            $status=2;
        }else{
            $status=1;
        }
        $data_r['status']=$status;
		Db::startTrans();
		try{
			$updata_role=Db::table('ipm_inst_user')->where('openid',$arr['openid'])->update($data_r);
			$result=Db::table('ipm_inst_user_role')->where('openid',$arr['openid'])->delete();
			if($result){
				foreach ($role_id as $k2 => $v2) {
					$data = array(
						"openid" => $arr['openid'],
						"role_id" => $v2
					);
					$result1 = Db::table('ipm_inst_user_role')->insert($data);

				}
			}
			//事物提交
		Db::commit();
		}catch (\Exception $e) {
			// 回滚事务
			Db::rollback();
		}
        if ($result1) {
            $res['success'] = true;
            $res['message'] = "success";
            echo json_encode($res);
        } else {
            $res['success'] = false;
            $res['message'] = "error";
            echo json_encode($res);
        }

    }
    //设置模板长
    public function add_modular(){
        $arr= $this->request->param();
        if (!isset($arr['openid']) || empty($arr['openid']) )
        {
            return json('openid为空');
        }
        if (!isset($arr['role_id'])  || !is_numeric($arr['role_id']))
        {
            return json('role_id为空');
        }
        $openid=$arr['openid'];
        $role_id= $arr['role_id'];
        $data['master']=1;
        $result=Db::table('ipm_inst_user_role')->where("role_id='$role_id' and openid='$openid'")->update($data);
        if($result)
        {
            $res['success'] = true;
            $res['message'] = "success";
        }else
        {
            $res['success'] = false;
            $res['message'] = "error";
        }
        return json ($res);
    }
    public function EditModelar(){
        $arr= $this->request->param();
        if (!isset($arr['openid']) || empty($arr['openid']) )
        {
            return json('openid为空');
        }
        if (!isset($arr['role_id'])  || !is_numeric($arr['role_id']))
        {
            return json('role_id为空');
        }

        $openid=$arr['openid'];
        $role_id=$arr['role_id'];
        $data['master']=2;
        $result=Db::table('ipm_inst_user_role')->where("role_id='$role_id' and openid='$openid'")->update($data);
        if($result)
        {
            $res['success'] = true;
            $res['message'] = "success";
        }else
        {
            $res['success'] = false;
            $res['message'] = "error";
        }
        return json ($res);
    }
    //设置组长
    public function add_modular_group (){
        $arr= $this->request->param();
        if (!isset($arr['openid']) || empty($arr['openid']) )
        {
            return json('openid为空');
        }
        if (!isset($arr['role_id'])  || !is_numeric($arr['role_id']))
        {
            return json('role_id为空');
        }
        if (!isset($arr['author'])  || empty($arr['author']))
        {
            return json('author为空');
        }
        $author=$arr['author'];
        $openid=$arr['openid'];
        $a=false;
        foreach($author as $kk=>$vv){
            $id=$vv['id'];
            $modular_group=Db::table('ipm_inst_modular_group')->where("modular_id=$id and openid='$openid'")->find();
            if($modular_group){
                $a=true;
                break;
            }
        }
        if($a){
            $res['success'] = false;
            $res['message'] = "此模板已经设置过了";
        }else{
            foreach($author as $k=>$v) {
                $data = array(
                    "openid" => $arr['openid'],
                    "role_id" => $arr['role_id'],
                    "modular_id" => $v['id'],
                    "groups" => $v['zz']
                );
                $result = Db::table('ipm_inst_modular_group')->insert($data);
                if ($result) {
                    $res['success'] = true;
                    $res['message'] = "success";
                } else {
                    $res['success'] = false;
                    $res['message'] = "error";
                }
            }
        }

        return json ($res);
    }
    public function DelModularGroup(){
        $arr= $this->request->param();
        if (!isset($arr['openid']) || empty($arr['openid']) )
        {
            return json('openid为空');
        }
        if (!isset($arr['role_id'])  || empty($arr['role_id']))
        {
            return json('role_id为空');
        }
        if (!isset($arr['modular_id'])  || empty($arr['modular_id']))
        {
            return json('modular_id为空');
        }
        $openid=$arr['openid'];
        $role_id=$arr['role_id'];
        $modular_id=$arr['modular_id'];
        $result=Db::table('ipm_inst_modular_group')->where("role_id='$role_id' and openid='$openid' and modular_id='$modular_id'")->delete();
        if($result)
        {
            $res['success'] = true;
            $res['message'] = "delete success";
        }else
        {
            $res['success'] = false;
            $res['message'] = "error";
        }
        return json ($res);
    }
    //删除设计院管理平台用户
    public function del_ipminst_user(){
        $arr= $this->request->param();
        if (!isset($arr['openid']) || empty($arr['openid']) ) {
            return json('openid empty');
        }
        $openid=$arr['openid'];
        Db::startTrans();
        try {
        $user_id=Db::table('ipm_inst_user')->where('openid',$openid)->delete();
		$user_id_roles=Db::table('ipm_inst_user_role')->where('openid',$openid)->delete();
		$group=Db::table('ipm_inst_modular_group')->where('openid',$openid)->delete();
               if($user_id){
                   $res['success'] = true;
                   $res['message'] = "success";
                   echo json_encode($res);
                    $this->PushMessage1($openid);

               } else{
                   $res['success'] = false;
                   $res['message'] = "error";
                   echo json_encode($res);
               }
            Db::commit();
            }catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
            }
        }

    public function PushMessage1($openid){
        $nickname= Db::table('ipm_user')->where('openid',$openid)->value('nickname');
        $wechat = new Wechat();
        $access_token = $wechat->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";
        $template_id ='Ye_U33PxTn0efrJGzsTdUFOpJLUxbs69ElyTtyjQmXo';
        $first['value'] = '移出设计院提醒';
        $first['color'] = '#173177';
        $keyword1['value'] =$nickname;
        $keyword1['color'] = '#173177';
        $keyword2['value'] = date("Y-m-d H:i:s");
        $keyword2['color'] = '#173177';
        $remark['value'] = '您好，您已被移出设计院';
        $remark['color'] = '#173177';
        $data['first'] = $first;
        $data['keyword1'] = $keyword1;
        $data['keyword2'] = $keyword2;
        $data['remark'] = $remark;
        $msg['touser'] = $openid;
        $msg['template_id'] = $template_id;
        $msg['data'] = $data;
        $wechat->post($url, json_encode($msg));

    }

    //获得用户授权的openid
    public function getOpenid(){
        if(request()->isGet()) {
            $code= input('code');
            if(!isset($code)  || empty($code)){
                return json ();
            }
            $appid = 'wx39535e8f079a2b4c';
            $appsecret = 'ce16533145b9e285e67a134c169e9df6';
            $json = file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=$code&grant_type=authorization_code");
            
            $array = json_decode($json, true);
            if(!isset($array["openid"]))
                $res["openid"] = "";
            else
                $res["openid"] = $array["openid"];
            return json ($res);    
        }
    }
    //个人的项目统计
   public function UserCount(){
       $arr= $this->request->param();
       $openid=$arr['openid'];
       $SubprojectsTable= new Subprojects();
       $arr=$SubprojectsTable->SubprojectCount($openid);
       $arr1=$SubprojectsTable->TaskCount($openid);
       $arr2=$SubprojectsTable->CreatorProblemCount($openid);
       $arr3=$SubprojectsTable->ChangerProblemCount($openid);
       $arr4=$SubprojectsTable->TaskState1($openid);
       $arr5=$SubprojectsTable->ProblemCountState($openid);
       $result['subproject_count']=$arr[0]['count'];
       $result['task_count']=$arr1[0]['TaskCount'];
       $result['creator_problem_count']=$arr2[0]['CreatorProblemCount'];
       $result['changer_problem_count']=$arr3[0]['ChangerProblemCount'];
       $result['incomplete_task_count']=$arr4[0]['incomplete'];
       $result['unsolve_problem_count']=$arr5[0]['unsolve'];
        return  json_encode($result);


   }
    public function postEditUser(){
        if(request()->isPost()) {
            $real_name= input('real_name');
            $mobile_phone= input('mobile_phone');
            $openid= input('openid');
            if(!isset($openid)  || empty($openid)){
                return json ();
            }
            if(isset($real_name))
            {
                $data['real_name']=$real_name;
                $res=Db::table('ipm_user')->where('openid',$openid)->update($data);
            }
            if(isset($mobile_phone)){
                $data['mobile_phone']=$mobile_phone;
                $res=Db::table('ipm_user')->where('openid',$openid)->update($data);
            }
            if($res){
                $res1['success'] = true;
                $res1['message'] = "update success";
                return json ($res1);
            }
        }
    }
	//20180106修改真实名字
	public function updateRealName(){
		$arr= $this->request->param();
		$openid=$arr['openid'];
		$real_name_new = $arr['real_name_new'];
		Db::query("update ipm_user set real_name='$real_name_new'
						 where openid='$openid'");
		$result['success'] = true;
		 return json ($result);
	}
}