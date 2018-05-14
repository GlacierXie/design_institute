<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\Cache;
use think\Request;
use app\home\Util\Wechat;
use app\admin\controller\Message;
require_once ROOT_PATH.'application\\common\\OssCommon.php';
use app\common\OssCommon;

class UploadFile extends Controller
{
	function run(){
           include('GlobalHelp.php');
           return new GlobalHelp();
    }
    public  function  upload()
    {
    	$ossClient = OssCommon::getOssClient(true);
        if($ossClient == null)
             return json();
        echo OssCommon::downloadUrl($ossClient,'test/1.txt');
    }

	public  function  uploadConfig()
	{
        if(request()->isPost())
		{
            $openid = input('openid');
            $company_id = input('company_id');
			if(isset($openid) && isset($company_id))
			{
				$ossClient = OssCommon::getOssClient(false);
                if($ossClient == null)
                 return json();

				$help=$this->run();
				if(!$help->isValidUser($company_id,$openid))
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '公司人员信息不对';
					$jsonarr["update_time"] = "";
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}

			    $update_time = date("Y-m-d H:i:s");
			    $creat_time = date("Y-m-d H:i:s");
				if (empty($_FILES))
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '上传文件为空';
					$jsonarr["update_time"] = "";
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}
				if ($_FILES["file"]["error"] != 0)
                {
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '文件上传有错误';
					$jsonarr["update_time"] = "";
					$result = json_encode($jsonarr);
					ob_clean();
	                echo $result;
		            return;
				}

                $fileName = $_FILES["file"]["name"];  //获取post过来的文件名
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

				//将文件数据插入到ipm_inst_file中 获取创建的自增ID
				$dataInsert =
                    [
                        'id' => -1,
                        'company_id' => $company_id,
                        'name' => $fileName,
                        'creator_id' => $openid,
                        'update_time' => $update_time,
                        'create_time' => $creat_time
                    ];
				$fileId = Db::table('ipm_inst_configuration')->insertGetId($dataInsert);

				//重命名POST文件名 保存到&path目录下面
				$path = $company_id.'/configFiles/'.$fileId.'.'.$fileNameTitle;
				if (!OssCommon::uploadFile($ossClient,$_FILES["file"]["tmp_name"],$path))
				{
					Db::query("delete from ipm_inst_configuration where id='$fileId'");
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '文件移动到服务器失败';
					$jsonarr["update_time"] = "";
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}

				$jsonarr["success"] = true;
				$jsonarr["state"] = 1;
				$jsonarr["message"] = '上传配置文件成功';
				$jsonarr["update_time"] = $update_time;
				$result = json_encode($jsonarr);
				ob_clean();
	            echo $result;
		        return;
			}
			else
			{
				$jsonarr["success"] = false;
				$jsonarr["state"] = -1;
				$jsonarr["message"] = '上传文件参数缺失';
				$jsonarr["update_time"] = "";
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
			$jsonarr["update_time"] = "";
			$result = json_encode($jsonarr);
			ob_clean();
	        echo $result;
		    return;
		}
	}

    public  function  uploadFile()
	{
        if(request()->isPost())
		{
			$ossClient = OssCommon::getOssClient(false);
            if($ossClient == null)
              return json();

            $openid = input('openid');
            $subproject_id = input('subproject_id');
            //task_id
            $file_type = input('file_type');
           
			if(isset($openid) && isset($subproject_id) && isset($file_type))
			{
				$help=$this->run();
				if($file_type != -1 && $file_type != -2 && !$help->isPrjValidTask($subproject_id,$file_type))
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '上传文件类型错误';
					$jsonarr["update_time"] = "";
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}

				if (!$help->isPrjValidUser($subproject_id,$openid))
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '当前用户不参与此项目';
					$jsonarr["update_time"] = "";
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}
				$filestate=1;
			    if($file_type==-1)
				    $filestate = 2;

			    $update_time = date("Y-m-d H:i:s");
			    $creat_time = date("Y-m-d H:i:s");
				if (empty($_FILES))
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '上传文件为空';
					$jsonarr["update_time"] = "";
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}
				if ($_FILES["file"]["error"] != 0)
                {
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '文件上传有错误';
					$jsonarr["update_time"] = "";
					$result = json_encode($jsonarr);
					ob_clean();
	                echo $result;
		            return;
				}
				$prjData= Db::query(" select a.state as state from ipm_inst_subproject a where a.id='$subproject_id'");
                $prjState = $prjData[0]['state'];

				//查询公司id和项目id
				$prjInfo= Db::query("select DISTINCT a.id as prj_id,a.company_id as company_id from ipm_inst_project a WHERE  a.id IN(SELECT DISTINCT b.project_id from ipm_inst_subproject b WHERE b.id='$subproject_id')");

				$company_id = $prjInfo[0]['company_id'];
                $prj_id = $prjInfo[0]['prj_id'];

                $fileName = $_FILES["file"]["name"];  //获取post过来的文件名
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

				if($file_type > 0)
				{
				   //查询任务负责部门
				   $read= Db::query("SELECT a.role_id,b.state FROM ipm_inst_subproject_taskgroup a left join ipm_inst_subproject_task as b on b.taskgroup_id = a.id  where b.id ='$file_type' and a.subproject_id = '$subproject_id'");
                    if(isset($read) && !empty($read))
					{
						$taskState = $read[0]['state'];
						if($taskState != 1)
						{
							 $jsonarr["success"] = false;
							 $jsonarr["state"] = -1;
							 $jsonarr["message"] = '该任务已提交,无需重复提交';
							 $jsonarr["update_time"] = "";
							 $result = json_encode($jsonarr);
							 ob_clean();
							 echo $result;
							 return;
						}
						 //获取文件后缀
				        $fileNameTitle = end($fileNameArry);

				        //将文件数据插入到ipm_inst_file中 获取创建的自增ID
				         $dataInsert = [
                             'id' => -1,
                             'name' => $fileName,
                             'ctreator_id' => $openid,
				             'subproject_id' => $subproject_id,
                             'state' => $filestate,
                             'type' => $file_type ,
                             'update_time' => $update_time,
                             'create_time' => $creat_time
                         ];

				         $fileId = Db::table('ipm_inst_file')->insertGetId($dataInsert);
				         $str = md5($fileId.$file_type."prj");

				         //重命名POST文件名 保存到&path目录下面
				         $path = $company_id.'/'.$prj_id.'/'.$subproject_id.'/'.$str.'.'.$fileNameTitle;
				         if (!OssCommon::uploadFile($ossClient,$_FILES["file"]["tmp_name"],$path))
				         {
					        Db::query("delete from ipm_inst_file where id='$fileId'");
					        $jsonarr["success"] = false;
				            $jsonarr["state"] = -1;
					        $jsonarr["message"] = '文件移动到服务器失败';
					        $jsonarr["update_time"] = "";
				            $result = json_encode($jsonarr);
				            ob_clean();
	                        echo $result;
		                    return;
				        }
						 //判断是否上传深化底图
						if($prjState != 2 && $read[0]['role_id'] == 2)
						{
							Db::query("UPDATE ipm_inst_subproject SET state = '2' ,update_time = '$update_time' WHERE id = '$subproject_id'");
							Db::query("INSERT INTO ipm_inst_subproject_state_change (id,openid,subproject_id,changed_state,prev_state,update_time,create_time) VALUES (-1,'$openid','$subproject_id',2,'$prjState','$update_time', '$creat_time')");
							$prjState = 2;
							//底图已深化
						}
						/*if($read[0]['role_id'] != 2)
						{
							Db::query("UPDATE ipm_inst_file SET state = '3' WHERE id = '$fileId'");
						}
						else
						{
							Db::query("UPDATE ipm_inst_file SET state = '2' WHERE id = '$fileId'");
						}*/
						//根据$file_type对应的任务名称为终版底图发送终版底图消息

						//上传成果
						$dataInsert =
                            [
                                'id' => -1,
                                'openid' => $openid,
                                'task_id' => $file_type,
                                'change_type' => 8,
                                'update_time' => $update_time,
                                'create_time' => $creat_time
                            ];
						Db::table('ipm_inst_subproject_task_change')->insertGetId($dataInsert);

						Db::query("UPDATE ipm_inst_subproject_task SET state = '2' ,end_time_real = '$update_time' WHERE id = '$file_type'");
					}

					$jsonarr["success"] = true;
					$jsonarr["state"] = $prjState;
					$jsonarr["message"] = '上传任务成果成功';
					$jsonarr["update_time"] = $update_time;

					$result = json_encode($jsonarr);
					ob_clean();
					echo $result;
                    $role_id=$read[0]['role_id'];
                    $this->Content($file_type,$role_id,$filestate,$openid);
					return;
				}
				 //获取文件后缀
				$fileNameTitle = end($fileNameArry);

				//将文件数据插入到ipm_inst_file中 获取创建的自增ID
				$dataInsert =
                    [
                        'id' => -1,
                        'name' => $fileName,
                        'ctreator_id' => $openid,
				        'subproject_id' => $subproject_id,
                        'state' => $filestate,
                        'type' => $file_type,
                        'update_time' => $update_time,
                        'create_time' => $creat_time
                    ];

				$fileId = Db::table('ipm_inst_file')->insertGetId($dataInsert);
				$str = md5($fileId.$file_type."prj");

				//重命名POST文件名 保存到&path目录下面
				$path = $company_id.'/'.$prj_id.'/'.$subproject_id.'/'.$str.'.'.$fileNameTitle;
				if (!OssCommon::uploadFile($ossClient,$_FILES["file"]["tmp_name"],$path))
				{
					Db::query("delete from ipm_inst_file where id='$fileId'");
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '文件移动到服务器失败';
					$jsonarr["update_time"] = "";
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}
				$jsonarr["success"] = true;
				$jsonarr["state"] = $prjState;
				$jsonarr["message"] = '上传文件成功';
				$jsonarr["update_time"] = $update_time;
				$result = json_encode($jsonarr);
				ob_clean();
	            echo $result;
		        return;
			}
			else
			{
				$jsonarr["success"] = false;
				$jsonarr["state"] = -1;
				$jsonarr["message"] = '上传文件参数缺失';
				$jsonarr["update_time"] = "";
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
			$jsonarr["update_time"] = "";
			$result = json_encode($jsonarr);
			ob_clean();
	        echo $result;
		    return;
		}
	}
    private function Content($file_type,$role_id,$filestate,$openid){
        $task_name=Db::table('ipm_inst_subproject_task')->where('id',$file_type)->value('name');
        $subproject_id=Db::table('ipm_inst_subproject_task')->where('id',$file_type)->value('subproject_id');
        $subproject_name=Db::table('ipm_inst_subproject')->where('id',$subproject_id)->value('name');
        $user=Db::table('ipm_user')->where('openid',$openid)->value('nickname');
        if($filestate==1){
            $filestate='待审核';
        }
        if($filestate==2){
            $filestate='待修改';
        }
        $data=array(
            'task_name'=>$task_name,
            'filestate'=>$filestate,
            'user'=>$user,
            'subproject_name'=>$subproject_name,
            'subproject_id'=>$subproject_id
        );
        $message=new Message();
        //如果是底图任务
        if($role_id==2){
            if($task_name=='设计部签收'){
                $task_id= $file_type+1;
                $changer_ids=Db::table('ipm_inst_subproject_task')->where('id',$task_id)->value('changer_id');
                $task_names=Db::table('ipm_inst_subproject_task')->where('id',$task_id)->value('name');
                //如果有负责人就通知，没有就直接返回
                if($changer_ids){
                    $data1=array(
                        'task_name'=>$task_names,
                        'filestate'=>$filestate,
                        'user'=>$user,
                        'subproject_name'=>$subproject_name,
                        'subproject_id'=>$subproject_id,
                        'openid'=>$changer_ids
                    );
                    //通知检查组，以及任务负责人

                    $message->PushMessage2($data1);
                }else{

                    $message->PushMessage1($data);
                }

            }else{
                //通知这个参与这个项目并且是检查组的人
                $message->PushMessage1($data);
            }
        //设计任务
        }elseif($role_id==7){
            //通知检查组以及设计组的模长
            $message->PushMessage($data);
        }
    }


	public  function  uploadTempFile()
	{
        if(request()->isPost())
		{
            $openid = input('openid');
            $subproject_id = input('subproject_id');
			
			$ossClient = OssCommon::getOssClient(false);
            if($ossClient == null)
              return json();

			if(isset($openid) && isset($subproject_id))
			{
				$help=$this->run();
				if (!$help->isPrjValidUser($subproject_id,$openid))
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '当前用户不参与此项目';
					$jsonarr["update_time"] = "";
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}
			    $update_time = date("Y-m-d H:i:s");
			    $creat_time = date("Y-m-d H:i:s");
				if (empty($_FILES))
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '上传文件为空';
					$jsonarr["update_time"] = "";
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}
				if ($_FILES["file"]["error"] != 0)
                {
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '文件上传有错误';
					$jsonarr["update_time"] = "";
					$result = json_encode($jsonarr);
					ob_clean();
	                echo $result;
		            return;
				}
				//查询公司id和项目id
				$prjInfo= Db::query("select DISTINCT a.id as prj_id,a.company_id as company_id 
				from ipm_inst_project a 
				WHERE  a.id IN(SELECT DISTINCT b.project_id from ipm_inst_subproject b WHERE b.id='$subproject_id')");
				
				$company_id = $prjInfo[0]['company_id'];	
                $prj_id = $prjInfo[0]['prj_id'];
				
                $fileName = $_FILES["file"]["name"];  //获取post过来的文件名	
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
				
				//将文件数据插入到ipm_inst_file中 获取创建的自增ID
				$dataInsert = ['id' => -1, 'name' => $fileName,'ctreator_id' => $openid,
				'subproject_id' => $subproject_id,'update_time' => $update_time,'create_time' => $creat_time];
				$fileId = Db::table('ipm_inst_tempfile')->insertGetId($dataInsert);
				$str = md5($fileId."tempprj");

				//重命名POST文件名 保存到&path目录下面	
				$path = $company_id.'/'.$prj_id.'/'.$subproject_id.'/'.$str.'.'.$fileNameTitle;
				if (!OssCommon::uploadFile($ossClient,$_FILES["file"]["tmp_name"],$path))
				{
					Db::query("delete from ipm_inst_tempfile where id='$fileId'");
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
					$jsonarr["message"] = '文件移动到服务器失败';
					$jsonarr["update_time"] = "";
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}


				$jsonarr["success"] = true;
				$jsonarr["state"] = 1;
				$jsonarr["message"] = '上传文件成功';
				$jsonarr["update_time"] = $update_time;
				$result = json_encode($jsonarr);
				ob_clean();
	            echo $result;
		        return;
			}
			else
			{
				$jsonarr["success"] = false;
				$jsonarr["state"] = -1;
				$jsonarr["message"] = '上传文件参数缺失';
				$jsonarr["update_time"] = "";
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
			$jsonarr["update_time"] = "";
			$result = json_encode($jsonarr);
			ob_clean();
	        echo $result;
		    return;
		}
	}
}