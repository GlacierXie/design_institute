<?php
namespace app\admin\controller;
use think\Controller;
use app\home\model\Roles;
use app\home\model\Tasks;
use app\home\model\Taskgroups;
use think\Db;
use think\Cache;
use app\home\Util\Wechat;

class Role extends Controller
{
    public function select_role(){
        $userTbale=new Roles();
        $res=$userTbale->roleList();
        return json ($res);
    }
  public function select_modular_role(){
    
    $arr= $this->request->param();
    $tb_role=new Roles();
    $res=$tb_role->modular_roleList($arr['role_id']);
    return json ($res);
  }
    //去重
    function getArrayUniqueByKeys($arr)
    {
        $arr_out =array();
        foreach($arr as $k => $v)
        {
            $key_out = $v['role_id']."-".$v['role_name']; //提取内部一维数组的key(name age)作为外部数组的键
            if(array_key_exists($key_out,$arr_out)){
                continue;
            }
            else{
                $arr_out[$key_out] = $arr[$k]; //以key_out作为外部数组的键
                $arr_wish[$k] = $arr[$k];  //实现二维数组唯一性
            }
        }
        return $arr_wish;
    }
    public function FindUserRoles()
    {
        $arr = $this->request->param();
        if (!isset($arr['currentpage']) || empty($arr['currentpage'])) {
            return json('currentpage empty ');
        }
        if (!isset($arr['itemsPerPage']) || empty($arr['itemsPerPage'])) {
            return json('itemsPerPage empty ');
        }
        $currentpage = $arr['currentpage'];
        $itemsPerPage = $arr['itemsPerPage'];
        $RolesTbale=new Roles();
        if ( isset($arr['company_id']) && !isset($arr['role_id']) && !isset($arr['moddle_id'])) {
      //只有company_id
            $company_id = $arr['company_id'];
			//查设计院所有人
            $list=$RolesTbale->role_master_groups_list($company_id,$currentpage,$itemsPerPage);
			$res = array();
			$count = 0;
			foreach($list as $k=>$v)
			{
				$bl = false;
				
				$arry_r = array('role_id'=>$v['role_id'],'role_name'=>$v['role_name'],'master'=>$v['master']);
				$arry_m = array('role_id'=>$v['role_id'],'groups'=>$v['groups'],'modular_id'=>$v['modular_id'],'modular_name'=>$v['modular_name']);
			
				//去重
				foreach($res as $kk=>$vv)
				{
					
					if($res[$kk]['openid'] != $v['openid'])
					{
						$bl = false;
					}
					else{
						$bl = true;
						//有重复的就拼接
						foreach($res[$kk]['roles_master_List'] as $kkk=>$vvv)
						{
							$bl_2 = false;
							if($vvv['role_id'] != $v['role_id'])
							{
								$bl_2 = false;
							}
							else{$bl_2 =true;break;}
						}
						if(!$bl_2)
							{
								array_push($res[$kk]['roles_master_List'],$arry_r);
							}
						array_push($res[$kk]['moduler_master_List'],$arry_m);
						break;
					}
				}
				if(!$bl)
				{
					//新插入一条
					//$res[$count] = $v;
					$res[$count]['headimgurl'] = $v['headimgurl'];
					$res[$count]['mobile_phone'] = $v['mobile_phone'];
					$res[$count]['nickname'] = $v['nickname'];
					$res[$count]['openid'] = $v['openid'];
					$res[$count]['real_name'] = $v['real_name'];
					$res[$count]['remark'] = $v['remark'];
					$res[$count]['role_id'] = $v['role_id'];
					$arry_r2 = array('role_id'=>$v['role_id'],'role_name'=>$v['role_name'],'master'=>$v['master']);
					$arry_m2 = array('role_id'=>$v['role_id'],'groups'=>$v['groups'],'modular_id'=>$v['modular_id'],'modular_name'=>$v['modular_name']);
					$res[$count]['roles_master_List'] = [];
					$res[$count]['moduler_master_List'] = [];
					array_push($res[$count]['roles_master_List'],$arry_r2);
					array_push($res[$count]['moduler_master_List'],$arry_m2);
					$count++;
				}
			}
           return json($res);
        }
        if ( isset($arr['company_id']) && isset($arr['role_id']) && !isset($arr['moddle_id'])) {
            //没有moddle_id
            $company_id = $arr['company_id'];
            $role_id = $arr['role_id'];
            $list = $RolesTbale->role_master_groups_list1($company_id, $role_id, $currentpage, $itemsPerPage);
            $res = array();
            $count = 0;
            foreach ($list as $k => $v) {
                $bl = false;
                $arry_r = array('role_id' => $v['role_id'], 'role_name' => $v['role_name'], 'master' => $v['master']);
                $arry_m = array('role_id' => $v['role_id'],'groups' => $v['groups'],'modular_id' => $v['modular_id'], 'modular_name' => $v['modular_name']);
                //去重
                foreach ($res as $kk => $vv) {
                    if ($res[$kk]['openid'] != $v['openid']) {
                        $bl = false;
                    } else {
                        $bl = true;
                        //有重复的就拼接
                        array_push($res[$kk]['roles_master_List'], $arry_r);
                        array_push($res[$kk]['moduler_master_List'], $arry_m);
                        break;
                    }
                }
                if (!$bl) {
                    //新插入一条
                    //$res[$count] = $v;
                    $res[$count]['headimgurl'] = $v['headimgurl'];
                    $res[$count]['mobile_phone'] = $v['mobile_phone'];
                    $res[$count]['nickname'] = $v['nickname'];
                    $res[$count]['openid'] = $v['openid'];
                    $res[$count]['real_name'] = $v['real_name'];
                    $res[$count]['remark'] = $v['remark'];
                    $res[$count]['role_id'] = $v['role_id'];
                    $arry_r2 = array('role_id' => $v['role_id'], 'role_name' => $v['role_name'], 'master' => $v['master']);
                    $arry_m2 = array('role_id' => $v['role_id'], 'groups' => $v['groups'], 'modular_id' => $v['modular_id'], 'modular_name' => $v['modular_name']);
                    $res[$count]['roles_master_List'] = [];
                    $res[$count]['moduler_master_List'] = [];
                    array_push($res[$count]['roles_master_List'], $arry_r2);
                    array_push($res[$count]['moduler_master_List'], $arry_m2);
                    $count++;
                }
            }
            foreach($res as $k=>$v){
                $masterlist=$v['roles_master_List'];
                $arr=$this->getArrayUniqueByKeys($masterlist);
                $res[$k]['roles_master_List']=$arr;
            }
            return json($res);
        }
        if (isset($arr['company_id']) && isset($arr['role_id']) && isset($arr['moddle_id'])) {
      //全都有
            $company_id = $arr['company_id'];
            $role_id = $arr['role_id'];
            $moddle_id= $arr['moddle_id'];
            $list=$RolesTbale->role_master_groups_list2($company_id,$role_id,$moddle_id,$currentpage,$itemsPerPage);
            $res = array();
            $count = 0;
            foreach($list as $k=>$v){
                $res[$count]['headimgurl'] = $v['headimgurl'];
                $res[$count]['mobile_phone'] = $v['mobile_phone'];
                $res[$count]['nickname'] = $v['nickname'];
                $res[$count]['openid'] = $v['openid'];
                $res[$count]['real_name'] = $v['real_name'];
                $res[$count]['remark'] = $v['remark'];
                $res[$count]['role_id'] = $v['role_id'];
                $arry_r2 = array('role_id'=>$v['role_id'],'role_name'=>$v['role_name'],'master'=>$v['master']);
                $arry_m2 = array('role_id'=>$v['role_id'],'groups'=>$v['groups'],'modular_id'=>$v['modular_id'],'modular_name'=>$v['modular_name']);
                $res[$count]['roles_master_List'] = [];
                $res[$count]['moduler_master_List'] = [];
                array_push($res[$count]['roles_master_List'],$arry_r2);
                array_push($res[$count]['moduler_master_List'],$arry_m2);
                $count++;
            }
            foreach($res as $k=>$v){
                $masterlist=$v['roles_master_List'];
                $arr=$this->getArrayUniqueByKeys($masterlist);
                $res[$k]['roles_master_List']=$arr;
            }

            return json($res);
        }

    }


    //给项目里面分配人员
    public function add_role(){
        $arr= $this->request->param();
        if (!isset($arr['subprj_id']) || empty($arr['subprj_id'])) {
            return json('subprj_id empty');
        }
        if (!isset($arr['openid']) || empty($arr['openid'])) {
            return json('openid empty');
        }
        $user_roles=Db::table('ipm_inst_user_role')->where('openid',$arr['openid'])->select();
        foreach($user_roles as $k=>$v){
            $data['role_id']=$v['role_id'];
            $data['subproject_id'] = $arr['subprj_id'];
            $data['openid'] = $arr['openid'];
            $data['create_time'] = date("Y-m-d H:i:s");
            $data['update_time'] = date("Y-m-d H:i:s");
            $id = Db::table('ipm_inst_subproject_user')->insert($data);
            if($id){
                $res['success'] = true;
                $res['message'] = "success";
            }
        }
        echo  json_encode($res);
        $data=array(
            'openid'=>$arr['openid'],
            'subprj_id'=>$arr['subprj_id'],
        );


        $this->PushMessage($data);

    }
    public function PushMessage($data){
        $opemid=$data['openid'];
        $subprj_id=$data['subprj_id'];
        $subprj_name = Db::table('ipm_inst_subproject')->where('id', $subprj_id)->value('name');
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
        $wechat = new Wechat();
        $access_token = $wechat->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";
        $template_id = 'xMS4moszdXP3_nD2P38djA6b8rIJ2tOc9lGa1ieW8jU';
        $first['value'] = '您被添加到该项目中，相关信息如下';
        $first['color'] = '#173177';
        $keyword1['value'] =$subprj_id;
        $keyword1['color'] = '#173177';
        $keyword2['value'] =$subprj_name;
        $keyword2['color'] = '#173177';
        $keyword3['value'] =$state;
        $keyword3['color'] = '#173177';
        $remark['value'] = '请注意查收';
        $remark['color'] = '#173177';
        $data['first'] = $first;
        $data['keyword1'] = $keyword1;
        $data['keyword2'] = $keyword2;
        $data['keyword3'] = $keyword3;
        $data['remark'] = $remark;
        $msg['touser'] = $opemid;
        $msg['template_id'] = $template_id;
        $msg['data'] = $data;
        $wechat->post($url, json_encode($msg));


    }
    // 查看某个项目已分配的人员和权限
    public function project_role_list(){
        $arr= $this->request->param();
        $subproject_id= $arr['subproject_id'];
        if (!isset($subproject_id) || empty($subproject_id)) {
            return json('subproject_id empty');
        }
        $roleTable = new Roles();
        $subproject_list=$roleTable->role_user_list($subproject_id);
        foreach ($subproject_list as $kk => $vv) {

			$openid= $vv['openid'];

            $idArray = explode(",", $vv['role_id']);
            sort($idArray);
            $rolesList = array();
            foreach ($idArray as $key => $id) {
                $rolesname = Db::table('ipm_inst_role')
                    ->alias('a')
                    ->join('ipm_inst_user_role b','a.id=b.role_id')
                    ->where("a.id=$id and b.openid='$openid'")
                    ->field('a.id,a.name,b.master')
                    ->select();
                if(empty($rolesname)) continue;
                $rolesList[] = $rolesname[0];
            }
            $subproject_list[$kk]['rolelist'] = $rolesList;
        }
        return json($subproject_list);
    }
    //删除项目权限表
    public function del_project_role(){
        $arr= $this->request->param();
        $subproject_id= $arr['subproject_id'];
        $openid= $arr['openid'];
        if (!isset($subproject_id) || empty($subproject_id)) {
            return json('111');
        }
        if (!isset($openid) || empty($openid)) {
            return json('222');
        }
        $result=Db::table('ipm_inst_subproject_user') ->where("subproject_id='$subproject_id' and openid='$openid'")->delete();
        if($result){
            $res['success'] = true;
            $res['message'] = "success";
            echo  json_encode($res);
            $data=array(
                'openid'=>$openid,
                'subprj_id'=>$subproject_id,
            );
            $this->PushMessage1($data);
        }
    }
    public function PushMessage1($data){
        $opemid=$data['openid'];
        $subprj_id=$data['subprj_id'];
        $subprj_name = Db::table('ipm_inst_subproject')->where('id', $subprj_id)->value('name');
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
        $wechat = new Wechat();
        $access_token = $wechat->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";
        $template_id = 'xMS4moszdXP3_nD2P38djA6b8rIJ2tOc9lGa1ieW8jU';
        $first['value'] = '您已从该项目中被移除，相关信息如下';
        $first['color'] = '#173177';
        $keyword1['value'] =$subprj_id;
        $keyword1['color'] = '#173177';
        $keyword2['value'] =$subprj_name;
        $keyword2['color'] = '#173177';
        $keyword3['value'] =$state;
        $keyword3['color'] = '#173177';
        $remark['value'] = '请注意查收';
        $remark['color'] = '#173177';
        $data['first'] = $first;
        $data['keyword1'] = $keyword1;
        $data['keyword2'] = $keyword2;
        $data['keyword3'] = $keyword3;
        $data['remark'] = $remark;
        $msg['touser'] = $opemid;
        $msg['template_id'] = $template_id;
        $msg['data'] = $data;
        $wechat->post($url, json_encode($msg));


    }
     public function test($n){
         echo $n.'&nbsp;&nbsp;';
         if($n>0){
            $this-> test($n-1);
         }else{
             echo '<---->';

         }
        // echo $n.'&nbsp;&nbsp;';

     }
    public function digui(){
        $a=$this->test(10);
        echo $a;
    }

}