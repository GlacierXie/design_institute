<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\Cache;
use think\Request;
require_once ROOT_PATH.'application\\common\\OssCommon.php';
use app\common\OssCommon;

class Orderproject extends Controller
{
	function run(){
           include('GlobalHelp.php');
           return new GlobalHelp();
	}

	//判断能否下单
	public  function  OrderSubProjectEnable()
	{
        if(request()->isGet()) 
		{
			$ossClient = OssCommon::getOssClient(true);
            if($ossClient == null)
              return json();
			$subproject_id = input('subproject_id');
			$help=$this->run();
			if (!$help->isValidSubPrj($subproject_id))
			{
				$jsonarr["success"] = false;
				$jsonarr["state"] = -1;
				$jsonarr["message"] = '当前用户不参与此项目';
				$result = json_encode($jsonarr);
				ob_clean();
	            echo $result;
		        return;
			}
			$prjData= Db::query(" select a.state as state
			from ipm_inst_subproject a
			where a.id='$subproject_id'");
			$prjState = $prjData[0]['state'];
			if($prjState != 4 && $prjState != 3)
			{
				$jsonarr["success"] = false;
				$jsonarr["state"] = -1;
				$jsonarr["message"] = '当前项目状态不允许下单';
				$result = json_encode($jsonarr);
				ob_clean();
	            echo $result;
		        return;
			}
            $role_id = 0;
			//标准层下单的 判断设计角色7的所有任务是否已完成 并且角色7的人没有待解决的问题
			if($prjState == 3)
			{
				$role_id = 7;
				$taskData= Db::query("SELECT a.id,c.real_name as nickname,a.name,b.name as taskgroup_name 
				FROM ipm_inst_subproject_task a  
				left join ipm_user c on a.changer_id=c.openid
				left join ipm_inst_subproject_taskgroup as b on a.taskgroup_id=b.id
				where b.subproject_id = '$subproject_id' and b.role_id = 7 and a.state != 3");
			    if(isset($taskData) && !empty($taskData))
			    {
					$jsonarr["success"] = false;
					$jsonarr["state"] = -1;
					$jsonarr["message"] = '当前项目设计组还存在未完成的任务，不能下单';
					$result = json_encode($jsonarr);
					ob_clean();
					echo $result;
					return;
			   }

			   $problemList = Db::query("SELECT DISTINCT a.id,c.real_name as nickname,a.title FROM ipm_inst_problem a  
			   left join ipm_user c on a.changer_id=c.openid
			   left join ipm_inst_subproject_user as b on a.changer_id=b.openid
			   where b.subproject_id = '$subproject_id' and b.role_id = 7 and a.state = 1");
			   if(isset($problemList) && !empty($problemList))
			   {
				   $jsonarr["success"] = false;
				   $jsonarr["state"] = -1;
				   $jsonarr["message"] = '当前项目设计组还存在未解决的的问题，不能下单';
				   $result = json_encode($jsonarr);
				   ob_clean();
				   echo $result;
				   return;
			  }
			  $prjState == 4;
			}
			//变化层下单的 判断设计角色7的所有任务是否已完成 并且角色7的人没有待解决的问题
			if($prjState == 4)
			{
				$role_id = 4;
				$taskData= Db::query("SELECT a.id,c.real_name as nickname,a.name,b.name as taskgroup_name 
				FROM ipm_inst_subproject_task a  
				left join ipm_user c on a.changer_id=c.openid
				left join ipm_inst_subproject_taskgroup as b on a.taskgroup_id=b.id
				where b.subproject_id = '$subproject_id' and b.role_id = 4 and a.state != 3");
			    if(isset($taskData) && !empty($taskData))
			    {
					$jsonarr["success"] = false;
					$jsonarr["state"] = -1;
					$jsonarr["message"] = '当前项目总工室变化层设计组还存在未完成的任务，不能下单';
					$result = json_encode($jsonarr);
					ob_clean();
					echo $result;
					return;
			   }

			   $problemList = Db::query("SELECT DISTINCT a.id,c.real_name as nickname,a.title FROM ipm_inst_problem a  
			   left join ipm_user c on a.changer_id=c.openid
			   left join ipm_inst_subproject_user as b on a.changer_id=b.openid
			   where b.subproject_id = '$subproject_id' and b.role_id = 4 and a.state = 1");
			   if(isset($problemList) && !empty($problemList))
			   {
				   $jsonarr["success"] = false;
				   $jsonarr["state"] = -1;
				   $jsonarr["message"] = '当前项目总工室变化层设计组还存在未解决的的问题，不能下单';
				   $result = json_encode($jsonarr);
				   ob_clean();
				   echo $result;
				   return;
			  }
			  $prjState == 5;
			}
            //查询公司id和项目id
			$prjInfo= Db::query("select DISTINCT a.id as prj_id,a.company_id as company_id 
				from ipm_inst_project a 
				WHERE  a.id IN(SELECT DISTINCT b.project_id from ipm_inst_subproject b WHERE b.id='$subproject_id')");
				
			$company_id = $prjInfo[0]['company_id'];	
			$prj_id = $prjInfo[0]['prj_id'];
			
			$list = Db::query("select a.id,a.name as file_name,a.type as file_type,
			                b.name as task_name,c.name as taskgroup_name,a.state as file_state 
			                from ipm_inst_file as a
                            left join ipm_inst_subproject_task as b on b.id=a.type
                            left join ipm_inst_subproject_taskgroup as c on c.id=b.taskgroup_id
                            where c.subproject_id= $subproject_id and a.state = 3  and b.state == 3 and c.role_id = $role_id");
			foreach($list as $k=>$v)
			{
				$id=$list[$k]['id'];
				$file_type=$list[$k]['file_type'];
				$fileName = $list[$k]['file_name'];
                $fileNameArry = explode(".", $fileName);
                if(empty($fileNameArry))
				{
					unset($list[$k]);
					continue;
				}
				
                //获取文件后缀
				$fileNameTitle = end($fileNameArry);
				$str = md5($id.$file_type."prj");
				$list[$k]['download_url'] = OssCommon::downloadUrl($ossClient,$company_id.'/'.$prj_id.'/'.$subproject_id.'/'.$str.'.'.$fileNameTitle);
			}
			$outputList = array();
            foreach($list as $k=>$v)
              $outputList[] = $v;
		
			$jsonarr["success"] = true;
			$jsonarr["state"] = $prjState;
			$jsonarr["message"] = '当前项目允许下单';
			$jsonarr["fileList"] = $outputList;
			return json($jsonarr);
		}
		else
		{
			$jsonarr["success"] = false;
			$jsonarr["state"] = -1;
			$jsonarr["message"] = '请求方式错误';
			$result = json_encode($jsonarr);
			ob_clean();
	        echo $result;
		    return;
		}
	}

    public  function  OrderSubProject()
	{
        if(request()->isPost()) 
		{
			$read = file_get_contents("php://input");
		    if (empty($read))
			{
                $jsonarr["success"] = false;
				$jsonarr["state"] = -1;
				$jsonarr["message"] = '下单项目参数缺失';
				$result = json_encode($jsonarr);
				ob_clean();
	            echo $result;
		        return;
            }
			$json = json_decode(trim($read,chr(239).chr(187).chr(191)),true);
			if (is_null($json)) 
			{
               $jsonarr["success"] = false;
			   $jsonarr["state"] = -1;
			   $jsonarr["message"] = '下单项目数据缺失，解析失败';
			   $result = json_encode($jsonarr);
			   ob_clean();
	           echo $result;
		       return;
			}
			$subproject_id = $json['subproject_id'];
			$openid = $json['openid'];
			if (!isset($subproject_id) || !isset($openid))
			{
			   $jsonarr["success"] = false;
			   $jsonarr["state"] = -1;
			   $jsonarr["message"] = '下单项目参数缺失';
			   $result = json_encode($jsonarr);
			   ob_clean();
	           echo $result;
		       return;
			}
			$help=$this->run();
			if (!$help->isPrjValidUser($subproject_id,$openid))
			{
				$jsonarr["success"] = false;
				$jsonarr["state"] = -1;
				$jsonarr["message"] = '当前用户不参与此项目';
				$result = json_encode($jsonarr);
				ob_clean();
	            echo $result;
		        return;
			}
			$update_time = date("Y-m-d H:i:s");
			$creat_time = date("Y-m-d H:i:s");
			
		    $prjData= Db::query("select a.state as state
                               from ipm_inst_subproject a
                               where a.id='$subproject_id'");
							   
		    $prjState = $prjData[0]['state'];
			if($prjState != 4 && $prjState != 3)
			{
			   $jsonarr["success"] = false;
			   $jsonarr["state"] = -1;
			   $jsonarr["message"] = '当前项目状态不允许下单';
			   $result = json_encode($jsonarr);
			   ob_clean();
	           echo $result;
		       return;
			}
			$prjNextstate= $prjState + 1;
		    Db::query("UPDATE ipm_inst_subproject SET state = '$prjNextstate' ,end_time_real = '$update_time',update_time = '$update_time' WHERE id = '$subproject_id'");
			Db::query("INSERT INTO ipm_inst_subproject_state_change (id,openid,subproject_id,changed_state,prev_state,update_time,create_time)
			VALUES(-1,'$openid','$subproject_id','$prjNextstate','$prjState','$update_time', '$creat_time')");
			$prjState = $prjNextstate;
			
			 $jsonarr["success"] = true;
			 $jsonarr["state"] = $prjState;
			 $jsonarr["message"] = '下单成功';
			 $result = json_encode($jsonarr);
			 ob_clean();
	         echo $result;
		     return;
        }
		else
		{
			 $jsonarr["success"] = false;
			 $jsonarr["state"] = -1;
			 $jsonarr["message"] = '请求方式错误';
			 $result = json_encode($jsonarr);
			 ob_clean();
	         echo $result;
		     return;
		}
    }
}