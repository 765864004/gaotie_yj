<?php
class UserManageAction extends AdminAction
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 学员列表
	 * */
	public function userList()
	{
		$currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
		$pageSize = empty($_REQUEST['pageSize']) ? 25 : $_REQUEST['pageSize'];

		//条件过滤
		$filter = array();

		//条件排序
		$order = array();
		if(!empty($_REQUEST['state']))
		{
			$filter['state'] =  $_REQUEST['state'];
			$this->assign('state', $_REQUEST['state']);
		}

		//条件排序
		$order = array();
		if($_REQUEST['_order'] && $_REQUEST['_sort'])
		{
			$order[$_REQUEST['_order']] =  $_REQUEST['_sort'];
		}
		else
		{
			$order['uid'] = ' desc';
		}


		$totalCount = $this->_userInfoModel->where($filter)->count();
		$list = $this->_userInfoModel->where($filter)->order($order)->page($currentPage)->limit($pageSize)->select();

		$this->assign('totalCount', $totalCount);
		$this->assign('list', $list);

		$this->assign('currentPage', $currentPage);
		$this->assign('pageSize', $pageSize);

		$this->display('UserManager:userList');
	}

	/**
	 * 添加管理员
	 * */
	public function addUser()
	{
		if($_POST)
		{
			if(M()->autoCheckToken($_POST))
			{
				$data = array();
				$data['uname'] = trim($_REQUEST['uname']);
				$data['stuid'] = trim($_REQUEST['stuid']);
				//$data['upwd'] = md5str($_REQUEST['upwd']);
				$data['upwd'] = trim($_REQUEST['upwd']);
				$data['state'] = $_REQUEST['state'];
				$data['usex'] = $_REQUEST['usex'];
				//$data['kspage'] = $_REQUEST['kspage'];
				$data['job'] = $_REQUEST['job'];
				$data['regtime'] = time();
				
				//检查名字是否重复
				$filter = array();
				$filter["uname"] = trim($_REQUEST['uname']);
				
				$isUserExists = $this->_userInfoModel->where($filter)->select();
				
				if(count($isUserExists) > 0)
				{
					$array = array('statusCode'=>'300','message'=>"该学员已经存在");
				}
				else
				{
					$newId = $this->_userInfoModel->add($data);
					if($newId)
					{
						$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'userlist','callbackType'=>'closeCurrent');
					}
					else
					{
						$array = array('statusCode'=>'300','message'=>"添加失败");
					}
				}
			}
			else
			{
				$array = array('statusCode'=>'300','message'=>"验证失败");
			}

			echo json_encode($array);
			exit();
		}
		else
		{
			$this->display('UserManager:addUser');
		}
	}

	/**
	 * 修改管理员
	 * */
	public function editUser()
	{
		if($_POST)
		{
			if(M()->autoCheckToken($_POST))
			{
				$data = array();
				$data['uname'] = trim($_REQUEST['uname']);
				$data['stuid'] = trim($_REQUEST['stuid']);
				$data['usex'] = $_REQUEST['usex'];
				$data['state'] = $_REQUEST['state'];
				//$data['kspage'] = $_REQUEST['kspage'];
				$data['job'] = $_REQUEST['job'];

				//如果填入密码就修改，不填密码不修改
				if(trim($_REQUEST['upwd']) != trim($_REQUEST['rupwd']))
				{
					$array = array('statusCode'=>300,'message'=>"密码不一致");
				}
				else
				{
					if(trim($_REQUEST['upwd']) == trim($_REQUEST['rupwd']) && trim($_REQUEST['upwd']) != "")
					{
						$data['upwd'] = $_REQUEST['upwd'];
						//$data['upwd'] = md5str($_REQUEST['upwd']);
					}

					$filter = array();
					$filter['uid'] = $_REQUEST['id'];

					$result = $this->_userInfoModel->where($filter)->save($data);

					if($result)
					{
						$array = array('statusCode'=>200,'message'=>"修改成功",'navTabId'=>'userlist','callbackType'=>'closeCurrent');
					}
					else
					{
						$array = array('statusCode'=>300,'message'=>"修改失败",'navTabId'=>'userlist','callbackType'=>'closeCurrent');
					}
				}
			}
			else
			{
				$array = array('statusCode'=>'300','message'=>"验证失败");
			}
			echo json_encode($array);
			exit();
		}
		else
		{
			$id = $_REQUEST['id'];

			$filter = array();
			$filter['uid'] = $id;
			$userPo = $this->_userInfoModel->where($filter)->find();

			$this->assign('Po', $userPo);

			$this->display('UserManager:editUser');
		}
	}

	/**
	 * 启用禁用删除管理员
	 * */
	public function userProcess()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];

		if($type && $id)
		{
			$filter = array();
			$filter['uid'] = $id;

			if($type == "show")
			{
				$filter['state'] = "1";
				$this->_userInfoModel->save($filter);

			}
			else if($type == 'hidden')
			{
				$filter['state'] = "0";
				$this->_userInfoModel->save($filter);
			}
			else if($type == "del")
			{
				$this->_userInfoModel->where($filter)->delete();
			}

			$data = array("statusCode"=>"200","message"=>"操作成功");
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
	}

}
?>