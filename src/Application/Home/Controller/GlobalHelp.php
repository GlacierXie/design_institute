<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\Cache;
use think\Request;

class GlobalHelp extends Controller
{
    public  function  isValidCompany($company_id)
	{
		if(!isset($company_id))
			return false;
         $read= Db::query("SELECT * FROM `ipm_inst_company` where id='$company_id'");
		 if(!isset($read) || empty($read))
			 return false;
		 return true;
    }
	function i_array_column($input, $columnKey, $indexKey=null){
    if(!function_exists('array_column')){ 
        $columnKeyIsNumber  = (is_numeric($columnKey))?true:false; 
        $indexKeyIsNull            = (is_null($indexKey))?true :false; 
        $indexKeyIsNumber     = (is_numeric($indexKey))?true:false; 
        $result                         = array(); 
        foreach((array)$input as $key=>$row){ 
            if($columnKeyIsNumber){ 
                $tmp= array_slice($row, $columnKey, 1); 
                $tmp= (is_array($tmp) && !empty($tmp))?current($tmp):null; 
            }else{ 
                $tmp= isset($row[$columnKey])?$row[$columnKey]:null; 
            } 
            if(!$indexKeyIsNull){ 
                if($indexKeyIsNumber){ 
                  $key = array_slice($row, $indexKey, 1); 
                  $key = (is_array($key) && !empty($key))?current($key):null; 
                  $key = is_null($key)?0:$key; 
                }else{ 
                  $key = isset($row[$indexKey])?$row[$indexKey]:0; 
                } 
            } 
            $result[$key] = $tmp; 
        } 
        return $result; 
    }else{
        return array_column($input, $columnKey, $indexKey);
    }
    }
	
	public  function  isValidUser($company_id,$openid)
	{
		if(!isset($company_id) || !isset($openid))
			return false;
         $read= Db::query("SELECT * FROM `ipm_inst_user` where company_id='$company_id' and `openid` ='$openid'");
		 if(!isset($read) || empty($read))
			 return false;
		 return true;
    }
	
	public  function  getUserName($openid)
	{
		if(!isset($openid))
			return false;
         $read= Db::query("SELECT real_name FROM `ipm_user` where `openid` ='$openid'");
		 if(!isset($read) || empty($read))
			 return "";
		 return $read[0]['real_name'];
    }
	
	public  function  getUserheadimgurl($openid)
	{
		if(!isset($openid))
			return false;
         $read= Db::query("SELECT headimgurl FROM `ipm_user` where `openid` ='$openid'");
		 if(!isset($read) || empty($read))
			 return "";
		 return $read[0]['headimgurl'];
    }
	
	public  function  isValidPrj($prjId)
	{
		if(!isset($prjId))
			return false;
         $read= Db::query("SELECT * FROM `ipm_inst_project` where id='$prjId'");
		 if(!isset($read) || empty($read))
			 return false;
		 return true;
    }
	
	public  function isValidSubPrj($subprjId)
	{
		if(!isset($subprjId))
			return false;
         $read= Db::query("SELECT * FROM `ipm_inst_subproject` where id='$subprjId'");
		 if(!isset($read) || empty($read))
			 return false;
		 return true;
    }
	
	public  function  isPrjValidUser($subprjId,$openid)
	{
		if(!isset($subprjId) || !isset($openid))
			return false;
         $read= Db::query("SELECT * FROM `ipm_inst_subproject_user` where subproject_id='$subprjId' and `openid`='$openid'");
		 if(!isset($read) || empty($read))
			 return false;
		 return true;
    }
	
	public  function  isValidPrjAndSubPrj($prjId,$subprjId)
	{
		if(!isset($subprjId) || !isset($prjId))
			return false;
         $read= Db::query("SELECT * FROM `ipm_inst_subproject` where id='$subprjId' and `project_id`='$prjId'");
		 if(!isset($read) || empty($read))
			 return false;
		 return true;
    }
	
	public  function  isPrjValidProblem($problem_id)
	{
		if(!isset($problem_id))
			return false;
         $read= Db::query("SELECT * FROM `ipm_inst_problem` where id ='$problem_id'");
		 if(!isset($read) || empty($read))
			 return false;
		 return true;
    }
	
	public  function  isPrjValidTask($subprjId,$TaskId)
	{
		if(!isset($subprjId)|| !isset($TaskId))
			return false;
         $read= Db::query("SELECT a.id  FROM ipm_inst_subproject_task a  
		  left join ipm_inst_subproject_taskgroup as b on a.taskgroup_id=b.id
		  where a.id ='$TaskId' and b.subproject_id = '$subprjId'");
		 if(!isset($read) || empty($read))
			 return false;
		 return true;
	}

	/*
    TripleDES加密
   */
    public function DesEncrypt($data)
    {    
      //Pad for PKCS7
     $blockSize = mcrypt_get_block_size('tripledes', 'ecb');
     $len = strlen($data);
     $pad = $blockSize - ($len % $blockSize);
     $data .= str_repeat(chr($pad), $pad);

     $key = "home";
     $key = md5($key,TRUE);
     $key .= substr($key,0,8); //comment this if you use 168 bits long key

      //Encrypt data
     $encData = mcrypt_encrypt('tripledes', $key, $data, 'ecb'); 
     return base64_encode($encData);
   }

   /*
    TripleDES解密
   */
    public  function DesDecrypt($data)
   {
     $key = "home";
     $key = md5($key, TRUE);
     $key .= substr($key, 0, 8);

     //Decrypt data
     $fromBase64Str = base64_decode($data);
     $decData = mcrypt_decrypt('tripledes', $key, $fromBase64Str, 'ecb');

     return $decData;
   }
}