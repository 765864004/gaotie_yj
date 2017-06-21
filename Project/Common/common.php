<?php
function md5str($str)
{
	return md5($str.C('MD5KEY'));
}

/**
 * 得到媒体信息途径，暂时网站上有 用户图片  用户证书  视频缩略图  视频文件的信息
 * 视频：media/video/视频id/视频文件名称or视频缩略图名称
 * 用户：media/user/用户id/用户头像名称
 * 证书:media/certificate/证书id/证书名称
 * @param int $mediaType 1:视频(默认) 2：用户 3：证书, 4:教学评价附件(文件夹名为教学评价id)
 * @author:lerry
 * @date 2012-7-15
 * */
function getObjectPath($objectId, $objectName, $mediaType = 1)
{
	$fileUrl = NULL;
	switch ($mediaType)
	{
		case '1' : $fileUrl = '/Teach/media/video/'.$objectId.'/'.$objectName;break;
		case '2' : $fileUrl = '/Teach/media/user/'.$objectId.'/'.$objectName;break;
		case '3' : $fileUrl = '/Teach/media/certificate/'.$objectId.'/'.$objectName;break;
		case '4' : $fileUrl = '/Teach/media/evaluation/'.$objectId.'/'.$objectName;break;
		default :break;
	}
	return $fileUrl;
}



/**
 * 随机生成字符串
 * @author lerry
 * @date 2012-7-14
 * */
function randomkeys($length)
{
	$pattern='3456789abcdefghijkmnpqrstuvwxyABCDEFGHIJKMNPQRSTUVWXY';
	for($i = 0; $i < $length; $i++)
	{
		$key .= $pattern{mt_rand(0,35)};    //生成php随机数
	}
	return $key;
}


/**
 * 字符串截取，支持中文和其他编码
 * @date 2012-7-14
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix="…")
{
	$strLenth = strlen($str);
	$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
	$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
	$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
	$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
	preg_match_all($re[$charset], $str, $match);
	$slice = join("",array_slice($match[0], $start, $length));//return $slice;
	
	if($strLenth/3 > $length)
	{
		return $slice.$suffix;
	}
	else
	{
		return $slice;
	}
}

/**
 * 说明:公用图片上传函数
 * @author lerry
 * @date 2012-7-14
 * 默认值
 * $maxSize		默认最大体积 ，			默认 2M
 * $allowExts	默认允许上传文件类型	JPG,gif,png,jpeg
 * $savePath	默认保存路径			
 * $w			限制图片最小宽			不足部分用空白填充
 * $h			限制图片最小高			不足部分用空白填充
 * @return array
 */
function douploadCommon($w,$h,$maxSize,$allowExts='JPG,gif,png,jpeg',$savePath="")
{
	import("ORG.Net.UploadFile");
	
	if($savePath==""){
		
		$savePath= C('MEDIA_DISK_PATH')."/Tmp/";
	}

	$thumbPrefix='b_,m_';
	$thumbMaxWidth= '220,158';
	$thumbMaxHeight= '285,113';
	$thumb=false; //是否开启缩略图

	if(isset($w)&&isset($h))
	{
		$upload = new UploadFile($w,$h);
	}
	else
	{
		$upload = new UploadFile();
	}
	
	if(!isset($maxSize))
	{
		$maxSize = C('IMAGE_UPLOAD_MAX_SIZE');
	}
	$upload->maxSize=(int)$maxSize ; // -1 不限制,默认 20K
	$upload->allowExts=explode(',',strtolower($allowExts));
	$upload->savePath=$savePath;
	$upload->thumb=$thumb;
	$upload->thumbPrefix = $thumbPrefix;
	$upload->thumbMaxWidth = $thumbMaxWidth;
	$upload->thumbMaxHeight =$thumbMaxHeight;
	$upload->saveRule="uniqid"; //time uniqid com_create_guid
	$uploadRs=array();
	if(!$upload->upload()){
		$uploadRs=array("error"=>$upload->getErrorMsg());
	}else{
		$filess=$upload->getUploadFileInfo();
		$uploadRs=array("error"=>'',"msg"=>$filess[0]['savename']);
	}

	return($uploadRs);
}

/**
 * 说明:媒体上传函数
 * @author lerry
 * @date 2012-7-14
 * 默认值
 * $maxSize		默认最大体积 ，			默认 100
 * $allowExts	默认允许上传文件类型	xlsx
 * $savePath	默认保存路径			APP_PATH ."/media/"
 * @return array
 */
function douploadVideo($maxSize,$allowExts='xlsx,xls',$savePath="")
{
	import("ORG.Net.UploadFile");

	if($savePath==""){
		$savePath = WEBSITE_DISK_PATH.'/Public/Images/Tmp/';
	}

	$upload = new UploadFile();

	if(!isset($maxSize))
	{
		$maxSize = C('VIDEO_UPLOAD_MAX_SIZE');
	}
	$upload->maxSize=((int)$maxSize)*1000000 ; // -1 不限制,默认 2G

	if($allowExts != "")
	{
		$upload->allowExts=explode(',',strtolower($allowExts));
	}
	$upload->savePath=$savePath;
	//$upload->thumb=$thumb;
	$upload->saveRule="uniqid"; //time uniqid com_create_guid
	$uploadRs=array();
	if(!$upload->upload()){
		$uploadRs=array("error"=>$upload->getErrorMsg());
	}else{
		$filess=$upload->getUploadFileInfo();
		$uploadRs=array("error"=>'',"msg"=>$filess[0]['savename']);
	}

	return($uploadRs);
}

?>