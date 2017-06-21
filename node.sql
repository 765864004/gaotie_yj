INSERT INTO t_m_node VALUES ('1', 'admin', '后台管理', '', 'Y', '1', '0', '0');

INSERT INTO t_m_node VALUES ('2', 'admin_manage', '人员管理', '', 'Y', '2', '1', '99');
INSERT INTO t_m_node VALUES ('3', 'adminlist', '管理员管理', '', 'Y', '3', '2', '1');
INSERT INTO t_m_node VALUES ('4', 'addadmin', '添加管理员', '', 'N', '3', '2', '0');
INSERT INTO t_m_node VALUES ('5', 'adminProcess', '管理员处理', '', 'N', '3', '2', '0');
INSERT INTO t_m_node VALUES ('6', 'editadmin', '编辑管理员', '', 'N', '3', '2', '0');
INSERT INTO t_m_node VALUES ('7', 'rolelist', '角色管理', '', 'Y', '3', '2', '99');
INSERT INTO t_m_node VALUES ('8', 'addrole', '添加角色', '', 'N', '3', '2', '0');
INSERT INTO t_m_node VALUES ('9', 'editrole', '编辑角色', '', 'N', '3', '2', '0');
INSERT INTO t_m_node VALUES ('10', 'roleProcess', '角色处理', '', 'N', '3', '2', '0');


INSERT INTO t_m_node VALUES ('11', 'dictionary_manage', '字典管理', '', 'Y', '2', '1', '1');

INSERT INTO t_m_node VALUES ('12', 'toollist', '检修工具管理', '', 'Y', '3', '11', '1');
INSERT INTO t_m_node VALUES ('13', 'tooladd', '添加工具', '', 'N', '3', '11', '0');
INSERT INTO t_m_node VALUES ('14', 'tooledit', '编辑工具', '', 'N', '3', '11', '0');
INSERT INTO t_m_node VALUES ('15', 'toolProcess', '工具处理', '', 'N', '3', '11', '0');
INSERT INTO t_m_node VALUES ('16', 'unitlist', '检修部件管理', '', 'Y', '3', '11', '3');
INSERT INTO t_m_node VALUES ('17', 'unitadd', '添加部件', '', 'N', '3', '11', '0');
INSERT INTO t_m_node VALUES ('18', 'unitedit', '编辑部件', '', 'N', '3', '11', '0');
INSERT INTO t_m_node VALUES ('19', 'unitProcess', '部件处理', '', 'N', '3', '11', '0');
INSERT INTO t_m_node VALUES ('20', 'positionlist', '检修热点管理', '', 'Y', '3', '11', '4');
INSERT INTO t_m_node VALUES ('21', 'positionadd', '添加热点', '', 'N', '3', '11', '0');
INSERT INTO t_m_node VALUES ('22', 'positionedit', '编辑热点', '', 'N', '3', '11', '0');
INSERT INTO t_m_node VALUES ('23', 'positionProcess', '热点处理', '', 'N', '3', '11', '0');

INSERT INTO t_m_node VALUES ('24', 'yjtasklist', '应急故障管理', '', 'N', '3', '11', '5');
INSERT INTO t_m_node VALUES ('25', 'yjtaskadd', '添加故障名称', '', 'N', '3', '11', '0');
INSERT INTO t_m_node VALUES ('26', 'yjtaskedit', '编辑故障名称', '', 'N', '3', '11', '0');
INSERT INTO t_m_node VALUES ('27', 'yjtaskProcess', '故障名称处理', '', 'N', '3', '11', '0');


INSERT INTO t_m_node VALUES ('28', 'task_manage', '检修管理', '', 'Y', '2', '1', '2');

INSERT INTO t_m_node VALUES ('29', 'tasklist', '检修任务管理', '', 'Y', '3', '28', '1');
INSERT INTO t_m_node VALUES ('30', 'taskadd', '添加检修任务', '', 'N', '3', '28', '0');
INSERT INTO t_m_node VALUES ('31', 'taskedit', '检修任务修改', '', 'N', '3', '28', '0');
INSERT INTO t_m_node VALUES ('32', 'taskprocess', '检修任务处理', '', 'N', '3', '28', '0');
INSERT INTO t_m_node VALUES ('33', 'educationlist', '检修答案管理', '', 'Y', '3', '28', '2');
INSERT INTO t_m_node VALUES ('34', 'educationadd', '检修答案添加', '', 'N', '3', '28', '0');
INSERT INTO t_m_node VALUES ('35', 'educationedit', '检修答案修改', '', 'N', '3', '28', '0');
INSERT INTO t_m_node VALUES ('36', 'educationprocess', '检修答案处理', '', 'N', '3', '28', '0');

INSERT INTO t_m_node VALUES ('37', 'ajaxGetPositionByType', '异步获取部位列表', '', 'N', '3', '28', '0');
INSERT INTO t_m_node VALUES ('38', 'ajaxGetTaskinfoById', '异步获取任务信息', '', 'N', '3', '28', '0');
INSERT INTO t_m_node VALUES ('39', 'ajaxGetPositionByUnitId', '异步获取热点信息', '', 'N', '3', '28', '0');


-- 试卷管理
INSERT INTO t_m_node VALUES ('40', 'ks_manage', '试卷管理', '', 'Y', '2', '1', '3');

INSERT INTO t_m_node VALUES ('41', 'taskresultlist', '成绩信息管理', '', 'Y', '3', '40', '1');  -- 成绩结果列表
INSERT INTO t_m_node VALUES ('42', 'reks', '设置重考', '', 'N', '3', '40', '1');		-- 重考设置
INSERT INTO t_m_node VALUES ('43', 'taskpagelist', '试卷信息管理', '', 'Y', '3', '40', '0');	-- 试卷列表
INSERT INTO t_m_node VALUES ('44', 'taskpageadd', '添加考试试卷', '', 'N', '3', '40', '0');	-- 添加试卷
INSERT INTO t_m_node VALUES ('45', 'taskpagedel', '删除考试试卷', '', 'N', '3', '40', '0');	-- 添加试卷
INSERT INTO t_m_node VALUES ('46', 'taskpagefplist', '试卷分配管理', '', 'Y', '3', '40', '0');  -- 试卷分配列表
INSERT INTO t_m_node VALUES ('47', 'taskpagefpadd', '试卷分配', '', 'N', '3', '40', '0');       -- 试卷分配
INSERT INTO t_m_node VALUES ('48', 'taskpagefpedit', '试卷分配修改', '', 'N', '3', '40', '0');  -- 试卷分配修改
INSERT INTO t_m_node VALUES ('49', 'taskpagefpdel', '试卷分配删除', '', 'N', '3', '40', '0');   -- 试卷分配删除


-- 学生管理
INSERT INTO t_m_node VALUES ('50', 'userlist', '学生管理', '', 'Y', '3', '2', '0'); 
INSERT INTO t_m_node VALUES ('51', 'userAdd', '添加学生', '', 'N', '3', '2', '0'); 
INSERT INTO t_m_node VALUES ('52', 'userEdit', '编辑学生', '', 'N', '3', '2', '0'); 
INSERT INTO t_m_node VALUES ('53', 'userDel', '删除学生', '', 'N', '3', '2', '0'); 


-- 班级管理
INSERT INTO t_m_node VALUES ('54', 'departlist', '部门管理', '', 'Y', '3', '2', '0'); 
INSERT INTO t_m_node VALUES ('55', 'departAdd', '添加部门', '', 'N', '3', '2', '0'); 
INSERT INTO t_m_node VALUES ('56', 'departEdit', '编辑部门', '', 'N', '3', '2', '0'); 
INSERT INTO t_m_node VALUES ('57', 'departDel', '删除部门', '', 'N', '3', '2', '0'); 

INSERT INTO t_m_node VALUES ('58', 'getTaskBaseUnit', '异步获取基本任务下面的热点信息', '', 'N', '3', '28', '0');

INSERT INTO t_m_node VALUES ('59', 'ajaxGetUserByDepartId', '异步获取基本任务下面的热点信息', '', 'N', '3', '40', '0');
INSERT INTO t_m_node VALUES ('60', 'taskpageFpuser', '查看试卷分配具体人员', '', 'N', '3', '40', '0');
INSERT INTO t_m_node VALUES ('61', 'toExcel', '导出数据到execl', '', 'N', '3', '40', '0');

INSERT INTO t_m_node VALUES ('62', 'userPlExcel', '导入excel学生信息', '', 'N', '3', '2', '0');
INSERT INTO t_m_node VALUES ('63', 'ajaxDouploadPic', '上传execl文件处理', '', 'N', '3', '2', '0');

INSERT INTO t_m_node VALUES ('64', 'materiallist', '检修材料管理', '', 'Y', '3', '11', '2');
INSERT INTO t_m_node VALUES ('65', 'materialadd', '添加材料', '', 'N', '3', '11', '0');
INSERT INTO t_m_node VALUES ('66', 'materialedit', '编辑材料', '', 'N', '3', '11', '0');
INSERT INTO t_m_node VALUES ('67', 'materialProcess', '材料处理', '', 'N', '3', '11', '0');