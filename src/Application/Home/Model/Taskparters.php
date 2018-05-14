<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Taskparters extends Model{
    protected $table = 'ipm_inst_subproject_taskparter';
    public  function add_taskparter($data){
        $list = DB::table($this->table)->insertGetId($data);
        return $list;

    }
    public  function update_taskparter($subtask_id,$data1){
        $list = DB::table($this->table)->where('task_id',$subtask_id)->update($data1);
        return $list;

    }
    public  function del_taskparter($subtask_id){
        $list = DB::table($this->table)->where('task_id',$subtask_id)->delete();
        return $list;

    }
    public  function del_taskparterlist($subtask_id,$data1){
        $list = DB::table($this->table)->where('task_id',$subtask_id)->update($data1);
        return $list;

    }
    public  function task_openid($task_id){
        $list = DB::table($this->table)->where('task_id',$task_id)->select();
        return $list;

    }
    public  function select_task($parters){
        $list = DB::table($this->table)->where('openid',$parters)->select();
        return $list;

    }
    public  function taskparter_id($task_id){
        $list = DB::table($this->table)
            ->alias('a')
            ->join('ipm_user b','a.openid=b.openid')
            ->field(
                ['a.openid','b.nickname','b.headimgurl','b.real_name'])
            ->where('task_id',$task_id)
            ->select();

        if(!isset($list) || empty($list))
            return "";
        return $list;
    }

}