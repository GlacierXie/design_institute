<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Defaulttaskgroup extends Model
{
    protected $table = 'ipm_inst_default_taskgroup';
    public  function default_taskgroup_List(){
        $list = DB::table($this->table)
            ->select();
        return $list;
    }
}