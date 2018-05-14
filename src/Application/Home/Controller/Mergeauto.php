<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\Cache;
use think\Request;
require_once ROOT_PATH.'application\\common\\OssCommon.php';
use app\common\OssCommon;

class Mergeauto extends Controller
{
    public function mergefilelist(){
        if(request()->isGet()) 
        {
        	//查询所有需要生成的图纸文件
        	//1.查询未生成的
        	//查询所有子项目信息
           $ossClient = OssCommon::getOssClient(true);
           if($ossClient == null)
              return json();
        	$sql = "select e.id as project_id,a.id as prjid,a.name as prjname,b.id as config_id,b.name as config_name,
        	        e.company_id 
                    from ipm_inst_subproject a
                    left join ipm_inst_project as e on a.project_id = e.id
                    left join ipm_inst_configuration as b on e.config_id = b.id
                    where a.id NOT IN(SELECT DISTINCT subproject_id FROM ipm_inst_mergefile)";
            $list= Db::query($sql);
            foreach($list as $k=>$v)
            {
            	$config_id=$list[$k]['config_id'];
				       $fileName = $list[$k]['config_name'];
                $fileNameArry = explode(".", $fileName);
                $company_idTmp=$list[$k]['company_id'];
                $project_idTmp=$list[$k]['project_id'];
                if(empty($fileNameArry))
				        {
                   unset($list[$k]);
                   continue;   
                }
				  
                //获取文件后缀
				        $fileNameTitle = end($fileNameArry);
                $list[$k]['config_url'] = OssCommon::downloadUrl($ossClient,$company_idTmp.'/'.'configFiles'.'/'.$config_id.'.'.$fileNameTitle);
                $subproject_id = $list[$k]['prjid'];
                $sql = "select c.id,d.name as task_name,
                     c.name as file_name,c.type as file_type,c.state as file_state 
                     from ipm_inst_subproject a 
        	         left join ipm_inst_file as c on a.id = c.subproject_id
        	         left join ipm_inst_subproject_task as d on d.id=c.type
        	         where d.name IN('底图签收','设计部签收','终版底图','墙','梁','楼板','楼梯','背楞','吊模','节点')
        	         and c.state = 3
        	         and a.id = '$subproject_id'";
              $prjlist= Db::query($sql);
              if (empty($prjlist)) {
              	 unset($list[$k]);
              	 continue;  
              }
              
              $truedata = true;
              $list[$k]['basedraw'] =  '';
              $list[$k]['wall'] =  '';
              $list[$k]['beam'] =  '';
              $list[$k]['slab'] =  '';
              $list[$k]['staris'] =  '';
              $list[$k]['walling'] =  '';
              $list[$k]['suspend'] =  '';
              $list[$k]['joint'] =  '';
              $list[$k]['basedrawFilename'] =  '';
              $list[$k]['wallFilename'] =  '';
              $list[$k]['beamFilename'] =  '';
              $list[$k]['slabFilename'] =  '';
              $list[$k]['starisFilename'] =  '';
              $list[$k]['wallingFilename'] =  '';
              $list[$k]['suspendFilename'] =  '';
              $list[$k]['jointFilename'] =  '';
              foreach($prjlist as $kk=>$vv)
              {
              	$file_typeTmp=$prjlist[$kk]['file_type'];
				        $fileName = $prjlist[$kk]['file_name'];
                $fileNameArry = explode(".", $fileName);
                $id=$prjlist[$kk]['id'];
                if(empty($fileNameArry))
				        {
					        $truedata = false;
	                break;
				         }
				        $str = md5($id.$file_typeTmp."prj");
                //获取文件后缀
				        $fileNameTitle = end($fileNameArry);
                $downUrl = OssCommon::downloadUrl($company_idTmp.'/'.$project_idTmp.'/'.$subproject_id.'/'.$str.'.'.$fileNameTitle);
                if ($prjlist[$kk]['task_name'] == '底图签收' || $prjlist[$kk]['task_name'] == '设计部签收'
                	|| $prjlist[$kk]['task_name'] == '终版底图') {
                 	$list[$k]['basedraw'] =  $downUrl;
                  $list[$k]['basedrawFilename'] =  $fileName;
                }
                if ($prjlist[$kk]['task_name'] == '墙') {
                 	$list[$k]['wall'] =  $downUrl;
                  $list[$k]['wallFilename'] =  $fileName;
                }
                if ($prjlist[$kk]['task_name'] == '梁') {
                 	$list[$k]['beam'] =  $downUrl;
                  $list[$k]['beamFilename'] =  $fileName;
                }
                if ($prjlist[$kk]['task_name'] == '楼板') {
                 	$list[$k]['slab'] =  $downUrl;
                  $list[$k]['slabFilename'] =  $fileName;
                }
                if ($prjlist[$kk]['task_name'] == '楼梯') {
                 	$list[$k]['staris'] =  $downUrl;
                  $list[$k]['starisFilename'] =  $fileName;
                }
                if ($prjlist[$kk]['task_name'] == '背楞') {
                 	$list[$k]['walling'] =  $downUrl;
                  $list[$k]['wallingFilename'] =  $fileName;
                }
                if ($prjlist[$kk]['task_name'] == '吊模') {
                 	$list[$k]['suspend'] =  $downUrl;
                  $list[$k]['suspendFilename'] =  $fileName;
                }
                if ($prjlist[$kk]['task_name'] == '节点') {
                 	$list[$k]['joint'] =  $downUrl;
                  $list[$k]['jointFilename'] =  $fileName;
                }
              }
              if (!$truedata) {
              	 unset($list[$k]);
              	 continue;  
              }
            }

        	//2.已经生成的需要比对的
        	$sql = "select e.id as project_id,a.id as prjid,a.name as prjname,b.id as config_id,b.name as config_name,
        	        e.company_id,c.update_time
                    from ipm_inst_subproject a
                    left join ipm_inst_project as e on a.project_id = e.id
                    left join ipm_inst_configuration as b on e.config_id = b.id
                    left join ipm_inst_mergefile as c on c.subproject_id = a.id
                    where a.id IN(SELECT DISTINCT subproject_id FROM ipm_inst_mergefile) ";

            $listTmp= Db::query($sql);
            foreach($listTmp as $k=>$v)
            {
				        $config_id=$listTmp[$k]['config_id'];
				        $fileName = $listTmp[$k]['config_name'];
				        $company_idTmp=$listTmp[$k]['company_id'];
                $project_idTmp=$listTmp[$k]['project_id'];
                $fileNameArry = explode(".", $fileName);
                if(empty($fileNameArry))
				        {
                   unset($listTmp[$k]);
                   continue;   
                }
				         $updtatime = $listTmp[$k]['update_time'];
                //获取文件后缀
				        $fileNameTitle = end($fileNameArry);
                $listTmp[$k]['config_url'] = OssCommon::downloadUrl($ossClient,$company_idTmp.'/'.'configFiles'.'/'.$config_id.'.'.$fileNameTitle);
                $subproject_id = $listTmp[$k]['prjid'];
                $sql = "select c.id,d.name as task_name,c.update_time,
                     c.name as file_name,c.type as file_type,c.state as file_state
                     from ipm_inst_subproject a 
        	         left join ipm_inst_file as c on a.id = c.subproject_id
        	         left join ipm_inst_subproject_task as d on d.id=c.type
        	         where d.name IN('底图签收','设计部签收','终版底图','墙','梁','楼板','楼梯','背楞','吊模','节点')
        	         and c.state = 3
        	         and a.id = '$subproject_id'";
              $prjlist= Db::query($sql);
              if (empty($prjlist)) {
              	 unset($listTmp[$k]);
              	 continue;  
              }
             
              $truedata = true;
              $newData = false;
              $listTmp[$k]['basedraw'] =  '';
              $listTmp[$k]['wall'] =  '';
              $listTmp[$k]['beam'] =  '';
              $listTmp[$k]['slab'] =  '';
              $listTmp[$k]['staris'] =  '';
              $listTmp[$k]['walling'] =  '';
              $listTmp[$k]['suspend'] =  '';
              $listTmp[$k]['joint'] =  '';
              $listTmp[$k]['basedrawFilename'] =  '';
              $listTmp[$k]['wallFilename'] =  '';
              $listTmp[$k]['beamFilename'] =  '';
              $listTmp[$k]['slabFilename'] =  '';
              $listTmp[$k]['starisFilename'] =  '';
              $listTmp[$k]['wallingFilename'] =  '';
              $listTmp[$k]['suspendFilename'] =  '';
              $listTmp[$k]['jointFilename'] =  '';
              foreach($prjlist as $kk=>$vv)
              {
              	$file_typeTmp=$prjlist[$kk]['file_type'];
				        $fileName = $prjlist[$kk]['file_name'];
                $fileNameArry = explode(".", $fileName);
                $id=$prjlist[$kk]['id'];
                if(empty($fileNameArry))
				        {
					        $truedata = false;
	                break;
				        }
				       if (strtotime($updtatime) < strtotime($prjlist[$kk]['update_time'])) {
					         $newData = true;
	                break;
				          }
				        $str = md5($id.$file_typeTmp."prj");
                //获取文件后缀
				        $fileNameTitle = end($fileNameArry);
                $downUrl =OssCommon::downloadUrl($ossClient,$company_idTmp.'/'.$project_idTmp.'/'.$subproject_id.'/'.$str.'.'.$fileNameTitle);
                if ($prjlist[$kk]['task_name'] == '底图签收' || $prjlist[$kk]['task_name'] == '设计部签收'
                  || $prjlist[$kk]['task_name'] == '终版底图') {
                  $listTmp[$k]['basedraw'] =  $downUrl;
                  $listTmp[$k]['basedrawFilename'] =  $fileName;
                }
                if ($prjlist[$kk]['task_name'] == '墙') {
                  $listTmp[$k]['wall'] =  $downUrl;
                  $listTmp[$k]['wallFilename'] =  $fileName;
                }
                if ($prjlist[$kk]['task_name'] == '梁') {
                  $listTmp[$k]['beam'] =  $downUrl;
                  $listTmp[$k]['beamFilename'] =  $fileName;
                }
                if ($prjlist[$kk]['task_name'] == '楼板') {
                  $listTmp[$k]['slab'] =  $downUrl;
                  $listTmp[$k]['slabFilename'] =  $fileName;
                }
                if ($prjlist[$kk]['task_name'] == '楼梯') {
                  $listTmp[$k]['staris'] =  $downUrl;
                  $listTmp[$k]['starisFilename'] =  $fileName;
                }
                if ($prjlist[$kk]['task_name'] == '背楞') {
                  $listTmp[$k]['walling'] =  $downUrl;
                  $listTmp[$k]['wallingFilename'] =  $fileName;
                }
                if ($prjlist[$kk]['task_name'] == '吊模') {
                  $listTmp[$k]['suspend'] =  $downUrl;
                  $listTmp[$k]['suspendFilename'] =  $fileName;
                }
                if ($prjlist[$kk]['task_name'] == '节点') {
                  $listTmp[$k]['joint'] =  $downUrl;
                  $listTmp[$k]['jointFilename'] =  $fileName;
                }
              }
              if (!$truedata || !$newData) {
              	 unset($listTmp[$k]);
              	 continue;  
              }
            }
            foreach($list as $kk=>$vv){
                $result[]=$vv;
            }
            foreach($listTmp as $kk=>$vv){
                $result[]=$vv;
            }
            return json($result);
        }
    }

  public  function  uploadFile()
	{
    if(request()->isPost())
		{
       $ossClient = OssCommon::getOssClient(false);
           if($ossClient == null)
              return json();
      $subproject_id = input('subproject_id');  
			if(isset($subproject_id))
			{
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

        //获取文件后缀
				$fileNameTitle = end($fileNameArry);
        
        $fileInfo= Db::query("select DISTINCT id,name from ipm_inst_mergefile WHERE subproject_id ='$subproject_id'");
        if(!empty($fileInfo))
        {
            $fileId = $fileInfo[0]['id'];
            $strTmp = md5($fileInfo[0]['id'].$fileInfo[0]['name']."merge");
            $fileNameArryTmp = explode(".", $fileInfo[0]['name']);
            if(!empty($fileNameArryTmp))
            {
              $fileNameTitleTmp = end($fileNameArryTmp);
              OssCommon::deletefile($ossClient,$company_id.'/'.$prj_id.'/'.$subproject_id.'/'.$strTmp.'.'.$fileNameTitleTmp);
            }
            Db::query("UPDATE ipm_inst_mergefile SET name = '$fileName' ,update_time = '$update_time' WHERE subproject_id = '$subproject_id'");
        }
        else
        {
           //将文件数据插入到ipm_inst_file中 获取创建的自增ID
            $dataInsert = [
                             'id' => -1,
                             'name' => $fileName,
                             'subproject_id' => $subproject_id,
                             'update_time' => $update_time,
                             'create_time' => $creat_time
                             ];
            $fileId = Db::table('ipm_inst_mergefile')->insertGetId($dataInsert);
        }
				
        $str = md5($fileId.$fileName."merge");

				//重命名POST文件名 保存到&path目录下面
				$path = $company_id.'/'.$prj_id.'/'.$subproject_id.'/'.$str.'.'.$fileNameTitle;
				if (!OssCommon::uploadFile($ossClient,$_FILES["file"]["tmp_name"],$path))
				{
					  Db::query("delete from ipm_inst_file a where a.id='$fileId'");
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