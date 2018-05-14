<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Subprojectusers extends Model{
    protected $table = 'ipm_inst_subproject_user';
    public  function add_role($data){
        $list = DB::table($this->table)->insertGetId($data);
        return $list;

    }
}