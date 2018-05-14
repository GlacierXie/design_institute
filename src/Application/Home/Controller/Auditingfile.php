<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\Cache;
use app\home\Util\Wechat;
use think\Request;

class Auditingfile extends Controller
{
	function run(){
           include('GlobalHelp.php');
           return new GlobalHelp();
	}
	
    public  function  Auditingfile()
	{
        if(request()->isPost())
		{
			$read = file_get_contents("php://input");
		    if (empty($read))
			{
               $jsonarr["success"] = false;
			   $jsonarr["state"] = -1;
			   $jsonarr["message"] = '审核文件数据缺失';
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
			   $jsonarr["message"] = '审核文件数据格式错误';
			   $result = json_encode($jsonarr);
			   ob_clean();
	           echo $result;
		       return;
			}
			$subproject_id = $json['subproject_id'];	
            $openid = $json['openid'];
            $fileid = $json['fileid'];
			$isok = $json['isok'];
			if(isset($openid) && isset($fileid) && isset($isok)&& isset($subproject_id))
			{
				$fileState=2;
			    if($isok)
				    $fileState=3;
				
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

				$fileData= Db::query(" select a.state as state,a.type as filetype 
                            from ipm_inst_file a
                            where a.id='$fileid'");

				$filePrevState = $fileData[0]['state'];
				$file_type = $fileData[0]['filetype'];
				if($filePrevState==4)
				{
					$jsonarr["success"] = false;
			        $jsonarr["state"] = -1;
					$jsonarr["message"] = '审核文件已删除';
			        $result = json_encode($jsonarr);
			        ob_clean();
	                echo $result;
		            return;
				}

				if ($fileState == $filePrevState)
				{

					$jsonarr["success"] = false;
			        $jsonarr["state"] = -1;
					$jsonarr["message"] = '审核文件状态不需要修改';
			        $result = json_encode($jsonarr);
			        ob_clean();
	                echo $result;
		            return;
				}

				//修改文件状态
				Db::query("UPDATE ipm_inst_file SET state = '$fileState',update_time = '$update_time' WHERE id = '$fileid'");
				
				//记录文件修改状态log
				Db::query("INSERT INTO ipm_inst_files_state_change (id,file_id,changer_id,changed_state,prev_state,update_time,create_time)
				      VALUES(-1,'$fileid','$openid','$fileState','$filePrevState','$update_time','$creat_time')");
				
				//审核变更跟踪单

				if($file_type == -2)
				{
				  if($isok)
				  {
					Db::query("UPDATE ipm_inst_file SET state = '1' ,update_time = '$update_time' WHERE state = '3' 
					           and subproject_id = '$subproject_id' and `type` !=-2");

					Db::query("UPDATE ipm_inst_subproject_task a SET a.state = '1' ,a.update_time = '$update_time' 
					           left join ipm_inst_subproject_taskgroup b on a.taskgroup_id = b.id 
								WHERE a.state != '1' and b.subproject_id = '$subproject_id'");
								
					$prjNextstate=1; 
					if ($prjNextstate != $prjState)
					{
						Db::query("UPDATE ipm_inst_subproject SET state = '$prjNextstate' ,update_time = '$update_time' WHERE id = '$subproject_id'");
						Db::query("INSERT INTO ipm_inst_subproject_state_change (id,openid,subproject_id,changed_state,prev_state,update_time,create_time)
						VALUES(-1,'$openid','$subproject_id','$prjNextstate','$prjState','$update_time', '$creat_time')");
						$prjState = $prjNextstate;
					}
				  }
				}
				//审核底图
				if($file_type > 0)
				{

					Db::query("UPDATE ipm_inst_subproject_task SET state = '$fileState',update_time = '$update_time' where id = '$file_type'");
					/*if($isok)
					{
						Db::query("UPDATE ipm_inst_subproject_task SET state = '3',update_time = '$update_time' where id = '$file_type'");
					}*/

					 //查询任务负责部门
					 $read= Db::query("SELECT a.role_id  FROM ipm_inst_subproject_taskgroup a  
					                   left join ipm_inst_subproject_task as b on b.taskgroup_id = a.id
					                   where b.id ='$file_type' and a.subproject_id = '$subproject_id'");

					if(isset($read) && !empty($read))
					{
						//判断是否上传深化底图
						if($read[0]['role_id'] == 2)
						{
						    $prjNextstate=1;
							if($isok)
								$prjNextstate=3;
							if ($prjNextstate != $prjState)
				            {
					             Db::query("UPDATE ipm_inst_subproject SET state = '$prjNextstate' ,update_time = '$update_time' WHERE id = '$subproject_id'");
				                 Db::query("INSERT INTO ipm_inst_subproject_state_change (id,openid,subproject_id,changed_state,prev_state,update_time,create_time)
				                 VALUES(-1,'$openid','$subproject_id','$prjNextstate','$prjState','$update_time', '$creat_time')");
				                 $prjState = $prjNextstate;
				            }
						}
					} 
			   }

			  $jsonarr["success"] = true;
			  $jsonarr["state"] = $prjState;
			  if ($isok)
			  {
			      $jsonarr["message"] = '审核文件成功';
                  $result = json_encode($jsonarr);
                  ob_clean();
                  echo $result;
                  $task_name=Db::table('ipm_inst_subproject_task')->where('id',$file_type)->value('name');
                  $subproject_name=Db::table('ipm_inst_subproject')->where('id',$subproject_id)->value('name');
                  $data=array(
                      'task_name'=>$task_name,
                      'file_type'=>$file_type,
                      'openid'=>$openid,
                      'status'=>'审核文件成功',
                      'subproject_name'=>$subproject_name
                  );

                  $this->PushMessage($data);
                  return;

			  }
			  else
			  {

				  $jsonarr["message"] = '打回文件成功';
                  $result = json_encode($jsonarr);
                  ob_clean();
                  echo $result;
                  $task_name=Db::table('ipm_inst_subproject_task')->where('id',$file_type)->value('name');
                  $subproject_name=Db::table('ipm_inst_subproject')->where('id',$subproject_id)->value('name');
                  $data=array(
                      'task_name'=>$task_name,
                      'file_type'=>$file_type,
                      'openid'=>$openid,
                      'status'=>'打回文件成功',
                      'subproject_name'=>$subproject_name
                  );
                 $this->PushMessage($data);
                  return;
			  }


			}
			else
			{
				$jsonarr["success"] = false;
				$jsonarr["state"] = -1;
				$jsonarr["message"] = '审核文件数据缺失';
				$result = json_encode($jsonarr);
				ob_clean();
	            echo $result;
		        return;
			}
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
    public function PushMessage($data){
        $task_name=$data['task_name'];
        $status=$data['status'];
        $file_type=$data['file_type'];
        $subproject_name=$data['subproject_name'];
        $openid=$data['openid'];
        $user=Db::table('ipm_user')->where("openid",$openid)->value('nickname');
        $roleTable=Db::table('ipm_inst_subproject_task')->where("id",$file_type)->select();
        if(!empty($roleTable)){
            foreach($roleTable as $k=>$v){
                $touser=$roleTable[$k]['changer_id'];

                $wechat = new Wechat();
                $access_token = $wechat->getAccessToken();
                $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";
                $template_id ='rHxj8JzABJCPQdlaI7hEjUxVfIEmvVVrxTGG6YlKY0w';
                $first['value'] = '你好，任务有新进度。';
                $first['color'] = '#173177';
                $keyword1['value'] =$task_name.'('.$subproject_name.')';
                $keyword1['color'] = '#173177';
                $keyword2['value'] = $status;
                $keyword2['color'] = '#173177';
                $keyword3['value'] = '无';
                $keyword3['color'] = '#173177';
                $keyword4['value'] = $user;
                $keyword4['color'] = '#173177';
                $remark['value'] = '请注意查看';
                $remark['color'] = '#173177';
                $data['first'] = $first;
                $data['keyword1'] = $keyword1;
                $data['keyword2'] = $keyword2;
                $data['keyword3'] = $keyword3;
                $data['keyword4'] = $keyword4;
                $data['remark'] = $remark;
                $msg['touser'] = $touser;
                $msg['template_id'] = $template_id;
                $msg['data'] = $data;
                $wechat->post($url, json_encode($msg));
            }
        }
    }
}