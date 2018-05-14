<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\Cache;
use think\Request;

class Overproject extends Controller
{
	function run(){
           include('GlobalHelp.php');
           return new GlobalHelp();
	}

    public  function  OverSubProject()
	{
        if(request()->isGet()) 
		{
            $openid = input('openid');
            $subproject_id = input('subproject_id');
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
			
		    $prjData= Db::query(" select a.state as state
                               from ipm_inst_subproject a
                               where a.id='$subproject_id'");
							   
		    $prjState = $prjData[0]['state'];
			if($prjState != 5)
			{
			   $jsonarr["success"] = false;
			   $jsonarr["state"] = -1;
			   $jsonarr["message"] = '当前项目状态不允许归档';
			   $result = json_encode($jsonarr);
			   ob_clean();
	           echo $result;
		       return;
			}

			$prjNextstate= 6;
		    Db::query("UPDATE ipm_inst_subproject SET state = '$prjNextstate' ,end_time_real = '$update_time',update_time = '$update_time' WHERE id = '$subproject_id'");
			Db::query("INSERT INTO ipm_inst_subproject_state_change (id,openid,subproject_id,changed_state,prev_state,update_time,create_time)
			VALUES(-1,'$openid','$subproject_id','$prjNextstate','$prjState','$update_time', '$creat_time')");
			$prjState = $prjNextstate;
			
			//判断是否所有子项目以归档
			$prjData= Db::query(" select a.project_id as project_id
                               from ipm_inst_subproject a
                               where a.id='$subproject_id'");
			$prjId = $prjData[0]['project_id'];
			$prjAll = Db::query("SELECT COUNT(id) as ids FROM `ipm_inst_subproject` WHERE `project_id` = '$prjId'");
			$prjStateAll = Db::query("SELECT COUNT(id) as ids FROM `ipm_inst_subproject` WHERE `project_id` = '$prjId' and `state` = 6");
			if($prjAll[0]['ids'] == $prjStateAll[0]['ids'])
			{
				Db::query("UPDATE ipm_inst_project SET state = 2,end_time_real = '$update_time',update_time = '$update_time' WHERE id = '$prjId'");
			}
		   $jsonarr["success"] = true;
		   $jsonarr["state"] = $prjState;
		   $jsonarr["message"] = '归档成功';
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