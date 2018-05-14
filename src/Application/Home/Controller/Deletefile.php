<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\Cache;
use think\Request;

class Deletefile extends Controller
{
	function run(){
           include('GlobalHelp.php');
           return new GlobalHelp();
	}
    public  function  deletefile()
	{
        if(request()->isPost())
		{
			$read = file_get_contents("php://input");
		    if (empty($read))
			{
                $jsonarr["success"] = false;
				$jsonarr["state"] = -1;
				$jsonarr["message"] = '删除文件数据缺失';
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
			   $jsonarr["message"] = '删除文件数据缺失，解析失败';
			   $result = json_encode($jsonarr);
			   ob_clean();
	           echo $result;
		       return;
			}
            $openid = $json['openid'];
            $subproject_id = $json['subproject_id'];
            $fileid = $json['fileid'];
			if(isset($openid) && isset($subproject_id) && isset($fileid))
			{
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
				if ($prjState==6)
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
				    $jsonarr["message"] = '项目已归档，不允许删除';
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}

				$fileData= Db::query(" select a.state as state,a.ctreator_id as ctreator_id,a.type as filetype 
                            from ipm_inst_file a
							where a.id='$fileid'");
						
				if(!isset($fileData) || empty($fileData))
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
				    $jsonarr["message"] = '文件不存在';
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}

				$filePrevState = $fileData[0]['state'];
				$ctreator_id = $fileData[0]['ctreator_id'];
				$file_type = $fileData[0]['filetype'];

				if ($filePrevState==3)
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
				    $jsonarr["message"] = '文件已审核，不允许删除';
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}
				
				if ($ctreator_id != $openid)
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
				    $jsonarr["message"] = '文件不是当前用户创建的，无权限删除';
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}

				if ($filePrevState==4)
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
				    $jsonarr["message"] = '文件已删除';
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}
				
				//修改文件状态
				Db::query("UPDATE ipm_inst_file SET state = '4' ,update_time = '$update_time' WHERE id = '$fileid'");
				 
				//记录文件修改状态log
				Db::query("INSERT INTO ipm_inst_files_state_change (id,file_id,changer_id,changed_state,prev_state,update_time,create_time)
					   VALUES(-1,'$fileid','$openid','4','$filePrevState','$update_time','$creat_time')");

				//删除任务文件
				if($file_type > 0)
				{
					$read= Db::query("SELECT a.role_id  FROM ipm_inst_subproject_taskgroup a  
					left join ipm_inst_subproject_task as b on b.taskgroup_id = a.id
					where b.id ='$file_type' and a.subproject_id = '$subproject_id'");
 
					 if(isset($read) && !empty($read))
					 {
						$dataInsert = ['id' => -1, 'openid' => $openid,'task_id' => $file_type,
						'change_type' => 7,'update_time' => $update_time,'create_time' => $creat_time];
					    Db::table('ipm_inst_subproject_task_change')->insertGetId($dataInsert);

						Db::query("UPDATE ipm_inst_subproject_task SET state = '1' ,update_time = '$update_time' WHERE id = '$file_type'");
						
						 //判断是否底图组
						if($prjState > 1 && $read[0]['role_id'] == 2)
						{
						    Db::query("UPDATE ipm_inst_subproject SET state = '1' ,update_time = '$update_time' WHERE id = '$subproject_id'");
							Db::query("INSERT INTO ipm_inst_subproject_state_change (id,openid,subproject_id,changed_state,prev_state,update_time,create_time)
							VALUES(-1,'$openid','$subproject_id',1,'$prjState','$update_time', '$creat_time')");
							$prjState = 1;
						}
                        
						//判断是否标准设计组
						if($prjState > 3 && $read[0]['role_id'] == 7)
						{
							Db::query("UPDATE ipm_inst_subproject SET state = '3' ,update_time = '$update_time' WHERE id = '$subproject_id'");
							Db::query("INSERT INTO ipm_inst_subproject_state_change (id,openid,subproject_id,changed_state,prev_state,update_time,create_time)
							VALUES(-1,'$openid','$subproject_id',3,'$prjState','$update_time', '$creat_time')");
							$prjState = 3;
						}

				        //判断是否总工室变化层设计组
						if($prjState > 4 && $read[0]['role_id'] == 4)
						{
							Db::query("UPDATE ipm_inst_subproject SET state = '4' ,update_time = '$update_time' WHERE id = '$subproject_id'");
							Db::query("INSERT INTO ipm_inst_subproject_state_change (id,openid,subproject_id,changed_state,prev_state,update_time,create_time)
							VALUES(-1,'$openid','$subproject_id',4,'$prjState','$update_time', '$creat_time')");
							$prjState = 4;
						}
					 } 
				}
			
			    $jsonarr["success"] = true;
			    $jsonarr["state"] = $prjState;
			    $jsonarr["message"] = '删除成功';
			    $result = json_encode($jsonarr);
			    ob_clean();
	            echo $result;
		        return;
			}
			else
			{
				$jsonarr["success"] = false;
				$jsonarr["state"] = -1;
				$jsonarr["message"] = '删除文件数据缺失';
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

	public  function  deletetempfile()
	{
        if(request()->isPost())
		{
			$read = file_get_contents("php://input");
		    if (empty($read))
			{
                $jsonarr["success"] = false;
				$jsonarr["state"] = -1;
				$jsonarr["message"] = '删除文件数据缺失';
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
			   $jsonarr["message"] = '删除文件数据缺失，解析失败';
			   $result = json_encode($jsonarr);
			   ob_clean();
	           echo $result;
		       return;
			}
            $openid = $json['openid'];
            $subproject_id = $json['subproject_id'];
            $fileid = $json['fileid'];
			if(isset($openid) && isset($subproject_id) && isset($fileid))
			{
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
				
				$fileData= Db::query(" select a.ctreator_id as ctreator_id,a.name as fileName 
                            from ipm_inst_tempfile a
							where a.id='$fileid'");

                if(!isset($fileData) || empty($fileData))
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
				    $jsonarr["message"] = '文件不存在';
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}

                $fileName = $fileData[0]['fileName'];
				$fileNameArry = explode(".", $fileName);
                if(empty($fileNameArry))
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '上传文件数据不对';
					$jsonarr["update_time"] = "";
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}
				
                //获取文件后缀
				$fileNameTitle = end($fileNameArry);		
				
				$ctreator_id = $fileData[0]['ctreator_id'];
				
				if ($ctreator_id != $openid)
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
				    $jsonarr["message"] = '文件不是当前用户创建的，无权限删除';
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}

				//修改文件状态
				Db::query("delete from ipm_inst_tempfile where id='$fileid'");

				$prjInfo= Db::query("select DISTINCT a.id as prj_id,a.company_id as company_id 
				from ipm_inst_project a 
				WHERE  a.id IN(SELECT DISTINCT b.project_id from ipm_inst_subproject b WHERE b.id='$subproject_id')");
				
				$company_id = $prjInfo[0]['company_id'];	
                $prj_id = $prjInfo[0]['prj_id'];
				$str = md5($fileid."tempprj");

				//重命名POST文件名 保存到&path目录下面	
				$file = FILE_PATH.$company_id.'/'.$prj_id.'/'.$subproject_id.'/'.$str.'.'.$fileNameTitle;

				if (file_exists($file)) 
				{ 
					unlink($file);
				} 
			    $jsonarr["success"] = true;
			    $jsonarr["state"] = 1;
			    $jsonarr["message"] = '删除成功';
			    $result = json_encode($jsonarr);
			    ob_clean();
	            echo $result;
		        return;
			}
			else
			{
				$jsonarr["success"] = false;
				$jsonarr["state"] = -1;
				$jsonarr["message"] = '删除文件数据缺失';
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
}