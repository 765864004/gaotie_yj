<?php
class AdminManageAction extends AdminAction
{
	private $_adminModel;
	private $_roleModel;
	private $_nodeModel;
	private $_roleAccessModel;
	public function __construct()
	{
		parent::__construct();

		$this->_adminModel = M('MAdmin');
		$this->_roleModel = M('MRole');
		$this->_nodeModel = M('MNode');
		$this->_roleAccessModel = M('MRoleAccess');
	}

	/**
	 * 管理员列表
	 * */
	public function adminList()
	{
		$filter = array();
		if($_REQUEST['status'])
		{
			$status = $_REQUEST['status'];
			$filter['t_m_admin.status'] = $status;
			$this->assign('status', $status);
		}
		$join = array ("INNER JOIN t_m_role role ON role.id = t_m_admin.role_id");
		$adminList = $this->_adminModel->where($filter)->join($join)->field(array('t_m_admin.*','role.name as role_name'))->select();

		$this->assign('list', $adminList);
		//dump($adminList);

		$this->display('AdminManager:adminList');
	}

	/**
	 * 添加管理员
	 * */
	public function addAdmin()
	{
		if($_POST)
		{
			if(M()->autoCheckToken($_POST))
			{
				$data = array();
				$data['name'] = $_REQUEST['username'];
				$data['true_name'] = $_REQUEST['tname'];
				//dump($_REQUEST['password']);exit;
				$data['password'] = md5str($_REQUEST['password']);
				//echo $data['password'];exit;
				$data['role_id'] = $_REQUEST['roleId'];
				$data['status'] = $_REQUEST['status'];
				$data['create_time'] = date('Y-m-d H:i:s', time());

				$newId = $this->_adminModel->add($data);
				if($newId)
				{
					$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'adminlist','callbackType'=>'closeCurrent');
				}
				else
				{
					$array = array('statusCode'=>'300','message'=>"添加失败");
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
			$roleList = $this->_roleModel->select();

			$this->assign("roleList", $roleList);

			$this->display('AdminManager:addAdmin');
		}
	}

	/**
	 * 修改管理员
	 * */
	public function editAdmin()
	{
		if($_POST)
		{
			if(M()->autoCheckToken($_POST))
			{
				$data = array();
				$data['id'] = $_REQUEST['id'];
				$data['name'] = $_REQUEST['username'];
				$data['true_name'] = $_REQUEST['tname'];

				//如果填入密码就修改，不填密码不修改
				if(trim($_REQUEST['password']) != trim($_REQUEST['confirm_password']))
				{
					$array = array('statusCode'=>300,'message'=>"密码不一致");
				}
				else
				{
					if(trim($_REQUEST['password']) == trim($_REQUEST['confirm_password']) && trim($_REQUEST['password']) != "")
					{
						$data['password'] = md5str($_REQUEST['password']);
					}

					$data['role_id'] = $_REQUEST['roleId'];
					$data['status'] = $_REQUEST['status'];

					$result = $this->_adminModel->save($data);

					$array = array('statusCode'=>200,'message'=>"修改成功",'navTabId'=>'adminlist','callbackType'=>'closeCurrent');
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
			$filter['id'] = $id;
			$adminPo = $this->_adminModel->where($filter)->find();

			$roleList = $this->_roleModel->select();

			$this->assign('adminPo', $adminPo);
			$this->assign("roleList", $roleList);

			$this->display('AdminManager:editAdmin');
		}
	}

	/**
	 * 启用禁用删除管理员
	 * */
	public function adminProcess()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];

		if($type && $id)
		{
			$filter = array();
			$filter['id'] = $id;

			if($type == "show")
			{
				$filter['status'] = "Y";
				$this->_adminModel->save($filter);

			}
			else if($type == 'hidden')
			{
				$filter['status'] = "N";
				$this->_adminModel->save($filter);
			}
			else if($type == "del")
			{
				$this->_adminModel->where($filter)->delete();
			}

			$data = array("statusCode"=>"200","message"=>"操作成功");
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
	}


	/**
	 * 角色列表
	 * */
	public function roleList()
	{
		$roleList = $this->_roleModel->select();

		$this->assign('list', $roleList);

		$this->display('AdminManager:roleList');
	}

	/**
	 * 添加角色
	 * */
	public function addRole()
	{
		if($_POST)
		{
			if(M()->autoCheckToken($_POST))
			{
				$data = array();
				$data['name'] = $_REQUEST['name'];
				$data['description'] = $_REQUEST['description'];
				$data['create_user'] = $_SESSION['manager']['trueName'];
				$data['status'] = $_REQUEST['status'];
				$data['create_time'] = date('Y-m-d H:i:s', time());

				$newId = $this->_roleModel->add($data);
				//echo $newId;exit;

				$parentNodeIdArr = array();
				$allParentsIds = array();

				if($newId)
				{
					$nodeIdArr = $_REQUEST['nodeId'];
					if(count($nodeIdArr) > 0)
					{
						foreach($nodeIdArr as $nodeId)
						{
							$nodePo = $this->_nodeModel->where(array('id'=>$nodeId))->find();
							$parentNodeIdArr[] = $nodePo['fid'];

							$dataRoleAccess = array();
							$dataRoleAccess['role_id'] = $newId;
							$dataRoleAccess['node_id'] = $nodeId;

							$this->_roleAccessModel->add($dataRoleAccess);
						}
						//dump($parentNodeIdArr);

						foreach($parentNodeIdArr as $fid)
						{
							$nodePo = $this->_nodeModel->where(array('id'=>$fid))->find();
							$allParentsIds[] = $nodePo['fid'];
							$allParentsIds[] = $fid;
						}

						$allParentsIds = array_unique($allParentsIds);

						//dump($allParentsIds);exit;
						foreach($allParentsIds as $nodeId)
						{
							$dataRoleAccess = array();
							$dataRoleAccess['role_id'] = $newId;
							$dataRoleAccess['node_id'] = $nodeId;

							$this->_roleAccessModel->add($dataRoleAccess);
							//echo $this->_roleAccessModel->getLastSql();echo "<br/>";
						}
						//exit;
					}

					$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'rolelist','callbackType'=>'closeCurrent');
				}
				else
				{
					$array = array('statusCode'=>'300','message'=>"添加失败");
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
			//查询级别为2的
			$moduleList = $this->_nodeModel->where(array('level'=>2))->order('sort asc')->select();

			//查询级别为3的 父级id=级别为2的id
			if(count($moduleList) > 0)
			{
				foreach($moduleList as $key=>$po)
				{
					$actionList = $this->_nodeModel->where(array('level'=>3, 'fid'=>$po['id']))->order('sort asc')->select();
					$moduleList[$key]['actionList'] = $actionList;
				}
			}
			//dump($moduleList);
			$this->assign("moduleList", $moduleList);

			$this->display('AdminManager:addRole');
		}
	}

	/**
	 * 修改角色
	 * */
	public function editRole()
	{
		if($_POST)
		{
			if(M()->autoCheckToken($_POST))
			{
				$data = array();
				$id = $_REQUEST['id'];

				$data['id'] = $id;
				$data['name'] = $_REQUEST['name'];
				$data['description'] = $_REQUEST['description'];
				$data['status'] = $_REQUEST['status'];

				$newId = $this->_roleModel->save($data);

				$selfRoleAccess = $this->_roleAccessModel->where(array('role_id'=>$id))->field("node_id")->select();
				$selfNodeIdArr = array();
				if(count($selfRoleAccess) > 0)
				{
					foreach($selfRoleAccess as $po)
					{
						$selfNodeIdArr[] = $po['node_id'];
					}
				}

				$parentNodeIdArr = array();
				$allParentsIds = array();
				$nodeIdArr = $_REQUEST['nodeId'];
				if(count($nodeIdArr) > 0)
				{
					foreach($nodeIdArr as $nodeId)
					{
						$nodePo = $this->_nodeModel->where(array('id'=>$nodeId))->find();
						$parentNodeIdArr[] = $nodePo['fid'];
					}
					//dump($parentNodeIdArr);

					foreach($parentNodeIdArr as $fid)
					{
						$nodePo = $this->_nodeModel->where(array('id'=>$fid))->find();
						$allParentsIds[] = $nodePo['fid'];
						$allParentsIds[] = $fid;
					}

					$allParentsIds = array_unique($allParentsIds);
				}

				$nodeIdArr = array_merge($nodeIdArr, $allParentsIds);

				//得到新增加的nodeid
				$newIdArr = array_diff($nodeIdArr, $selfNodeIdArr);
				//删掉的nodeid
				$delIdArr = array_diff($selfNodeIdArr, $nodeIdArr);

				//dump($newIdArr);dump($delIdArr);exit;

				if(count($newIdArr) > 0)
				{
					foreach($newIdArr as $nodeId)
					{
						$dataRoleAccess = array();
						$dataRoleAccess['role_id'] = $id;
						$dataRoleAccess['node_id'] = $nodeId;

						$this->_roleAccessModel->add($dataRoleAccess);
					}
				}

				if(count($delIdArr) > 0)
				{
					$this->_roleAccessModel->where(array('role_id'=>$id, 'node_id'=>array('in', $delIdArr)))->delete();
					//echo $this->_roleAccessModel->getLastSql();exit;
				}

				$array = array('statusCode'=>'200','message'=>"修改成功",'navTabId'=>'','callbackType'=>'closeCurrent');
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
			$moduleList = $this->_nodeModel->where(array('level'=>2))->order('sort asc')->select();

			if(count($moduleList) > 0)
			{
				foreach($moduleList as $key=>$po)
				{
					$actionList = $this->_nodeModel->where(array('level'=>3, 'fid'=>$po['id']))->order('sort asc')->select();
					$moduleList[$key]['actionList'] = $actionList;
				}
			}

			//得到角色信息
			$id = $_REQUEST['id'];
			$rolePo = $this->_roleModel->where(array('id'=>$id))->find();

			//得到角色的权限信息
			$roleAccessNodeIdArr = $this->_roleAccessModel->where(array('role_id'=>$id))->field('node_id as nid')->select();
			$roleNodeIdArr = array();
			if(count($roleAccessNodeIdArr) > 0)
			{
				foreach($roleAccessNodeIdArr as $v)
				{
					$roleNodeIdArr[] = $v['nid'];
				}
			}

			$this->assign('rolePo', $rolePo);
			$this->assign('roleNodeIdArr', $roleNodeIdArr);
			$this->assign("moduleList", $moduleList);

			$this->display('AdminManager:editRole');
		}
	}

	/**
	 * 启用禁用删除角色
	 * */
	public function roleProcess()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];

		if($type && $id)
		{
			$filter = array();
			$filter['id'] = $id;

			if($type == "show")
			{
				$filter['status'] = "Y";
				$this->_roleModel->save($filter);

			}
			else if($type == 'hidden')
			{
				$filter['status'] = "N";
				$this->_roleModel->save($filter);
			}
			else if($type == "del")
			{
				$isAdminUseRole = $this->_adminModel->where(array('role_id'=>$id))->select();
				if(count($isAdminUseRole) > 0)
				{
					$data = array("statusCode"=>"300","message"=>"有管理员使用该角色，先删除管理员或者解除绑定后在删除该角色");
					echo json_encode($data);
					exit;
				}
				else
				{
					$this->_roleAccessModel->where(array('role_id'=>$id))->delete();
					$this->_roleModel->where(array('id'=>$id))->delete();
				}
			}
			$data = array("statusCode"=>"200","message"=>"操作成功",'navTabId'=>'rolelist');
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
	}

	/*********************************学生管理****************************************/
	public function userList()
	{
		$currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
		$pageSize = empty($_REQUEST['pageSize']) ? 200 : $_REQUEST['pageSize'];

		//条件过滤
		$filter = array();
		$uname = trim($_REQUEST['uname']);//账号
		if(!empty($uname))
		{
			$filter['uname'] = array('like', '%'.$uname.'%');
			$this->assign('uname', $uname);
		}

		$truename = trim($_REQUEST['truename']);//真实姓名
		if(!empty($truename))
		{
			$filter['truename'] = array('like', '%'.$truename.'%');
			$this->assign('truename', $truename);
		}

		$usex = trim($_REQUEST['usex']);//性别
		if($usex === '1' || $usex === '0')
		{
			$filter['usex'] = array('eq', $usex);
			$this->assign('usex', $usex);
		}

		$card = trim($_REQUEST['card']);//身份证
		if(!empty($card))
		{
			$filter['card'] = array('eq', $card);
			$this->assign('card', $card);
		}

		$departid = trim($_REQUEST['departid']);//部门
		if(!empty($departid))
		{
			$filter['departid'] = array('eq', $departid);
			$this->assign('departid', $departid);
		}

		//条件排序
		$order = array();
		if($_REQUEST['_order'] && $_REQUEST['_sort'])
		{
			$order[$_REQUEST['_order']] =  $_REQUEST['_sort'];
		}
		else
		{
			$order['uid'] = 'desc';
		}


		$totalCount = $this->_userInfoModel->where($filter)->count();
		$list = $this->_userInfoModel->where($filter)->order($order)->page($currentPage)->limit($pageSize)->select();

		$departList = $this->_departModelModel->select();
		if($departList)
		{
			$departArr = array();
			foreach($departList as $departPo)
			{
				$departArr[$departPo['id']] = $departPo['name'];
			}
		}
		$this->assign("departArr", $departArr);

		$this->assign('totalCount', $totalCount);
		$this->assign('list', $list);

		$this->assign('currentPage', $currentPage);
		$this->assign('pageSize', $pageSize);

		//得到所有的部门
		$departList = $this->_departModelModel->select();
		$this->assign("departList", $departList);

		$this->display('AdminManager:userList');
	}

	//添加学生
	public function userAdd()
	{
		if($_POST)
		{
			extract($_POST);

			$data = array();
			$data['uname'] = trim($uname);
			$data['truename'] = trim($truename);
			$data['usex'] = trim($usex);
			$data['card'] = trim($card);
			$data['upwd'] = md5str(trim($password));
			$data['departid'] = trim($departid);
			$data['regtime'] = time();
			$data['state'] = 1;

			//检查该部门是否存在，存在的话就不在添加，提示错误
			$isDepartExists = $this->_userInfoModel->where("uname = '".trim($uname)."'")->find();
			if($isDepartExists)
			{
				$array = array('statusCode'=>'300','message'=>"该学员用户名已经存在，请重新输入");
			}
			else
			{
				//检查身份证号是否已使用
				$isUserCardUsed = $this->_userInfoModel->where("card = '".$data['card']."'")->select();
				if($isUserCardUsed)
				{
					//$this->ajaxReturn("该身份证号已经使用，请重新输入", 0, 0);
					$array = array('statusCode'=>'300','message'=>"该身份证号已经使用，请重新输入");
				}
				else
				{
					$newId = $this->_userInfoModel->add($data);

					if($newId)
					{
						$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'userList','callbackType'=>'closeCurrent');
					}
					else
					{
						$array = array('statusCode'=>'300','message'=>"添加失败");
					}
				}
			}
			echo json_encode($array);
			exit();
		}
		else
		{
			//得到所有的部门
			$departList = $this->_departModelModel->select();
			$this->assign("departList", $departList);

			$this->display('AdminManager:userAdd');
		}
	}

	/**
	 * 导入excel学生信息
	 * @data:2013-09-02
	 * */
	public function userPlExcel()
	{
		if ($_REQUEST['submit'] == 1)
		{
			$fileName = $_REQUEST['imageContent'];
			$file = WEBSITE_DISK_PATH.'/Public/Images/Tmp/'.$fileName;
			//dump($file);
			vendor("PHPExcel.PHPExcel");

			$PHPExcel = new PHPExcel();

			//读取2007格式的Excel
			$PHPReader = new PHPExcel_Reader_Excel2007();
			//为了从上向下兼容，先判断是否为2007的，再判断是否为非2007的
			if (!$PHPReader->canRead($file))
			{
				//echo "222";exit;
			 	//非2007格式的Excel
			 	$PHPReader = new PHPExcel_Reader_Excel5();
			 	//判断是否为正确的Excel文件
			 	if (!$PHPReader->canRead($file))
			 	{
			 		$array = array('statusCode'=>'300','message'=>"请上传正确的excel文件",'navTabId'=>'','callbackType'=>'');
			 		echo json_encode($array);
					exit();
			 	}
			}
			//echo "333";exit;
			//filepath Excel文件路径
			$PHPExcel = $PHPReader->load($file);
			//dump($PHPExcel);
			//转换为数组方便读取
			$arr = $PHPExcel->getSheet(0)->toArray();

			if (count($arr) <= 1)
			{
				$array = array('statusCode'=>'300','message'=>"文件内容为空",'navTabId'=>'','callbackType'=>'');
				echo json_encode($array);
				exit();
			}
			else
			{
				//得到所有的部门
				$departList = $this->_departModelModel->select();
				if (!$departList)
				{
					$array = array('statusCode'=>'300','message'=>"系统表中没有部门信息",'navTabId'=>'','callbackType'=>'');
					echo json_encode($array);
					exit();
				}
				//dump(count($arr));exit;
				$addCount = 0;
				for ($i = 1; $i < count($arr); $i++)
				{
					//插入用户信息到用户表中
					$tmpUserArr = $arr[$i];

					$data = array();
					$data['uname'] = trim($tmpUserArr[0]);;
					$data['truename'] = trim($tmpUserArr[1]);
					if (trim($tmpUserArr[2]) == "男")
					{
						$data['usex'] = 0;
					}
					else
					{
						$data['usex'] = 1;
					}
					$data['card'] = trim($tmpUserArr[3]);
					$data['upwd'] = md5str("123456");

					foreach ($departList as $po)
					{
						if(trim($tmpUserArr[4]) == $po['name'])
						{
							$data['departid'] = $po['id'];
							break;
						}
					}

					$data['regtime'] = time();
					$data['state'] = 1;

					$newId = $this->_userInfoModel->add($data);

					if($newId)
					{
						$addCount++;
					}
				}

				$array = array('statusCode'=>'200', 'message'=>"添加成功,成功添加{$addCount}位学员信息", 'navTabId'=>'userList', 'callbackType'=>'closeCurrent');
				echo json_encode($array);
				exit();
			}
		}
		else
		{
			$this->display('AdminManager:userPlExcel');
		}
	}

	/**
	 * 用户ajax上传图片验证
	 * */
	function ajaxDouploadPic()
	{
		$uploadRs=douploadVideo();

		echo json_encode($uploadRs);
		exit;
		//echo $uploadRs['msg'];
	}

	//编辑学生信息
	public function userEdit()
	{
		if ($_POST)
		{
			extract($_POST);

			$data = array();
			$data['uname'] = trim($uname);
			$data['truename'] = trim($truename);
			$data['usex'] = trim($usex);
			$data['card'] = trim($card);

			if (trim($password) != trim($confirm_password))
			{
				$array = array('statusCode'=>300,'message'=>"密码不一致");
			}
			else
			{
				if (trim($password) == trim($confirm_password) && trim($password) != "")
				{
					$data['password'] = md5str($password);
				}
				$data['departid'] = trim($departid);
				//$data['state'] = $state;

				//检查该学生是否存在，存在的话就不在添加，提示错误
				$isDepartExists = $this->_userInfoModel->where("uid != $id and uname = '".trim($uname)."'")->find();
				if ($isDepartExists)
				{
					$array = array('statusCode'=>'300','message'=>"该学员用户名已经存在，请重新输入");
				}
				else
				{
					$uid = $_REQUEST['id'];
					$newId = $this->_userInfoModel->where("uid = $id")->save($data);

					if($newId)
					{
						$array = array('statusCode'=>'200','message'=>"修改成功",'navTabId'=>'','callbackType'=>'closeCurrent');
					}
					else
					{
						$array = array('statusCode'=>'300','message'=>"修改失败");
					}
				}
				echo json_encode($array);
				exit();
			}
		}
		else
		{
			$id = $_REQUEST['id'];

			$stuInfo = $this->_userInfoModel->where("uid = $id")->find();
			$this->assign("stuInfo", $stuInfo);

			//得到所有的部门
			$departList = $this->_departModelModel->select();
			$this->assign("departList", $departList);

			$this->display('AdminManager:userEdit');
		}
	}

	//删除学生，永久删除
	public function userDel()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];

		if ($type && $id)
		{
			$filter = array();
			$filter['uid'] = $id;

			if ($type == "del")
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
		exit;
	}

	/***********************************班级管理*******************************************/
	public function departList()
	{
		$currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
		$pageSize = empty($_REQUEST['pageSize']) ? 200 : $_REQUEST['pageSize'];

		//条件过滤
		$filter = array();
		$name = trim($_REQUEST['name']);
		if (!empty($name))
		{
			$filter['name'] = array('like', '%'.$name.'%');
			$this->assign('name', $name);
		}

		//条件排序
		$order = array();
		if ($_REQUEST['_order'] && $_REQUEST['_sort'])
		{
			$order[$_REQUEST['_order']] =  $_REQUEST['_sort'];
		}
		else
		{
			$order['id'] = 'desc';
		}

		$totalCount = $this->_departModelModel->where($filter)->count();
		$list = $this->_departModelModel->where($filter)->order($order)->page($currentPage)->limit($pageSize)->select();

		$this->assign('totalCount', $totalCount);
		$this->assign('list', $list);

		$this->assign('currentPage', $currentPage);
		$this->assign('pageSize', $pageSize);

		$this->display('AdminManager:departList');
	}

	//添加部门
	public function departAdd()
	{
		if ($_POST)
		{
			extract($_POST);

			$data = array();
			$data['name'] = trim($name);
			$data['description'] = trim($description);

			//检查该部门是否存在，存在的话就不在添加，提示错误
			$isDepartExists = $this->_departModelModel->where("name='".$name."'")->find();
			if ($isDepartExists)
			{
				$array = array('statusCode'=>'300','message'=>"该部门已经存在，请输输入别的名称");
			}
			else
			{
				$newId = $this->_departModelModel->add($data);

				if ($newId)
				{
					$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'departlist','callbackType'=>'closeCurrent');
				}
				else
				{
					$array = array('statusCode'=>'300','message'=>"添加失败");
				}
			}
			echo json_encode($array);
			exit();
		}
		else
		{
			$this->display('AdminManager:departAdd');
		}
	}

	//修改部门
	public function departEdit()
	{
		if ($_POST)
		{
			extract($_POST);

			$data = array();
			$data['name'] = trim($name);
			$data['description'] = trim($description);

			$filter = array();
			$filter['id'] = $_REQUEST['id'];

			$isDepartExists = $this->_departModelModel->where("id !=".$id." and name='".$name."'")->select();

			if ($isDepartExists)
			{
				$array = array('statusCode'=>300,'message'=>"该部门已经存在，请重新输入");
			}
			else
			{
				$result = $this->_departModelModel->where($filter)->save($data);

				$array = array('statusCode'=>200,'message'=>"修改成功",'navTabId'=>'','callbackType'=>'closeCurrent');
			}

			echo json_encode($array);
			exit();
		}
		else
		{
			$id = $_REQUEST['id'];

			$filter = array();
			$filter['id'] = $id;
			$departPo = $this->_departModelModel->where($filter)->find();

			$this->assign('po', $departPo);

			$this->display('AdminManager:departEdit');
		}
	}

	//删除部门
	public function departDel()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];

		if ($type && $id)
		{
			$filter = array();
			$filter['id'] = $id;

			if($type == "del")
			{
				$this->_departModelModel->where($filter)->delete();
			}

			$data = array("statusCode"=>"200","message"=>"操作成功");
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
		exit;
	}
}
?>