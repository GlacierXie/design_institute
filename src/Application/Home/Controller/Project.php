<?php
namespace app\home\controller;
use think\Controller;
use app\home\model\Projects;
use think\Db;
use think\Cache;
use think\Request;
require_once ROOT_PATH.'application\\common\\OssCommon.php';
use app\common\OssCommon;

class Project extends Controller
{
	function run(){
           include('GlobalHelp.php');
           return new GlobalHelp();
    }

    public  function  project_name()
    {
        if(request()->isGet()) 
        {
            $project_id = input('project_id');
            if(!isset($project_id))
               return json();
            $list= Db::query(" SELECT name from ipm_inst_project where id = $project_id");
            return json($list);
        }
        else
        {
            return json();
        }
    }

    public  function  subproject_name()
    {
        if(request()->isGet()) 
        {
            $subproject_id = input('subproject_id');
            if(!isset($subproject_id))
               return json();
            $list= Db::query(" SELECT name from ipm_inst_subproject where id = $subproject_id");
            return json($list);
        }
        else
        {
            return json();
        }
    }

    public  function  subprojectInfo_list()
    {
        if(request()->isGet())
		{
            $project_id = input('project_id');
            $openid = input('openid');
            if(!isset($project_id)){
                return json();
            }
            $sql= " SELECT DISTINCT a.id,a.name from ipm_inst_subproject as a left join ipm_inst_project as b on
                          a.project_id=b.id left join ipm_inst_subproject_user as c on a.id=c.subproject_id where a.project_id =$project_id";
            if(isset($openid))
            {
                $sql = $sql." and (b.creator_id='$openid' or c.openid='$openid')";
            }
            $list= Db::query($sql);
            return json($list);
        }
        else
        {
            return json();
        }
    }

    public  function  projectInfo_list()
    {
        $arr= $this->request->param();
        if (!isset($arr['company_id']) || empty($arr['company_id'])) {
            return json('company_id empty');
        }
        $company_id=$arr['company_id'];
        if(!isset($arr['openid']) || empty($arr['openid'])){
            $list= Db::query(" SELECT id,name from ipm_inst_project where company_id = $company_id");
            return json($list);
        }else{
            $openid=$arr['openid'];
            $projectTable= new Projects();
            $list=$projectTable->project_name1($openid);
            return json($list);
        }
    }

    public  function  project_list()
	{
        if(request()->isGet()) 
		{
            $ossClient = OssCommon::getOssClient(true);
            if($ossClient == null)
              return json();
            $company_id = input('company_id');
            $project_id = input('project_id');
            $subproject_id = input('subproject_id');
            $openid = input('openid');
            $state = input('state');
			
			$sql = "SELECT DISTINCT a.id AS project_id,a.name,a.creator_id,c.real_name as creator_nickname, b.id as config_id,
                    b.name as config_name,a.state,a.start_time_plan,a.end_time_plan,a.start_time_real,a.end_time_real 
                    from ipm_inst_project a
                    left join ipm_inst_configuration as b on a.config_id = b.id
                    left join ipm_user c on a.creator_id = c.openid 
                    left join ipm_inst_subproject d on d.project_id = a.id
                    where a.id != 0";
            if(isset($subproject_id))
            {
                $sql = $sql." and d.id = $subproject_id ";
            }

            if(isset($company_id))
            {
                $sql = $sql." and a.company_id = $company_id ";
            }
            if(isset($project_id))
            {
                $sql = $sql." and a.id = $project_id ";
            }
            if(isset($state))
            {
               $sql = $sql." and a.state = $state ";
            }
            $list = Db::query($sql);
			if(!isset($list) || empty($list))
			{
			  return json();
			}
			foreach($list as $k=>$v) 
			{
				$config_id=$list[$k]['config_id'];
				$fileName = $list[$k]['config_name'];
                $fileNameArry = explode(".", $fileName);
                if(empty($fileNameArry))
				{
                   unset($list[$k]);
                   continue;   
                }
				  
                //获取文件后缀
				$fileNameTitle = end($fileNameArry);
                $list[$k]['config_url'] = OssCommon::downloadUrl($ossClient,$company_id.'/'.'configFiles'.'/'.$config_id.'.'.$fileNameTitle);
		
                $prj_id = $list[$k]['project_id'];
                $sql = "SELECT DISTINCT a.id AS subproject_id,a.name,a.state,a.start_time_plan,a.end_time_plan,a.start_time_real,a.end_time_real 
                        from ipm_inst_subproject a 
                        left join ipm_inst_subproject_user b on b.subproject_id = a.id
                        where a.project_id = '$prj_id'";
                 
                 if(isset($subproject_id))
                 {
                     $sql = $sql." and a.id = $subproject_id ";
                 }
                 if(isset($openid))
                 {
                     $sql = $sql." and b.openid = '$openid' ";
                 }
                 $list[$k]['subproject_list'] = Db::query($sql);
				 if(empty($list[$k]['subproject_list']))
				 {
					unset($list[$k]);
					continue;	
				 }
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
}