<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Defaulttasks extends Model
{
    protected $table = 'ipm_inst_default_task';
    public function default_taskList($task_group_id){
        $list = DB::table($this->table)->where('taskgroup_id',$task_group_id)->select();
        return $list;
    }
}