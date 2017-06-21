<?php
class IndexAction extends HomeAction
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function result()
	{
		$kstime = (int)$_REQUEST["kstime"]; //考试时间
		$uid = (int)$_REQUEST["uid"];		//用户id
		
		if($kstime && $uid)
		{
			//考试结果总分
			$kaoshiResult = M()->table("t_kaoshi_2013")->where("kstime={$kstime} and uid={$uid}")->find();
			$this->assign("kaoshiResult", $kaoshiResult);

			$userInfo = M()->table("t_user_info_2013")->where("uid=$uid")->find();
			$this->assign("userInfo", $userInfo);
			
			//工具总得分
			$filter = array();
			$filter['kstime'] = array("eq", $kstime);
			$filter['uid'] = array("eq", $uid);
			$taskToolResultPo = M()->table("t_task_tool_result_2013")->where($filter)->find();
			$this->assign("taskToolResultPo", $taskToolResultPo);

			//操作答题得分
// 			$filter1 = array();
// 			$filter1['result.kstime'] = array("eq", $kstime);
// 			$filter1['result.uid'] = array("eq", $uid);
// 			$filter1['result.taskid'] = array("neq", 0);
// 			$taskResultPo = M()->table("t_task_result_2013 as result")->join("t_task_base as tb on result.taskid=tb.autoid")->where($filter)->field("tb.taskname as taskname,result.defen1 as defen1,result.defen2 as defen2,tb.autoid")->order("tb.autoid desc")->select();
// 			$this->assign("taskResultPo", $taskResultPo);
			
			$sql = "SELECT unit.unitname, tb.taskname AS taskname,result.defen1 AS defen1,result.defen2 AS defen2 
					FROM t_task_result_2013 AS result 
					INNER JOIN t_task_base AS tb ON result.taskid=tb.autoid AND ( result.kstime = $kstime ) AND ( result.uid = $uid ) AND result.taskid > 0  
					INNER JOIN t_unit AS unit ON unit.id = result.unitid ORDER BY unitid ASC, tb.autoid ASC";
			$taskResultPo = M()->query($sql);//dump($taskResultPo);exit;

			//dump($taskResultPo);
			
			$taskResultArr = array ();
			if ( $taskResultPo )
			{
				foreach ( $taskResultPo as $po )
				{
					$taskResultArr[$po['unitname']][] = $po;
				}
			}
			$this->assign("taskResultPo", $taskResultArr);
			//dump($taskResultArr);
			
			//dump($kaoshiResult);dump($userInfo);dump($taskToolResultPo);dump($taskResultPo);exit;
			$this->display("Index:result");
		}
		else
		{
			echo "parameter error";exit;
		}
	}
	
	/**
	 * 学生注册  用户名 密码  身份证 密码 性别  部门
	 * */
	public function reg()
	{
		if($_REQUEST['submit'] == 1)
		{
			$data['uname'] = trim($_REQUEST['uname']);
			$data['truename'] = trim($_REQUEST['truename']);
			$data['usex'] = trim($_REQUEST['usex']);
			$data['card'] = trim($_REQUEST['card']);
			$data['departid'] = trim($_REQUEST['depardid']);
			$data['upwd'] = md5str(trim($_REQUEST['pass']));
			$data['regtime'] = time();
			
			//检查用户名是否已经使用
			$isUserExists = $this->_userInfoModel->where("uname = '".$data['uname']."'")->select();
			if($isUserExists)
			{
				$this->ajaxReturn("该用户名已经使用，请更换用户名注册", 0, 0);
			}
			else
			{
				//检查身份证号是否已使用
				$isUserCardUsed = $this->_userInfoModel->where("card = '".$data['card']."'")->select();
				if($isUserCardUsed)
				{
					$this->ajaxReturn("该身份证号已经使用，请重新输入", 0, 0);
				}
				else
				{
					$newId = $this->_userInfoModel->add($data);
					if($newId)
					{
						$this->ajaxReturn("注册成功", 1, 1);
					}
					else 
					{
						$this->ajaxReturn("数据库繁忙，请重试", 0, 0);
					}
				}				
			}
		}
		else
		{
			//部门列表
			$bumenList = $this->_departModelModel->select();
			$this->assign("departList", $bumenList);
			
			$this->display("Index:reg");
		}
		
	}
	
	/**
	 * 找回密码  通过用户名和身份证找回密码
	 * */
	public function findPassword()
	{
		if($_REQUEST['submit'] == 1)
		{
			$data['uname'] = trim($_REQUEST['uname']);
			$data['card'] = trim($_REQUEST['card']);
			$data['upwd'] = md5str(trim($_REQUEST['pass']));
		
			//检查用户名是否存在
			$isUserExists = $this->_userInfoModel->where("uname = '".$data['uname']."'")->find();
			if($isUserExists)
			{
				//检查用户名和身份证号是否一致
				if($isUserExists['card'] == $data['card'])
				{
					if($data['pass'] == $isUserExists['upwd'])
					{
						$this->ajaxReturn("密码找回成功!", 1, 1);
					}
					else
					{
						$userId = $isUserExists['uid'];
						$result = $this->_userInfoModel->where("uid = $userId")->save($data);
						if($result)
						{
							$this->ajaxReturn("密码找回成功!", 1, 1);
						}
						else
						{
							$this->ajaxReturn("系统繁忙，请稍后再试", 0, 0);
						}
					}
				}
				else 
				{
					$this->ajaxReturn("该用户名和身份证号不一致，请重新输入", 0, 0);
				}
			}
			else
			{
				$this->ajaxReturn("该用户不存在，请重新输入", 0, 0);
			}
		}
		else
		{
			$this->display("Index:findPassword");
		}
	}
	
	/**
	 * 查询成绩
	 * */
	public function serach()
	{
		$this->display("Index:serach");
	}
}
?>