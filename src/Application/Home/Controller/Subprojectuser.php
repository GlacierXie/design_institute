<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\Cache;
use think\Request;

class Subprojectuser extends Controller
{
	public  function selectAllUser(){
        if(request()->isGet()) {
            $subproject_id= input('subproject_id');
            if(empty($subproject_id)){
                return json ();
            }
            $list= Db::query(" select a.openid as open_id,GROUP_CONCAT(DISTINCT a.role_id) as role_ids,c.real_name as nickname 
                                      from ipm_inst_subproject_user a
									  left join ipm_user c on a.openid=c.openid
                                      where a.subproject_id='$subproject_id' group by open_id
                         ");   
			foreach($list as $k=>$v) 
			{
			      $list[$k]['rights']= array();
                  $res = ",".$list[$k]['role_ids'];
                  $list[$k]['rights']['is_manager'] = false;
                  $list[$k]['rights']['is_drawer'] = false;
                  $list[$k]['rights']['is_totalroomer'] = false;
                  $list[$k]['rights']['is_totalroomDesigner'] = false;
                  $list[$k]['rights']['is_totalroomContioner'] = false;
				          $list[$k]['rights']['is_checker'] = false;
				          $list[$k]['rights']['is_designer'] = false;
                  if(stripos($res,'1')){
                      $list[$k]['rights']['is_manager'] = true;
                  }
                  if(stripos($res,'2')) {
                     $list[$k]['rights']['is_drawer'] = true;
                  }
                  if(stripos($res,'3')) {
                      $list[$k]['rights']['is_totalroomer'] = true;
                  }
                  if(stripos($res,'4')) {
                      $list[$k]['rights']['is_totalroomDesigner'] = true;
                  }
                  if(stripos($res,'5')) {
                      $list[$k]['rights']['is_totalroomContioner'] = true;
                  }
				          if(stripos($res,'6')) {
                      $list[$k]['rights']['is_checker'] = true;
                  }
				         if(stripos($res,'7')) {
                      $list[$k]['rights']['is_designer'] = true;
                  }
            }
			     return json($list);
        }
		else
		{
			return json();
		}
    }
	
    public function subproject_role(){
        if(request()->isGet()) 
		{
            $openid = input('openid');
            $subproject_id = input('subproject_id');
            $list['is_manager'] = false;
            $list['is_drawer'] = false;
            $list['is_totalroomer'] = false;
            $list['is_totalroomDesigner'] = false;
            $list['is_totalroomContioner'] = false;
		    $list['is_checker'] = false;
		    $list['is_designer'] = false;
            if (empty($openid)) {
                return json($list);
            }
            if (empty($subproject_id)) {
                 return json($list);
            }
            $read= Db::query(" select subproject_id,GROUP_CONCAT(DISTINCT role_id) as  role_ids
                                      from ipm_inst_subproject_user
                                    where subproject_id='$subproject_id' AND openid='$openid'  group by subproject_id
                         ");
             if (empty($read)) {
                return json($list);
            }
            $res = ",".$read[0]['role_ids'];
            if(stripos($res,'1')){
                $list['is_manager'] = true;
            }
             if(stripos($res,'2')) {
                $list['is_drawer'] = true;
            }
            if(stripos($res,'3')) {
              $list['is_totalroomer'] = true;
            }
            if(stripos($res,'4')) {
                $list['is_totalroomDesigner'] = true;
            }
            if(stripos($res,'5')) {
                $list['is_totalroomContioner'] = true;
            }
			      if(stripos($res,'6')) {
                $list['is_checker'] = true;
            }
			      if(stripos($res,'7')) {
                $list['is_designer'] = true;
			}
            return json($list);
        }
    }
    public function sdag(){
        echo 444;

    }
}

