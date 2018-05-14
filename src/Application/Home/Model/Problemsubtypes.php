<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Problemsubtypes extends Model
{
    protected $table = 'ipm_inst_problem_subtype';
    public function subtype_Liset($type_id){
        $list = DB::table($this->table)
            ->where("type_id='$type_id'")
            ->select();
        return $list;
    }
}