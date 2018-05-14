<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Users extends Model
{
    protected $table = 'ipm_user';
    public  function select_users($openid){
        $list = DB::table($this->table)
            ->where("openid='$openid'")
            ->select();
        return $list;
    }

    public  function ipm_users($currentpage,$itemsPerPage){
        /*$list= Db::query("select a.*,b.* from ipm_trail as a
                 right join ipm_user as b on
                       a.openid=b.openid limit ".($currentpage-1)*$itemsPerPage .','.$itemsPerPage);*/
		$list= Db::query("select *from ipm_user limit ".($currentpage-1)*$itemsPerPage .','.$itemsPerPage);
        return $list;
    }
    public  function update_ipm_users($data){                                                                                                                                                       -
        $list = DB::table($this->table)
            ->where('openid',$data['openid'])
            ->update($data);
        return $list;
    }
    public function subproject_user_list($openid){
        $list= Db::query("  select a.subproject_id,a.openid,b.project_id,b.name,b.state,b.start_time_plan,b.end_time_plan,b.start_time_real,b.start_time_real
                                from ipm_inst_subproject_user as a
                               left join  ipm_inst_subproject as b on a.subproject_id=b.id
                               where a.openid='$openid'
                               group by a.subproject_id");
        return $list;
    }
    public function project_config_users($project_id){
        $data = Db::query("  select a.id as project_id,a.name,a.company_id,a.creator_id,c.nickname as creator_nickname,b.id as config_id,b.name as config_name,
                                 a.state,a.start_time_plan,a.end_time_plan,a.start_time_real,a.end_time_real
                          from ipm_inst_project a
                           left join ipm_inst_configuration as b on a.config_id=b.id
                           left join ipm_user c on a.creator_id=c.openid
                           where  a.id='$project_id'
                           ");
        return $data;
    }
	
	public function fuzz_search($fuzz_var)
	{
		$data = Db::query("SELECT * FROM ipm_user WHERE nickname like '%$fuzz_var%' or real_name LIKE '%$fuzz_var%'");
        return $data;
	}

}