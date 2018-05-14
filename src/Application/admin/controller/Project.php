<?php
namespace app\admin\controller;
use app\home\model\Projects;
use app\home\model\Subprojects;
use app\home\model\Defaulttaskgroup;
use app\home\model\Defaulttasks;
use app\home\model\Taskgroups;
use app\home\model\Tasks;
use app\home\model\Roles;
use app\home\model\Taskparters;
use think\Controller;
use think\Db;
use think\Cache;
use think\Request;
use think\Loader;
require_once ROOT_PATH.'application\\common\\OssCommon.php';
use app\common\OssCommon;

class Project extends Controller
{
	//查询项目甘特图
	public function gantts(){
		$arr= $this->request->param();
		$subprj_id = $arr['subprj_id'];
	
		$data= Db::query("SELECT d.role_id,c.state,d.id FROM ipm_inst_subproject_task AS c JOIN(
							  SELECT
								a.id,
								b.task_id,
								b.role_id
							  FROM
								ipm_inst_subproject AS a
							  JOIN
								(
								SELECT
								  id AS task_id,
								  subproject_id,
								  role_id
								FROM
								  ipm_inst_subproject_taskgroup
							  ) AS b ON b.subproject_id = a.id
							WHERE
							  a.id = $subprj_id
							) AS d ON d.task_id = c.taskgroup_id");
		
		$count_finished_dwg = 0;
		$count_finished_design = 0;
		$count_sum_dwg = 0;
		$count_sum_design = 0;
		foreach($data as $k=>$v)
		{
			//底图计数
			if($data[$k]['role_id'] == 2 )
			{
				$count_sum_dwg = $count_sum_dwg+1;
				if($data[$k]['state'] !=1)
				{
					$count_finished_dwg = $count_finished_dwg +1;
				}
			}
			//设计计数
			if($data[$k]['role_id'] != 2 )
			{	
				$count_sum_design = $count_sum_design+1;
				if($data[$k]['state'] !=1){
					$count_finished_design = $count_finished_design +1;
				}
			}
		}
		
		$data2 = Db::query("SELECT *from ipm_inst_subproject where id= $subprj_id");
		$result['count_sum_dwg'] = $count_sum_dwg;
		$result['count_finished_dwg'] = $count_finished_dwg;
		$result['count_finished_design'] = $count_finished_design;
		$result['count_sum_design'] = $count_sum_design;
		$result['start_time_plan'] = $data2[0]['start_time_plan'];
		$result['design_start_plan'] = $data2[0]['design_start_plan'];
		$result['dwg_end_plan'] = $data2[0]['dwg_end_plan'];
		$result['end_time_plan'] = $data2[0]['end_time_plan'];
		return json ($result);

	}
	
    //添加项目
    public  function  add_project(){
            $arr= $this->request->param();
            if (!isset($arr['company_id']) || empty($arr['company_id'])) {
                return json('company_id empty');
            }
            if (!isset($arr['prjname']) || empty($arr['prjname'])) {
                return json('prjname empty');
            }
        $arr = array(
            "name" => $arr['prjname'],
            "company_id" =>$arr['company_id'],
            "creator_id" => $arr['creator_id'],
            "state" => 1,
            "start_time_plan" => date("Y-m-d H:i:s"),
            "end_time_plan" => date("Y-m-d H:i:s"),
            "start_time_real" => date("Y-m-d H:i:s"),
            "end_time_real" => date("Y-m-d H:i:s"),
            "update_time" => date("Y-m-d H:i:s"),
            "create_time" => date("Y-m-d H:i:s"),
        );
        //自增一条 记录
        $id = Db::table('ipm_inst_project')->insertGetId($arr);
        if($id){
            $res['success'] = true;
            $res['message'] = "success";
        }
        return json ($res);

    }
    //查看所有项目信息
    public function any_company_list(){
        $projectTable= new Projects();
        //查询所有项目的所有信息
        $ossClient = OssCommon::getOssClient(true);
        if($ossClient == null)
             return json();

        $list=$projectTable->any_company_list();
        $currentpage=1;
        $itemsPerPage=20;
        if($list){
            foreach($list as $k=>$v){
                $prj_id = $list[$k]['project_id'];
                $company_id = $list[$k]['company_id'];
                $config_id=$list[$k]['config_id'];
                $config_name=$list[$k]['config_name'];
                $arr=explode(".",$config_name);
                $suffix=$arr[1];
                $list[$k]['config_url'] = OssCommon::downloadUrl($ossClient,$company_id.'/configFiles/'.$config_id.'.'.$suffix);
                //根据project_id来查询项目信息以及子项目信息
                $list[$k]['subproject_list']=$projectTable->subproject_project_list($prj_id,$currentpage,$itemsPerPage,'');
            }

        }else{
            return json();
        }
        return json($list);
    }
    //任务看板
    public function month_task_list($subprj_id){
        //获取当月的第一天
        $start_time=date('Y-m-01', strtotime(date("Y-m-d")));
        $str='%Y-%m-%d';
        $ye=  explode('-', $start_time)[0];
        $me=  explode('-', $start_time)[1];
        //获取当月的最后一天
        $var = date("t",strtotime($start_time));
        $TaskgroupsTable=new Taskgroups();
        $taskTable= new Tasks();
        //根据subprj_id来查询总任务
        $res=$TaskgroupsTable->taskparter_list($subprj_id);
        //从第一天开始，小于每个月的最后一天，依次循环

        for($d=1;$d<=$var;$d++){
            //年月日拼接
            $time = $ye.'-'.$me.'-'.$d;
            $result[$d]['time'] = $time;
            $result[$d]['sum'] = 0;
            $result[$d]['sum_1'] = 0;
            foreach($res as $k=>$v){
                $taskgroup_id=$res[$k]['id'];
                //根据taskgroup_id 并且state!=1的来查询子任务数
                $rel= $taskTable->select_TaskList($taskgroup_id,$time,$str);
                //根据taskgroup_id 来查询子任务数
                $rel_1= $taskTable->select_TaskList_1($taskgroup_id,$time,$str);
                //拼接

                $result[$d]['sum'] = intval($result[$d]['sum']) + intval($rel[0]['sum']);
                $result[$d]['sum_1'] = intval($result[$d]['sum_1']) + intval($rel_1[0]['sum_1']);
            }
        }
        return $result;
    }

    //根据company_id查看所有项目的所有信息
    public function project_list(){
        $arr= $this->request->param();
        if (!isset($arr['company_id']) || empty($arr['company_id'])) {
            return json();
        }
        $ossClient = OssCommon::getOssClient(true);
        if($ossClient == null)
             return json();

            $company_id = $arr['company_id'];
            $currentpage = $arr['currentpage'];
            $itemsPerPage = $arr['itemsPerPage'];
			$subprjState = $arr['subprjState'];
            $projectTable= new Projects();
            if(empty($arr['openid']) || !isset($arr['openid'])){
                $list=$projectTable->user_project_list($company_id,$currentpage,$itemsPerPage,'');
                if($list){
                    foreach($list as $k=>$v){
                        $prj_id = $list[$k]['project_id'];
                        $config_id=$list[$k]['config_id'];
                        $config_name=$list[$k]['config_name'];
                        $arr=explode(".",$config_name);
                        $suffix = end($arr);
                        $list[$k]['config_url'] = OssCommon::downloadUrl($ossClient,$company_id.'/'.'configFiles/'.$config_id.'.'.$suffix);
                        $list[$k]['subproject_list']=$projectTable->subproject_project_list($prj_id,$currentpage,$itemsPerPage,$subprjState,'');
                    }
                }else{
                    return json();
                }
            }else{
                $openid = $arr['openid'];
				$mysubprj = $projectTable->project_my($openid);
                $list=$projectTable->user_project_list($company_id,$currentpage,$itemsPerPage,$openid);
                if($list){
                    foreach($list as $k=>$v){
                        $prj_id = $list[$k]['project_id'];
                        $config_id=$list[$k]['config_id'];
                        $config_name=$list[$k]['config_name'];
                        $arr=explode(".",$config_name);
                        $suffix = end($arr);
                        $list[$k]['config_url'] = OssCommon::downloadUrl($ossClient,$company_id.'/'.'configFiles/'.$config_id.'.'.$suffix);
                        $list[$k]['subproject_list']=$projectTable->subproject_project_list($prj_id,$currentpage,$itemsPerPage,$subprjState,$openid);
                        foreach($list[$k]['subproject_list'] as $kk=>$vv){
                            $sub_prjid=$list[$k]['subproject_list'][$kk]['subproject_id'];
                            $aaa=$projectTable->subproject_role_list($sub_prjid,$openid,$currentpage,$itemsPerPage);
                            //   $list[$k]['subproject_list'][$kk]['statics'] = $this->month_task_list($sub_prjid);
                            if($aaa){
                                foreach($aaa as $key=>$val ){
                                    $id=$aaa[$key]['role_id'];
                                    if($id==3){
                                        $list[$k]['subproject_list'][$kk]['zg_roles']= true;
                                    }else{
                                        $list[$k]['subproject_list'][$kk]['zg_roles']= false;
                                    }
                                }
                            }else{
                                $list[$k]['subproject_list'][$kk]['zg_roles']= false;
                            }
                        }
                    }
                }else{
                    return json();
                }
            }
            //根据company_id查询所有项目configuration，user信息
              return json($list);
    }

    public function Download_list(){
        $arr= $this->request->param();
        $company_id=$arr['company_id'];
        $project_id=$arr['project_id'];
        $subproject_id=$arr['subproject_id'];
        $SubprojectTable= new Subprojects();
        $ossClient = OssCommon::getOssClient(true);
        if($ossClient == null)
             return json();
        $list=$SubprojectTable->file_subprj_project_list($subproject_id,$project_id,$company_id);
        $list1=$SubprojectTable->subpr_project_config_list($project_id,$company_id,$subproject_id);
        foreach($list as $k=>$v){
            $names=$list[$k]['name'];
            $fileNameArry = explode(".", $names);
            //获取数组最后一位
            $fileNameTitle = end($fileNameArry);
            $type=$list[$k]['type'];
            $file_id=$list[$k]['file_id'];
            $str = md5($file_id.$type."prj");
            $res['prj_file'][$k]['filename'] = $names;
            $res['prj_file'][$k]['file']= OssCommon::downloadUrl($ossClient,$company_id.'/'.$project_id.'/'.$subproject_id.'/'.$str.'.'.$fileNameTitle);
        }

        foreach($list1 as $k=>$v){
            $name=$list1[$k]['config_name'];
            $fileNameArry = explode(".", $name);
            //获取数组最后一位
            $fileNameTitle = end($fileNameArry);
            $config_id=$list1[$k]['config_id'];
            $res['prj_conf'][$k]['filename']=$name;
            $res['prj_conf'][$k]['file']=OssCommon::downloadUrl($ossClient,$company_id.'/'.'configFiles'.'/'.$config_id.'.'.$fileNameTitle);

        }
        return  json ($res) ;

    }
   
    //   删除项目
    public function del_project(){
        $arr= $this->request->param();
        if (!isset($arr['project_id']) || empty($arr['project_id'])){
            return json('project_id empty');
        }
        $ossClient = OssCommon::getOssClient(false);
        if($ossClient == null)
             return json();
            $project_id=$arr['project_id'];
            $state = Db::table('ipm_inst_project')->where('id',$project_id)->value('state');
            $company_id=Db::table('ipm_inst_project')->where('id',$project_id)->value('company_id');
            $dirName= $company_id.'/'.$project_id.'/'; //路径
            if(isset($state) && $state==1 ){
                $subprojectTable= new Subprojects();
                $sublist=$subprojectTable->get_state($project_id);
                if(empty($sublist) || !isset($sublist)){
                    $id = Db::table('ipm_inst_project')->where('id', $project_id)->delete();
                    if ($id) {
                        $is=Common::deleteDir($dirName);
                        if($is){
                            $res['success'] = true;
                            $res['message'] = "delete success";

                        }else {
                            $res['success'] = false;
                            $res['message'] = "delete error";

                        }
                    }
                }else{
                    $a=false;
                    //20180107暂时不判断项目状态--直接删除
					/*foreach($sublist as $k=>$v){
                        if($sublist[$k]['state']!=1){
                            $a = true;
                            break;
                        }
                    }*/
                    if($a)
                    {
                        $res['success'] = false;
                        $res['message'] = 'state error';

                    }else {
                        //事物操作
                        Db::startTrans();
                        try {
                            $projectTable = Db::table('ipm_inst_project')->where('id',$project_id)->delete();
                            if($projectTable){
                                $subprojectlist = Db::table('ipm_inst_subproject')->where('project_id', $project_id)->select();
                                if($subprojectlist){
                                    foreach ($subprojectlist as $k => $v) {
                                        $subproject_id = $subprojectlist[$k]['id'];
                                        $taskgroup = Db::table('ipm_inst_subproject_taskgroup')->where('subproject_id', $subproject_id)->delete();
                                    }
                                    foreach ($subprojectlist as $k => $v) {
                                        $subproject_id = $subprojectlist[$k]['id'];
                                        $state_change = Db::table('ipm_inst_subproject_state_change')->where('subproject_id', $subproject_id)->delete();
                                    }
                                    foreach ($subprojectlist as $k => $v) {
                                        $subproject_id = $subprojectlist[$k]['id'];
                                        $subproject_user = Db::table('ipm_inst_subproject_user')->where('subproject_id', $subproject_id)->delete();
                                    }
                                    foreach ($subprojectlist as $k => $v) {
                                        $subproject_id = $subprojectlist[$k]['id'];
                                        $inst_fileTable = Db::table('ipm_inst_file')->where('subproject_id', $subproject_id)->select();
                                        foreach($inst_fileTable as $key=>$val){
                                            $file_id = $subprojectlist[$k]['id'];
                                            $inst_file = Db::table('ipm_inst_files_state_change')->where('file_id', $file_id)->delete();
                                        }
                                        $inst_file = Db::table('ipm_inst_file')->where('subproject_id', $subproject_id)->delete();
                                    }
                                    foreach ($subprojectlist as $k => $v) {
                                        $subproject_id = $subprojectlist[$k]['id'];
                                        $inst_tempfile = Db::table('ipm_inst_tempfile')->where('subproject_id', $subproject_id)->delete();
                                    }
                                    $subprojectTable = Db::table('ipm_inst_subproject')->where('project_id',$project_id)->delete();

                                    $problemTable = Db::table('ipm_inst_problem')->where('project_id', $project_id)->select();
                                    foreach($problemTable as $kk=>$vv){
                                        $problem_id = $problemTable[$k]['id'];
                                        $inst_file = Db::table('ipm_inst_problem_files')->where('problem_id', $problem_id)->delete();
                                    }

                                    $deleteproblemTable = Db::table('ipm_inst_problem')->where('project_id', $project_id)->delete();
                                    $taskTable = Db::table('ipm_inst_subproject_task')->where('project_id', $project_id)->select();
                                    foreach ($taskTable as $kk => $vv) {
                                        $task_id = $taskTable[$kk]['id'];
                                        $taskparterTable = Db::table('ipm_inst_subproject_taskparter')->where('task_id', $task_id)->delete();
                                    }
                                    foreach ($taskTable as $kk => $vv) {
                                        $task_id = $taskTable[$kk]['id'];
                                        $taskparterTable = Db::table('ipm_inst_subproject_task_change')->where('task_id', $task_id)->delete();
                                    }
                                    $taskTable= Db::table('ipm_inst_subproject_task')->where('project_id', $project_id)->delete();
                                    if($taskTable){
                                        $is=OssCommon::deleteDir($ossClient,$dirName);
                                        if($is){
                                            $res['success'] = true;
                                            $res['message'] = "delete success";
                                        }else {
                                            $res['success'] = false;
                                            $res['message'] = "delete error";

                                        }
                                    }
                                }
                            }

                            Db::commit();
                        } catch (\Exception $e) {
                            // 回滚事务
                            Db::rollback();
                        }

                }
            }
        }
        return json ($res);
    }

    public function project_subprojectlist(){
        $arr= $this->request->param();
        if (!isset($arr['prj_id']) || empty($arr['prj_id'])){
            return json('prj_id  不能为空');
        }
        $subprojectTable=new Subprojects();
        $res=$subprojectTable->subproject_List($arr['prj_id']);
        if($res){
            return json($res);
        }else{
            return json();
        }
    }

    private function checkRequestData()
    {

        $json = file_get_contents("php://input");
        if (empty($json)) {
            $res['success'] = false;
            $res['message'] = 'Empty RequestData';
            return json ($res);
        }
        $read = json_decode($json,true);
        if (is_null($read)) {
            $res['success'] = false;
            $res['message'] = "json_decode_error";
            return json ($res);
        }
        return $read;
    }
    //1.获取总项目列表
    public function getProjectList(){
        if (request()->isGet()) {
            $company_id = input('company_id');
            $start = input('start');
            $count = input('count');
            $keyword = input('keyword');
            $state = input('state');
     
            if (!isset($company_id) || empty($company_id)) {
                return json('company_id  不能为空');
            }
            if (!isset($start)) {
                return json('start  不能为空');
            }
            if (!isset($count) || empty($count)) {
                return json('count   不能为空');
            }

            $ossClient = OssCommon::getOssClient(true);
           if($ossClient == null)
              return json();
            $ProjectTable = new Projects();
            $sql = $ProjectTable->user_project_list1($company_id,$start,$count);
            if(isset($keyword))
            {
                $sql = $sql." and  a.name LIKE '%$keyword%'";
            }
            if (isset($state)) {
                $sql = $sql . " and  a.state= $state ";
            }
            if (isset($start) && isset($count)) {
                $sql = $sql . " LIMIT " . $start . "," . $count;
            }
            $list = Db::query($sql);
            if ($list) {
                foreach ($list as $k => $v) {
                    $config_id = $list[$k]['config_id'];
                    $project_id = $list[$k]['project_id'];
                    $config_name = $list[$k]['config_name'];
                    $arr = explode(".", $config_name);
                    $suffix = end($arr);
                    $list[$k]['config_url'] = OssCommon::downloadUrl($ossClient,$company_id . '/' . 'configFiles/' . $config_id . '.' . $suffix);
                    $subprojectTable = new Subprojects();
                    $lists = $subprojectTable->CountList($project_id);
                    $list1 = $subprojectTable->CountList1($project_id);
                    $list2 = $subprojectTable->CountList2($project_id);
                    $list3 = $subprojectTable->CountList3($project_id);
                    $list4 = $subprojectTable->CountList4($project_id);
                    $list5 = $subprojectTable->CountList5($project_id);
                    $list6 = $subprojectTable->CountList6($project_id);
                    $count = $lists[0]['count'];
                    $count1 = $list1[0]['count'];
                    $count2 = $list2[0]['count'];
                    $count3 = $list3[0]['count'];
                    $count4 = $list4[0]['count'];
                    $count5 = $list5[0]['count'];
                    $count6 = $list6[0]['count'];
                    $list[$k]['subptoject_count'] = $count;
                    $list[$k]['subptoject_state_count1'] = $count1;
                    $list[$k]['subptoject_state_count2'] = $count2;
                    $list[$k]['subptoject_state_count3'] = $count3;
                    $list[$k]['subptoject_state_count4'] = $count4;
                    $list[$k]['subptoject_state_count5'] = $count5;
                    $list[$k]['subptoject_state_count6'] = $count6;
                }
            } else {
                return json();
            }
            return json($list);
        }
    }
    //2.根据总项目id获取子项目列表
    public function getSubprojectList(){
        if(request()->isGet()) {
            $project_id= input('project_id');
            $start= input('start');
            $count= input('count');
            $subprj_id= input('subprj_id');
            $state= input('state');
            $keyword= input('keyword');
            $unfinished= input('unfinished');
            $priority= input('priority');
            if (!isset($project_id) || empty($project_id)) {
                return json('project_id  不能为空');
            }
            if (!isset($start)) {
                return json('start  不能为空');
            }
            if (!isset($count) || empty($count)) {
                return json('count   不能为空');
            }
            $ProjectTable = new Projects();
            $sql = $ProjectTable->subproject_project_list1($project_id);

            if(isset($unfinished)){
                $lists= Db::query(" select * from ipm_inst_subproject_task where state='1' and project_id='$project_id' and DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(end_time_real) group by subproject_id");

                if(isset($unfinished) && empty($state) && empty($keyword) && empty($priority) ){
                    foreach($lists as $k=>$v){
                        $subprjid=$lists[$k]['subproject_id'];
                        $list1[]= Db::query(" select id,project_id,name,state,priority,
                                  DATE_FORMAT(start_time_plan, '%Y-%m-%d') as start_time_plan,
                                  DATE_FORMAT(dwg_end_plan, '%Y-%m-%d') as dwg_end_plan,
                                  DATE_FORMAT(design_start_plan, '%Y-%m-%d') as design_start_plan,
                                  DATE_FORMAT(end_time_plan, '%Y-%m-%d') as end_time_plan,
                                  DATE_FORMAT(start_time_real, '%Y-%m-%d') as start_time_real,
                                  DATE_FORMAT(end_time_real, '%Y-%m-%d') as end_time_real,
                                  DATE_FORMAT(create_time, '%Y-%m-%d') as create_time
                                  from  ipm_inst_subproject
                                where  id='$subprjid' "." LIMIT ".$start.",".$count);

                    }
                }

               if(isset($unfinished) && isset($state) && empty($keyword) && empty($priority) ){
                   foreach($lists as $k=>$v){
                       $subprjid=$lists[$k]['subproject_id'];
                       $list1[]= Db::query(" select id,project_id,name,state,priority,
                                      DATE_FORMAT(start_time_plan, '%Y-%m-%d') as start_time_plan,
									  DATE_FORMAT(dwg_end_plan, '%Y-%m-%d') as dwg_end_plan,
									  DATE_FORMAT(design_start_plan, '%Y-%m-%d') as design_start_plan,
                                      DATE_FORMAT(end_time_plan, '%Y-%m-%d') as end_time_plan,
                                      DATE_FORMAT(start_time_real, '%Y-%m-%d') as start_time_real,
                                      DATE_FORMAT(end_time_real, '%Y-%m-%d') as end_time_real,
                                      DATE_FORMAT(create_time, '%Y-%m-%d') as create_time
                                      from  ipm_inst_subproject
                                    where   id='$subprjid' and  state= '$state'"." LIMIT ".$start.",".$count);

                   }
               }

              if( isset($unfinished) && isset($state) && isset($keyword) && empty($priority) ){
                    foreach($lists as $k=>$v){
                        $subprjid=$lists[$k]['subproject_id'];
                        $list1[]= Db::query(" select id,project_id,name,state,priority,
                                      DATE_FORMAT(start_time_plan, '%Y-%m-%d') as start_time_plan,
									  DATE_FORMAT(dwg_end_plan, '%Y-%m-%d') as dwg_end_plan,
									  DATE_FORMAT(design_start_plan, '%Y-%m-%d') as design_start_plan,
                                      DATE_FORMAT(end_time_plan, '%Y-%m-%d') as end_time_plan,
                                      DATE_FORMAT(start_time_real, '%Y-%m-%d') as start_time_real,
                                      DATE_FORMAT(end_time_real, '%Y-%m-%d') as end_time_real,
                                      DATE_FORMAT(create_time, '%Y-%m-%d') as create_time
                                      from  ipm_inst_subproject
                                    where id='$subprjid' and  state= '$state' and  name LIKE '%$keyword%'"." LIMIT ".$start.",".$count);

                    }
              }
              if( isset($unfinished) && isset($state) && empty($keyword) && isset($priority) ){
                    foreach($lists as $k=>$v){
                        $subprjid=$lists[$k]['subproject_id'];
                        $list1[]= Db::query(" select id,project_id,name,state,priority,
                                      DATE_FORMAT(start_time_plan, '%Y-%m-%d') as start_time_plan,
									  DATE_FORMAT(dwg_end_plan, '%Y-%m-%d') as dwg_end_plan,
									  DATE_FORMAT(design_start_plan, '%Y-%m-%d') as design_start_plan,
                                      DATE_FORMAT(end_time_plan, '%Y-%m-%d') as end_time_plan,
                                      DATE_FORMAT(start_time_real, '%Y-%m-%d') as start_time_real,
                                      DATE_FORMAT(end_time_real, '%Y-%m-%d') as end_time_real,
                                      DATE_FORMAT(create_time, '%Y-%m-%d') as create_time
                                      from  ipm_inst_subproject
                                    where  id='$subprjid' and  state= '$state' and  priority= '$priority' "." LIMIT ".$start.",".$count);

                    }
              }
              if(isset($unfinished) && empty($state) && isset($keyword) && empty($priority) ){
                    foreach($lists as $k=>$v){
                        $subprjid=$lists[$k]['subproject_id'];
                        $list1[]= Db::query(" select id,project_id,name,state,priority,
                                      DATE_FORMAT(start_time_plan, '%Y-%m-%d') as start_time_plan,
									  DATE_FORMAT(dwg_end_plan, '%Y-%m-%d') as dwg_end_plan,
									  DATE_FORMAT(design_start_plan, '%Y-%m-%d') as design_start_plan,
                                      DATE_FORMAT(end_time_plan, '%Y-%m-%d') as end_time_plan,
                                      DATE_FORMAT(start_time_real, '%Y-%m-%d') as start_time_real,
                                      DATE_FORMAT(end_time_real, '%Y-%m-%d') as end_time_real,
                                      DATE_FORMAT(create_time, '%Y-%m-%d') as create_time
                                      from  ipm_inst_subproject
                                    where  id='$subprjid' and  name LIKE '%$keyword%'"." LIMIT ".$start.",".$count);

                    }
                }
                if(isset($unfinished) && empty($state) && isset($keyword) && isset($priority) ){
                    foreach($lists as $k=>$v){
                        $subprjid=$lists[$k]['subproject_id'];
                        $list1[]= Db::query(" select id,project_id,name,state,priority,
                                      DATE_FORMAT(start_time_plan, '%Y-%m-%d') as start_time_plan,
									  DATE_FORMAT(dwg_end_plan, '%Y-%m-%d') as dwg_end_plan,
									  DATE_FORMAT(design_start_plan, '%Y-%m-%d') as design_start_plan,
                                      DATE_FORMAT(end_time_plan, '%Y-%m-%d') as end_time_plan,
                                      DATE_FORMAT(start_time_real, '%Y-%m-%d') as start_time_real,
                                      DATE_FORMAT(end_time_real, '%Y-%m-%d') as end_time_real,
                                      DATE_FORMAT(create_time, '%Y-%m-%d') as create_time
                                      from  ipm_inst_subproject
                                    where  id='$subprjid' and  priority= '$priority' and  name LIKE '%$keyword%'"." LIMIT ".$start.",".$count);

                    }
                }
                if(isset($unfinished) && empty($state) && empty($keyword) && isset($priority) ){
                    foreach($lists as $k=>$v){
                        $subprjid=$lists[$k]['subproject_id'];
                        $list1[]= Db::query(" select id,project_id,name,state,priority,
                                      DATE_FORMAT(start_time_plan, '%Y-%m-%d') as start_time_plan,
									  DATE_FORMAT(dwg_end_plan, '%Y-%m-%d') as dwg_end_plan,
									  DATE_FORMAT(design_start_plan, '%Y-%m-%d') as design_start_plan,
                                      DATE_FORMAT(end_time_plan, '%Y-%m-%d') as end_time_plan,
                                      DATE_FORMAT(start_time_real, '%Y-%m-%d') as start_time_real,
                                      DATE_FORMAT(end_time_real, '%Y-%m-%d') as end_time_real,
                                      DATE_FORMAT(create_time, '%Y-%m-%d') as create_time
                                      from  ipm_inst_subproject
                                    where  id='$subprjid' and  priority= '$priority'"." LIMIT ".$start.",".$count);

                    }
                }
                if(isset($unfinished) && isset($state) && isset($keyword) && isset($priority) ){
                    foreach($lists as $k=>$v){
                        $subprjid=$lists[$k]['subproject_id'];
                        $list1[]= Db::query(" select id,project_id,name,state,priority,
                                      DATE_FORMAT(start_time_plan, '%Y-%m-%d') as start_time_plan,
									  DATE_FORMAT(dwg_end_plan, '%Y-%m-%d') as dwg_end_plan,
									  DATE_FORMAT(design_start_plan, '%Y-%m-%d') as design_start_plan,
                                      DATE_FORMAT(end_time_plan, '%Y-%m-%d') as end_time_plan,
                                      DATE_FORMAT(start_time_real, '%Y-%m-%d') as start_time_real,
                                      DATE_FORMAT(end_time_real, '%Y-%m-%d') as end_time_real,
                                      DATE_FORMAT(create_time, '%Y-%m-%d') as create_time
                                      from  ipm_inst_subproject
                                    where  id='$subprjid' and  state= '$state' and  priority= '$priority' and  name LIKE '%$keyword%' "." LIMIT ".$start.",".$count);

                    }
                }

                    foreach ($list1 as $kk => $vv) {
                        if(empty($vv)){
                            $b[]=0;
                        }else{
                            foreach ($vv as $key => $val) {
                                $b[] = $val;
                            }
                        }

                    }


                foreach($b as $k=>$v) {

                    if (!$v) {
                        unset($b[$k]);//删除
                    }
                }

                return json($b);


            }else{

                if (isset($subprj_id)) {
                    $sql = $sql . " and  id= $subprj_id";
                }
                if (isset($state)) {
                    $sql = $sql . " and  state= $state ";
                }
                if (isset($priority)) {
                    $sql = $sql . " and  priority= $priority ";
                }
                if (isset($keyword)) {
                    $sql = $sql . " and  name LIKE '%$keyword%'";
                }


                if(isset($start) && isset($count))
                {
                    $sql = $sql." order by state asc  LIMIT ".$start.",".$count;
                }

                $list = Db::query($sql);
                if ($list) {
                    return json($list);
                } else {
                    return json();
                }
            }
        }

    }

    public function ListUserSubproject(){
        if(request()->isGet()) {
            $openid = input('openid');
            $start = input('start');
            $count = input('count');
            $project_id = input('project_id');
            $keyword = input('keyword');
            $state = input('state');
            if (!isset($openid) || empty($openid)) {
                return json('openid  不能为空');
            }
            if (!isset($start)) {
                return json('start  不能为空');
            }
            if (!isset($count) || empty($count)) {
                return json('count  不能为空');
            }
            $ProjectTable = new Projects();
            $sql = $ProjectTable->subproject_project_list2($openid,$start,$count);
            if (isset($project_id)) {
                $sql = $sql . " and  b.id= $project_id";
            }
            if (isset($state)) {
                $sql = $sql . " and  a.state= $state ";
            }
            if (isset($keyword)) {
                $sql = $sql . " and  (a.name LIKE '%$keyword%' OR b.name LIKE '%$keyword%')";
            }
            if(isset($start) && isset($count))
            {
                $sql = $sql."  order by state asc LIMIT ".$start.",".$count;
            }
            $list = Db::query($sql);
            if ($list) {
                return json($list);
            } else {
                return json();
            }

            return json($list);
        }
    }
    public function CountSubproject($project_id)
        {
            $subprojectTable = new Subprojects();
            $list = $subprojectTable->CountList($project_id);
            $list1 = $subprojectTable->CountList1($project_id);
            $list2 = $subprojectTable->CountList2($project_id);
            $list3 = $subprojectTable->CountList3($project_id);
            $list4 = $subprojectTable->CountList4($project_id);
            $list5 = $subprojectTable->CountList5($project_id);
            $list6 = $subprojectTable->CountList6($project_id);
            $count = $list[0]['count'];
            $count1 = $list1[0]['count'];
            $count2 = $list2[0]['count'];
            $count3 = $list3[0]['count'];
            $count4 = $list4[0]['count'];
            $count5 = $list5[0]['count'];
            $count6 = $list6[0]['count'];
            $result['subptoject_count'] = $count;
            $result['subptoject_state_count1'] = $count1;
            $result['subptoject_state_count2'] = $count2;
            $result['subptoject_state_count3'] = $count3;
            $result['subptoject_state_count4'] = $count4;
            $result['subptoject_state_count5'] = $count5;
            $result['subptoject_state_count6'] = $count6;
            return json();
    }
//更新模板厂名称
	 public function updateSubprjName(){
		 $arr= $this->request->param();
		 $prj_id=$arr['alt_prj_id'];
		 $prj_name=$arr['alt_prj_name'];
		 Db::query("update ipm_inst_project set name ='$prj_name' where id='$prj_id'");
		 $result['success'] = true;
		  return json ($result);
	 }
}