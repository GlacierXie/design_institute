<?php
namespace app\admin\controller;
use think\Controller;
use app\home\model\Projects;
use think\Db;
use think\Cache;
use think\Request;

class Alter extends Controller
{
    //修改时间
    /**
     * @return \think\response\Json
     */
	 
	public function alter_time(){
		$arr= $this->request->param();
		switch($arr['type'])
		{
			case 1:
			$data['start_time_plan'] = date('Y-m-d H:i:s',strtotime($arr['time_var']));
			break;
			case 2:
			$data['dwg_end_plan'] = date('Y-m-d H:i:s',strtotime($arr['time_var']));
			break;
			case 3:
			$data['design_start_plan'] = date('Y-m-d H:i:s',strtotime($arr['time_var']));
			break;
			case 4:
			$data['end_time_plan'] = date('Y-m-d H:i:s',strtotime($arr['time_var']));
			break;
		}
		if($arr['prj_id'] == 0)
		{
			$arr = Db::table('ipm_inst_subproject')->where('id',$arr['subprj_id'])->update($data);
			if($arr)
			{
				$result['success'] = true;
				 return json ($result);
			}
			else{
				$result['success']=false;
				return json ($result);
				}
		}
		if($arr['subprj_id'] == 0)
		{
			$arr = Db::table('ipm_inst_project')->where('id',$arr['prj_id'])->update($data);
			if($arr)
			{
				$result['success'] = true;
				 return json ($result);
			}
			else{
				$result['success']=false;
				return json ($result);
				}
		}
	}
}