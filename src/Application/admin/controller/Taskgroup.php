<?php
namespace app\admin\controller;
use app\home\model\Taskparters;
use app\home\model\Tasks;
use think\Controller;
use app\home\model\Taskgroups;
use app\home\model\Projects;
use app\home\model\Subprojectusers;
use think\Db;
use think\Cache;
use think\Request;
require_once ROOT_PATH.'application\\common\\OssCommon.php';
use app\common\OssCommon;

class Taskgroup extends Controller
{
    public  function add_taskgroup(){
        $arr= $this->request->param();
        if (!isset($arr['subprj_id']) || empty($arr['subprj_id'])) {
            return json('111');
        }
        if (!isset($arr['openid']) || empty($arr['openid']) ) {
            return json('222');
        }
        if (!isset($arr['task_group_name']) || empty($arr['task_group_name']) ) {
            return json('333');
        }
        if (!isset($arr['role_id']) || empty($arr['role_id']) ) {
            return json('444');
        }
        //封装成一个数组
            $data['subproject_id']=$arr['subprj_id'];
            $data['creator_id']=$arr['openid'];
            $data['name']=$arr['task_group_name'];
            $data['role_id']=$arr['role_id'];
            $data['create_time']=date("Y-m-d H:i:s");
            $data['update_time']=date("Y-m-d H:i:s");
            //new model 类
           // $taskgroupTbale= new Taskgroups();
            //添加一条数据
            $res=Db::table('ipm_inst_subproject_taskgroup')->insertGetId($data);
            if($res){
                $res1['success'] = true;
                $res1['message'] = "add success";
            }else{
                $res1['success'] = false;
                $res1['message'] = 'error';
            }
        return json ($res1);

    }
    public function taskgroup_task_list(){
        if(request()->isGet())
        {
            $subproject_id = input('subprj_id');
           // $project_id = 20;
            $project_id = input('project_id');
            $taskgroup_id  = input('taskgroup_id');
            $urgent  = input('urgent');
            $state  = input('state');
            $creator_id  = input('creator_id');
            $changer_id  = input('changer_id');
            $parter_id  = input('parter_id');
            $role_id  = input('role_id');
            $open_id  = input('open_id');
            $task_id = input('task_id');
            $company_id=1;
            if(!isset($subproject_id))
                return json();
            $ossClient = OssCommon::getOssClient(true);
            if($ossClient == null)
              return json();
            $sql = "SELECT DISTINCT a.id,a.default_taskgroup_id,a.name,a.creator_id,c.nickname as creator_nickname,
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
            foreach($list as $k=>$v)
            {
                $sql = "SELECT DISTINCT a.id,a.name,a.creator_id,c.nickname as creator_nickname,
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

//                if(empty($taskList))
//                {
//                    unset($list[$k]);
//                    continue;
//                }
                $TaskpartersTable=new Taskparters();
                foreach($taskList as $kk=>$vv)
                {
                    $taskList[$kk]['changer_nickname'] = $this->getUserName($taskList[$kk]['changer_id']);
                    $taskList[$kk]['real_name'] = $this->real_name($taskList[$kk]['changer_id']);
                    $taskList[$kk]['changer_headimgurl'] = $this->getUserheadimgurl($taskList[$kk]['changer_id']);
                    $task_idTmp= $taskList[$kk]['id'];
                    $taskList[$kk]['parter_list']=$TaskpartersTable->taskparter_id($task_idTmp);
                }
                $list[$k]['subtask_list'] = $taskList;

                $aa=Db::query("
                    select a.id as merge_id,a.name as merge_name from ipm_inst_mergefile a where subproject_id='$subproject_id'
                     ");
                if($aa)
                {
                    $merge_name = $aa[0]['merge_name'];
                    $merge_id = $aa[0]['merge_id'];
                    $strTmp = md5($merge_id . $merge_name . "merge");
                    $fileNameArryTmp = explode(".", $merge_name);
                    if (!empty($fileNameArryTmp))
                    {
                        $fileNameTitleTmp = end($fileNameArryTmp);
                        $list[$k]['merge_name']=$merge_name;
                        $list[$k]['merge_url'] = OssCommon::downloadUrl($ossClient,$company_id.'/'.$project_id.'/'.$subproject_id.'/'.$strTmp.'.'.$fileNameTitleTmp);
                    }
                }
                else{
                    $list[$k]['merge_url']='';
                    $list[$k]['merge_name']='';
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


    public  function  getUserName($openid)
    {
        if(!isset($openid))
            return false;
        $read= Db::query("SELECT nickname FROM `ipm_user` where `openid` ='$openid'");
        if(!isset($read) || empty($read))
            return "";
        return $read[0]['nickname'];
    }
    public  function  real_name($openid)
    {
        if(!isset($openid))
            return false;
        $read= Db::query("SELECT real_name FROM `ipm_user` where `openid` ='$openid'");
        if(!isset($read) || empty($read))
            return "";
        return $read[0]['real_name'];
    }

    public  function  getUserheadimgurl($openid)
    {
        if(!isset($openid))
            return false;
        $read= Db::query("SELECT headimgurl FROM `ipm_user` where `openid` ='$openid'");
        if(!isset($read) || empty($read))
            return "";
        return $read[0]['headimgurl'];
    }

    public function del_taskgroup(){
        $arr= $this->request->param();
        $taskgroup_id=$arr['taskgroup_id'];
        $taskgroupTable=new Taskgroups();
        $taskTable=new Tasks();
        $taskgrouplist=$taskTable->task_id($taskgroup_id);
        $a=false;
        foreach($taskgrouplist as $k=>$v){
            if($taskgrouplist[$k]['state']==2 or $taskgrouplist[$k]['state']==3){
                $a = true;
                     break;
            }
        }
        if($a)
        {
            $res1['success'] = false;
            $res1['message'] = 'state==2 or state==3';
            return json ($res1);
        }else{
            $res=$taskgroupTable->del_taskgroup($taskgroup_id);
            if($res){
                $result=$taskTable->del_task_taskgroup_id($taskgroup_id);
                    $res1['success'] = true;
                    $res1['message'] = "delete success";
                    return json ($res1);
            }
        }
    }
    public function getTaskgroupList(){
        if(request()->isGet()) {
            $subprj_id = input('subprj_id');
            $start = input('start');
            $count = input('count');
            $keyword = input('keyword');
            if (!isset($subprj_id) || empty($subprj_id)) {
                return json('subprj_id empty');
            }
            if (!isset($start)) {
                return json('start empty');
            }
            if (!isset($count) || empty($count)) {
                return json('count empty');
            }

            $taskgroupTable = new Taskgroups();
            $sql = $taskgroupTable->taskgroup_list1($subprj_id);
            if (isset($keyword)) {
                $sql = $sql . " and  a.name LIKE '%$keyword%' limit " . $start . ',' . $count;
            }
            $list = Db::query($sql);
            foreach ($list as $k => $v) {
                $taskgroup_id = $list[$k]['id'];
                $taskTable = new Tasks();
                $task_count = $taskTable->CountTasklist1($taskgroup_id);
                $task_count_incomplete = $taskTable->CountTaskIncomplete($taskgroup_id);
                $list[$k]['task_count'] = $task_count[0]['sum_1'];
                $list[$k]['task_count_incomplete'] = $task_count_incomplete[0]['sum'];
            }

            if ($list) {

                return json($list);
            } else {
                return json();
            }
        }
    }
    public function getTaskList()
    {
        if (request()->isGet()) {
            $taskgoup_id = input('taskgroup_id');
            $start = input('start');
            $count = input('count');
            $state = input('state');
            $urgent = input('urgent');
            $keyword = input('keyword');
            if (!isset($taskgoup_id) || empty($taskgoup_id)) {
                return json('taskgroup_id  不能为空');
            }
            if (!isset($start)) {
                return json('start  不能为空');
            }
            if (!isset($count) || empty($count)) {
                return json('count   不能为空');
            }
            $taskgroupTable = new Taskgroups();
            $sql = $taskgroupTable->gettaskList($taskgoup_id);
            if (isset($state)) {
                $sql = $sql . " and  state= $state ";
            }
            if (isset($urgent)) {
                $sql = $sql . " and  urgent= $urgent ";
            }
            if(isset($keyword))
            {
                $sql = $sql." and  name LIKE '%$keyword%'";
            }
            if (isset($start) && isset($count)) {
                $sql = $sql . " LIMIT " . $start . "," . $count;
            }
            $list = Db::query($sql);
            $TaskpartersTable = new Taskparters();
            foreach ($list as $kk => $vv) {
                $list[$kk]['changer_nickname'] = $this->getUserName($list[$kk]['changer_id']);
                $list[$kk]['changer_headimgurl'] = $this->getUserheadimgurl($list[$kk]['changer_id']);
                $task_idTmp = $list[$kk]['id'];
                $list[$kk]['parter_list'] = $TaskpartersTable->taskparter_id($task_idTmp);
            }
            if ($list) {
                return json($list);
            } else {
                return json();
            }
        }
    }
    public function UserTasklist()
    {
        if (request()->isGet()) {
            $openid = input('openid');
            $start = input('start');
            $count = input('count');
            $project_id = input('project_id');
            $subproject_id = input('subproject_id');
            $taskgroup_id = input('taskgroup_id');
            $state = input('state');
            $urgent = input('urgent');
            $keyword = input('keyword');
            if (!isset($openid) || empty($openid)) {
                return json('openid  不能为空');
            }
            if (!isset($start)) {
                return json('start  不能为空');
            }
            if (!isset($count) || empty($count)) {
                return json('count   不能为空');
            }
            $TaskTable = new Tasks();
            //$sql = $TaskTable->project_subproject_name($openid);
            $sql = $TaskTable->project_subproject_name1($openid);
            if (isset($project_id)) {
                $sql = $sql . " and a.project_id=$project_id";
            }
            if (isset($subproject_id)) {
                $sql = $sql . " and a.subproject_id=$subproject_id";
            }
            if (isset($taskgroup_id)) {
                $sql = $sql . " and a.taskgroup_id=$taskgroup_id";
            }
            if (isset($state)) {
                $sql = $sql . " and a.state=$state";
            }
            if (isset($urgent)) {
                $sql = $sql . " and a.urgent=$urgent";
            }
            if (isset($keyword)) {
                $sql = $sql . " and  (f.name LIKE '%$keyword%' OR e.name LIKE '%$keyword%' OR d.name LIKE '%$keyword%'  OR a.name LIKE '%$keyword%')";
            }
            if (isset($start) && isset($count)) {
                $sql = $sql . " LIMIT " . $start . "," . $count;
            }
            $list = Db::query($sql);
            $TaskpartersTable = new Taskparters();
            foreach ($list as $kk => $vv) {
                $list[$kk]['changer_nickname'] = $this->getUserName($list[$kk]['changer_id']);
                $list[$kk]['changer_headimgurl'] = $this->getUserheadimgurl($list[$kk]['changer_id']);
                $task_idTmp = $list[$kk]['task_id'];
                $list[$kk]['parter_list'] = $TaskpartersTable->taskparter_id($task_idTmp);
            }
            if ($list) {
                return json($list);
            } else {
                return json();
            }

        }
    }
    //我参与总任务信息
    public function UserTaskgroupList(){
        if (request()->isGet()) {
            $openid = input('openid');
            $project_id = input('project_id');
            $subproject_id = input('subproject_id');
            $taskgroupTable = new Taskgroups();
            $res=$taskgroupTable->UserTaskgrouplist($openid,$project_id,$subproject_id);
            if ($res) {
                return json($res);
            } else {
                return json();
            }
        }
    }



}