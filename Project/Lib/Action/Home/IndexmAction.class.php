<?php
class IndexmAction extends HomeAction
{
	public function __construct()
	{
		parent::__construct();
	}

	//查看
	public function mresult()
	{
		//$kstime = (int)$_REQUEST["kstime"]; //考试时间
		$uid = (int)$_REQUEST["userid"]; //用户id
		$pageid = $_REQUEST['pageid']; //试卷id

		$task = M()->table("m_task_page_list")->where("pageid = $pageid")->find();
		$taskid= $task["taskid"]; //任务编号
		
		if($uid && $pageid)
		{
			//考试结果总分
			$kaoshiResult = M()->table("m_score")->where(" userid={$uid} and pageid={$pageid} ")->find();
			$this->assign("kaoshiResult", $kaoshiResult);
			//echo M()->table("m_score")->getLastSql();

			//用户信息
			$userInfo = M()->table("t_user_info_2013")->where("uid=$uid")->find();
			$this->assign("userInfo", $userInfo);

			//试卷信息
			$page = M()->table("m_page")->where("id = $pageid")->find();
			$this->assign("page",$page);

			//步骤得分
			$filter = array();
			//$filter['kstime'] = array("eq", $kstime);
			$filter['userid'] = $uid;
			$filter['pageid'] = $pageid;
			$taskStepScore = M()->table("m_step_score")->where($filter)->select();

			//步骤标准分
			$bztask = M()->table("m_task_step")->where("taskid = $taskid")->select();


			if($taskStepScore && $bztask){
				//标准分放入步骤得分
				foreach($taskStepScore as $k=>&$v){
					foreach($bztask as $k2=>$v2){
						if($v["stepid"] == $v2["stepid"]){
							$v["bzscore"] = $v2["score"]; //标准分
							$v["taskname"] = $v2["taskname"]; //任务名称
							$v["stepname"] = $v2["stepname"]; //步骤编号
						}
					}
				}
			}


			$taskResultArr = array ();
			if ( $taskStepScore )
			{
				foreach ( $taskStepScore as $po )
				{
					$taskResultArr[$po['taskname']][] = $po;
				}
			}
			//dump($taskResultArr);
			$this->assign("taskResultPo", $taskResultArr);

			$this->display("Indexm:result");
		}
		else
		{
			echo "parameter error";exit;
		}
	}

}
?>