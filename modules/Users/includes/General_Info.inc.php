<?php
echo '<TABLE class="width-100p cellspacing-0 cellpadding-6">';
echo '<TR class="st"><TD style="max-width:150px;" class="valign-top">';
// IMAGE
if (AllowEdit() && !isset($_REQUEST['_ROSARIO_PDF'])):
?>
	<script> 
	//toggle form & photo
	$('#aFormUserPhoto').click(function () {
		$('#formUserPhoto').toggle();
		$('#userImg').toggle();
		return false;
	});
	$('form[name="staff"]').submit(function () {
		if ($('#photo').val())
			$('#loading').show();
	});
	</script> 
	<a href="#" id="aFormUserPhoto"><img src="assets/plus.gif" height="9" />&nbsp;<?php echo _('User Photo'); ?></a><br />
	<div id="formUserPhoto" style="display:none;">
		<br />
		<input type="file" id="photo" name="photo" accept="image/*" /><img src="assets/spinning.gif" alt="Spinner" id="loading" style="display:none;" />
		<BR /><span class="legend-gray"><?php echo _('User Photo'); ?> (.jpg)</span>
	</div>
<?php endif;

if ($_REQUEST['staff_id']!='new' && ($file = @fopen($picture_path=$UserPicturesPath.UserSyear().'/'.UserStaffID().'.jpg','r')) || ($file = @fopen($picture_path=$UserPicturesPath.(UserSyear()-1).'/'.UserStaffID().'.jpg','r'))):
	fclose($file);
?>
	<IMG SRC="<?php echo $picture_path.(!empty($new_photo_file)? '?cacheKiller='.rand():''); ?>" width="150" id="userImg" />
<?php endif;
// END IMAGE

echo '</TD><TD class="valign-top">';

echo '<TABLE class="width-100p cellpadding-5"><TR class="st">';

echo '<TD>';

//modif Francois: add translation
$titles_array = array('Mr'=>_('Mr'),'Mrs'=>_('Mrs'),'Ms'=>_('Ms'),'Miss'=>_('Miss'),'Dr'=>_('Dr'));
$suffixes_array = array('Jr'=>_('Jr'),'Sr'=>_('Sr'),'II'=>_('II'),'III'=>_('III'),'IV'=>_('IV'),'V'=>_('V'));

if(AllowEdit() && !isset($_REQUEST['_ROSARIO_PDF']))
{
	if($_REQUEST['staff_id']=='new' || Preferences('HIDDEN')!='Y' || $_REQUEST['moodle_create_user'])
//modif Francois: last & first name required
		echo '<TABLE><TR class="st"><TD>'.SelectInput($staff['TITLE'],'staff[TITLE]',_('Title'),$titles_array,'').'</TD><TD>'.TextInput($staff['FIRST_NAME'],'staff[FIRST_NAME]',($staff['FIRST_NAME']==''?'<span class="legend-red">':'')._('First Name').($staff['FIRST_NAME']==''?'</span>':''),'maxlength=50 required', ($_REQUEST['moodle_create_user'] ? false : true)).'</TD><TD>'.TextInput($staff['MIDDLE_NAME'],'staff[MIDDLE_NAME]',_('Middle Name'),'maxlength=50').'</TD><TD>'.TextInput($staff['LAST_NAME'],'staff[LAST_NAME]',($staff['LAST_NAME']==''?'<span class="legend-red">':'')._('Last Name').($staff['LAST_NAME']==''?'</span>':''),'maxlength=50 required', ($_REQUEST['moodle_create_user'] ? false : true)).'</TD><TD>'.SelectInput($staff['NAME_SUFFIX'],'staff[NAME_SUFFIX]',_('Suffix'),$suffixes_array,'').'</TD></TR></TABLE>';
	else
	{
		echo '<DIV id="user_name"><div class="onclick" onclick=\'addHTML("';
		
		$toEscape = '<TABLE><TR class="st"><TD>'.SelectInput(str_replace("'",'&#39;',$staff['TITLE']),'staff[TITLE]',_('Title'),$titles_array,'','',false).'</TD><TD>'.TextInput(str_replace("'",'&#39;',$staff['FIRST_NAME']),'staff[FIRST_NAME]',_('First Name'),'maxlength=50 required',false).'</TD><TD>'.TextInput(str_replace("'",'&#39;',$staff['MIDDLE_NAME']),'staff[MIDDLE_NAME]',_('Middle Name'),'maxlength=50',false).'</TD><TD>'.TextInput(str_replace("'",'&#39;',$staff['LAST_NAME']),'staff[LAST_NAME]',_('Last Name'),'maxlength=50 required',false).'</TD><TD>'.SelectInput(str_replace("'",'&#39;',$staff['NAME_SUFFIX']),'staff[NAME_SUFFIX]',_('Suffix'),$suffixes_array,'','',false).'</TD></TR></TABLE>';
		echo str_replace('"','\"',$toEscape);

		echo '","user_name",true);\'><span class="underline-dots">'.$titles_array[$staff['TITLE']].' '.$staff['FIRST_NAME'].' '.$staff['MIDDLE_NAME'].' '.$staff['LAST_NAME'].' '.$suffixes_array[$staff['NAME_SUFFIX']].'</span></div></DIV><span class="legend-gray">'._('Name').'</span>';
	}
}
else
	echo ($staff['TITLE']!=''||$staff['FIRST_NAME']!=''||$staff['MIDDLE_NAME']!=''||$staff['LAST_NAME']!=''||$staff['NAME_SUFFIX']!=''?$titles_array[$staff['TITLE']].' '.$staff['FIRST_NAME'].' '.$staff['MIDDLE_NAME'].' '.$staff['LAST_NAME'].' '.$suffixes_array[$staff['NAME_SUFFIX']]:'-').'<BR /><span class="legend-gray">'._('Name').'</span>';
echo '</TD>';

echo '<TD colspan="1">';
echo NoInput($staff['STAFF_ID'],sprintf(_('%s ID'),Config('NAME')));
echo '</TD>';

echo '<TD colspan="1">';
echo NoInput($staff['ROLLOVER_ID'],sprintf(_('Last Year %s ID'),Config('NAME')));
echo '</TD>';

echo '</TR><TR class="st">';

//modif Francois: Moodle integrator
//username, password required
echo '<TD>';
//echo TextInput($staff['USERNAME'],'staff[USERNAME]',_('Username'),'size=12 maxlength=100');
if (AllowEdit())
	echo TextInput($staff['USERNAME'],'staff[USERNAME]',($_REQUEST['moodle_create_user'] && !$staff['USERNAME']?'<span class="legend-red">':'')._('Username').($_REQUEST['moodle_create_user'] && !$staff['USERNAME']?'</span>':''),'size=12 maxlength=100 '.($_REQUEST['moodle_create_user'] || $old_user_in_moodle ? 'required' : ''));
else
	echo TextInput($staff['USERNAME'],'staff[USERNAME]',_('Username'),'size=12 maxlength=100 ');
	
echo '</TD>';

echo '<TD>';
//echo TextInput($staff['PASSWORD'],'staff[PASSWORD]','Password','size=12 maxlength=100');
//modif Francois: add password encryption
//echo TextInput((!$staff['PASSWORD']?'':str_repeat('*',8)),'staff[PASSWORD]',($staff['USERNAME']&&!$staff['PASSWORD']?'<span style="color:red">':'')._('Password').($staff['USERNAME']&&!$staff['PASSWORD']?'</span>':''),'size=12 maxlength=42');
//modif Francois: Moodle integrator / password
if (AllowEdit())
	echo TextInput((!$staff['PASSWORD'] || $_REQUEST['moodle_create_user']?'':str_repeat('*',8)),'staff[PASSWORD]',($_REQUEST['moodle_create_user']?'<span class="legend-red">':'<span class="legend-gray">').($_REQUEST['moodle_create_user'] || $old_user_in_moodle?'<SPAN style="cursor:help" title="'._('The password must have at least 8 characters, at least 1 digit, at least 1 lower case letter, at least 1 upper case letter, at least 1 non-alphanumeric character').'">':'')._('Password').($_REQUEST['moodle_create_user'] || $old_user_in_moodle?'*</SPAN>':'').'</span>','size=12 maxlength=42 autocomplete=off'.($_REQUEST['moodle_create_user'] || $old_user_in_moodle ? ' required' : ''), ($_REQUEST['moodle_create_user'] ? false : true));
else
	echo TextInput((!$staff['PASSWORD']?'':str_repeat('*',8)),'staff[PASSWORD]',_('Password'),'size=12 maxlength=42');

echo '</TD>';

echo '<TD>';
echo NoInput(makeLogin($staff['LAST_LOGIN']),_('Last Login'));
echo '</TD>';

echo '</TR></TABLE>';
echo '</TD></TR></TABLE>';

echo '<HR>';

echo '<TABLE class="width-100p cellpadding-6">';
if(basename($_SERVER['PHP_SELF'])!='index.php')
{
	echo '<TR class="st">';

	echo '<TD>';
	echo '<TABLE><TR><TD>';
	unset($options);
	$options = array('admin'=>_('Administrator'),'teacher'=>_('Teacher'),'parent'=>_('Parent'),'none'=>_('No Access'));
	echo SelectInput($staff['PROFILE'],'staff[PROFILE]',(!$staff['PROFILE']?'<span style="color:red">':'')._('User Profile').(!$staff['PROFILE']?'</span>':''),$options,false,'',($_REQUEST['moodle_create_user'] ?false:true));

	echo '</TD></TR><TR><TD>';

	unset($profiles);
	if($_REQUEST['staff_id']!='new')
	{
		$profiles_RET = DBGet(DBQuery("SELECT ID,TITLE FROM USER_PROFILES WHERE PROFILE='".$staff['PROFILE']."' ORDER BY ID"));
		foreach($profiles_RET as $profile)
//modif Francois: add translation
			$profiles[$profile['ID']] = _($profile['TITLE']);
		$na = _('Custom');
	}
	else
		$na = _('Default');
	echo SelectInput($staff['PROFILE_ID'],'staff[PROFILE_ID]',_('Permissions'),$profiles,$na);
	echo '</TD></TR></TABLE>';
	echo '</TD>';

	echo '<TD>';
	$sql = "SELECT ID,TITLE FROM SCHOOLS WHERE SYEAR='".UserSyear()."'";
	$QI = DBQuery($sql);
	$schools_RET = DBGet($QI);
	unset($options);
	if(count($schools_RET))
	{
		$i = 0;
		echo '<TABLE><TR>';
		foreach($schools_RET as $value)
		{
			if($i%3==0)
				echo '</TR><TR>';
			echo '<TD>'.CheckboxInput(((mb_strpos($staff['SCHOOLS'],','.$value['ID'].',')!==false)?'Y':''),'staff[SCHOOLS]['.$value['ID'].']',$value['TITLE'],'',false,'<IMG SRC="assets/check_button.png" width="15">','<IMG SRC="assets/x_button.png" width="15">').'</TD>';
			$i++;
		}
		echo '</TR></TABLE>';
		echo '<span class="legend-gray">'._('Schools').'</span>';
	}
	//echo SelectInput($staff['SCHOOL_ID'],'staff[SCHOOL_ID]','School',$options,'All Schools');
	echo '</TD><TD>';
	echo '</TD>';
	echo '</TR>';
}
echo '<TR class="st">';
echo '<TD>';
//modif Francois: Moodle integrator
//email required
//echo TextInput($staff['EMAIL'],'staff[EMAIL]',_('Email Address'),'size=12 maxlength=100');
if (AllowEdit())
	echo TextInput($staff['EMAIL'],'staff[EMAIL]',($_REQUEST['moodle_create_user'] && !$staff['EMAIL']?'<span class="legend-red">':'')._('Email Address').($_REQUEST['moodle_create_user'] && !$staff['EMAIL']?'</span>':''),'size=12 maxlength=100'.($_REQUEST['moodle_create_user'] || $old_user_in_moodle ?' required':''));
else
	echo TextInput($staff['EMAIL'],'staff[EMAIL]',_('Email Address'),'size=12 maxlength=100');

echo '</TD>';
echo '<TD>';
echo TextInput($staff['PHONE'],'staff[PHONE]',_('Phone Number'),'size=12 maxlength=100');
echo '</TD>';
echo '</TR>';
echo '</TABLE>';

$_REQUEST['category_id'] = '1';
include('modules/Users/includes/Other_Info.inc.php');
?>
