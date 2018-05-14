<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Subprojects extends Model
{
    protected $table = 'ipm_inst_subproject';
    public function file_subprj_project_list($subproject_id,$project_id,$company_id){
        $data= Db::query(" select b.type,b.id as file_id,a.id as subproject_id,c.company_id,c.id as project_id,b.name from ipm_inst_subproject as a
                                     left join ipm_inst_file as b on
                                      a.id=b.subproject_id
                                     left join ipm_inst_project as c on
                                     c.id=a.project_id
                                     where a.id=$subproject_id and  a.project_id=$project_id and c.company_id=$company_id
                         ");
        return $data;
    }
    public function CountList($project_id){
        $data= Db::query("select count(id) as count from  ipm_inst_subproject
                           where project_id=$project_id
                         ");
        return $data;
    }
    public function CountList1($project_id){
        $data= Db::query("select count(id) as count from  ipm_inst_subproject
                           where project_id=$project_id and state=1
                         ");
        return $data;
    }
    public function CountList2($project_id){
        $data= Db::query("select count(id) as count from  ipm_inst_subproject
                           where project_id=$project_id and state=2
                         ");
        return $data;
    }
    public function CountList3($project_id){
        $data= Db::query("select count(id) as count from  ipm_inst_subproject
                           where project_id=$project_id and state=3
                         ");
        return $data;
    }
    public function CountList4($project_id){
        $data= Db::query("select count(id) as count from  ipm_inst_subproject
                           where project_id=$project_id and state=4
                         ");
        return $data;
    }
    public function CountList5($project_id){
        $data= Db::query("select count(id) as count from  ipm_inst_subproject
                           where project_id=$project_id and state=5
                         ");
        return $data;
    }
    public function CountList6($project_id){
        $data= Db::query("select count(id) as count from  ipm_inst_subproject
                           where project_id=$project_id and state=6
                         ");
        return $data;
    }
    public function SubprojectCount($openid){
        $data= Db::query("   select  count(DISTINCT b.id) as count
                                    from ipm_inst_project as a
                                   left join ipm_inst_subproject  as b on
                                     a.id=b.project_id
                                     left join ipm_inst_subproject_taskgroup as d on
                                     b.id=d.subproject_id
                                      left join ipm_inst_subproject_user as c on
                                      b.id=c.subproject_id
                                     where a.creator_id='$openid'
                                       or d.creator_id='$openid'
                                       or c.openid='$openid'
                         ");
        return $data;
    }
    public function TaskCount($openid){
//        $data= Db::query("select count(a.id) as TaskCount from ipm_inst_subproject_task  as a
//                   left join ipm_inst_subproject_taskparter as b on
//                   a.id=b.task_id
//                   where  a.changer_id='$openid' or b.openid='$openid'
//                         ");
//        return $data;
        $data= Db::query("select count(DISTINCT a.id) as TaskCount from ipm_inst_subproject_task  as a
                   left join ipm_inst_subproject_taskparter as b on
                   a.id=b.task_id
                   where  a.changer_id='$openid' or b.openid='$openid'
                         ");
        return $data;
    }
    public function TaskState1($openid){
//        $data= Db::query("select count(a.id) as incomplete from ipm_inst_subproject_task  as a
//                   left join ipm_inst_subproject_taskparter as b on
//                   a.id=b.task_id
//                   where  (a.changer_id='$openid' or b.openid='$openid') and  a.state!=3
//                         ");
//        return $data;
                $data= Db::query("select count(DISTINCT a.id) as incomplete from ipm_inst_subproject_task  as a
                   left join ipm_inst_subproject_taskparter as b on
                   a.id=b.task_id
                   where  (a.changer_id='$openid' or b.openid='$openid') and  a.state=1
                         ");
        return $data;
    }
    public function CreatorProblemCount($openid){
        $data= Db::query(" select count(id) CreatorProblemCount from  ipm_inst_problem
                           where creator_id='$openid'
                         ");
        return $data;
    }
    public function ChangerProblemCount($openid){
        $data= Db::query(" select count(id) ChangerProblemCount from  ipm_inst_problem
                           where changer_id='$openid'
                         ");
        return $data;
    }
    public function ProblemCountState($openid){
        $data= Db::query(" select count(id) unsolve from  ipm_inst_problem
                           where changer_id='$openid'  and state<3
                         ");
        return $data;
    }
    public function subpr_project_config_list($project_id,$company_id,$subproject_id){
        $data= Db::query("     select
                                     a.id as subproject_id,c.company_id,c.id as project_id,d.id as config_id,d.name as config_name
                                      from ipm_inst_subproject as a
                                    left join ipm_inst_project as c on
                                     c.id=a.project_id
                                    left join ipm_inst_configuration as d on
                                     d.id=c.config_id
                                      where  a.project_id=$project_id and c.company_id=$company_id and a.id=$subproject_id
                         ");
        return $data;
    }
    public function get_state($project_id){
        $list = DB::table($this->table)->where('project_id',$project_id)->select();
        return $list;
    }
    public function getfind_state($subprj_id){
        $list = DB::table($this->table)->where('id',$subprj_id)->select();
        return $list;
    }
    public  function subproject_List($prj_id){
        $list = DB::table($this->table)
            ->field(['id','name'])
            ->where("project_id='$prj_id'")
            ->select();
        return $list;
    }
    public function find_state($subprj_id,$state){
//        $data= Db::query("select
//                                 a.changed_state,a.update_time,a.create_time,a.prev_state,b.nickname,b.headimgurl
//                                  from ipm_inst_subproject_state_change as a
//                                  left JOIN ipm_user as b on
//                                  a.openid=b.openid
//                                  where  a.subproject_id='$subprj_id' and a.changed_state<='$state'
//                                  order by a.create_time
//                         ");
//        return $data;
                $data= Db::query("select c.nickname,c.headimgurl,a.changed_state as state,a.create_time as time from ipm_inst_subproject_state_change a
                                       inner join
                                       (
                                       select changed_state,max(create_time) create_time
                                       from ipm_inst_subproject_state_change
                                       where   subproject_id=$subprj_id
                                              and changed_state<=$state
                                             group by changed_state
                                       )b on a.changed_state=b.changed_state
                                     and a.create_time=b.create_time
                                     inner join ipm_user c on
                                     a.openid=c.openid

                                    order by a.changed_state
                         ");
        return $data;
    }

	
}