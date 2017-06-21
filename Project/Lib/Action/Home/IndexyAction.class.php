<?php
class IndexyAction extends HomeAction
{
	public function __construct()
	{
		parent::__construct();
	}

	//查看
	public function yresult()
	{
		$khid = $_REQUEST["khid"]; //试卷id
		$userid = $_REQUEST["userid"];//用户id

		$shijuan = M()->table("y_kaohegroup")->where("id = $khid")->find();
		$errornum = $shijuan["errornum"];

		if($khid && $userid)
		{
			//总分
			//考试结果总分
			$kaoshiResult = M()->table("y_kaohe_userscore")->where("khid={$khid} and userid={$userid}")->find();
			$this->assign("kaoshiResult", $kaoshiResult);
			//dump($kaoshiResult);

			//学员信息
			$userInfo = M()->table("t_user_info_2013")->where("uid=$userid")->find();
			$this->assign("userInfo", $userInfo);
			//dump($userInfo);

			//步骤分
			//1.步骤得分 根据试卷id 用户id 查询步骤分
			$stepscore = M()->table("y_kaohe_base_new as stepdf")
				//->where('khid='.$khid and 'userid='.$userid)
				->where("khid = $khid and userid = $userid")
				->field("stepdf.*")
				->select();
			//dump($stepscore);

			//2.标准步骤分 试卷id 查询任务编号
			$task = M()->table("y_kaohegroup")->where("id = $khid")->find();
			$errornum =  $task["errornum"];
			//查询标准步骤 （根据试卷id 查询任务编号）
			$bzstep = M()->table("y_step_base_new")
				->where("errornum = $errornum")
//				->join("y_task_base ON y_task_base.errornum = y_step_base_new.errornum")
//				->field("y_step_base_new.* ,y_task_base.errorname")
				->select();
			foreach($bzstep as &$v){
				$enum=$v["errornum"];
				$taskbase = M()->table("y_task_base")->where("errornum = $enum")->find();
				$v["errorname"] = $taskbase["errorname"];
			}
			//dump($bzstep);

			//3.将得分放在标准步骤得分数组里面
			foreach($bzstep as $k=>&$v){
				foreach($stepscore as $k2=>$v2){
					if( $v["toolid"] == $v2["toolid"] && $v["objid"] == $v2["objid"] && $v["stateid"] == $v2["stateid"] && $v["roleid"] == 2 && $v2["roleid"] == 2){
						$v["dfscore"] = $v2["score"];
					}
				}
			}

			//dump($bzstep);

			//4.成绩查询结果
			$taskResultArr = array ();
			if ( $bzstep )
			{
				foreach ( $bzstep as $po )
				{
					$taskResultArr[$po['errorname']][] = $po;
				}
			}
			dump($taskResultArr);
			$this->assign("taskResultPo", $taskResultArr);

			$this->display("Indexy:result");
		}
		else
		{
			echo "parameter error";exit;
		}

	}


	//评分
	public function score(){

		$khid = (int)$_REQUEST["khid"]; //试卷id (试卷id对应课程任务编号errornum)
		$userid = (int)$_REQUEST["userid"];//用户id

		$shijuan = M()->table("y_kaohegroup")->where("id = $khid")->find();
		$errornum = $shijuan["errornum"];

		//步骤得分
		$stepscore = M()->table("y_kaohe_base_new as stepdf")
			->where("khid={$khid} and userid={$userid}")
			->field("stepdf.*")
			->select();

		//标准步骤
		$bzstep = M()->table("y_step_base_new")->where("errornum = $errornum")->select();

		//1.给步骤打分
		foreach($stepscore as $k=>&$v){
			$id= $v["id"];
			foreach($bzstep as $k2=>$v2){
				//若三项全部相等 则改步骤分为标准分

//				echo "</br>";
//				print($v['toolid'].":".$v2['toolid'].",".$v['objid'].":".$v2['objid'].",".$v['stateid'].":".$v2['stateid'].",".$v['roleid'].":2");
//				echo "</br>";

				if($v['toolid'] == $v2['toolid'] && $v['objid'] == $v2['objid'] && $v['stateid'] == $v2['stateid'] && $v['roleid'] ==2){
					//1.给步骤打分
					$data['score']=$v2['score']; //步骤得分等于标准分
					break;
				} else{
					$data['score']=0;
				}
			}
			//将得分存入表中
			M()->table("y_kaohe_base_new")
				->where("khid=".$khid." and userid=".$userid." and id = ".$id."")
				->save($data);
		}

		//2.计算总分 查询学员的所有步骤分
		$stepscore2 = M()->table("y_kaohe_base_new as stepdf")
			->where("khid={$khid} and userid={$userid} and stepdf.roleid=2")
			->field("stepdf.*")
			->select();

		$grade = 0 ;//总得分
		//总得分
		foreach($stepscore2 as $step){
			$grade+=$step["score"];
		}
		//总标准分
		$count = M()->table("y_step_base_new")
			->where("errornum = $errornum and roleid=2")
			->count("id");

		//成绩百分比（总得分/总标准分）
		$data3["score"]=round(($grade/$count)*100);
		$data=M()->table("y_kaohe_userscore")->where("khid={$khid} and userid={$userid}")->save($data3); //总分存入成绩表
		//如果评分成功
		if($data){
			$uscore = M()->table("y_kaohe_userscore")->where("khid={$khid} and userid={$userid}")->find();
		}
		$uscore["data"] = $data;
		$this->ajaxReturn($uscore);

	}


	//数据可视化
	public function echart(){

		$khid = $_REQUEST["khid"]; //试卷id
		$userid = $_REQUEST["userid"];//用户id

		//1.步骤得分 根据试卷id 用户id 查询步骤分
		$stepscore = M()->table("y_kaohe_base_new")
			//->where('khid='.$khid and 'userid='.$userid)
			->where("khid = $khid and userid = $userid")
			->field("y_kaohe_base_new.*")
			->select();

		$this->assign("khid",$khid);
		$this->assign("userid",$userid);
		//dump($stepscore);
		//$this->ajaxReturn($stepscore);
		//$this->assign("stepscore", $stepscore);

		$this->display();
		//exit(json_encode($stepscore));
	}

	public function returnEchart(){
		$khid = $_REQUEST["khid"]; //试卷id
		$userid = $_REQUEST["userid"];//用户id

		//1.步骤得分 根据试卷id 用户id 查询步骤分
		$stepscore = M()->table("y_kaohe_base_new")
			//->where('khid='.$khid and 'user='.$userid)
			->where("khid = $khid and userid = $userid")
			->field("y_kaohe_base_new.*")
			->select();


		//$this->ajaxReturn($stepscore);
		echo json_encode($stepscore,true);

		//exit(json_encode($stepscore));

	}

}
?>