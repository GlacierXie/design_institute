<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Problems extends Model
{
    protected $table = 'ipm_inst_problem';
    public  function problemList($company_id,$subproject_id){
        $list = DB::table($this->table)
            ->alias('a')
            ->join('ipm_user b','a.creator_id=b.openid')
            ->field(['a.*','b.nickname'])
            ->where("company_id='$company_id' and subproject_id='$subproject_id'")
            ->select();
        return $list;
    }
    public function CountProblelist($subproject_id){
        $list=Db::query(" select count(id) as sum from  ipm_inst_problem  where subproject_id='$subproject_id'");
    return $list;
    }
    public function ProblemFinish($subproject_id){
        $list=Db::query(" select count(id) as sum from  ipm_inst_problem  where subproject_id='$subproject_id' and state='3'");
        return $list;
    }
    public function TodayCountProblemlist($subproject_id){
        $list=Db::query(" select count(id) as sum from  ipm_inst_problem  where subproject_id='$subproject_id' and  to_days(update_time)=to_days(now())");
        return $list;
    }
    public function WeekCountProblemlist($subproject_id){
        $list=Db::query(" select count(id) as sum from  ipm_inst_problem  where subproject_id='$subproject_id'  and DATE_FORMAT(update_time, '%Y%m' ) = DATE_FORMAT( CURDATE( ) , '%Y%m' )");
        return $list;
    }
    public function MonthCountProblemlist($subproject_id){
        $list=Db::query(" select count(id) as sum from  ipm_inst_problem  where subproject_id='$subproject_id'  and DATE_FORMAT(update_time, '%Y%m' ) = DATE_FORMAT( CURDATE( ) , '%Y%m' )");
        return $list;
    }
    public function addTodayCountProblemlist($subproject_id){
        $list=Db::query(" select count(id) as sum from  ipm_inst_problem  where subproject_id='$subproject_id' and  to_days(create_time)=to_days(now())");
        return $list;
    }

}