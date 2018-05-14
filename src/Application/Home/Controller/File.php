<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\Cache;
use think\Request;
require_once ROOT_PATH.'application\\common\\OssCommon.php';
use app\common\OssCommon;

class File extends Controller
{
    public function filetypelist(){
        if(request()->isGet()) 
        {
            $subproject_id= input('subproject_id');
            if(empty($subproject_id))
            {
                return json ();
            }
			$sql = "select DISTINCT a.type as file_type from ipm_inst_file a where a.subproject_id = $subproject_id ";
            $list= Db::query($sql);
            foreach($list as $k=>$v)
            {
				$file_typeTmp=$list[$k]['file_type'];
                //增加任务信息
                if($file_typeTmp > 0)
                {
                    $sql = "SELECT DISTINCT a.id,b.role_id,b.id as taskgroup_id,a.name as task_name,b.name as taskgroup_name 
                    from ipm_inst_subproject_task a 
                    left join ipm_inst_subproject_taskgroup b on b.id = a.taskgroup_id 
                    where a.id = $file_typeTmp";
                    $taskData = Db::query($sql);
                    if(empty($taskData))
                    {
                      continue;	
                    }
                    $list[$k]['filetype_name'] = $taskData[0]['taskgroup_name']."-".$taskData[0]['task_name'];
                }
                if($file_typeTmp == -1)
                {
                    $list[$k]['filetype_name'] = "答疑文件和底图依据";
                }
                if($file_typeTmp == -2)
                {
                    $list[$k]['filetype_name'] = "变更跟踪单";
                }
            }
           return json($list);
        }
    }

    public function file_list(){
        if(request()->isGet()) 
        {
            $subproject_id= input('subproject_id');
            if(empty($subproject_id))
            {
                return json ();
            }
            $ossClient = OssCommon::getOssClient(true);
            if($ossClient == null)
              return json();
			$sql = "select a.id,a.name as file_name,a.type as file_type,a.ctreator_id as creator_openid,a.state as file_state,
                            b.real_name as creator_nickname,b.headimgurl,
                            a.create_time,a.update_time,c.project_id,d.company_id 
                            from ipm_inst_file as a
                            left join ipm_user as b on b.openid=a.ctreator_id
                            left join ipm_inst_subproject as c on a.subproject_id=c.id
                            left join ipm_inst_project as d on c.project_id=d.id
                            left join ipm_inst_subproject_task as e on e.id=a.type
                            left join ipm_inst_subproject_taskgroup as f on f.id=e.taskgroup_id
                            where a.subproject_id = $subproject_id ";
			$creator_id = input('creator_id');
			if(isset($creator_id))
			{
				$sql = $sql." and a.ctreator_id='$creator_id'";
			}
			$file_type = input('file_type');
			if(isset($file_type))
			{
				$sql = $sql." and a.type='$file_type'";
			}
			$file_state = input('file_state');
			if(isset($file_state))
			{
				$sql = $sql." and a.state='$file_state'";
            }
            else
            {
                $sql = $sql." and a.state!=4";
            }
            $taskgroup_id  = input('taskgroup_id ');
			if(isset($taskgroup_id ))
			{
				$sql = $sql." and f.id='$taskgroup_id'";
            }
            $role_id  = input('role_id');
			if(isset($role_id ))
			{
                if($role_id == 2)
                {
                    $sql = $sql." and (f.role_id='$role_id' or a.type='-1' or a.type='-2')";
                }
                else
                {
                    $sql = $sql." and f.role_id='$role_id'";
                }
            }
            $keyword  = input('keyword'); 
            if(isset($keyword))
			{
				$sql = $sql." and a.name LIKE '%$keyword%'";
			}
            $login_id = input('login_id');
            if(isset($login_id))
            {
                $result = $this->subproject_role($login_id,$subproject_id);
                //分公司经理和总工室可以下载任何文件
                if($result['is_manager'])
                {
                    ;
                }
                else if($result['is_totalroomer'])
                {
                   ;
                }
                else 
                {
                    $sqlTmp = "a.type='-1' or a.type='-2'";
                    if($result['is_drawer'])
                    {
                        $sqlTmp = $sqlTmp." or f.role_id = '2'";
                    }
                    if($result['is_totalroomDesigner'])
                    {
                        $sqlTmp = $sqlTmp." or f.role_id = '4' or f.role_id = '2'";
                    }
                    if($result['is_totalroomContioner'])
                    {
                        $sqlTmp = $sqlTmp." or f.role_id = '4' or f.role_id = '7'";
                    }
                    if($result['is_designer'])
                    {
                        $sqlTmp = $sqlTmp." or f.role_id = '7' or f.role_id = '2'";
                    }
                    if($result['is_checker'])
                    {
                        $sqlTmp = $sqlTmp." or f.role_id = '6' or f.role_id = '2' or f.role_id = '4' or f.role_id = '7'  or f.role_id = '5'";
                    }
                    $sql = $sql." and(".$sqlTmp.")";
                }
            }

            $start = input('start');
            $count = input('count');
            if(isset($start) && isset($count))
			{
				$sql = $sql." LIMIT ".$start.",".$count;
            }
            $list= Db::query($sql);
            foreach($list as $k=>$v)
            {
                $company_idTmp=$list[$k]['company_id'];
                $project_idTmp=$list[$k]['project_id'];
                $openidTmp=$list[$k]['creator_openid'];
                $id=$list[$k]['id'];
				$file_typeTmp=$list[$k]['file_type'];
				$fileName = $list[$k]['file_name'];
                $fileNameArry = explode(".", $fileName);
                if(empty($fileNameArry))
				{
					$jsonarr["success"] = false;
				    $jsonarr["state"] = -1;
				    $result = json_encode($jsonarr);
				    ob_clean();
	                echo $result;
		            return;
				}
				$str = md5($id.$file_typeTmp."prj");
                //获取文件后缀
				$fileNameTitle = end($fileNameArry);
                $list[$k]['download_url'] = OssCommon::downloadUrl($ossClient,$company_idTmp.'/'.$project_idTmp.'/'.$subproject_id.'/'.$str.'.'.$fileNameTitle);
                
                //增加任务信息
                if($file_typeTmp > 0)
                {
                    $sql = "SELECT DISTINCT a.id,b.role_id,b.id as taskgroup_id,a.name as task_name,b.name as taskgroup_name 
                    from ipm_inst_subproject_task a 
                    left join ipm_inst_subproject_taskgroup b on b.id = a.taskgroup_id 
                    where a.id = $file_typeTmp";
                    $taskData = Db::query($sql);
                    if(empty($taskData))
                    {
                      continue;	
                    }
                    $list[$k]['task_id'] = $taskData[0]['id'];
                    $list[$k]['task_name'] = $taskData[0]['task_name'];
                    $list[$k]['taskgroup_name'] = $taskData[0]['taskgroup_name'];
                    $list[$k]['taskgroup_id'] = $taskData[0]['taskgroup_id'];
                    $list[$k]['role_id'] = $taskData[0]['role_id'];
                    $list[$k]['filetype_name'] = $taskData[0]['taskgroup_name']."-".$taskData[0]['task_name'];
                }
                if($file_typeTmp == -1)
                {
                    $list[$k]['filetype_name'] = "答疑文件和底图依据";
                    $list[$k]['role_id'] = 2;
                }
                if($file_typeTmp == -2)
                {
                    $list[$k]['filetype_name'] = "变更跟踪单";
                    $list[$k]['role_id'] = 2;
                }
            }
            /*foreach($list as $kk=>$vv){
                $list[$kk]['creator_status']=$result[$kk];
            }*/
           return json($list);
        }
    }

    public function subproject_role($openid,$subproject_id)
    {
        $read= Db::query(" select subproject_id,GROUP_CONCAT(role_id) as  role_ids
                                      from ipm_inst_subproject_user
                                    where subproject_id='$subproject_id' AND openid='$openid'  group by subproject_id
                         ");
            if($read){
                $res = ",".$read[0]['role_ids'];
                $list['is_manager'] = false;
                $list['is_drawer'] = false;
                $list['is_totalroomer'] = false;
                $list['is_totalroomDesigner'] = false;
                $list['is_totalroomContioner'] = false;
                $list['is_checker'] = false;
                $list['is_designer'] = false;
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
                return ($list);

            }else{
                $list['is_manager'] = false;
                $list['is_drawer'] = false;
                $list['is_totalroomer'] = false;
                $list['is_totalroomDesigner'] = false;
                $list['is_totalroomContioner'] = false;
                $list['is_checker'] = false;
                $list['is_designer'] = false;
                return ($list);
            }
    }

    public function tempfile_list(){
        if(request()->isGet()) 
        {
            $subproject_id= input('subproject_id');
            if(empty($subproject_id))
            {
                return json ();
            }
            $ossClient = OssCommon::getOssClient(true);
            if($ossClient == null)
              return json();
            $sql = "select a.id,a.name as file_name,a.ctreator_id as creator_openid,
                            b.real_name as creator_nickname,b.headimgurl,
                            a.create_time,a.update_time,c.project_id,d.company_id 
                            from ipm_inst_tempfile as a
                            left join ipm_user as b on b.openid=a.ctreator_id
                            left join ipm_inst_subproject as c on a.subproject_id=c.id
                            left join ipm_inst_project as d on c.project_id=d.id
                            where a.subproject_id = $subproject_id ";
            $creator_id = input('creator_id');
            if(isset($creator_id))
            {
                $sql = $sql." and a.ctreator_id='$creator_id'";
            }
            $keyword  = input('keyword'); 
            if(isset($keyword))
            {
                $sql = $sql." and a.name LIKE '%$keyword%'";
            }
            $start = input('start');
            $count = input('count');
            if(isset($start) && isset($count))
            {
                $sql = $sql." LIMIT ".$start.",".$count;
            }
            $list= Db::query($sql);
            foreach($list as $k=>$v)
            {
                $company_idTmp=$list[$k]['company_id'];
                $project_idTmp=$list[$k]['project_id'];
                $openidTmp=$list[$k]['creator_openid'];
                $id=$list[$k]['id'];
                $fileName = $list[$k]['file_name'];
                $fileNameArry = explode(".", $fileName);
                if(empty($fileNameArry))
                {
                    $jsonarr["success"] = false;
                    $jsonarr["state"] = -1;
                    $result = json_encode($jsonarr);
                    ob_clean();
                    echo $result;
                    return;
                }
                $str = md5($id."tempprj");
                //获取文件后缀
                $fileNameTitle = end($fileNameArry);
                $list[$k]['download_url'] = OssCommon::downloadUrl($ossClient,$company_idTmp.'/'.$project_idTmp.'/'.$subproject_id.'/'.$str.'.'.$fileNameTitle);
            }
           return json($list);
        }
    }
}