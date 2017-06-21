<?php
class TaskManageAction extends AdminAction
{
	public function __construct()
	{
		parent::__construct();
	}
	
	/*******************************************检修任务管理*********************************************************/
	/**
	 * 检修任务列表
	 * */
	public function taskList()
	{
		$currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
		$pageSize = empty($_REQUEST['pageSize']) ? 200 : $_REQUEST['pageSize'];
		
		//条件过滤
		$filter = array();
		$taskname = trim($_REQUEST['taskname']);
		if(!empty($taskname))
		{
			$filter['taskname'] = array('like', '%'.$taskname.'%');
			$this->assign('taskname', $taskname);
			
			$strFilter = " and tb.taskname like '%".$taskname."%'";
		}

		//条件排序
		$order = array();
		if($_REQUEST['_order'] && $_REQUEST['_sort'])
		{
			$order[$_REQUEST['_order']] =  $_REQUEST['_sort'];
		}
		else
		{
			$order['autoid'] = ' desc';
		}

		$totalCount = M()->table("t_task_base as tb")->where($filter)->count();
		$startNum = ($currentPage-1)*$pageSize;
		$sql = "select top $pageSize tb.*, t.tipname as name1,u.unitname as name2,tm.tipname as name3 from t_task_base as tb ,t_position_mod as tm,t_unit as u,t_tool as t where t.id=tb.tool and u.id=tb.unit and tm.id=tb.modelid $strFilter
and tb.autoid not in ( select top $startNum tb1.autoid from t_task_base as tb1 ,t_position_mod as tm,t_unit as u,t_tool as t where t.id=tb1.tool and u.id=tb1.unit and tm.id=tb1.modelid $strFilter order by tb1.autoid desc ) order by tb.autoid desc";
		
		$list = M()->query($sql);

		$this->assign('totalCount', $totalCount);
		$this->assign('list', $list);
		
		$this->assign('currentPage', $currentPage);
		$this->assign('pageSize', $pageSize);

		$this->display('TaskManager:taskList');
	}

	/**
	 * 添加检修任务
	 * */
	public function taskAdd()
	{
		if($_POST)
		{
			extract($_POST);

			$data = array();
			$data['CRH'] = trim($CRH);				//车型
			$data['level'] = trim($level);			// 级别 值 1一级检修 2二级检修 4应急故障
			$data['area'] = trim($area);			//任务分类1 部位  1车头  2车身 4车外 8车顶 16车底
			$data['unit'] = trim($unit);			//部件
			$data['childid'] = trim($childid);		//任务内部子任务序号
			
			$data['opttype'] = $opttype;
			$data['taskname'] = trim($taskname);	//任务名称
			
			$data['modelid'] = trim($unitid);		// 热点编号 t_position_mod外键
			$data['txttip'] = trim($txttip);		//点击检修后信息栏内容
			
			$data['txtkstip'] = trim($txtkstip);
			
			$data['helptxt'] = trim($helptxt);
		
			$data['tnum'] = trim($tnum);			//作业框中选择的数量
			
			if($tnum == 0)
			{
				$array = array('statusCode'=>'300','message'=>"没有选择作业框的数量");
			}
			else 
			{
				for($i = 1; $i <= $tnum; $i++)
				{
					$txtname = "txt".$i;
					$picname = "pic".$i;
					$retstate = "retstate".$i;
					
					$data[$txtname] =  trim($$txtname);			//答案
					$data[$picname] =  trim($$picname);			//贴图
					$data[$retstate] =  trim($$retstate);		//属性 0：正常 1：故障 3：干扰
				}

				$data['tool'] = trim($tool);
				
				$data['material'] = trim($material);			// add 20130902
				
// 				$data['aniname'] = trim($aniname);
				
// 				$data['time1'] = trim($time1);
// 				$data['time2'] = trim($time2);
				$data['pow'] = trim($pow);
				$data['hold1'] = trim($hold1);
				$data['hold2'] = trim($hold2);
				
				$newId = $this->_taskbaseModel->add($data);
				
				if($newId)
				{
					$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'tasklist','callbackType'=>'closeCurrent');
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
			//CRH1车头部件列表
			$unitList = $this->_unitModel->where("CRH & 1 = 1 and area & 1 = 1")->order("id asc")->select();
			$this->assign('unitList', $unitList);
			
			//工具列表
			$toolList = $this->_toolModel->order("id asc")->select();
			$this->assign('toollist', $toolList);
			
			//redian列表
			$materialList = $this->_materialModel->order("id asc")->select();
			$this->assign("materialList", $materialList);
			

			$this->display('TaskManager:taskAdd');
		}
	}

	/**
	 * 修改检修任务
	 * */
	public function taskEdit()
	{
		if($_POST)
		{
			extract($_POST);

			$data = array();
			$data['CRH'] = trim($CRH);				//车型
			$data['level'] = trim($level);			// 级别 值 1一级检修 2二级检修 4应急故障 8巡检
			$data['area'] = trim($area);			//任务分类1 部位  1车头  2车身 4车外 8车顶 16车底
			$data['unit'] = trim($unit);			//部件
			$data['childid'] = trim($childid);		//任务内部子任务序号
			
			$data['opttype'] = $opttype;
			$data['taskname'] = trim($taskname);	//任务名称
			
			$data['modelid'] = trim($unitid);		// 热点编号 t_position_mod外键
			$data['txttip'] = trim($txttip);		//点击检修后信息栏内容
			
			$data['txtkstip'] = trim($txtkstip);
			
			$data['helptxt'] = trim($helptxt);
		
			$data['tnum'] = trim($tnum);			//作业框中选择的数量
			
			if($tnum == 0)
			{
				$array = array('statusCode'=>'300','message'=>"没有选择作业框的数量");
			}
			else 
			{
				for($i = 1; $i <= $tnum; $i++)
				{
					$txtname = "txt".$i;
					$picname = "pic".$i;
					$retstate = "retstate".$i;
					
					$data[$txtname] =  trim($$txtname);			//答案
					$data[$picname] =  trim($$picname);			//贴图
					$data[$retstate] =  trim($$retstate);		//属性 0：正常 1：故障 3：干扰
				}

				$data['tool'] = trim($tool);
				
				$data['material'] = trim($material);			// add 20130902
				
				$data['pow'] = trim($pow);
				$data['hold1'] = trim($hold1);
				$data['hold2'] = trim($hold2);
				
				$filter = array();
				$filter['autoid'] = $_REQUEST['id'];
				
				$result = $this->_taskbaseModel->where($filter)->save($data);
				
				if($result)
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
		else
		{
			$id = $_REQUEST['id'];

			$filter = array();
			$filter['autoid'] = $id;
			$taskPo = $this->_taskbaseModel->where($filter)->find();
			$this->assign('taskPo', $taskPo);
			
			//工具列表
			$toolList = $this->_toolModel->order("id asc")->select();
			$this->assign('toollist', $toolList);
			
			//查找车型 部位下面的所有部件
			$unitList = $this->_unitModel->where("area & $taskPo[area] = $taskPo[area] and CRH & $taskPo[CRH] = $taskPo[CRH]")->order("id asc")->select();
			$this->assign('unitList', $unitList);
			
			//查找部件下面的所有热点
			$redianList = $this->_positionModel->where("unitid = $taskPo[unit]")->order("id asc")->select();
			$this->assign('redianList', $redianList);
			
			//redian列表
			$materialList = $this->_materialModel->order("id asc")->select();
			$this->assign("materialList", $materialList);
			
			$this->display('TaskManager:taskEdit');
		}
	}

	/**
	 * 启用禁用删除检修任务
	 * */
	public function taskProcess()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];

		if($type && $id)
		{
			$filter = array();
			$filter['autoid'] = $id;

			if($type == "del")
			{
				$this->_taskbaseModel->where($filter)->delete();
			}

			$data = array("statusCode"=>"200","message"=>"操作成功");
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
	}
	/*
	id		int		NOT NULL PRIMARY KEY,			-- 数字编号
	picname		char(128)	not null,				-- 图片名称(允许字母数字下划线)
	tipname		char(128)	,					-- 提示信息(汉字名称)
	usetype		int		not null default 0,			-- 用途（bit位 0一级检修 1二级检修 2应急故障 3巡检）
	*/
	/*******************************************检修答案*********************************************************/
	/**
	 * 检修答案列表
	 * */
	public function educationList()
	{
		$currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
		$pageSize = empty($_REQUEST['pageSize']) ? 200 : $_REQUEST['pageSize'];

		//条件过滤
		$filter = array();
		$taskname = trim($_REQUEST['taskname']);
		if(!empty($taskname))
		{
			$filter['taskname'] = array('like', '%'.$taskname.'%');
			$this->assign('taskname', $taskname);
		
			$strFilter = " and tb.taskname like '%".$taskname."%'";
		}

		//条件排序
		$order = array();
		if($_REQUEST['_order'] && $_REQUEST['_sort'])
		{
			$order[$_REQUEST['_order']] =  $_REQUEST['_sort'];
		}
		else
		{
			$order['autoid'] = 'desc';
		}


		$totalCount = $this->_taskstuModel->where($filter)->count();
		//$sql = "select ts.*, tb.taskname as name1 from t_task_stu as ts, t_task_base as tb where ts.taskid=tb.autoid order by ts.autoid desc";
		$startNum = ($currentPage-1)*$pageSize;
		
		$sql = "select top $pageSize tu.autoid as mid,tu.ret as tret, tu.rettxt as trettxt, tu.tooltip as ttooltip, tb.*, t.tipname as name1,u.unitname as name2,tm.tipname as name3 from t_task_base as tb ,t_position_mod as tm,t_unit as u,t_tool as t ,t_task_stu as tu where tb.autoid=tu.taskid and t.id=tb.tool and u.id=tb.unit and tm.id=tb.modelid 
 $strFilter and tb.autoid  not in ( select top $startNum tb1.autoid from t_task_base as tb1 ,t_position_mod as tm,t_unit as u,t_tool as t,t_task_stu as tu where tb.autoid=tu.taskid and t.id=tb1.tool and u.id=tb1.unit and tm.id=tb1.modelid $strFilter order by tb1.autoid desc ) order by tb.autoid desc";

		//$sql = "select top $pageSize ts.*, t.tipname as name1,u.unitname as name2,tm.tipname as name3 from t_task_stu as ts ,t_position_mod as tm,t_unit as u,t_tool as t where t.id=ts.tool and u.id=ts.unit and tm.id=ts.modelid
		//and ts.autoid not in ( select top $startNum tb1.autoid from t_task_base as tb1 ,t_position_mod as tm,t_unit as u,t_tool as t where t.id=tb1.tool and u.id=tb1.unit and tm.id=tb1.modelid order by tb1.autoid desc ) order by ts.autoid desc";
		//echo $sql;exit;
		
		$list = M()->query($sql);

		$this->assign('totalCount', $totalCount);
		$this->assign('list', $list);

		$this->assign('currentPage', $currentPage);
		$this->assign('pageSize', $pageSize);

		$this->display('TaskManager:educationList');
	}

	/**
	 * 添加检修答案
	 * */
	public function educationAdd()
	{
		if($_POST)
		{
			extract($_POST);

			$data = array();
			//$data['name'] = trim($name);

			$taskInfo = $this->_taskbaseModel->where("autoid = $taskid")->find();
			$data['CRH'] = $taskInfo['CRH'];				//车型
			$data['level'] = $taskInfo['level'];			// 级别 值 1一级检修 2二级检修 4应急故障 8巡检
			$data['area'] = $taskInfo['area'];			//任务分类1 部位  1车头  2车身 4车外 8车顶 16车底
			$data['unit'] = $taskInfo['unit'];			//部件
			$data['childid'] = $taskInfo['childid'];		//任务内部子任务序号
			
			$data['taskid'] = trim($taskid);
			
			//dump($taskInfo);
			
			
			if($taskInfo['opttype'] == 1 || $taskInfo['opttype'] == 3)
			{
				$data['ret'] = $ret;				//选择对话体答案
				$data['picshow'] = $ret;
			}
			if($taskInfo['opttype'] == 2)
			{
				$data['rettxt'] = trim($rettxt);	//填空题答案
			}
			
			$data['tooltip'] = trim($tooltip);			//作业框中选择的数量
			
			//dump($data);exit;

			$newId = $this->_taskstuModel->add($data);

			if($newId)
			{
				$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'educationlist','callbackType'=>'closeCurrent');
			}
			else
			{
				$array = array('statusCode'=>'300','message'=>"添加失败");
			}

			echo json_encode($array);
			exit();
		}
		else
		{
			$order['autoid'] = ' desc';
			$list = $this->_taskbaseModel->order($order)->select();
			$this->assign('taskBasePos', $list);
			
			$this->display('TaskManager:educationAdd');
		}
	}

	/**
	 * 修改教学任务
	 * */
	public function educationEdit()
	{
		if($_POST)
		{
			extract($_POST);

			$data = array();
			//$data['name'] = trim($name);

			$taskInfo = $this->_taskbaseModel->where("autoid = $taskid")->find();
			$data['CRH'] = $taskInfo['CRH'];				//车型
			$data['level'] = $taskInfo['level'];			// 级别 值 1一级检修 2二级检修 4应急故障 8巡检
			$data['area'] = $taskInfo['area'];			//任务分类1 部位  1车头  2车身 4车外 8车顶 16车底
			$data['unit'] = $taskInfo['unit'];			//部件
			$data['childid'] = $taskInfo['childid'];		//任务内部子任务序号
			
			$data['taskid'] = trim($taskid);
			
			//dump($taskInfo);
			
			
			if($taskInfo['opttype'] == 1 || $taskInfo['opttype'] == 3)
			{
				$data['ret'] = $ret;				//选择答案
				$data['picshow'] = $ret;
			}
			if($taskInfo['opttype'] == 2)
			{
				$data['rettxt'] = trim($rettxt);	//填空题答案
			}
			
			$data['tooltip'] = trim($tooltip);

			$filter = array();
			$filter['autoid'] = $_REQUEST['id'];


			$result = $this->_taskstuModel->where($filter)->save($data);

			$array = array('statusCode'=>200,'message'=>"修改成功",'navTabId'=>'educationlist','callbackType'=>'closeCurrent');
			
			echo json_encode($array);
			exit();
		}
		else
		{
			$id = $_REQUEST['id'];

			//基本任务
			$order['autoid'] = ' desc';
			$list = $this->_taskbaseModel->order($order)->select();
			$this->assign('taskBasePos', $list);
			
			//教学任务
			$taskstuPo = $this->_taskstuModel->where("autoid = $id")->find();
			$this->assign('taskstuPo', $taskstuPo);
			
			//教学任务下面的贴图和答案html
			$entiHtml = $this->getTaskinfoByTaskPo($taskstuPo);
			$this->assign('str', $entiHtml);

			$this->display('TaskManager:educationEdit');
		}
	}

	/**
	 * 启用禁用删除教学任务
	 * */
	public function educationProcess()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];

		if($type && $id)
		{
			$filter = array();
			$filter['autoid'] = $id;

			if($type == "del")
			{
				$this->_taskstuModel->where($filter)->delete();
			}

			$data = array("statusCode"=>"200","message"=>"操作成功");
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
	}

	
	/*根据车型部位得到下面的所有部件*/
	public function ajaxGetPositionByType()
	{
		$type = $_REQUEST['type'];
		$CRH = $_REQUEST['CRH'];
		$level = $_REQUEST['level'];
	
		if(empty($level))
		{
			$positionList = $this->_unitModel->where("(area & $type = $type) and (CRH & $CRH = $CRH)")->order("id asc")->select();
		}
		else
		{
			$positionList = $this->_unitModel->where("(area & $type = $type) and (level & $level = $level) and (CRH & $CRH = $CRH)")->order("id asc")->select();
		}
		
		//echo $this->_unitModel->getLastSql();
		$str = "";
		if($positionList)
		{
			$str .= "<option value='0'>请选择";
			foreach($positionList as $value)
			{
				$str .= "<option value=".$value['id'].">".$value['unitname'];
			}
		}
		
		$this->ajaxReturn($str,1,1);
	}
	
	/*得到基本任务下面的部件*/
	public function getTaskBaseUnit()
	{
		$type = $_REQUEST['type'];
		$CRH = $_REQUEST['CRH'];
		$level = $_REQUEST['level'];
		
		$str = "";
		
		$taskBaseInfos = $this->_taskbaseModel->where("area & $type = $type and level & $level = $level and CRH & $CRH = $CRH")->select();
		//echo $this->_taskbaseModel->getLastSql();

		if($taskBaseInfos)
		{
			$unitIdArrs = array();
			
			$filter = array();
			foreach($taskBaseInfos as $vo)
			{
				if(in_array($vo['unit'], $unitIdArrs))
				{
					continue;
				}
				else
				{
					$unitIdArrs[] = $vo['unit'];
				}
			}
			$filter['id'] = array('in', $unitIdArrs);
			$unitVos = $this->_unitModel->where($filter)->select();
			if($unitVos)
			{
				foreach($unitVos as $value)
				{
					$str .= "<option value=".$value['id']." selected>".$value['unitname'];
				}
			}
		}
		$this->ajaxReturn($str,1,1);
	}

	/*根据部件id得到部件下面的热点部位*/
	public function ajaxGetPositionByUnitId()
	{
		$unitid = $_REQUEST['unitId'];
	
		$redianList = $this->_positionModel->where("unitid = $unitid")->order("id asc")->select();
	
		$str = "";
		if($redianList)
		{
			$str .= "<option value='0'>请选择";
			foreach($redianList as $value)
			{
				$str .= "<option value=".$value['id'].">".$value['tipname'];
			}
		}

	
		$this->ajaxReturn($str,1,1);
	}
	
	//通过检修任务id得到检修任务答案信息
	public function ajaxGetTaskinfoById()
	{
		$crh = C("CRHTYPE");
		
		$id = $_REQUEST['taskid'];
		
		$taskPo = $this->_taskbaseModel->where("autoid = $id")->find();

		$str = "";
		/*
		$str .= "<tr class='taststucont'>
			<td align='center'  width='160'><label>显示的贴图</label></td>
			<td align='left'>
				<select name='picshow'>
					<option value='0'>无贴图";
					for($i=1;$i<=$taskPo['tnum'];$i++)
					{
						$str .= "<option value='".$i."'>".$taskPo['pic'.$i];
					}
		$str .= "</select>
			</td>
		</tr>";
		*/
		$chageType = C("changeType");//选择题类型
		
		$optType = $taskPo['opttype'];
		if($optType == 1)
		{
			$str .= "<tr class='taststucont'>
			<td align='center'  width='160'><label>选择题</label></td>
			<td align='left'>
				<select name='ret'>";
					for($i=1;$i<=$taskPo['tnum'];$i++)
					{//dump($taskPo['retstate'.$i]);
						if ($taskPo['retstate'.$i] != 2)
						{
							$str .= "<option value='".$i."'>".$taskPo['txt'.$i];
						}
						
						//$str .= "<option value='".$i."'>".$chageType[$i];
					}
		$str .= "</select>
			</td>
			</tr>";
		}
		if($optType == 2)
		{
			$str .="<tr class='taststucont'>
                <td align='center'  width='160'><label>填空题答案</label></td>
                <td align='left'>
                    <input type='text' name='rettxt' style='width:300px;'/>
                </td>
            </tr>";
		}
		
		$this->ajaxReturn($str, 1, 1);
	}
	
	//通过检修任务得到检修任务答案信息
	public function getTaskinfoByTaskPo($taskStuPo)
	{
		$id = $taskStuPo['taskid'];
	
		$taskPo = $this->_taskbaseModel->where("autoid = $id")->find();
	
		$str = "";
		/*
		$str .= "<tr class='taststucont'>
		<td align='center'  width='160'><label>显示的贴图</label></td>
		<td align='left'>
		<select name='picshow'>
		<option value='0'>无贴图";
		for($i=1;$i<=$taskPo['tnum'];$i++)
		{
			$str .= "<option value='".$i."'";
			if($taskStuPo['picshow'] == $i)
			{
				$str .= " selected";
			}
			$str .= ">".$taskPo['pic'.$i];
		}
		$str .= "</select>
		</td>
		</tr>";
		*/
		$chageType = C("changeType");//选择题类型
		
		$optType = $taskPo['opttype'];
		if($optType == 1)
		{
			$str .= "<tr class='taststucont'>
			<td align='center'  width='160'><label>选择题答案</label></td>
			<td align='left'>
			<select name='ret'>";
			for($i=1;$i<=$taskPo['tnum'];$i++)
			{
				if ($taskPo['retstate'.$i] != 2)
				{
					$str .= "<option value='".$i."'";
					if($taskStuPo['ret'] == $i)
					{
						$str .= " selected";
					}
					$str .= ">".$taskPo['txt'.$i];
				}
			}
			$str .= "</select>
			</td>
			</tr>";
		}
		if($optType == 2)
		{
				$str .="<tr class='taststucont'>
				<td align='center'  width='160'><label>填空题答案</label></td>
				<td align='left'>
				<input type='text' name='rettxt' style='width:300px;' value='".$taskStuPo['rettxt']."'/>
			</td>
			</tr>";
		}
	
		return $str;
	}
	
	
	/***
	 * 故障考试列表
	 * */
	public function taskKsList()
	{
		$currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
		$pageSize = empty($_REQUEST['pageSize']) ? 25 : $_REQUEST['pageSize'];
		
		//条件过滤
		$filter = array();
		
		
		//条件排序
		$order = array();
		if($_REQUEST['_order'] && $_REQUEST['_sort'])
		{
			$order[$_REQUEST['_order']] =  $_REQUEST['_sort'];
		}
		else
		{
			$order['autoid'] = ' desc';
		}
		
		
		$totalCount = $this->_taskKsModel->where($filter)->count();
		$list = $this->_taskKsModel->where($filter)->order($order)->page($currentPage)->limit($pageSize)->select();
		
		$this->assign('totalCount', $totalCount);
		$this->assign('list', $list);
		
		$this->assign('currentPage', $currentPage);
		$this->assign('pageSize', $pageSize);
		
		$this->display('TaskManager:taskKsList');
	}
	
	/***
	 * 故障考试添加
	* */
	public function taskKsAdd()
	{
		if($_POST)
		{
			extract($_POST);

			$data = array();
			$data['name'] = trim($name);
			
			if(empty($stuid))
			{
				$array = array('statusCode'=>'300','message'=>"没有选择座位");
				echo json_encode($array);
				exit();
			}

			$taskStuInfo = $this->_taskstuModel->where("autoid = $taskstuid")->find();  //dump($taskStuInfo);
			$data['CRH'] = $taskStuInfo['CRH'];				//车型
			$data['level'] = $taskStuInfo['level'];			// 级别 值 1一级检修 2二级检修 4应急故障 8巡检
			$data['area'] = $taskStuInfo['area'];			//任务分类1 部位  1车头  2车身 4车外 8车顶 16车底
			$data['unit'] = $taskStuInfo['unit'];			//部件
			$data['childid'] = $taskStuInfo['childid'];		//任务内部子任务序号
			
			$data['taskid'] = $taskStuInfo['taskid'];
			
			//dump($taskInfo);
			$data['picshow'] = $taskStuInfo['picshow'];
			$data['tooltip'] = $taskStuInfo['tooltip'];			//作业框中选择的数量
			
			$taskPo = $this->_taskbaseModel->where("autoid = $taskStuInfo[taskid]")->find();
			
			if($taskPo['opttype'] == 1 || $taskPo['opttype'] == 3)
			{
				$data['ret'] = $taskStuInfo['ret'];				//选择对话体答案
			}
			if($taskPo['opttype'] == 2)
			{
				$data['rettxt'] = $taskStuInfo['rettxt'];	//填空题答案
			}
			$data['opttype'] = $taskPo['opttype'];
			
			//dump($data);exit;
			for($i=0;$i<count($stuid); $i++)
			{
				$data['stuid'] = trim($stuid[$i]);
				$data['macip'] = -1062731511+$stuid[$i];
				$newId = $this->_taskKsModel->add($data);
			}

			if($newId)
			{
				$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'taskkslist','callbackType'=>'closeCurrent');
			}
			else
			{
				$array = array('statusCode'=>'300','message'=>"添加失败");
			}

			echo json_encode($array);
			exit();
		}
		else
		{
			$taskStu = $this->_taskstuModel->select();
			$this->assign('taskstus', $taskStu);
			
			$this->display('TaskManager:taskKsAdd');
		}
	}
	
	/***
	 * 故障考试编辑
	* */
	public function taskKsEdit()
	{
		$this->display('TaskManager:taskKsEdit');
	}
	
	/***
	 * 故障考试处理
	* */
	public function taskKsProcess()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];
		
		if($type && $id)
		{
			$filter = array();
			$filter['autoid'] = $id;
		
			if($type == "del")
			{
				$this->_taskKsModel->where($filter)->delete();
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