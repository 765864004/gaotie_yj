use d_train;

create table  t_m_admin
(
	id		int		identity (1,1) NOT NULL,		-- 主键id
	name		varchar(50)	NOT NULL,				-- 管理员登录名
	true_name	varchar(50),						-- 管理员姓名
	password	char(32)	NOT NULL,				-- 登录密码
	status		char(1)		NOT NULL DEFAULT 'Y',			-- 状态
	is_root		char(1)		NOT NULL DEFAULT 'N',			-- 是否超级管理员
	create_time	datetime	NOT NULL,				-- 创建时间
	last_login_time	datetime,						-- 上次登录时间
	last_login_ip	varchar(15),						-- 上次登录IP
	role_id		int		default NULL,
	PRIMARY KEY(id)
);

-- 后台角色
create table  t_m_role 
(
	id		int		NOT NULL identity (1,1),		-- 主键id
	name		varchar(100)	DEFAULT NULL,				-- 角色名称
	create_user	char(100)	default NULL,				-- 创建者
	description	text,							-- 描述
	status		char(1)		DEFAULT NULL,				-- 状态 Y N
	PRIMARY KEY (id)
) ;

-- 角色权限表
create table  t_m_role_access 
(
	role_id		 int		NOT NULL,
	node_id		 int		NOT NULL,
	PRIMARY KEY (role_id,node_id)
) ;

-- 操作表
create table  t_m_node 
(
	id		int		NOT NULL,	-- 节点的ID,
	name		varchar(100)	NOT NULL DEFAULT '',		-- 节点的名称,
	title		varchar(50)	NOT NULL DEFAULT '',		-- 节点的title，用于菜单上的链接上的title,
	remark		varchar(255)	NOT NULL DEFAULT '',		-- 节点的描述，可能在界面中告诉用户方法的作用,
	status		char(1)		NOT NULL DEFAULT 'Y',		-- 节点的状态，Y为显示，N不显示,
	level		int		NOT NULL DEFAULT '0',		-- 节点的等级，总共有3级, 1：组名  2：模块名 3级为操作名
	fid		int		NOT NULL DEFAULT '0',		-- 节点的父节点的ID,
	sort		int		NOT NULL DEFAULT '0',		-- 节点在一个小菜单的排序,在前台从小打到排序
	PRIMARY KEY (id)
);


INSERT INTO t_m_node VALUES ('1', 'admin', '后台管理', '', 'Y', '1', '0', '0');
INSERT INTO t_m_node VALUES ('2', 'admin_manage', '管理员管理', '', 'Y', '2', '1', '99');
INSERT INTO t_m_node VALUES ('3', 'adminlist', '管理员列表', '', 'Y', '3', '2', '0');
INSERT INTO t_m_node VALUES ('4', 'addadmin', '添加管理员', '', 'N', '3', '2', '0');
INSERT INTO t_m_node VALUES ('5', 'adminProcess', '管理员处理', '', 'N', '3', '2', '0');
INSERT INTO t_m_node VALUES ('6', 'editadmin', '编辑管理员', '', 'N', '3', '2', '0');
INSERT INTO t_m_node VALUES ('7', 'role_manage', '角色管理', '', 'Y', '2', '1', '100');
INSERT INTO t_m_node VALUES ('8', 'rolelist', '角色列表', '', 'Y', '3', '7', '0');
INSERT INTO t_m_node VALUES ('9', 'addrole', '添加角色', '', 'N', '3', '7', '0');
INSERT INTO t_m_node VALUES ('10', 'editrole', '编辑角色', '', 'N', '3', '7', '0');
INSERT INTO t_m_node VALUES ('11', 'roleProcess', '角色处理', '', 'N', '3', '7', '0');

INSERT INTO t_m_node VALUES ('12', 'dictionary_manage', '字典管理', '', 'Y', '2', '1', '1');
INSERT INTO t_m_node VALUES ('13', 'positionlist', '热点列表', '', 'Y', '3', '12', '4');
INSERT INTO t_m_node VALUES ('14', 'positionadd', '添加热点', '', 'N', '3', '12', '0');
INSERT INTO t_m_node VALUES ('15', 'positionedit', '编辑热点', '', 'N', '3', '12', '0');
INSERT INTO t_m_node VALUES ('16', 'positionProcess', '热点处理', '', 'N', '3', '12', '0');
INSERT INTO t_m_node VALUES ('17', 'toollist', '工具列表', '', 'Y', '3', '12', '2');
INSERT INTO t_m_node VALUES ('18', 'tooladd', '添加工具', '', 'N', '3', '12', '0');
INSERT INTO t_m_node VALUES ('19', 'tooledit', '编辑工具', '', 'N', '3', '12', '0');
INSERT INTO t_m_node VALUES ('20', 'toolProcess', '工具处理', '', 'N', '3', '12', '0');
INSERT INTO t_m_node VALUES ('21', 'unitlist', '部件列表', '', 'Y', '3', '12', '3');
INSERT INTO t_m_node VALUES ('22', 'unitadd', '添加部件', '', 'N', '3', '12', '0');
INSERT INTO t_m_node VALUES ('23', 'unitedit', '编辑部件', '', 'N', '3', '12', '0');
INSERT INTO t_m_node VALUES ('24', 'unitProcess', '部件处理', '', 'N', '3', '12', '0');

INSERT INTO t_m_node VALUES ('25', 'task_manage', '检修管理', '', 'Y', '2', '1', '2');
INSERT INTO t_m_node VALUES ('26', 'tasklist', '基本任务列表', '', 'Y', '3', '25', '1');
INSERT INTO t_m_node VALUES ('27', 'taskadd', '添加基本任务', '', 'N', '3', '25', '0');
INSERT INTO t_m_node VALUES ('28', 'taskedit', '基本任务修改', '', 'N', '3', '25', '0');
INSERT INTO t_m_node VALUES ('29', 'taskprocess', '基本任务处理', '', 'N', '3', '25', '0');
INSERT INTO t_m_node VALUES ('30', 'educationlist', '教学任务列表', '', 'Y', '3', '25', '2');
INSERT INTO t_m_node VALUES ('31', 'educationadd', '教学任务添加', '', 'N', '3', '25', '0');
INSERT INTO t_m_node VALUES ('32', 'educationedit', '教学任务修改', '', 'N', '3', '25', '0');
INSERT INTO t_m_node VALUES ('33', 'educationprocess', '教学任务处理', '', 'N', '3', '25', '0');
INSERT INTO t_m_node VALUES ('34', 'ajaxGetPositionByType', '异步获取工具列表', '', 'N', '3', '25', '0');
INSERT INTO t_m_node VALUES ('35', 'ajaxGetTaskinfoById', '异步获取任务信息', '', 'N', '3', '25', '0');

INSERT INTO t_m_admin VALUES ('admin', 'admin', '61b4afbfd199b74b038173ac00feefe4', 'Y', 'N', '2012-10-23 11:01:57', null, null, '1');   --  用户名admin  密码123456

INSERT INTO t_m_role VALUES ('admin', '', 'admin', 'Y');



INSERT INTO t_m_role_access VALUES ('1', '1');
INSERT INTO t_m_role_access VALUES ('1', '2');
INSERT INTO t_m_role_access VALUES ('1', '3');
INSERT INTO t_m_role_access VALUES ('1', '4');
INSERT INTO t_m_role_access VALUES ('1', '5');
INSERT INTO t_m_role_access VALUES ('1', '6');
INSERT INTO t_m_role_access VALUES ('1', '7');
INSERT INTO t_m_role_access VALUES ('1', '8');
INSERT INTO t_m_role_access VALUES ('1', '9');
INSERT INTO t_m_role_access VALUES ('1', '10');
INSERT INTO t_m_role_access VALUES ('1', '11');
INSERT INTO t_m_role_access VALUES ('1', '12');
INSERT INTO t_m_role_access VALUES ('1', '13');
INSERT INTO t_m_role_access VALUES ('1', '14');
INSERT INTO t_m_role_access VALUES ('1', '15');
INSERT INTO t_m_role_access VALUES ('1', '16');
INSERT INTO t_m_role_access VALUES ('1', '17');
INSERT INTO t_m_role_access VALUES ('1', '18');
INSERT INTO t_m_role_access VALUES ('1', '19');
INSERT INTO t_m_role_access VALUES ('1', '20');
INSERT INTO t_m_role_access VALUES ('1', '21');