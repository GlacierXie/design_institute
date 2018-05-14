<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Cache;
use app\home\model\Configs;
use think\Request;
require_once ROOT_PATH.'application\\common\\OssCommon.php';
use app\common\OssCommon;

class Config extends Controller
{
    public  function Config_list(){
        if(request()->isGet()) {
            $company_id = input('company_id');
            if (!isset($company_id) ||  empty($company_id) || !is_numeric($company_id) || $company_id<1) {
                return json();
            }
            $userTbale = new Configs();
            //根据company_id来查找这个创建的配置文件
            $res = $userTbale->configList($company_id);
            return json($res);
        }
    }
    public function upload_Config(){
        $arr= $this->request->param();
        if (!isset($arr['openid']) || empty($arr['openid'])) {
            return json('openid empty');
        }
        if (!isset($arr['company_id']) ||  empty($arr['company_id'])) {
            return json();
        }
        if (!isset($arr['project_id']) ||  empty($arr['project_id'])) {
            return json();
        }
		$ossClient = OssCommon::getOssClient(false);
        if($ossClient == null)
             return json();
        $company_id=$arr['company_id'];
        $openid=$arr['openid'];
        $project_id=$arr['project_id'];
        //upload传过来的文件名
        $fileName = $_FILES["file"]["name"];
        $fileArray= explode(".", $fileName);
        $endfileName = end($fileArray);
        if($endfileName=='zip' || $endfileName=='rar'){
            $arr=array(
                'company_id' => $company_id,
                'name' => $fileName,
                'creator_id' => $openid,
                "create_time"=>date("Y-m-d H:i:s"),
                "update_time"=>date("Y-m-d H:i:s"),
            );
            // 数据表插入一条数据
            $config_id=Db::table('ipm_inst_configuration')->insertGetId($arr);
            //根据id拿到单个字段
            if($config_id){
                $data['config_id']=$config_id;
                $projectTable=Db::table('ipm_inst_project')->where('id',$project_id)->update($data);
                if($projectTable){
                    $fileNames=Db::table('ipm_inst_configuration')->where('id',$config_id)->value('name');
                    //以.分割
                    $fileNameArry = explode(".", $fileNames);
                    //获取数组最后一位
                    $fileNameTitle = end($fileNameArry);
                    if($config_id){
                        //上传过来的数据
                        $file = request()->file('file');
                        //设置大小。允许的格式，存放的位置（以插入的Id来命名）
                        if (!$file->validate(['size'=>156780000,'ext'=>'zip,rar'])) {
                             $res['success'] = false;
                             $res['message'] = "上传错误";
                        }
                        $path = $company_id.'/configFiles/'.$config_id.'.'.$fileNameTitle; 
                        if(OssCommon::uploadFile($ossClient,$_FILES["file"]["tmp_name"],$path))
                        {
                            $res['success'] = true;
                            $res['message'] = "上传成功";
                        }else{

                             $res['success'] = false;
                             $res['message'] = "上传错误";
                        }
                    }
                }else{
                     $res['success'] = false;
                    $res['message'] = "project 更新失败";
                }
            }

        }else{
          //  $this->success('上传文件的后缀不符合', SET_URLS."/design_inst/#/index/project/configuration");
            $res['success'] = false;
            $res['message'] = "上传文件的后缀不符合";

        }
        return json($res);
    }

    //删除配置表
    public function del_config(){
        $arr= $this->request->param();
        $config_id=$arr['config_id'];
        if (!isset($config_id) || empty($config_id)) {
            return json('111');
        }
        //先查找配置文件有没有在使用中
        $ossClient = OssCommon::getOssClient(false);
        if($ossClient == null)
             return json();

        $result1=Db::table('ipm_inst_configuration')
            ->field(['company_id','name'])
            ->where("id='$config_id'")
            ->select();
        if($result1){
            $company_id=$result1[0]['company_id'];
            $filename=$result1[0]['name'];
            $fileNameArry = explode(".", $filename);
            //获取数组最后一位
            $fileNameTitle = end($fileNameArry);
            $result=Db::table('ipm_inst_configuration')
                ->alias('a')
                ->join('ipm_inst_project b','b.config_id=a.id')
                ->where("a.id='$config_id'")
                ->select();
            if($result){

                $res['success'] = false;
                $res['message'] = "error";
                return json($res);
            }else{
                $dir=$company_id.'/configFiles'.$config_id.'.'.$fileNameTitle; //路径
                $result=Db::table('ipm_inst_configuration')->where('id',$config_id)->delete();
                if($result){
                        //删除当前文件夹：
                        if(OssCommon::deletefile($ossClient,$dir)) {
                            $res['success'] = true;
                            $res['message'] = "success";
                            return json($res);
                        }
                        else{
                            return json('false');
                        }

                }else{
                    $res['success'] = false;
                    $res['message'] = "error";
                    return json($res);
                }
           }

        }else{
            return json();
        }
    }

}