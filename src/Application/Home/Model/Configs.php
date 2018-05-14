<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Configs extends Model
{
    protected $table = 'ipm_inst_configuration';
    public  function configList($company_id){
        $list = DB::table($this->table)
            ->alias('a')
            ->join('ipm_user b','a.creator_id=b.openid')
            ->field(['a.id','a.name','a.creator_id','b.nickname','b.headimgurl','a.company_id','a.create_time','a.update_time'])
            ->where("company_id='$company_id'")
            ->select();
        return $list;
    }
}