<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\Cache;
use think\Request;

class Projecttask extends Controller
{
	function run(){
           include('GlobalHelp.php');
           return new GlobalHelp();
    }
	
	 public  function ProjecttaskgroupInfo()
	{
        if(request()->isGet()) 
		{
            $subproject_id = input('subproject_id');
			if(!isset($subproject_id))
			  return json();
		    $taskgroup_id  = input('taskgroup_id');
			$urgent  = input('urgent');
			$state  = input('state');
			$creator_id  = input('creator_id');
			$changer_id  = input('changer_id');
			$parter_id  = input('parter_id');
			$role_id  = input('role_id');
			$open_id  = input('open_id');
			$task_id = input('task_id');
			
		    $sql = "SELECT DISTINCT a.id,a.name from ipm_inst_subproject_taskgroup a 
			left join ipm_inst_subproject_task d on d.taskgroup_id = a.id
			left join ipm_inst_subproject_taskparter c on d.id = c.task_id
			where a.subproject_id=$subproject_id";
			if(isset($taskgroup_id))
			{
				$sql = $sql." and  a.id = '$taskgroup_id'";
			}
			if(isset($role_id))
			{
				$sql = $sql." and  a.role_id = '$role_id'";
			}
			if(isset($task_id))
			{
				$sql = $sql." and  d.id = '$task_id'";
			}
			if(isset($urgent))
            {
              $sql = $sql." and  d.urgent = '$urgent'";
            }
            if(isset($state))
            {
              $sql = $sql." and  d.state = '$state'";
            }
            if(isset($open_id))
            {
              $sql = $sql." and ( d.creator_id = '$open_id' or  d.changer_id = '$open_id' or c.openid = '$open_id')";
            }
            if(isset($creator_id))
            {
              $sql = $sql." and  d.creator_id = '$creator_id'";
            }
            if(isset($changer_id))
            {
              $sql = $sql." and  d.changer_id = '$changer_id'";
            }
            if(isset($parter_id))
            {
              $sql = $sql." and  c.openid = '$parter_id'";
            }
			$list= Db::query($sql);
			if(!isset($list) || empty($list))
			{
			   return json();
			}
			$outputList = array();
            foreach($list as $k=>$v)
              $outputList[] = $v;
            return json($outputList);		
        }
		else
		{
			return json();
		}
    }
	
	public  function ProjectsubtaskInfo()
	{
        if(request()->isGet()) 
		{
			$taskgroup_id  = input('taskgroup_id');
			$urgent  = input('urgent');
			$state  = input('state');
			$creator_id  = input('creator_id');
			$changer_id  = input('changer_id');
			$parter_id  = input('parter_id');
			$role_id  = input('role_id');
			$open_id  = input('open_id');
			$task_id = input('task_id');
			if(!isset($taskgroup_id))
			  return json();
		    $sql = "SELECT DISTINCT a.id,a.name from ipm_inst_subproject_task a 
			left join ipm_inst_subproject_taskparter c on a.id = c.task_id
			where a.taskgroup_id = $taskgroup_id";
			if(isset($task_id))
			{
				$sql = $sql." and  a.id = '$task_id'";
			}
			if(isset($urgent))
            {
              $sql = $sql." and  a.urgent = '$urgent'";
            }
            if(isset($state))
            {
              $sql = $sql." and  a.state = '$state'";
            }
            if(isset($open_id))
            {
              $sql = $sql." and ( a.creator_id = '$open_id' or  a.changer_id = '$open_id' or c.openid = '$open_id')";
            }
            if(isset($creator_id))
            {
              $sql = $sql." and  a.creator_id = '$creator_id'";
            }
            if(isset($changer_id))
            {
              $sql = $sql." and  a.changer_id = '$changer_id'";
            }
            if(isset($parter_id))
            {
              $sql = $sql." and  c.openid = '$parter_id'";
            }
			$list= Db::query($sql);
			if(!isset($list) || empty($list))
			{
			   return json();
			}
			$outputList = array();
            foreach($list as $k=>$v)
              $outputList[] = $v;
            return json($outputList);		
        }
		else
		{
			return json();
		}
    }
	
	public  function Projectsubtaskchanger()
	{
        if(request()->isGet()) 
		{
			$subproject_id = input('subproject_id');
			$taskgroup_id  = input('taskgroup_id');
			$urgent  = input('urgent');
			$state  = input('state');
			$creator_id  = input('creator_id');
			$changer_id  = input('changer_id');
			$parter_id  = input('parter_id');
			$role_id  = input('role_id');
			$open_id  = input('open_id');
			$task_id = input('task_id');
			if(!isset($subproject_id))
			  return json();
		    $sql = "SELECT DISTINCT a.changer_id,c.real_name as changer_Name 
			from ipm_inst_subproject_task a 
			left join ipm_user c on a.changer_id = c.openid 
			left join ipm_inst_subproject_taskgroup d on d.id = a.taskgroup_id
			left join ipm_inst_subproject_taskparter b on a.id = b.task_id
			where d.subproject_id = $subproject_id and a.changer_id !=''";
			if(isset($taskgroup_id))
			{
				$sql = $sql." and  d.id = '$taskgroup_id'";
			}
			if(isset($role_id))
			{
				$sql = $sql." and  d.role_id = '$role_id'";
			}
			if(isset($task_id))
			{
				$sql = $sql." and  a.id = '$task_id'";
			}
			if(isset($urgent))
            {
              $sql = $sql." and  a.urgent = '$urgent'";
            }
            if(isset($state))
            {
              $sql = $sql." and  a.state = '$state'";
            }
            if(isset($open_id))
            {
              $sql = $sql." and ( a.creator_id = '$open_id' or  a.changer_id = '$open_id' or b.openid = '$open_id')";
            }
            if(isset($creator_id))
            {
              $sql = $sql." and  a.creator_id = '$creator_id'";
            }
            if(isset($changer_id))
            {
              $sql = $sql." and  a.changer_id = '$changer_id'";
            }
            if(isset($parter_id))
            {
              $sql = $sql." and  b.openid = '$parter_id'";
            }
			$list= Db::query($sql);
			if(!isset($list) || empty($list))
			{
			   return json();
			}
			$outputList = array();
            foreach($list as $k=>$v)
              $outputList[] = $v;
            return json($outputList);		
        }
		else
		{
			return json();
		}
    }
	public  function Projectsubtaskparter()
	{
        if(request()->isGet()) 
		{
			$subproject_id = input('subproject_id');
			$taskgroup_id  = input('taskgroup_id');
			$urgent  = input('urgent');
			$state  = input('state');
			$creator_id  = input('creator_id');
			$changer_id  = input('changer_id');
			$parter_id  = input('parter_id');
			$role_id  = input('role_id');
			$open_id  = input('open_id');
			$task_id = input('task_id');
			if(!isset($subproject_id))
			  return json();
		    $sql = "SELECT DISTINCT a.openid,c.real_name as parter_Name 
			from ipm_inst_subproject_taskparter a 
			left join ipm_inst_subproject_task b on a.task_id = b.id
			left join ipm_user c on a.openid = c.openid 
			left join ipm_inst_subproject_taskgroup d on d.id = b.taskgroup_id
			where d.subproject_id = $subproject_id and a.openid !='' and b.changer_id !=''";
			if(isset($taskgroup_id))
			{
				$sql = $sql." and  d.id = '$taskgroup_id'";
			}
			if(isset($role_id))
			{
				$sql = $sql." and  d.role_id = '$role_id'";
			}
			if(isset($task_id))
			{
				$sql = $sql." and  b.id = '$task_id'";
			}
			if(isset($urgent))
            {
              $sql = $sql." and  b.urgent = '$urgent'";
            }
            if(isset($state))
            {
              $sql = $sql." and  b.state = '$state'";
            }
            if(isset($open_id))
            {
              $sql = $sql." and ( b.creator_id = '$open_id' or  b.changer_id = '$open_id' or a.openid = '$open_id')";
            }
            if(isset($creator_id))
            {
              $sql = $sql." and  b.creator_id = '$creator_id'";
            }
            if(isset($changer_id))
            {
              $sql = $sql." and  b.changer_id = '$changer_id'";
            }
            if(isset($parter_id))
            {
              $sql = $sql." and  a.openid = '$parter_id'";
            }
			$list= Db::query($sql);
			if(!isset($list) || empty($list))
			{
			   return json();
			}
			$outputList = array();
            foreach($list as $k=>$v)
              $outputList[] = $v;
            return json($outputList);		
        }
		else
		{
			return json();
		}
    }
	
    public  function ProjecttaskInfo()
	{
        if(request()->isGet()) 
		{
            $subproject_id = input('subproject_id');
			$taskgroup_id  = input('taskgroup_id');
			$urgent  = input('urgent');
			$state  = input('state');
			$creator_id  = input('creator_id');
			$changer_id  = input('changer_id');
			$parter_id  = input('parter_id');
			$role_id  = input('role_id');
			$open_id  = input('open_id');
			$task_id = input('task_id');
			if(!isset($subproject_id))
			   return json();
			$sql = "SELECT DISTINCT a.id,a.name,a.creator_id,c.real_name as creator_nickname,
			c.headimgurl as creator_headimgurl,a.role_id,a.update_time,a.create_time 
			from ipm_inst_subproject_taskgroup a 
			left join ipm_user c on a.creator_id = c.openid 
			left join ipm_inst_subproject_task d on d.taskgroup_id = a.id
			where a.subproject_id  =$subproject_id";
			if(isset($taskgroup_id))
			{
				$sql = $sql." and  a.id = '$taskgroup_id'";
			}
			if(isset($role_id))
			{
				$sql = $sql." and  a.role_id = '$role_id'";
			}
			if(isset($task_id))
			{
				$sql = $sql." and  d.id = '$task_id'";
			}
			
			$list= Db::query($sql);
			if(!isset($list) || empty($list))
			{
			   return json();
			}
			$help=$this->run();
			foreach($list as $k=>$v) 
			{
				 $sql = "SELECT DISTINCT a.id,a.name,a.creator_id,c.real_name as creator_nickname,
			             a.changer_id,a.urgent,a.state, a.remarks,a.start_time_plan,a.end_time_plan,a.start_time_real,
			             a.end_time_real,c.headimgurl as creator_headimgurl,a.update_time,a.create_time 
			             from ipm_inst_subproject_task a 
			             left join ipm_user c on a.creator_id = c.openid";
				if(isset($parter_id) || isset($open_id))
			    {
				  $sql = $sql." left join ipm_inst_subproject_taskparter d on a.id = d.task_id";
			    }
				$tmp_taskgroup_id = $list[$k]['id'];
				$sql = $sql." where a.taskgroup_id  = $tmp_taskgroup_id";
				if(isset($urgent))
			    {
				  $sql = $sql." and  a.urgent = '$urgent'";
			    }
			    if(isset($state))
			    {
				  $sql = $sql." and  a.state = '$state'";
			    }
				if(isset($open_id))
				{
				  $sql = $sql." and ( a.creator_id = '$open_id' or  a.changer_id = '$open_id' or d.openid = '$open_id')";
				}
				if(isset($creator_id))
			    {
				  $sql = $sql." and  a.creator_id = '$creator_id'";
			    }
				if(isset($changer_id))
			    {
				  $sql = $sql." and  a.changer_id = '$changer_id'";
			    }
				if(isset($parter_id))
				{
				  $sql = $sql." and  d.openid = '$parter_id'";
				}
				if(isset($task_id))
				{
					$sql = $sql." and  a.id = '$task_id'";
				}
				$taskList = Db::query($sql);
				if(empty($taskList))
			    {
				  unset($list[$k]);
			      continue;	
			    }
				foreach($taskList as $kk=>$vv) 
				{
					$taskList[$kk]['changer_nickname'] = $help->getUserName($taskList[$kk]['changer_id']);
					$taskList[$kk]['changer_headimgurl'] = $help->getUserheadimgurl($taskList[$kk]['changer_id']);
				}
				$list[$k]['subtask_list'] = $taskList;
			}
			$outputList = array();
            foreach($list as $k=>$v)
              $outputList[] = $v;
            return json($outputList);		
        }
		else
		{
			return json();
		}
    }
	
	public  function Projecttaskupdata()
	{
        if(request()->isPost())
		{
			$read = file_get_contents("php://input");
		    if (empty($read))
			{
               $jsonarr["success"] = false;
			   $jsonarr["state"] = -1;
			   $jsonarr["message"] = '更新任务数据缺失';
			   $jsonarr["update_time"] = '';
			   $result = json_encode($jsonarr);
			   ob_clean();
	           echo $result;
		       return;
            }
			$json = json_decode(trim($read,chr(239).chr(187).chr(191)),true);
			if (is_null($json) || empty($json)) 
			{
               $jsonarr["success"] = false;
			   $jsonarr["state"] = -1;
			   $jsonarr["message"] = '更新任务数据格式错误';
			   $jsonarr["update_time"] = '';
			   $result = json_encode($jsonarr);
			   ob_clean();
	           echo $result;
		       return;
			}
			foreach($json as $js=>$jv)
			{
              $openid = $jv['openid'];
              $task_id = $jv['task_id'];
              $change_type = $jv['change_type'];
              $change_content = $jv['change_content'];
              if(isset($openid) && isset($task_id) && isset($change_type)&& isset($change_content))
              {
                if($change_type < 0 || $change_type > 14)
                {
              	  $jsonarr["success"] = false;
                  $jsonarr["state"] = -1;
                  $jsonarr["message"] = '更新任务数据格式错误';
				  $jsonarr["update_time"] = '';
                  $result = json_encode($jsonarr);
                  ob_clean();
                  echo $result;
                  return;
                }
                $update_time = date("Y-m-d H:i:s");
                $creat_time = date("Y-m-d H:i:s");
              
                $prjData= Db::query("select a.state 
                                 from ipm_inst_subproject a 
                                 left join ipm_inst_subproject_taskgroup b on b.subproject_id = a.id 
                                 left join ipm_inst_subproject_task c on b.id = c.taskgroup_id 
                                 where c.id='$task_id'");
                if(!isset($prjData) || empty($prjData))
                {
                  $jsonarr["success"] = false;
                  $jsonarr["state"] = -1;
                  $jsonarr["message"] = '当前任务所在的项目不存在';
				  $jsonarr["update_time"] = '';
                  $result = json_encode($jsonarr);
                  ob_clean();
                  echo $result;
              	  return;
                }
                if($prjData[0]['state'] == 6)
                {
                  $jsonarr["success"] = false;
                  $jsonarr["state"] = -1;
                  $jsonarr["message"] = '当前任务所在的项目已归档';
				  $jsonarr["update_time"] = '';
                  $result = json_encode($jsonarr);
                  ob_clean();
                  echo $result;
              	  return;
                }
                $taskData= Db::query("select a.state 
                                 from ipm_inst_subproject_task a
                                 left join ipm_inst_subproject_taskparter d on a.id = d.task_id
                                 where a.id='$task_id' and (a.changer_id = '$openid' or a.creator_id = '$openid' or d.openid = '$openid')");
               
                if(!isset($taskData) || empty($taskData))
                {
              	  $jsonarr["success"] = false;
                  $jsonarr["state"] = -1;
                  $jsonarr["message"] = '该任务负责人不是所指定用户';
				  $jsonarr["update_time"] = '';
                  $result = json_encode($jsonarr);
                  ob_clean();
                  echo $result;
              	  return;
                }
                $taskPrevState = $taskData[0]['state'];
                $taskNextState = $taskPrevState;
                if($change_type == 7)
                {
              	  $taskNextState = 1;
              	  if($taskPrevState == $taskNextState)
              	  {
              		   $jsonarr["success"] = false;
                       $jsonarr["state"] = -1;
                       $jsonarr["message"] = '该任务还未完成,无需重做';
					   $jsonarr["update_time"] = '';
                       $result = json_encode($jsonarr);
                       ob_clean();
                       echo $result;
              	       return;
              	  }
                }
                if($change_type == 8)
                {
              	  $taskNextState = 2;
              	  if($taskPrevState != 1)
              	  {
              		   $jsonarr["success"] = false;
                       $jsonarr["state"] = -1;
                       $jsonarr["message"] = '该任务已提交,无需重复提交';
					   $jsonarr["update_time"] = '';
                       $result = json_encode($jsonarr);
                       ob_clean();
                       echo $result;
              	       return;
              	  }
                }
                //将文件数据插入到ipm_inst_subproject_task_change中 获取创建的自增ID
                $dataInsert = ['id' => -1, 'openid' => $openid,'task_id' => $task_id,
              	'change_type' => $change_type,'change_content'=> $change_content,'update_time' => $update_time,'create_time' => $creat_time];
                Db::table('ipm_inst_subproject_task_change')->insertGetId($dataInsert);
              
                if($change_type == 1 || $change_type == 2)
                {
              	  Db::query("UPDATE ipm_inst_subproject_task SET remarks = '$change_content' ,update_time = '$update_time' WHERE id = '$task_id'");
                }
                else if($change_type == 3 || $change_type == 4)
                {
              	  Db::query("UPDATE ipm_inst_subproject_task SET start_time_real = '$change_content' ,update_time = '$update_time' WHERE id = '$task_id'");
                }
                else if($change_type == 5 || $change_type == 6)
                {
              	  Db::query("UPDATE ipm_inst_subproject_task SET end_time_real = '$change_content' ,update_time = '$update_time' WHERE id = '$task_id'");
                }
                else if($change_type == 7 || $change_type == 8)
                {
              	  Db::query("UPDATE ipm_inst_subproject_task SET state = '$taskNextState' ,update_time = '$update_time' WHERE id = '$task_id'");
                }
                else if($change_type == 9 || $change_type == 10)
                {
              	  Db::query("UPDATE ipm_inst_subproject_task SET end_time_plan = '$change_content' ,update_time = '$update_time' WHERE id = '$task_id'");
                }
                else if($change_type == 11)
                {
                   Db::query("DELETE from ipm_inst_subproject_taskparter WHERE task_id = '$task_id' and openid = '$change_content'");
              	   $dataInsert = ['id' => -1, 'openid' => $change_content,'task_id' => $task_id,
              	  'update_time' => $update_time,'create_time' => $creat_time];
                   Db::table('ipm_inst_subproject_taskparter')->insertGetId($dataInsert);
                }
                else if($change_type == 12)
                {
              	  Db::query("DELETE from ipm_inst_subproject_taskparter WHERE task_id = '$task_id' and openid = '$change_content'");
                }
                else if($change_type == 13)
                {
              	  Db::query("UPDATE ipm_inst_subproject_task SET changer_id = '$change_content' ,update_time = '$update_time' WHERE id = '$task_id'");
                }
                else if($change_type == 14 || $change_type == 15)
                {
              	  Db::query("UPDATE ipm_inst_subproject_task SET start_time_plan = '$change_content' ,update_time = '$update_time' WHERE id = '$task_id'");
                }
                if($change_type == 8)
                {
              	  Db::query("UPDATE ipm_inst_subproject_task SET state = '$taskNextState' ,end_time_real = '$update_time' WHERE id = '$task_id'");
                }
              
                //重做任务删除任务成果
                if($change_type == 7)
                {
                  $fileData= Db::query(" select a.id,a.state as state,a.ctreator_id as ctreator_id from ipm_inst_file a
              				where a.type='$task_id' and a.state != 4");
              			
              	  if(isset($fileData) && !empty($fileData))
              	  {
              		foreach($fileData as $k=>$v)
              		{
              			$filePrevState = $fileData[$k]['state'];
              			$fileid = $fileData[$k]['id'];
              
              			//修改文件状态
              			Db::query("UPDATE ipm_inst_file SET state = '4' ,update_time = '$update_time' WHERE id = '$fileid'");
              		 
              			//记录文件修改状态log
              			 Db::query("INSERT INTO ipm_inst_files_state_change (id,file_id,changer_id,changed_state,prev_state,update_time,create_time)
              			   VALUES(-1,'$fileid','$openid','4','$filePrevState','$update_time','$creat_time')");
              		}
                  }
				  
				//暂时添加 20180110 Ryan 写死的！！！！
					 $temp = Db::query("select state from ipm_inst_subproject_task where id = ('$task_id'+1)");
					 if($temp[0]['state'] == 3)
					 {
						 Db::query("update ipm_inst_subproject_task set state =1 where id = ('$task_id'+1)");
					 }
				///////////////////////////////////////
                }
				
                $jsonarr["success"] = true;
                $jsonarr["state"] = $taskNextState;
                $jsonarr["message"] = '更新任务成功';
				$jsonarr["update_time"] = $update_time;
                $result = json_encode($jsonarr);
			  }
			  else
			  {
				$jsonarr["success"] = false;
				$jsonarr["state"] = -1;
				$jsonarr["message"] = '更新任务数据缺失';
				$jsonarr["update_time"] = '';
				$result = json_encode($jsonarr);
			 }
	       } 
		   ob_clean();
	       echo $result;
		   return;
        }
		else
		{
			$jsonarr["success"] = false;
			$jsonarr["state"] = -1;
			$jsonarr["message"] = '请求方式错误';
			$jsonarr["update_time"] = '';
			$result = json_encode($jsonarr);
			ob_clean();
	        echo $result;
		    return;
		}
	}

	public  function getProjecttasktrailinfos()
	{
        if(request()->isGet()) 
		{
			$task_id  = input('task_id');
	        $outputList = array();
            $help=$this->run();
			//创建子任务轨迹
			$sql = "SELECT a.name,b.name as taskgroup_name,c.real_name as creator_nickname,
			     a.changer_id,c.headimgurl,a.create_time,a.creator_id 
			     from ipm_inst_subproject_task a 
				 left join ipm_inst_subproject_taskgroup b on a.taskgroup_id = b.id 
			     left join ipm_user c on a.creator_id = c.openid
				 where a.id  =$task_id and a.changer_id != '' ORDER BY a.create_time ASC";
			
			$list= Db::query($sql);
            foreach($list as $k=>$v)
			{
			  $info['time'] = $list[$k]['create_time'];
			  $info['openid'] = $list[$k]['creator_id'];
			  $info['name'] = $list[$k]['creator_nickname'];
			  $info['headimgurl'] = $list[$k]['headimgurl'];
			  $info['info'] = "创建了任务";
			  $info['content'] = $list[$k]['taskgroup_name']."-".$list[$k]['name']/*.",并指派给".$help->getUserName($list[$k]['changer_id'])*/;
              array_push($outputList,$info);
            }
			
			//参与任务轨迹
			/*$sql = "SELECT a.openid,d.name as taskgroup_name,b.name as task_name,c.real_name as creator_nickname,
			c.headimgurl,a.create_time 
			from ipm_inst_subproject_taskparter a 
			left join ipm_inst_subproject_task b on a.task_id = b.id 
			left join ipm_inst_subproject_taskgroup d on b.taskgroup_id = d.id 
			left join ipm_user c on a.openid = c.openid 
			where a.task_id  =$task_id and b.changer_id != '' ORDER BY a.create_time ASC";
			
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
			where a.task_id  =$task_id  and b.changer_id != '' ORDER BY a.create_time ASC";
			
			$taskState=array('','更新了任务备注','清空了任务备注','更新了开始时间','清空了开始时间','更新了截止时间','清空了截止时间','重做了任务','完成了任务','更新计划截止时间',
				'清空计划截止时间','添加任务参与人','删除任务参与人','指派任务负责人','更新了计划开始时间','清空了计划开始时间');
			$list= Db::query($sql);
            foreach($list as $k=>$v)
			{
			  if($list[$k]['change_type'] < 0 || $list[$k]['change_type'] > 15)
				  continue;
			  $info['time'] = $list[$k]['create_time'];
			  $info['openid'] = $list[$k]['openid'];
			  $info['name'] = $list[$k]['creator_nickname'];
			  $info['headimgurl'] = $list[$k]['headimgurl'];
			  $info['info'] = $taskState[$list[$k]['change_type']];
			  $info['content'] = $list[$k]['change_content'];/*$list[$k]['taskgroup_name']."-".$list[$k]['task_name']."   ".*/
			  if($list[$k]['change_type'] == 11 || $list[$k]['change_type'] == 12 || $list[$k]['change_type'] == 13)
			  {
			     $info['content'] = $help->getUserName($list[$k]['change_content']);
			  }
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
	
	public  function ProjecttasksInfo()
	{
        if(request()->isGet()) 
		{
            $subproject_id = input('subproject_id');
			$taskgroup_id  = input('taskgroup_id');
			$urgent  = input('urgent');
			$state  = input('state');
			$creator_id  = input('creator_id');
			$changer_id  = input('changer_id');
			$parter_id  = input('parter_id');
			$role_id  = input('role_id');
			$open_id  = input('open_id');
			$task_id = input('task_id');
			$start = input('start');
			$count = input('count');
			$keyword  = input('keyword'); 
			if(!isset($subproject_id))
			   return json();
		    $help=$this->run();
			$sql = "SELECT DISTINCT a.id,a.name,b.id as task_groupid,b.name as taskgroup_name,b.role_id,a.creator_id,c.real_name as creator_nickname,
			             a.changer_id,a.urgent,a.state, a.remarks,a.start_time_plan,a.end_time_plan,a.start_time_real,
			             a.end_time_real,c.headimgurl as creator_headimgurl,a.update_time,a.create_time 
			             from ipm_inst_subproject_task a 
						 left join ipm_inst_subproject_taskgroup b on a.taskgroup_id = b.id
			             left join ipm_user c on a.creator_id = c.openid 
						 left join ipm_inst_subproject_taskparter d on a.id = d.task_id
						 where b.subproject_id  =$subproject_id and a.changer_id !=''";
			if(isset($taskgroup_id))
			{
				$sql = $sql." and  b.id = '$taskgroup_id'";
			}
			if(isset($role_id))
			{
				$sql = $sql." and  b.role_id = '$role_id'";
			}
			if(isset($task_id))
			{
				$sql = $sql." and  a.id = '$task_id'";
			}
			
			if(isset($urgent))
			{
				$sql = $sql." and  a.urgent = '$urgent'";
			}
			if(isset($state))
			{
			   $sql = $sql." and  a.state = '$state'";
			}
			if(isset($open_id))
			{
			   $sql = $sql." and ( a.creator_id = '$open_id' or  a.changer_id = '$open_id' or d.openid = '$open_id')";
		    }
			if(isset($creator_id))
			{
			   $sql = $sql." and  a.creator_id = '$creator_id'";
			}
			if(isset($changer_id))
			{
			  $sql = $sql." and  a.changer_id = '$changer_id'";
			}
			if(isset($parter_id))
			{
			  $sql = $sql." and  d.openid = '$parter_id'";
			}
			if(isset($task_id))
			{
				$sql = $sql." and  a.id = '$task_id'";
			}
			if(isset($keyword))
			{
				$sql = $sql." and  (a.name LIKE '%$keyword%' OR b.name LIKE '%$keyword%' OR a.remarks LIKE '%$keyword%' OR a.create_time LIKE '%$keyword%')";
			}
			$sql = $sql." ORDER BY a.id ASC";
			if(isset($start) && isset($count))
			{
				$sql = $sql." LIMIT ".$start.",".$count;
			}
			$taskList = Db::query($sql);
			if(empty($taskList))
			{
			  return json();
			  continue;	
			}
			foreach($taskList as $kk=>$vv) 
			{
				$taskList[$kk]['changer_nickname'] = $help->getUserName($taskList[$kk]['changer_id']);
				$taskList[$kk]['changer_headimgurl'] = $help->getUserheadimgurl($taskList[$kk]['changer_id']);
				$taskList[$kk]['partitionNickname'] = $this->getTaskParters($taskList[$kk]['id']);
			}
			$outputList = array();
            foreach($taskList as $k=>$v)
              $outputList[] = $v;
            return json($outputList);		
        }
		else
		{
			return json();
		}
    }
	
	private function getTaskParters($task_id)
	{
		if(!isset($task_id))
			return json();
         $read= Db::query("SELECT DISTINCT a.openid, c.real_name as parter_nickname,c.headimgurl as parter_headimgurl
		                 FROM ipm_inst_subproject_taskparter a 
						 left join ipm_user c on a.openid = c.openid 
						 where a.task_id='$task_id'");
		 return  $read;
	}
}