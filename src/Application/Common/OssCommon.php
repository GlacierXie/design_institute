<?php
namespace app\common;
if (is_file(ROOT_PATH.'application\\common\\autoload.php')) {
    require_once ROOT_PATH.'application\\common\\autoload.php';
}

use think\Controller;

use OSS\OssClient;
use OSS\Core\OssException;
/**
 * Class OssHelp
 *
 * 示例程序【Samples/*.php】 的Common类，用于获取OssClient实例和其他公用方法
 */
class OssCommon extends Controller
{
    const cnameendpoint = 'file.rili-tech.com';
    const accessKeyId = 'LTAIM4Y2YE3mMMVn';
    const accessKeySecret = 'YwXHvk6zV9OcKdCUSwI3Tpn8eX0VHj';
    const bucket = 'bucket-rili-file';

    const endpoint = 'oss-cn-shenzhen.aliyuncs.com';
    /**
     * 根据Config配置，得到一个OssClient实例
     *
     * @return OssClient 一个OssClient实例
     */
    public static function getOssClient($isCName)
    {
        try {
            $endpointTmp = $isCName ? self::cnameendpoint : self::endpoint;
            $ossClient = new OssClient(self::accessKeyId, self::accessKeySecret, $endpointTmp, $isCName);
        } catch (OssException $e) {
            printf(__FUNCTION__ . "creating OssClient instance: FAILED\n");
            printf($e->getMessage() . "\n");
            return null;
        }
        return $ossClient;
    }

    public static function getBucketName()
    {
        return self::bucket;
    }
    
    public static function uploadFile($ossClient,$file,$object)
    {
        if (is_null($ossClient)) 
            return false;
         $bucket = self::getBucketName();
         try {
            if($ossClient->doesObjectExist($bucket, $object))
                $ossClient->deleteObject($bucket, $object);
            $result = $ossClient->uploadFile($bucket, $object, $file);
            var_dump($result);
            return $result['info']['http_code'] == 200;

        } catch (OssException $e) {
            printf(__FUNCTION__ . "creating OssClient instance: FAILED\n");
            printf($e->getMessage() . "\n");
           return false;
        }
        return false;
    }
    
    public static function deleteDir($ossClient,$object)
    {
        if (is_null($ossClient)) 
            return false;
        $bucket = self::getBucketName();
        $prefix = $object;
        $delimiter = '/';
        $nextMarker = '';
        $maxkeys = 1000;
        $options = array(
        'delimiter' => $delimiter,
        'prefix' => $prefix,
        'max-keys' => $maxkeys,
        'marker' => $nextMarker,
           );
       try 
       {
           $listObjectInfo = $ossClient->listObjects($bucket, $options);
           $objectList = $listObjectInfo->getObjectList(); // 文件列表
           $prefixList = $listObjectInfo->getPrefixList(); // 目录列表
           if (!empty($objectList)) 
           {
             foreach ($objectList as $objectInfo)
             {
                $ossClient->deleteObject($bucket, $objectInfo->getKey());
             }
               
             foreach ($prefixList as $prefixInfo)
             {
                OssCommon::deleteDir($prefixInfo->getPrefix());
             }
           }
           return true;
       } 
       catch (OssException $e) 
       {
         return false;
       }
      return false;
    }
    
    public static function deletefile($ossClient,$object)
    {
        if (is_null($ossClient)) 
            return false;
         $bucket = self::getBucketName();
         try {
            if($ossClient->doesObjectExist($bucket, $object))
                $ossClient->deleteObject($bucket, $object);
            return true;

        } catch (OssException $e) {
           return false;
        }
        return false;
    }

    public static function downloadUrl($ossClient,$object)
    {
        if (is_null($ossClient)) 
            return false;
        
         $bucket = self::getBucketName();
         try {
            //if(!$ossClient->doesObjectExist($bucket, $object))
             //   return '';
            $signedUrl = $ossClient->signUrl($bucket, $object, 3600);
            return $signedUrl;

        } catch (OssException $e) {
             echo $e->getMessage();
           return '';
        }
        return '';
    }

    /**
     * 工具方法，创建一个存储空间，如果发生异常直接exit
     */
    public static function createBucket($ossClient)
    {
        if (is_null($ossClient))
         exit(1);
        $bucket = self::getBucketName();
        $acl = OssClient::OSS_ACL_TYPE_PUBLIC_READ;
        try {
            $ossClient->createBucket($bucket, $acl);
        } catch (OssException $e) {

            $message = $e->getMessage();
            if (\OSS\Core\OssUtil::startsWith($message, 'http status: 403')) {
                echo "Please Check your AccessKeyId and AccessKeySecret" . "\n";
                exit(0);
            } elseif (strpos($message, "BucketAlreadyExists") !== false) {
                echo "Bucket already exists. Please check whether the bucket belongs to you, or it was visited with correct endpoint. " . "\n";
                exit(0);
            }
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
        print(__FUNCTION__ . ": OK" . "\n");
    }

    public static function println($message)
    {
        if (!empty($message)) {
            echo strval($message) . "\n";
        }
    }
}
