<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Roles extends Model
{
    protected $table = 'ipm_inst_role';
    public  function roleList(){
        $list = DB::table($this->table)
            ->select();
        return $list;
    }
    public  function value_role($openid){
        $list = DB::table('ipm_inst_subproject_user')
            ->field(['role_id'])
            ->where('openid',$openid)
            ->select();
        return $list;
    }
    public function role_master_groups_list($company_id,$currentpage,$itemsPerPage){
        $list = Db::query("   select d.modular_id,e.name as modular_name,a.master,a.role_id,f.name as role_name,b.openid,b.nickname,b.headimgurl,b.real_name,b.mobile_phone,b.remark,d.groups
                            from ipm_inst_user_role  as a
                            left join ipm_user as b on
                          a.openid=b.openid
                           inner join ipm_inst_user as c on
                          a.openid=c.openid
                           left join ipm_inst_modular_group d on
                          a.openid=d.openid and a.role_id=d.role_id
                           left join ipm_inst_modular_role e on
                          d.modular_id=e.id
                            left join ipm_inst_role f on
                          a.role_id=f.id
                           where c.company_id=$company_id order by a.role_id limit " . ($currentpage - 1) * $itemsPerPage . ',' . $itemsPerPage);
        return $list;
    }
    public function role_master_groups_list1($company_id,$role_id,$currentpage,$itemsPerPage){
        $list = Db::query("  select d.modular_id,e.name as modular_name,a.master,a.role_id,f.name as role_name,b.openid,b.nickname,b.headimgurl,b.real_name,b.mobile_phone,b.remark,d.groups
                            from ipm_inst_user_role  as a
                            left join ipm_user as b on
                          a.openid=b.openid
                           inner join ipm_inst_user as c on
                          a.openid=c.openid
                           left join ipm_inst_modular_group d on
                          a.openid=d.openid and a.role_id=d.role_id
                           left join ipm_inst_modular_role e on
                          d.modular_id=e.id
                            left join ipm_inst_role f on
                          a.role_id=f.id
                           where  c.company_id=$company_id and a.role_id=$role_id  order by a.role_id  limit " . ($currentpage - 1) * $itemsPerPage . ',' . $itemsPerPage);
        return $list;
    }
    public function role_master_groups_list2($company_id,$role_id,$moddle_id,$currentpage,$itemsPerPage){
        $list=Db::query("  select d.modular_id,e.name as modular_name,a.master,a.role_id,f.name as role_name,b.openid,b.nickname,b.headimgurl,b.real_name,b.mobile_phone,b.remark,d.groups
                            from ipm_inst_user_role  as a
                            left join ipm_user as b on
                          a.openid=b.openid
                           inner join ipm_inst_user as c on
                          a.openid=c.openid
                           left join ipm_inst_modular_group d on
                          a.openid=d.openid and a.role_id=d.role_id
                           left join ipm_inst_modular_role e on
                          d.modular_id=e.id
                            left join ipm_inst_role f on
                          a.role_id=f.id
                           where  c.company_id=$company_id and a.role_id=$role_id  and d.modular_id=$moddle_id  order by a.role_id limit ".($currentpage-1)*$itemsPerPage .','.$itemsPerPage);

        return $list;
    }

    public function role_user_list($subproject_id){
        $list= Db::query("  select a.subproject_id,a.openid,a.create_time,GROUP_CONCAT(DISTINCT a.role_id) as role_id,c.headimgurl,c.nickname,c.real_name,c.mobile_phone  from  ipm_inst_subproject_user as a
                                    left join  ipm_inst_role as b on a.role_id=b.id
                                    left join  ipm_user as c on a.openid=c.openid
									 left join  ipm_inst_user_role as d on a.openid = d.openid
                                    where  a.subproject_id=$subproject_id
                                     group by a.openid
                         ");
        return $list;
    }
    public function role_user_list1($subproject_id,$role_id){
        $list= Db::query("  select a.subproject_id,a.openid,a.create_time,c.headimgurl,c.nickname  from  ipm_inst_subproject_user as a
                                    left join  ipm_inst_role as b on a.role_id=b.id
                                    left join  ipm_user as c on a.openid=c.openid
									 left join  ipm_inst_user_role as d on a.openid = d.openid
                                    where  a.subproject_id=$subproject_id and a.role_id=$role_id
                                     group by a.openid
                         ");
        return $list;
    }
	public function modular_roleList($role_id)
	{
		$list= Db::query("select id,name from ipm_inst_modular_role where role_id=$role_id");
        return $list;
	}
	
}