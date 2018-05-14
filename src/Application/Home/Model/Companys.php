<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Companys extends Model
{
    protected $table = 'ipm_inst_company';
    public function select_company(){
        $list = DB::table($this->table)
            ->select();
        return $list;

    }
    public function del_company($company_id){
        $list = DB::table($this->table)
            ->where('id',$company_id)
            ->delete();
        return $list;

    }
	public function company_info($company_id){
		$list = DB::table($this->table)
            ->where('id',$company_id)
            ->select();
        return $list;
	}
}