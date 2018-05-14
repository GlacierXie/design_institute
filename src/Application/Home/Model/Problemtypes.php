<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Problemtypes extends Model
{
    protected $table = 'ipm_inst_problem_type';
    public function type_Liset(){
        $list = DB::table($this->table)
            ->select();
        return $list;
    }
}