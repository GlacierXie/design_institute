<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Tasks extends Model{
    protected $table = 'ipm_inst_subproject_task';
    public  function add_task($data){
        $list = DB::table($this->table)->insertGetId($data);
        return $list;

    }
    public  function update_task($subtask_id,$data){
        $list = DB::table($this->table)->where('id',$subtask_id)->update($data);
        return $list;
    }

    public function task_id($taskgroup_id){
        $list = DB::table($this->table)->where('taskgroup_id',$taskgroup_id)->select();
        return $list;
    }
    public function task_state($task_id){
        $list = DB::table($this->table)->where('id',$task_id)->value('state');
        return $list;
    }
    public  function tasklist($taskgroup_id){
        $list = DB::table($this->table)
             ->alias('a')
            ->join('ipm_user b','a.changer_id=b.openid')
            ->field(
                    ['a.id as task_id','a.taskgroup_id','a.name','a.changer_id','b.nickname','a.urgent','a.state','a.remarks','a.start_time_plan','a.end_time_plan'

                    ])
            ->where('taskgroup_id',$taskgroup_id)
            ->select();
        return $list;
    }
    public  function tasklist1($taskid){
        $list = DB::table('ipm_inst_subproject_taskparter')
            ->alias('a')
            ->join('ipm_user b','a.openid=b.openid')
            ->field(
                ['a.openid','b.nickname'])
            ->where('task_id',$taskid)
            ->select();
        return $list;
    }
    public  function CountTasklist1($taskgroup_id){
        $list=Db::query(" select count(id) as sum_1 from  ipm_inst_subproject_task where  taskgroup_id='$taskgroup_id'
                         ");
        return $list;
    }
    public  function CountTaskIncomplete($taskgroup_id){
        $list=Db::query(" select count(id) as sum from  ipm_inst_subproject_task where  taskgroup_id='$taskgroup_id' and state!='3'
                         ");
        return $list;
    }
    public  function CountTasklist($taskgroup_id){
        $list=Db::query(" select count(id) as sum from  ipm_inst_subproject_task where  taskgroup_id='$taskgroup_id' and state!='1' and state!='2'
                         ");
        return $list;
    }
    public  function TodayCountTasklist($taskgroup_id){
        $list=Db::query(" select count(id) as sum from  ipm_inst_subproject_task where  taskgroup_id='$taskgroup_id' and state='3'  and  to_days(update_time)=to_days(now())
                         ");
        return $list;
    }
    public  function WeekCountTasklist($taskgroup_id){
        $list=Db::query("  select count(id) as sum from ipm_inst_subproject_task where taskgroup_id='$taskgroup_id' and state='3'  and YEARWEEK(date_format(update_time,'%Y-%m-%d')) = YEARWEEK(now())
                         ");
        return $list;
    }
    public  function MonthCountTasklist($taskgroup_id){
        $list=Db::query("  SELECT count(id) as sum FROM ipm_inst_subproject_task WHERE taskgroup_id='$taskgroup_id' and state='3'  and DATE_FORMAT(update_time, '%Y%m' ) = DATE_FORMAT( CURDATE( ) , '%Y%m' )
                         ");
        return $list;
    }
    public  function addTodayCountTasklist($taskgroup_id){
        $list=Db::query("  select count(state) as sum from  ipm_inst_subproject_task where  taskgroup_id='$taskgroup_id'  and  to_days(create_time)=to_days(now())
                         ");
        return $list;
    }

    public function select_TaskList($taskgroup_id,$time,$str){
        $list=Db::query("select count(id) as sum from ipm_inst_subproject_task where taskgroup_id='$taskgroup_id' and state!='1'
                                            AND
                                           date_format('$time','$str')>=date_format(update_time, '$str')and
                                            date_format('$time','$str')<=date_format(update_time, '$str')
                         ");
        return $list;
    }
    public function select_TaskList_1($taskgroup_id,$time,$str){
        $list=Db::query("select count(id) as sum_1 from ipm_inst_subproject_task where taskgroup_id='$taskgroup_id' and
                                           date_format('$time','$str')>=date_format(update_time, '$str')and
                                            date_format('$time','$str')<=date_format(update_time, '$str')
                         ");
        return $list;
    }
    public function select_TaskList_2($taskgroup_id,$create_time,$update_time,$str){
        $list=Db::query("select count(id) as sum_1 from ipm_inst_subproject_task where taskgroup_id='$taskgroup_id' and
                                           date_format('$update_time','$str')>=date_format(create_time, '$str')and
                                            date_format('$create_time','$str')<=date_format(create_time, '$str')
                         ");
        return $list;
    }


    public function del_task($task_id){
        $list = DB::table($this->table)->where('id',$task_id)->delete();
        return $list;
    }
    public function del_task_taskgroup_id($taskgroup_id){
        $list = DB::table($this->table)->where('taskgroup_id',$taskgroup_id)->delete();
        return $list;
    }
    public function update_state($taskgroup_id,$data){
        $list = DB::table($this->table)->where('taskgroup_id',$taskgroup_id)->update($data);
        return $list;
    }
    public function check_chargerlist($subproject_id){
        $list=Db::query("SELECT DISTINCT a.changer_id,c.nickname as changer_Name,c.openid,c.headimgurl
                              from ipm_inst_subproject_task a
                              left join ipm_user c on a.changer_id = c.openid
                              left join ipm_inst_subproject_taskgroup d on d.id = a.taskgroup_id
                              where d.subproject_id = '$subproject_id' and d.role_id != 6 and a.changer_id !=''
                         ");
        return $list;
    }
    public  function taskparter_list($subprj_id){
        $list = DB::table('ipm_inst_subproject_taskgroup')
            ->alias('a')
            ->join('ipm_user b','a.creator_id=b.openid')
            ->field(['a.id','a.name','a.creator_id','b.nickname','a.role_id','a.create_time','a.update_time','a.subproject_id'])
            ->where('subproject_id',$subprj_id)->select();
        return $list;
    }


    public function project_subproject_name($openid){
        $list=" select DISTINCT b.id as sub_id,a.id as project_id,a.name as project_name,b.name as sub_name from ipm_inst_project as a
                                     left join ipm_inst_subproject  as b on
                                     a.id=b.project_id
                                     left join ipm_inst_subproject_taskgroup as d on
                                     b.id=d.subproject_id
                                     left join ipm_inst_subproject_task as e on
                                     d.id=e.taskgroup_id
                                     left join ipm_inst_subproject_taskparter as g on
                                     e.id=g.task_id
                                      where ((a.creator_id='$openid'  or  b.id in
                                      (select c.subproject_id from ipm_inst_subproject_taskgroup c where c.creator_id='$openid')
                                      or  d.id in (select f.taskgroup_id from ipm_inst_subproject_task f where f.creator_id='$openid' or changer_id='$openid')
                                      or e.id in (select h.task_id from ipm_inst_subproject_taskparter h where h.openid='$openid')))";
        return $list;
    }
    public function project_subproject_name1($openid){
        $list="    SELECT DISTINCT a.id as task_id,a.name,a.taskgroup_id,d.name as taskgroup_name,a.subproject_id,f.name as subprj_name,a.project_id,
                       e.name as prj_name,a.creator_id,c.nickname as creator_nickname,
    			             a.changer_id,a.urgent,a.state, a.remarks,a.start_time_plan,a.end_time_plan,a.start_time_real,
    			             a.end_time_real,c.headimgurl as creator_headimgurl,a.update_time,a.create_time
    			             from ipm_inst_subproject_task a
    			             left join ipm_user as c on a.changer_id = c.openid
                       left join  ipm_inst_subproject_taskparter as b on a.id=b.task_id
                       left join  ipm_inst_subproject as f on a.subproject_id=f.id
                       left join   ipm_inst_subproject_taskgroup as d on a.taskgroup_id=d.id
                       left join ipm_inst_project as e on a.project_id=e.id
    			             where (a.changer_id= '$openid' or b.openid='$openid')";
        return $list;
    }
    public function task_name($sub_id,$openid){
        $list= "    select DISTINCT b.id as task_id,b.name as taske_name,a.id as taskgroup_id,a.name as taskgroup_name,b.state,b.urgent from ipm_inst_subproject_taskgroup as a
                                       left join ipm_inst_subproject_task  as b on
                                      a.id=b.taskgroup_id
                                      left join ipm_inst_subproject_taskparter as c on
                                      b.id=c.task_id
                                      where ((a.subproject_id='$sub_id'  and  c.openid='$openid') or (a.subproject_id='$sub_id'  and b.changer_id='$openid'))";
        return $list;
    }
}