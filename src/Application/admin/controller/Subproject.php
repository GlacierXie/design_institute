<?php
namespace app\admin\controller;
use think\Controller;
use app\home\model\Subprojects;
use app\home\model\Defaulttaskgroup;
use app\home\model\Defaulttasks;
use app\home\model\Taskgroups;
use think\Db;
use think\Cache;
require_once ROOT_PATH.'application\\common\\OssCommon.php';
use app\common\OssCommon;

class Subproject extends Controller
{

    //添加项目
    public  function add_subproject(){
        $arr= $this->request->param();
        if (!isset($arr['company_id']) || empty($arr['company_id'])) {
            return json('company_id empty');
        }
        if (!isset($arr['prj_id']) || empty($arr['prj_id'])) {
            return json('project_id empty');
        }
        if (!isset($arr['subprjname']) || empty($arr['subprjname'])) {

            return json('subprjname empty');
        }
        if (!isset($arr['creator_id']) || empty($arr['creator_id'])) {
            return json('creator_id empty');
        }
        if(!isset($arr['start_time_plan']) || empty($arr['start_time_plan'])){
            return json('start_time_plan  empty');
        }
        if (!isset($arr['priority']) || empty($arr['priority'])) {
            return json('priority empty');
        }
        if (!isset($arr['location']) || empty($arr['location'])) {
            return json('location empty');
        }
        if (!isset($arr['build_number']) || empty($arr['build_number'])) {
            return json('build_number empty');
        }
        $prj_status =$arr['prj_status'];
        $project_id =$arr['prj_id'];
        $name = $arr['subprjname'];
        $company_id = $arr['company_id'];
        $creator_id = $arr['creator_id'];
        $priority = $arr['priority'];
        $start_time_plan = $arr['start_time_plan'];
        $dwg_end_plan = $arr['dwg_end_plan'];
        $design_start_plan = $arr['design_start_plan'];
        $end_time_plan = $arr['end_time_plan'];
        $location = $arr['location'];
        $build_number = $arr['build_number'];

        $arr1 = array(
            "project_id" => $project_id,
            "name" =>$name,
            "priority" =>$priority,
            "state" => $prj_status,
            "start_time_plan" => $start_time_plan,
            "dwg_end_plan"=>$dwg_end_plan,
            "design_start_plan"=>$design_start_plan,
            "end_time_plan" => $end_time_plan,
            "location" => $location,
            "build_number" => $build_number,
            "start_time_real" => date("Y-m-d H:i:s"),
            "create_time" => date("Y-m-d H:i:s"),
            "update_time" => date("Y-m-d H:i:s")
        );
          $id1 = Db::table('ipm_inst_subproject')->insertGetId($arr1);
          $user_roles = DB::table('ipm_inst_user_role')->where('openid',$creator_id)->select();
        foreach($user_roles as $k=>$v){
            $data_role1['role_id']=$v['role_id'];
            $data_role1['subproject_id'] = $id1;
            $data_role1['openid'] = $creator_id;
            $data_role1['create_time'] = date("Y-m-d H:i:s");
            $data_role1['update_time'] = date("Y-m-d H:i:s");
            $id3 = Db::table('ipm_inst_subproject_user')->insert($data_role1);
        }
            /*$userlist=  Db::query(" select openid,role_id,master from ipm_inst_user_role group by openid  having count(openid)<=1 and role_id=7");
            if(isset($userlist) && !empty($userlist)){
                foreach($userlist as $k=>$v){
                    $data_role['role_id']=$v['role_id'];
                    $data_role['subproject_id'] = $id1;
                    $data_role['openid'] = $v['openid'];
                    $data_role['create_time'] = date("Y-m-d H:i:s");
                    $data_role['update_time'] = date("Y-m-d H:i:s");
                    $id3 = Db::table('ipm_inst_subproject_user')->insert($data_role);
                }
            }*/
            //创建一个可读写的文件
            if($id1){
                $arr5 = array(
                    "openid" =>$creator_id,
                    "subproject_id" => $id1,
                    "changed_state" => 1,
                    "prev_state" => 1,
                    "create_time" => date("Y-m-d H:i:s"),
                    "update_time" => date("Y-m-d H:i:s")
                );
                $state_change= Db::table('ipm_inst_subproject_state_change')->insertGetId($arr5);
                if($state_change){
                    $default_taskgroupTable= new Defaulttaskgroup();
                    $list=$default_taskgroupTable->default_taskgroup_List();
					$time_temp = date("Y-m-d H:i:s");
                    foreach($list as $k=>$v){
                        $data['name']=$list[$k]['name'];
                        $data['role_id']=$list[$k]['role_id'];
                        $data['default_taskgroup_id']=$list[$k]['id'];
                        $data['creator_id']=$creator_id;
                        $data['subproject_id']=$id1;
                        $data['update_time']=date("Y-m-d H:i:s");
                        $data['create_time']=date("Y-m-d H:i:s");
                        $taskgroup_id= Db::table('ipm_inst_subproject_taskgroup')->insertGetId($data);
                        $subprj_id= Db::table('ipm_inst_subproject_taskgroup')->where('id',$taskgroup_id)->value('subproject_id');
                        $project_id1= Db::table('ipm_inst_subproject')->where('id',$subprj_id)->value('project_id');
                        $defualt_task = new Defaulttasks();
                        $list2 = $defualt_task->default_taskList($list[$k]['id']);
                        foreach($list2 as $kk=>$vv){
                            $data1['name']=$list2[$kk]['name'];
                            $data1['taskgroup_id']=$taskgroup_id;
                            $data1['subproject_id']=$subprj_id;
                            $data1['project_id']=$project_id1;
                            $data1['creator_id']=$creator_id;
                            $data1['start_time_plan'] = $time_temp;
                            $data1['end_time_plan'] = date("Y-m-d H:i:s",strtotime("$time_temp + 1 day"));
                            $data1['create_time']=date("Y-m-d H:i:s");
                            $data1['update_time']=date("Y-m-d H:i:s");
                            $result1= Db::table('ipm_inst_subproject_task')->insertGetId($data1);
							$time_temp = date("Y-m-d H:i:s",strtotime("$time_temp + 1 day"));
                       }
                   }
                    if($result1){
                        $res['success'] = true;
                        $res['message'] = "success";
                    }
               }
            }
        return json ($res);
    }
    //查找项目状态
    public function find_state(){
        $arr= $this->request->param();
        if (!isset($arr['subproject_id']) || empty($arr['subproject_id'])) {
            return json('111');
        }
        $state = DB::table('ipm_inst_subproject')->where('id',$arr['subproject_id'])->value('state');
        if($state){
            $subprj_id=$arr['subproject_id'];
            $subprojectTable= new Subprojects();
            $arr=$subprojectTable->find_state($subprj_id,$state);
            if($arr){
                return json($arr);
            }
       }else{
            $res['success'] = false;
            $res['message'] = "查询错误";
            return json($res);
        }

    }
    //删除和项目有关的表
    public function del_subproject()
    {
        $arr = $this->request->param();
        if (!isset($arr['subproject_id']) || empty($arr['subproject_id'])) {
            return json('subproject_id empty');
        }
        if (!isset($arr['openid']) || empty($arr['openid'])) {
            return json('openid empty');
        }
        $ossClient = OssCommon::getOssClient(false);
        if($ossClient == null)
             return json();
        $subproject_id = $arr['subproject_id'];
        $openid = $arr['openid'];
        //  $state = Db::table('ipm_inst_subproject')->where('id',$subproject_id)->value('state');
        // Db::startTrans();
        // try {
            $project_id = Db::table('ipm_inst_subproject')->where('id', $subproject_id)->value('project_id');
            $company_id = Db::table('ipm_inst_project')->where('id', $project_id)->value('company_id');
            $dirName = $company_id.'/'.$project_id.'/'.$subproject_id.'/'; //路径
             $is = OssCommon::deleteDir($ossClient,$dirName);
             if ($is){
                 $taskgroupTable = Db::table('ipm_inst_subproject_taskgroup')->where('subproject_id', $subproject_id)->select();
                 $state_changeTable = Db::table('ipm_inst_subproject_state_change')->where('subproject_id', $subproject_id)->select();
                 $userTable = Db::table('ipm_inst_subproject_user')->where('subproject_id', $subproject_id)->select();
                 $taskTable = Db::table('ipm_inst_subproject_task')->where('subproject_id', $subproject_id)->select();
                 $fileTable = Db::table('ipm_inst_file')->where('subproject_id', $subproject_id)->select();
                 $problemTable = Db::table('ipm_inst_problem')->where('subproject_id', $subproject_id)->select();
                 $tempfile = Db::table('ipm_inst_tempfile')->where('subproject_id', $subproject_id)->select();
                 $subprojectTable = Db::table('ipm_inst_subproject')->where('id', $subproject_id)->select();
                 if ($taskgroupTable) {
                     Db::table('ipm_inst_subproject_taskgroup')->where('subproject_id', $subproject_id)->delete();
                 }
                 if ($state_changeTable) {
                     Db::table('ipm_inst_subproject_state_change')->where('subproject_id', $subproject_id)->delete();
                 }
                 if ($userTable) {
                     Db::table('ipm_inst_subproject_user')->where('subproject_id', $subproject_id)->delete();
                 }
                 if ($taskTable) {
                     foreach ($taskTable as $kk => $vv) {
                         $task_id = $taskTable[$kk]['id'];
                         Db::table('ipm_inst_subproject_taskparter')->where('task_id', $task_id)->delete();
                     }
                     foreach ($taskTable as $kk => $vv) {
                         $task_id = $taskTable[$kk]['id'];
                         Db::table('ipm_inst_subproject_task_change')->where('task_id', $task_id)->delete();
                     }
                     Db::table('ipm_inst_subproject_task')->where('subproject_id', $subproject_id)->delete();
                 }
                 if ($fileTable) {
                     foreach ($fileTable as $kk => $vv) {
                         $file_id = $taskTable[$kk]['id'];
                         Db::table('ipm_inst_files_state_change')->where('file_id', $file_id)->delete();
                     }
                     Db::table('ipm_inst_file')->where('subproject_id', $subproject_id)->delete();
                 }
                 if ($problemTable) {
                     foreach ($problemTable as $k => $v) {
                         $problem_id = $taskTable[$k]['id'];
                         Db::table('ipm_inst_problem_files')->where('problem_id', $problem_id)->delete();
                     }
                     Db::table('ipm_inst_problem')->where('subproject_id', $subproject_id)->delete();
                 }
                 if ($tempfile) {
                     Db::table('ipm_inst_tempfile')->where('subproject_id', $subproject_id)->delete();
                 }
                 if ($subprojectTable) {
                     Db::table('ipm_inst_subproject')->where('id', $subproject_id)->delete();
                 }
                 $res['success'] = true;
                 $res['message'] = "delete success";
                 return json_encode($res);
             }else {
                 $res['success'] = false;
                 $res['message'] = "file error";
                 return json_encode($res);
             }
           //事物提交
//            Db::commit();
//        } catch (\Exception $e) {
//            //  回滚事务
//            Db::rollback();
//        }
     //  return json($res);

    }
    public function aa(){
        //            Db::startTrans();
//            try {
//                Db::table('ipm_inst_subproject_taskgroup')->where('subproject_id', $subproject_id)->delete();
//                Db::table('ipm_inst_subproject_state_change')->where('subproject_id', $subproject_id)->delete();
//                Db::table('ipm_inst_subproject_user')->where('subproject_id', $subproject_id)->delete();
//                $taskTable = Db::table('ipm_inst_subproject_task')->where('subproject_id', $subproject_id)->select();
//                foreach ($taskTable as $kk => $vv) {
//                    $task_id = $taskTable[$kk]['id'];
//                    Db::table('ipm_inst_subproject_taskparter')->where('task_id', $task_id)->delete();
//                }
//                foreach ($taskTable as $kk => $vv) {
//                    $task_id = $taskTable[$kk]['id'];
//                    Db::table('ipm_inst_subproject_task_change')->where('task_id', $task_id)->delete();
//                }
//                $fileTable = Db::table('ipm_inst_file')->where('subproject_id', $subproject_id)->select();
//                foreach ($fileTable as $kk => $vv) {
//                    $file_id = $taskTable[$kk]['id'];
//                    Db::table('ipm_inst_files_state_change')->where('file_id', $file_id)->delete();
//                }
//                Db::table('ipm_inst_file')->where('subproject_id', $subproject_id)->delete();
//                $problemTable = Db::table('ipm_inst_problem')->where('subproject_id', $subproject_id)->select();
//                foreach ($problemTable as $k => $v) {
//                    $problem_id = $taskTable[$k]['id'];
//                    Db::table('ipm_inst_problem_files')->where('problem_id', $problem_id)->delete();
//                }
//                Db::table('ipm_inst_problem')->where('subproject_id', $subproject_id)->delete();
//                Db::table('ipm_inst_subproject_task')->where('subproject_id', $subproject_id)->delete();
//                Db::table('ipm_inst_tempfile')->where('subproject_id', $subproject_id)->delete();
//                $result = Db::table('ipm_inst_subproject')->where('id', $subproject_id)->delete();
//                if ($result) {
//                    $is = $this->delDirAndFile($dirName);
//                    $lists['openid'] = $openid;
//                    $lists['subproject_id'] = $subproject_id;
//                    $lists['create_time'] = date("Y-m-d H:i:s");
//                    $lists['update_time'] = date("Y-m-d H:i:s");
//                    $delete_subporjectDb = Db::table('ipm_inst_subproject')->insertGetId($lists);
//                    if ($delete_subporjectDb) {
//
//                        $res['success'] = true;
//                        $res['message'] = "delete success";
//                    } else {
//
//                        $res['success'] = false;
//                        $res['message'] = "delete error";
//                    }
//                    return json($res);
//                }
//                //事物提交
//                Db::commit();
//            } catch (\Exception $e) {
//                //  回滚事务
//                Db::rollback();
//            }
        //echo json_decode($json);
        //  return json($res);
    }
    public function change_subprj_status(){
        $arr= $this->request->param();
        if (!isset($arr['subprj_id']) || empty($arr['subprj_id'])) {
            return json('subprj_id empty');
        }
        if (!isset($arr['status']) || empty($arr['status'])) {
            return json('status empty');
        }
        $data['id']=$arr['subprj_id'];
        $data['state']=$arr['status'];
        $list = DB::table('ipm_inst_subproject')->where('id',$arr['subprj_id'])->update($data);
        if($list){
            $res['success'] = true;
            $res['message'] = "success";
        }
        return json ($res);
    }
	//更新子项目名称
	 public function updateSubprjName(){
		 $arr= $this->request->param();
		 $subprj_id=$arr['alt_subprj_id'];
		 $subprj_name=$arr['alt_subprj_name'];
		 Db::query("update ipm_inst_subproject set name ='$subprj_name' where id='$subprj_id'");
		 $result['success'] = true;
		  return json ($result);
	 }
	 public function test(){
		 $time_temp = date("Y-m-d H:i:s");
		 $time_temp_plus1 = date("Y-m-d H:i:s",strtotime("$time_temp + 1 day"));
		 echo $time_temp_plus1;
	 }
}