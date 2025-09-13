<?php
$lang = array(
    'appname' => '机构用户',
    'explorer_gropuperm' => array(
        '协作成员',
        '管理员',
        '创始人'
    ),
    'orgname' => '机构名称',
    'orgname_placeholder' => '输入机构(群组)名称',
    'change' => '更换',
    'org_description' => '简介',
    'org_description_placeholder' => '输入机构(群组)简要介绍',
    'org_space_assign' => '分配空间大小',
    'org_space_assign_tip' => '当前可以分配的最大可用空间',
    'org_space_assign_tips' => ' <li>单位M，留空或者0表示不限制，-1表示无空间</li><li>限制整个机构或部门（包括下级所有部门）可以使用的空间大小(机构下所有部门的空间使用总和不能超过这个限制）</li><li>部门分配的空间只能从上级部门的可用空间里面划分；一旦分配，上级部门的剩余空间就会相应减少，不管这些分配的空间是否实际使用完</li>',
    'space_use_department' => '部门空间使用',
    'space_use_org' => '机构总空间使用',
    'space_use_org_tips' => '<li>限制整个机构或部门（包括下级所有部门）可以使用的空间大小</li><li>下级部门分配的空间会从上级的可用空间里面分配</li>',
    'org_update_success' => '更新资料成功',
    'org_img_uploaded_tip' => '该图片已经上传过了，只需勾选即可',
    'clicktoupload' => '点击上传',
    // admin/orguser/detail_org.html
    'export_excl' => '导出此部门的所有用户到excl文件',
    'detail_org_no_enable' => '如不启用，下级所有部门中将不能使用共享目录；启用后，企业盘才会显示共享目录。',
    'detail_org_explorerapp_enable' => '开启后,可在网盘等应用中能够使用该机构或部门的目录。',
    'detail_org_enable' => '启用后，企业盘机构下才会显示此部门的共享目录。',
    'detail_org_creation' => '创建快捷方式后，所属成员桌面默认都会有相应快捷方式。',
    'group_org_no_enable' => '开启后，资源管理器会显示该机构群组选项。',
    'group_org_enable' => '不开启，资源管理器不会显示该机构群组选项。',
    'detail_org_administrator' => '<strong class="float-start" style="margin-left:-45px;">注：</strong>
  <li>机构管理员权限：设置本机构下所有部门管理员，管理本机构中所有人员，管理本机构所有共享目录。</li>
  <li>部门管理员权限：设置本部门下所有子部门管理员，管理本部门中所有人员，管理本部门所有共享目录。</li>',
    'inport_guide_download' => '下载人员信息导入模板',
    'inport_guide_template' => '模板的项目会根据用户资料项的多少自动生成',
    'inport_guide_user' => '根据模板编辑需要人员信息表。',
    'inport_guide_step' => '步骤3：选择excel表',
    'inport_guide_layout' => '选择编辑好的人员信息表，支持 .xls、.xlsx格式',
    'inport_guide_notice' => '编辑人员信息表注意事项',
    'inport_guide_notice_text' => '<li>1、根据模板字段来编辑需要导入的人员信息，如果已经有档案信息表，则只需将现有的档案信息表中的相关字段名称改为与“模板”里提供的名称一致即可，只要名称对应，字段的位置不影响结果。</li>
<li>2、模板中“用户名”字段必填，其他字段根据需要填写。</li>
<li>3、表中“邮箱”、“用户名”字段中的信息必须是唯一的，不可重复。如果邮箱为空，系统导入时将随机生成邮箱地址。</li>
<li class="danger">4、多级部门创建：方法1：表中添加多列“所属部门”，从左到右分别为一级部门、二级部门、三级部门，顺序排列，系统会根据从左到右的原则，依次创建部门、下级部门、下下级部门等。方法2：表格中使用单列，上下级部门使用“/”来分割（例如：小学/一年级/一班）。</li>
<li>5、导入用户只能按机构导入，多个机构需要分批导入。</li>
<li>6、表中“登录密码”字段可为空，管理员在导入时能够为用户批量设置统一的密码。用户使用统一密码登录后可自行修改。</li>
<li>7、导入界面中有“增量”、“覆盖”两种导入方式。增量方式为：遇到相同用户，只会增加用户缺少的字段信息，原有信息不变。（例如：系统中已有用户A，密码为123。在批量导入表格中，也有用户A，导入时设置了统一密码为abc。导入完成后，其他用户的密码都为abc，原用户A还是保持他的原有密码，即123。）覆盖导入：将系统中原有的用户信息完全替换为表格中的信息。</li>
<li>8、若需导入的人员较多，建议先做一个少量人员的测试表，测试无误后，再使用增量方式导入所有用户。</li>',
    'import_list_organization' => '选择要导入到的机构，如果不导入任何机构，会根据用户信息表中的所属部门来生成新的机构和部门，没有则会直接导入到"无机构人员"下',
    'import_list_password' => '默认用户密码，当登录密码项未设置时，会使用此处设置的密码作为新导入的用户的密码',
    'import_list_coverage' => '增量导入方式：新导入的用户信息智能的增加到原有用户信息中；覆盖导入：新导入的信息覆盖原有用户的信息,建议使用增量方式。',
    'import_list_text' => '<li>用户名和邮箱项目为必填项</li>
      <li>点击下面的导入项的内容，可以临时编辑，编辑部门时注意，部门每行是上下级的关系，上一行为下一行的上级部门</li>
      <li>不需要导入的项目，可以点击右侧的"X"删除掉</li>
      <li>点击导入按钮，导入当前项目，全部导入按钮，按顺序导入所有项，中途可以再次点击停止</li>',
    // admin/orguser/tree.html
    'orguser_tree_delete' => '您确定要彻底删除此用户(用户的所有数据和文件都会彻底删除）吗？',
    'orguser_tree_permission_delete' => '此处删除，仅从部门中移除此用户，移除后您可能没有操作此用户的权限，您确定要移除此用户吗？',
    'orguser_tree_batch_delete' => '机构或部门不支持批量删除',
    'orguser_tree_all_delete' => '删除部门前，必须先删除此部门的所有下级部门，并且删除共享目录中的文件，您确定要删除此部门吗？',
    // admin/orguser/ajax.php
    'orguser_ajax_delete' => '在机构或部门中的用户，不支持彻底删除，请先从机构或部门中删除后重试',
    'no_parallelism_jurisdiction' => '没有对应部门的权限',
    // admin/orguser/edituser.php
    'orguser_edituser_add_user' => '<div class="well alert alert-danger">抱歉！您没有在此机构或部门下添加用户的权限！<br><br>可以在左侧选择有权限的部门，再重试添加</div>',
    'orguser_edituser_add_user1' => '<div class="well alert alert-danger">抱歉！您没有此用户的管理权限！<br><br>可以在左侧选择有权限管理的用户，再重试添加</div>',
    // admin/orguser/import.php
    'orguser_import_user' => '没有权限，只有系统与机构部门管理员才能导入用户',
    'orguser_import_user_table' => '人员信息表上传未成功，请重新上传',
    'orguser_import_xls_xlsx' => '只允许导入xls,xlsx类型的文件',
    'orguser_import_user_message' => '人员信息表上传成功，正在调转到导入页面',
    'orguser_import_tautology' => '上传信息表未成功，请稍候重试',
    'orguser_import_user_message_table' => '请选择人员信息表',
    // admin/orguser/vidw.php
    'orguser_vidw_delete' => '<div class="well alert alert-danger">抱歉！您没有此部门的管理权限！<br><br>可以在左侧选择有权限管理的部门</div>',
    // admin/member/adduser.html
    'adduser_login_email_text' => '必填，可用于系统登录，员工关注企业号时，会根据邮箱来匹配。',
    'adduser_compellation_text' => '必填，系统中显示，便于同事辨识',
    'adduser_phone_number_text' => '选填，微信绑定的手机号码，员工关注企业号时，会根据员工微信绑定的手机来匹配。',
    'adduser_weixin_text' => '选填，员工微信号，员工关注企业号时，会根据员工的微信号来匹配。',
    'adduser_exceptional_space_text' => '单位M，额外增加用户存储空间（用户的总空间=默认空间+额外空间）',
    'adduser_disable_user_text' => '用户停用后，该用户将不能登录系统，请谨慎操作',
    'adduser_usergroup_text' => '设置用户为系统管理员后，此用户将拥有系统管理权限，请慎重！',
    // admin/member/edituser.html
    'edituser_login_email_text' => '选填，可用于系统登录，员工关注企业号时，会根据员工的邮箱来匹配。',
    'edituser_weixin_text' => '选填，员工微信号，员工关注企业号时，会根据员工的微信号来匹配。如果已经关注，此项不能修改。',
    'supervisor_position' => '上司职位',
    'send_password_user_mailbox' => '发送密码到用户邮箱',
    'login_email_required' => '登录邮箱必填',
    'name_will' => '用户名必填',
    'none_write_login_password' => '还没有填写登录密码',
    'none_write_affirm_password' => '还没有填写确认密码',
    'phone_number' => '手机号码',
    'phone_number_illegal' => '手机号码不合法',
    'weixin_phone_number' => '微信绑定的手机号码',
    'weixin_illegal' => '微信号不合法',
    'user_weixin' => '员工微信号',
    'weixin_exist' => '微信号已经存在',
    'random_password' => '生成随机密码',
    'exceptional_space' => '额外空间',
    'disable_user' => '停用此用户',
    'block_up' => '停用',
    'set_system_administrator' => '设为系统管理员',
    'add_a_item' => '增加一项',
    'add_user' => '添加用户',
    'add_user_success' => '添加用户成功',
    'edit_user_success' => '修改用户信息成功',
    'email_registered_retry' => '邮箱已经被注册，请更换邮箱再试',
    'export_user' => '导出用户',
    'shared_directory_set' => '共享目录设置',
    'group_on_set' => '群组功能设置',
    'shared_directory_desktop_shortcut' => '共享目录桌面快捷方式',
    'position_management' => '职位管理',
    'add_position' => '添加职位',
    'position_name' => '职位名称',
    'organization_department' => '机构部门',
    'share_enable_successful' => '共享目录启用成功!',
    'share_close_successful' => '共享目录关闭成功!',
    'group_on_successful' => '群组功能开启成功!',
    'group_close_successful' => '群组功能关闭成功!',
    'login_username_placeholder' => '登录用户名',
    'login_username_text' => '必填，可用于系统登录',
    'required_used_login_system' => '必填，可用于系统登录',
    'space_not_change_password' => '留空，不修改密码',
    'export_range_user' => '选择导出范围,此范围内的所有用户都会导出',
    'export_data' => '导出资料项',
    'import_nbsp' => '导&nbsp;入',
    'creation_agency' => '新建机构',
    'creation_bottom_section' => '新建下级部门',
    'creation_equally_section' => '新建同级部门',
    'please_select_same_type_node' => '请选择相同类型的节点',
    'please_select_same_section_node' => '请选择相同部门的节点',
    'add_administrator_unsuccess' => '添加管理员失败',
    'no_open_Shared_directory' => '没有开启共享目录，无法设置',
    'please_select_range_export' => '请选择导出范围',
    'please_select_project_export' => '请选择导出项目',
    'user_information_table' => '人员信息表',
    'bulk_import_user_template' => '批量导入用户模板',
    'name_email_empty' => '用户名和邮箱不能为空',
    'lack_required_fields_name' => '缺少必填字段"用户名"',
    'lack_required_fields_name_email' => '缺少必填字段”用户名“或”邮箱“',
    'bulking' => '增量',
    'coverage' => '覆盖',
    'user_phone_illegal' => '用户手机号码不合法',
    'user_phone_exist' => '手机号码已经存在',
    'user_phone_registered' => '用户手机号码已经被注册',
    'weixin_registered' => '该微信号已经被注册',
    'user_registered_retry' => '该用户名已经被注册，请更换用户名再试',
    'import_user' => '导入用户',
    'orguser_guide_text' => '<h4><strong>组织管理使用说明</strong></h4>
	<ul class="">
		<li><img src="dzz/system/images/organization.png" />&nbsp;选中机构为设置机构信息</li>
		<li><img src="dzz/system/images/department.png" />&nbsp;选中部门为设置部门信息</li>
		<li><img src="dzz/system/images/user.png" />&nbsp;选中人员为设置人员信息</li>
		<li>人员、部门、机构可直接拖拽移动更换位置。移动是更换人员所属部门、和更换部门上级机构或上级部门。</li>
		<li>按住<code>ctrl</code>键移动人员或部门为复制。用于将人员同时加入多个部门。</li>
		<li>按住<code>ctrl</code>键可多选，多选后松开<code>ctrl</code>键移动为批量移动。 不松开<code>ctrl</code>键移动为批量复制。</li>
    <li>按住<code>shift</code>键可以批量选择。</li>
		<li>在部门、机构、人员上点鼠标右键可出现右键菜单。菜单中有对应的更多操作。</li>
	</ul>
	<div class="alert alert-warning">
		<h4><strong>删除用户说明：</strong></h4>
		<ul class="mb-0">
			<li>所有机构、部门中删除用户，只是从本机构，或部门中移除，用户将不能再拥有本机构或部门的所有使用权限，不是将用户从系统中删除。</li>
			<li>当用户没有所属机构和部门时会出现在“未加入机构用户列表”中。 “未加入机构用户列表”只有系统管理员可管理。</li>
			<li style="color:red">系统管理员在“未加入机构用户列表”中删除用户，用户会在系统中彻底删除，并且删除用户所有系统数据及保存文件。请管理员谨慎使用，确定成员要删除后再删除。</li>
		</ul>
	</div>',
    'no_institution_users' => '无机构用户',
    'usergroup' => '用户组',
    'save_changes' => '保存更改',
    'department' => '部门',
    'organization' => '机构',
    'compellation' => '用户名',
);
?>