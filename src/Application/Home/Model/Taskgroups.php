<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Taskgroups extends Model{
    protected $table = 'ipm_inst_subproject_taskgroup';
    public function add_taskgroup($data){
        $list = DB::table($this->table)->insertGetId($data);
        return $list;
    }
    public  function taskparter_list($subprj_id){
        $list = DB::table($this->table)
            ->alias('a')
            ->join('ipm_user b','a.creator_id=b.openid')
            ->field(['a.id','a.name','a.creator_id','b.nickname','a.role_id','a.create_time','a.subproject_id'])
            ->where('subproject_id',$subprj_id)->select();
        return $list;
    }
    public  function taskGroup_list($subprj_id,$start,$count){
        $list = DB::table($this->table)
            ->alias('a')
            ->join('ipm_user b','a.creator_id=b.openid')
            ->field(['a.id','a.name','a.creator_id','b.nickname creator_nickname','b.headimgurl creator_headimgurl','a.role_id','a.create_time','a.update_time'])
            ->where('subproject_id',$subprj_id)->select();
        return $list;

    }
    public  function taskgroup_list1($subprj_id){
        $list= "SELECT a.id,a.name,a.creator_id,b.nickname,b.headimgurl,a.role_id,a.create_time,a.update_time
			             from ipm_inst_subproject_taskgroup  as a
			             left join ipm_user b on a.creator_id = b.openid
			             where subproject_id  = $subprj_id " ;
        return $list;
        //  limit ".$currentpage .','.$itemsPerPage
    }

    public function gettaskList($taskgoup_id){
        $list="SELECT DISTINCT a.id,a.name,a.creator_id,c.nickname as creator_nickname,
			             a.changer_id,a.urgent,a.state, a.remarks,a.start_time_plan,a.end_time_plan,a.start_time_real,
			             a.end_time_real,c.headimgurl as creator_headimgurl,a.update_time,a.create_time
			             from ipm_inst_subproject_task a
			             left join ipm_user c on a.creator_id = c.openid
			             where a.taskgroup_id  = $taskgoup_id  ";
        return $list;
    }

    public function del_taskgroup($taskgroup_id){
        $list = DB::table($this->table)->where('id',$taskgroup_id)->delete();
        return $list;
    }
    public function taskgroup_id($subprj_id){
        $list = DB::table($this->table)->where('subproject_id',$subprj_id)->select();
        return $list;
    }
    public function select_id($subproject_id){
        $list= Db::query("    select b.id as taskgroup_id,b.name,a.creator_id,a.changer_id,a.urgent,c.openid,count(a.taskgroup_id)as num from ipm_inst_subproject_task as a
                                     left join ipm_inst_subproject_taskgroup as b on
                                     a.taskgroup_id=b.id
                                     left join  ipm_inst_subproject_taskparter as c on
                                     c.task_id=a.id where b.subproject_id='$subproject_id' group by a.taskgroup_id
                         ");
        return $list;
    }
    public function UserTaskgrouplist($openid,$project_id,$subproject_id){
        $list= Db::query("    select DISTINCT a.taskgroup_id,a.taskgroup_name from ipm_inst_subproject_task as a
                           left join  ipm_inst_subproject_taskparter as b on
                           a.id=b.task_id
                           where a.project_id='$project_id' and a.subproject_id=$subproject_id  and (a.changer_id='$openid' or b.openid='$openid')
                         ");
        return $list;
    }

}

