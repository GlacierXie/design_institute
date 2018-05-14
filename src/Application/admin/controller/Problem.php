<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Cache;
use app\home\model\Problems;
use think\Request;
require_once ROOT_PATH.'application\\common\\OssCommon.php';
use app\common\OssCommon;

class Problem extends Controller
{
    public function ProblemCount(){
        $arr= $this->request->param();
        if (!isset($arr['subproject_id']) || empty($arr['subproject_id'])){
            return json('subproject_id  不能为空');
        }
        $TaskgroupsTable = new Problems();
        $result['CountProblem'] = 0;
        $result['ProblemFinish'] = 0;
        $result['TodayCountProblemlist'] = 0;
        $result['WeekCountProblemlist'] = 0;
        $result['MonthCountProblemlist'] = 0;
        $result['addTodayCountProblemlist'] = 0;
        $subproject_id=$arr['subproject_id'];
            $Count = $TaskgroupsTable->CountProblelist($subproject_id);
            $Finish = $TaskgroupsTable->ProblemFinish($subproject_id);
            $Today = $TaskgroupsTable->TodayCountProblemlist($subproject_id);
            $Week = $TaskgroupsTable->WeekCountProblemlist($subproject_id);
            $Month = $TaskgroupsTable->MonthCountProblemlist($subproject_id);
            $addToday = $TaskgroupsTable->addTodayCountProblemlist($subproject_id);
            $result['CountProblem']  = intval( $result['CountProblem'] ) + intval($Count[0]['sum']);
            $result['ProblemFinish']  = intval($result['ProblemFinish'] ) + intval($Finish[0]['sum']);
            $result['TodayCountProblemlist']  = intval($result['TodayCountProblemlist'] ) + intval($Today[0]['sum']);
            $result['WeekCountProblemlist']  = intval($result['WeekCountProblemlist'] ) + intval($Week[0]['sum']);
            $result['MonthCountProblemlist']  = intval($result['MonthCountProblemlist'] ) + intval($Month[0]['sum']);
            $result['addTodayCountProblemlist']  = intval($result['addTodayCountProblemlist'] ) + intval($addToday[0]['sum']);
        return json($result);

    }
    public function DelProblem(){
        $arr= $this->request->param();
        if (!isset($arr['problem_id']) || empty($arr['problem_id'])){
            return json('problem_id  不能为空');
        }
        if (!isset($arr['openid']) || empty($arr['openid'])){
            return json('openid  不能为空');
        }
        $problem_id=$arr['problem_id'];
        $openid=$arr['openid'];
        $ossClient = OssCommon::getOssClient(false);
        if($ossClient == null)
             return json();

        Db::startTrans();
        try {
            $prjInfo= Db::query("SELECT DISTINCT a.project_id as project_id,a.company_id as company_id ,a.subproject_id as subproject_id,a.creator_id
				                     from ipm_inst_problem a
				                    WHERE a.id = '$problem_id' and creator_id='$openid' ");
            if($prjInfo){
                $company_id = $prjInfo[0]['company_id'];
                $project_idTmp = $prjInfo[0]['project_id'];
                $subproject_idTmp = $prjInfo[0]['subproject_id'];
                $problemfilelist = Db::query("SELECT DISTINCT id from ipm_inst_problem_files where problem_id ='$problem_id'");
                if(!empty($problemfilelist))
                {
                    foreach ($problemfilelist as $kk => $vv)
                    {
                        $str = md5($problemfilelist[$kk]['id']."jpg");
                        OssCommon::deletefile($ossClient,$company_id.'/'.$project_idTmp.'/'.$subproject_idTmp.'/'.$str.'.jpg');
                    }
                }
                $problemTable = Db::table('ipm_inst_problem')->where('id',$problem_id)->delete();
                if($problemTable){
                    $result = Db::table('ipm_inst_problem_files')->where('problem_id',$problem_id)->delete();
                    if($result){
                        $res['success'] = true;
                        $res['message'] = "删除成功";
                    }else{
                        $res['success'] = false;
                        $res['message'] = "删除失败";
                    }
                }
            }else{
                $res['success'] = false;
                $res['message'] = "操作者不是任务负责人";
            }
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }
        return json($res);
    }
}