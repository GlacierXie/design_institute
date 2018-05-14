<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\Cache;
use think\Request;

class Projecttrailinfo extends Controller
{
	function run(){
           include('GlobalHelp.php');
           return new GlobalHelp();
    }
    public  function getProjecttrailinfos()
	{
        if(request()->isGet()) 
		{
			$openid = input('openid');
            $prj_id = input('prj_id');
            $subproject_id = input('subproject_id');
			$help=$this->run();
	        $outputList = array();
	        if(isset($prj_id))
			{
				if (!$help->isValidPrj($prj_id,$subproject_id))
			    {
			      return json();
			    }
				//查询项目轨迹 表ipm_inst_project
			    $sql = "SELECT a.name,c.real_name as creator_nickname,c.headimgurl,a.creator_id,
                                   b.name as config_name,
                                   a.create_time 
                                   from ipm_inst_project a
                                   left join ipm_inst_configuration as b on a.config_id=b.id
                                   left join ipm_user c on a.creator_id=c.openid
                                   where a.id = '$prj_id'";
			    if(isset($openid))
			    {
				   $sql = $sql." and a.creator_id='$openid'";
			    }
			    $sql = $sql." ORDER BY create_time ASC";
			    $list= Db::query($sql);
                foreach($list as $k=>$v)
			    {
			      $info['time'] = $list[$k]['create_time'];
			      $info['openid'] = $list[$k]['creator_id'];
			      $info['name'] = $list[$k]['creator_nickname'];
			      $info['headimgurl'] = $list[$k]['headimgurl'];
			      $info['info'] = "创建模板厂";
			      $info['content'] = $list[$k]['name']."，并指定项目配置".$list[$k]['config_name'];
                  array_push($outputList,$info);
                }
			}
			else
			{
				if (!$help->isValidSubPrj($subproject_id))
			    {
			       return json();
			    }
			}
			
			//查询子项目轨迹 ipm_inst_subproject
			$sql = "SELECT b.name,a.creator_id,c.real_name as creator_nickname,c.headimgurl,b.create_time 
			        from ipm_inst_project a
                    left join ipm_inst_subproject b on a.id=b.project_id
                    left join ipm_user c on a.creator_id=c.openid
                    where b.id ='$subproject_id'";
			if(isset($openid))
			{
				$sql = $sql." and a.creator_id='$openid'";
			}
			$sql = $sql." ORDER BY a.create_time ASC";
			$list= Db::query($sql);
            foreach($list as $k=>$v)
			{
			  $info['time'] = $list[$k]['create_time'];
			  $info['openid'] = $list[$k]['creator_id'];
			  $info['name'] = $list[$k]['creator_nickname'];
			  $info['headimgurl'] = $list[$k]['headimgurl'];
			  $info['info'] = "创建项目";
			  $info['content'] =$list[$k]['name'];
              array_push($outputList,$info);
            }
	        
			//子项目状态修改轨迹
			$sql = "SELECT a.changed_state,a.prev_state,c.real_name as creator_nickname,a.openid,
			       c.headimgurl,b.name,a.create_time from 
				   ipm_inst_subproject_state_change a 
				   left join ipm_user c on a.openid=c.openid 
				   left join ipm_inst_project b on b.id= a.subproject_id 
                   where a.subproject_id ='$subproject_id' and a.changed_state in(4,5,6)";
			if(isset($openid))
			{
				$sql = $sql." and a.openid='$openid'";
			}
			$sql = $sql." ORDER BY a.create_time ASC";
			$list= Db::query($sql);
            foreach($list as $k=>$v)
			{
			  if($list[$k]['changed_state'] < 0 || $list[$k]['changed_state'] > 6
			  ||$list[$k]['prev_state'] < 0 || $list[$k]['prev_state'] > 6)
				  continue;
			  $info['openid'] = $list[$k]['openid'];
			  $info['time'] = $list[$k]['create_time'];
			  $info['name'] = $list[$k]['creator_nickname'];
			  $info['headimgurl'] = $list[$k]['headimgurl'];
			  if($list[$k]['changed_state'] == 4)
			  {
			  	$info['info'] = "标准层下单";
			    $info['content'] ="将项目".$list[$k]['name']."的标准层下单";;
			  }
			  else if($list[$k]['changed_state'] == 5)
			  {
			  	$info['info'] = "变化层下单";
			    $info['content'] ="将项目".$list[$k]['name']."的变化层下单";;
			  }
			  else if($list[$k]['changed_state'] == 6)
			  {
			  	$info['info'] = "项目归档";
			    $info['content'] ="将项目".$list[$k]['name']."归档";;
			  }
			  else
			  	continue;
              array_push($outputList,$info);
            }
			
			//查询项目人员加入权限轨迹
			$sql = "SELECT GROUP_CONCAT(DISTINCT a.role_id) as role_ids,a.openid,b.name,c.real_name as creator_nickname,c.headimgurl,a.create_time 
			        from ipm_inst_subproject_user a 
					left join ipm_user c on a.openid=c.openid 
					left join ipm_inst_subproject b on a.subproject_id=b.id 
					where a.subproject_id ='$subproject_id'";
			if(isset($openid))
			{
				$sql = $sql." and a.openid='$openid'";
			}
			$sql = $sql." group by a.subproject_id,a.openid ORDER BY a.create_time ASC";
			$list= Db::query($sql);
            foreach($list as $k=>$v)
			{
			  $info['time'] = $list[$k]['create_time'];
			  $info['openid'] = $list[$k]['openid'];
			  $info['name'] = $list[$k]['creator_nickname'];
			  $info['headimgurl'] = $list[$k]['headimgurl'];
			  $info['info'] = "加入项目";
			  $info['content'] =$list[$k]['name']."权限为";
			  $res = ",".$list[$k]['role_ids'];
			  if(stripos($res,'1')){
                             $info['content'] = $info['content']." 工程部";
                        }
                        if(stripos($res,'2')) {
                            $info['content'] = $info['content']." 底图组";
                        }
                        if(stripos($res,'3')) {
                            $info['content'] = $info['content']." 总工室";
                        }
                        if(stripos($res,'4')) {
                            $info['content'] = $info['content']." 变化层";
                        }
                        if(stripos($res,'6')) {
                            $info['content'] = $info['content']." 检查组";
                        }
                        if(stripos($res,'7')) {
                            $info['content'] = $info['content']." 标准层设计组";
                        }
              array_push($outputList,$info);
            }
			
			//创建任务分组轨迹
			/*$sql = "SELECT a.name,a.creator_id ,c.real_name as creator_nickname,
			c.headimgurl,a.create_time 
			from ipm_inst_subproject_taskgroup a 
			left join ipm_user c on a.creator_id = c.openid 
			where a.subproject_id  =$subproject_id and a.$_COOKIE";
			if(isset($openid))
			{
				$sql = $sql." and a.creator_id='$openid'";
			}
			$sql = $sql." ORDER BY a.create_time ASC";
			$list= Db::query($sql);
            foreach($list as $k=>$v)
			{
			  $info['time'] = $list[$k]['create_time'];
			  $info['openid'] = $list[$k]['creator_id'];
			  $info['name'] = $list[$k]['creator_nickname'];
			  $info['headimgurl'] = $list[$k]['headimgurl'];
			  $info['info'] = "创建了任务分组";
			  $info['content'] =$list[$k]['name'];
              array_push($outputList,$info);
            }*/
			
			//创建子任务轨迹
			$sql = "SELECT a.name,b.name as taskgroup_name,c.real_name as creator_nickname,
			     a.changer_id,c.headimgurl,a.create_time,a.creator_id 
			     from ipm_inst_subproject_task a 
				 left join ipm_inst_subproject_taskgroup b on a.taskgroup_id = b.id 
			     left join ipm_user c on a.creator_id = c.openid
				 where b.subproject_id  =$subproject_id and a.changer_id != ''";
			if(isset($openid))
			{
				$sql = $sql." and a.creator_id='$openid'";
			}
			
			$sql = $sql." ORDER BY a.create_time ASC";
			$list= Db::query($sql);
            foreach($list as $k=>$v)
			{
			  $info['time'] = $list[$k]['create_time'];
			  $info['openid'] = $list[$k]['creator_id'];
			  $info['name'] = $list[$k]['creator_nickname'];
			  $info['headimgurl'] = $list[$k]['headimgurl'];
			  $info['info'] = "创建了任务";
			  $info['content'] = $list[$k]['taskgroup_name']."-".$list[$k]['name'].",并指派给".$help->getUserName($list[$k]['changer_id']);
              array_push($outputList,$info);
            }
			
			//参与任务轨迹
			/*$sql = "SELECT a.openid,d.name as taskgroup_name,b.name as task_name,c.real_name as creator_nickname,
			c.headimgurl,a.create_time 
			from ipm_inst_subproject_taskparter a 
			left join ipm_inst_subproject_task b on a.task_id = b.id 
			left join ipm_inst_subproject_taskgroup d on b.taskgroup_id = d.id 
			left join ipm_user c on a.openid = c.openid 
			where d.subproject_id  =$subproject_id and b.changer_id != ''";
			if(isset($openid))
			{
				$sql = $sql." and a.openid='$openid'";
			}
			
			$sql = $sql." ORDER BY a.create_time ASC";
			$list= Db::query($sql);
            foreach($list as $k=>$v)
			{
			  $info['time'] = $list[$k]['create_time'];
			  $info['openid'] = $list[$k]['openid'];
			  $info['name'] = $list[$k]['creator_nickname'];
			  $info['headimgurl'] = $list[$k]['headimgurl'];
			  $info['info'] = "参与了任务";
			  $info['content'] = $list[$k]['taskgroup_name']."-".$list[$k]['task_name'];
              array_push($outputList,$info);
            }*/
			
			//任务修改轨迹
			$sql = "SELECT a.openid,d.name as taskgroup_name,b.name as task_name,c.real_name as creator_nickname,
			c.headimgurl,a.create_time,a.change_type,a.change_content 
			from ipm_inst_subproject_task_change a 
			left join ipm_inst_subproject_task b on a.task_id = b.id 
			left join ipm_inst_subproject_taskgroup d on b.taskgroup_id = d.id 
			left join ipm_user c on a.openid = c.openid 
			where d.subproject_id  =$subproject_id and (a.change_type = 7 or a.change_type = 8) and b.changer_id != ''";
			if(isset($openid))
			{
				$sql = $sql." and a.openid='$openid'";
			}
			$taskState=array('','更新了任务备注','清空了任务备注','更新了开始时间','清空了开始时间','更新了截止时间','清空了截止时间','重做了任务','完成了任务','更新计划截止时间',
				'清空计划截止时间','添加任务参与人','删除任务参与人','指派任务负责人','更新了计划开始时间','清空了计划开始时间');
			$sql = $sql." ORDER BY a.create_time ASC";
			$list= Db::query($sql);
            foreach($list as $k=>$v)
			{
			  /*if($list[$k]['change_type'] < 0 || $list[$k]['change_type'] > 8)
				  continue;*/
			  if($list[$k]['change_type'] != 8 && $list[$k]['change_type'] != 7)
				  continue;
			  $info['time'] = $list[$k]['create_time'];
			  $info['openid'] = $list[$k]['openid'];
			  $info['name'] = $list[$k]['creator_nickname'];
			  $info['headimgurl'] = $list[$k]['headimgurl'];
			  $info['info'] = $taskState[$list[$k]['change_type']];
			  $info['content'] = $list[$k]['taskgroup_name']."-".$list[$k]['task_name']."   ".$list[$k]['change_content'];
			  if($list[$k]['change_type'] == 11 || $list[$k]['change_type'] == 12 || $list[$k]['change_type'] == 13)
			  {
			     $info['content'] = $list[$k]['taskgroup_name']."-".$list[$k]['task_name']."   ".$help->getUserName($list[$k]['change_content']);
			  }
              array_push($outputList,$info);
            }
			
			//文件上传轨迹
			$sql = "SELECT a.name,c.real_name as creator_nickname,a.ctreator_id,c.headimgurl,
			        a.type,a.create_time 
			        from ipm_inst_file a 
					left join ipm_user c on a.ctreator_id=c.openid 
					where a.subproject_id ='$subproject_id' and (a.type =-1 or a.type =-2)";

			if(isset($openid))
			{
				$sql = $sql." and a.ctreator_id='$openid'";
			}

			$sql = $sql." ORDER BY a.create_time ASC";
			$list= Db::query($sql);
            foreach($list as $k=>$v)
			{
			  if($list[$k]['type'] != -1 && $list[$k]['type'] != -2)
				  continue;
			  $info['time'] = $list[$k]['create_time'];
			  $info['openid'] = $list[$k]['ctreator_id'];
			  $info['name'] = $list[$k]['creator_nickname'];
			  $info['headimgurl'] = $list[$k]['headimgurl'];
			  if($list[$k]['type'] == -1)
				 $info['info'] = "上传答疑文件和图纸依据文件";
			  else if($list[$k]['type'] == -2)
			     $info['info'] = "变更跟踪单";
			  $info['content'] =$list[$k]['name'];
              array_push($outputList,$info);
            }
			
			//文件状态修改轨迹
			$sql = "SELECT a.changer_id,a.changed_state,a.prev_state,e.name as task_name,d.name as taskgroup_name,
			        c.real_name as creator_nickname,c.headimgurl,b.name,a.create_time,b.type
			        from ipm_inst_files_state_change a 
					left join ipm_user c on a.changer_id=c.openid 
					left join ipm_inst_file b on b.id= a.file_id 
					left join ipm_inst_subproject_task e on e.id = b.type
					left join ipm_inst_subproject_taskgroup d on e.taskgroup_id = d.id
					where b.subproject_id ='$subproject_id'";
			if(isset($openid))
			{
				$sql = $sql." and a.changer_id='$openid'";
			}
			$sql = $sql." ORDER BY a.create_time ASC";
			$list= Db::query($sql);
			$fileState=array('','待审核','待修改','已审核','已删除');
            foreach($list as $k=>$v)
			{
			  if($list[$k]['changed_state'] < 0 || $list[$k]['changed_state'] > 4
			  ||$list[$k]['prev_state'] < 0 || $list[$k]['prev_state'] > 4)
				  continue;
			  $info['time'] = $list[$k]['create_time'];
			  $info['openid'] = $list[$k]['changer_id'];
			  $info['name'] = $list[$k]['creator_nickname'];
			  $info['headimgurl'] = $list[$k]['headimgurl'];
			  if($list[$k]['changed_state'] == 2)
			  {
				  if($list[$k]['type'] == -1)
				  {
					$info['info'] = "打回了答疑文件和底图依据";
					$info['content'] = $list[$k]['name'];
				  }
				  else if($list[$k]['type'] == -2)
				  {
					$info['info'] = "打回了变更跟踪单";
					$info['content'] = $list[$k]['name'];
				  }
				  else if($list[$k]['type'] > 0)
				  {
					$info['info'] = "打回了任务成果";
					$info['content'] = $list[$k]['taskgroup_name']."-".$list[$k]['task_name'].":".$list[$k]['name'];
				  }
			  }
			  else if($list[$k]['changed_state'] == 3)
			  {
				  if($list[$k]['type'] == -1)
				  {
					$info['info'] = "审核了答疑文件和底图依据";
					$info['content'] = $list[$k]['name'];
				  }
				  else if($list[$k]['type'] == -2)
				  {
					$info['info'] = "审核了变更跟踪单";
					$info['content'] = $list[$k]['name'];
				  }
				  else if($list[$k]['type'] > 0)
				  {
					$info['info'] = "审核了任务成果";
					$info['content'] = $list[$k]['taskgroup_name']."-".$list[$k]['task_name'].":".$list[$k]['name'];
				  }
			  }
			  else if($list[$k]['changed_state'] == 4)
			  {
				  if($list[$k]['type'] == -1)
				  {
					$info['info'] = "删除了答疑文件和底图依据";
					$info['content'] = $list[$k]['name'];
				  }
				  else if($list[$k]['type'] == -2)
				  {
					$info['info'] = "删除了变更跟踪单";
					$info['content'] = $list[$k]['name'];
				  }
				  else if($list[$k]['type'] > 0)
				  {
					$info['info'] = "删除了任务成果";
					$info['content'] = $list[$k]['taskgroup_name']."-".$list[$k]['task_name'].":".$list[$k]['name'];
				  }
			  }
			  else
			    continue;
			  /*$info['content'] ="将文件".$list[$k]['name']."由".$fileState[$list[$k]['prev_state']]."修改为".$fileState[$list[$k]['changed_state']];*/
              array_push($outputList,$info);
            }
			
			//上传问题轨迹
			$sql = "SELECT d.name as type_name,e.name as subtype_name,c.real_name as creator_nickname,c.headimgurl,a.prjState,
			        a.problemGrade,a.title,a.creator_id,a.changer_id,a.create_time 
			        from ipm_inst_problem a 
					left join ipm_user c on a.creator_id=c.openid 
					left join ipm_inst_subproject b on a.subproject_id=b.id 
					left join ipm_inst_problem_type d on a.type_id=d.id 
					left join ipm_inst_problem_subtype e on a.subtype_id=e.id 
					where a.subproject_id ='$subproject_id'";

			if(isset($openid))
			{
				$sql = $sql." and a.creator_id='$openid'";
			}
			$proPrjState=array('','底图深化阶段','配模阶段','制图阶段','预拼装阶段','现场施工阶段');
			$sql = $sql." ORDER BY a.create_time ASC";
			$list= Db::query($sql);
			foreach($list as $k=>$v)
			{
			  if($list[$k]['prjState'] < 1 || $list[$k]['prjState'] > 5)
			  	continue;
			  $info['time'] = $list[$k]['create_time'];
			  $info['openid'] = $list[$k]['creator_id'];
			  $info['name'] = $list[$k]['creator_nickname'];
			  $info['headimgurl'] = $list[$k]['headimgurl'];
			  $info['info'] = "上传问题记录";
			  $info['content'] ="在项目处于".$proPrjState[$list[$k]['prjState']]."时上传了一个".$list[$k]['type_name']."-".$list[$k]['subtype_name']."严重等级".
			  $list[$k]['problemGrade']."标题为".$list[$k]['title']."的问题,问题负责人是".$help->getUserName($list[$k]['changer_id']);
              array_push($outputList,$info);
            }
			
			//问题审核轨迹
			$sql = "SELECT d.name as type_name,e.name as subtype_name,c.real_name as creator_nickname,c.headimgurl,a.prjState,
			        a.problemGrade,a.title,a.creator_id,a.changer_id,a.update_time 
			        from ipm_inst_problem a 
					left join ipm_user c on a.changer_id=c.openid 
					left join ipm_inst_subproject b on a.subproject_id=b.id 
					left join ipm_inst_problem_type d on a.type_id=d.id 
					left join ipm_inst_problem_subtype e on a.subtype_id=e.id 
					where a.subproject_id ='$subproject_id' and a.state = 3";
			if(isset($openid))
			{
				$sql = $sql." and a.changer_id='$openid'";
			}
			$sql = $sql." ORDER BY a.update_time ASC";
			$list= Db::query($sql);
			foreach($list as $k=>$v)
			{
			  $info['time'] = $list[$k]['update_time'];
			  $info['openid'] = $list[$k]['changer_id'];
			  $info['name'] = $list[$k]['creator_nickname'];
			  $info['headimgurl'] = $list[$k]['headimgurl'];
			  $info['info'] = "解决问题";
			  $info['content'] ="解决了由".$help->getUserName($list[$k]['creator_id'])."上传的".$list[$k]['type_name']."-".$list[$k]['subtype_name']."严重等级".
			  $list[$k]['problemGrade']."标题为".$list[$k]['title']."的问题";
              array_push($outputList,$info);
			}
			array_multisort($help->i_array_column($outputList,'time'),SORT_DESC,$outputList);
			$current_time = date("Y-m-d H:i:s");
			foreach($outputList as $k=>$v)
			{
				$time = $v['time'];
				$data = round(strtotime($current_time) - strtotime($time));
				if(round($data) < 1 && round($data) > -1)
				{
					$outputList[$k]['time'] =  "刚刚";
				}
				else if(round($data/60) < 1)
				{
					$outputList[$k]['time'] =  round($data)."秒前";
				}
				else if(round($data/3600) < 1)
				{
					$outputList[$k]['time'] =  round($data/60)."分钟前";
				}
				else if(round($data/86400) < 1)
				{
					$outputList[$k]['time'] =  round($data/3600)."小时前";
				}
				else if(round($data/86400) < 10)
				{
					$outputList[$k]['time'] =  round($data/86400)."天前";
				}
			}
			   
			return json($outputList);
        }
		else
		{
		    return json();
		}
	}
}