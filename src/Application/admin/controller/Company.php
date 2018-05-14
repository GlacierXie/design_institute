<?php
namespace app\admin\controller;
use think\Controller;
use app\home\model\Projects;
use think\Db;
use think\Cache;
use app\home\model\Companys;
use think\Request;

class Company extends Controller
{
    //查看本公司所有项目的所有信息
    public  function company_project_list(){
        if(request()->isGet()) {
            $company_id = input('company_id');
            if (!isset($company_id) || empty($company_id)) {
                return json('111');
            }
            $companyTbale=new Projects();
            //查看本公司所有项目的所有信息
            $res=$companyTbale->company_projectList($company_id);
            return json ($res);
        }
    }

    public function company_list(){
        $companyTable=new Companys();
        //查询company表所有数据
        $res=$companyTable->select_company();
        if($res){
            return json ($res);
        }else{
            $res['success'] = false;
            $res['message'] = 'error';
            return json ($res);
        }
    }
    public function del_company(){
        $arr= $this->request->param();
        $company_id=$arr['company_id'];
        $companyTable=new Companys();
        //根据ID来删除这条数据
        $res=$companyTable->del_company($company_id);
        if($res){
            $res['success'] = true;
            $res['message'] = "delete success";
            return json ($res);
        }else{
            $res['success'] = false;
            $res['message'] = 'error';
            return json ($res);
        }
    }
    public  function add_company(){
        //post ,get接收都可以
            $arr= $this->request->param();
            $company_id=$arr['company_id'];
            $name=$arr['name'];
            $address=$arr['address'];
            $create_time=date("Y-m-d H:i:s");
            if (!isset($company_id) || empty($company_id)) {
                return json('111');
            }
            if (!isset($address) || empty($address)) {
                return json('222');
            }
            if (!isset($name) || empty($name)) {
                return json('333');
            }
            //封装成一个数组
            $arr=array(
                'company_id'=>$company_id,
                'name'=>$name,
                'address'=>$address,
                'create_time'=>$create_time,
                'update_time'=>$create_time

            );
            //插入一条记录
            $id1 = Db::table('ipm_inst_company')->insertGetId($arr);
            if($id1){
                $res['success'] = true;
                $res['message'] = "success";
                return json ($res);
            }else{
                $res['success'] = false;
                $res['message'] = 'error';
                return json ($res);
            }

        }
	//查某一公司的信息
	public function one_company_info()
	{
		$arr= $this->request->param();
        $company_id=$arr['company_id'];
        $companyTable=new Companys();
        //根据ID来删除这条数据
        $res=$companyTable->company_info($company_id);
        if($res){
            
            return json ($res);
        }else{
            $res['success'] = false;
            $res['message'] = 'error';
            return json ($res);
        }
	}
}