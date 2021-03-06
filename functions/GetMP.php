<?php

function GetMP($mp,$column='TITLE')
{	global $_ROSARIO;

	// mab - need to translate marking_period_id to title to be useful as a function call from dbget
	// also, it doesn't make sense to ask for same thing you give
	if($column=='MARKING_PERIOD_ID')
		$column='TITLE';

	if(!isset($_ROSARIO['GetMP']))
	{
		$_ROSARIO['GetMP'] = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,TITLE,POST_START_DATE,POST_END_DATE,MP,SORT_ORDER,SHORT_NAME,START_DATE,END_DATE,DOES_GRADES,DOES_COMMENTS 
		FROM SCHOOL_MARKING_PERIODS 
		WHERE SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"),array(),array('MARKING_PERIOD_ID'));
	}
	$suffix = '';

	if($mp==0 && $column=='TITLE')
		return _('Full Year').$suffix;
	else
		return $_ROSARIO['GetMP'][$mp][1][$column].$suffix;
}

function GetAllMP($mp,$marking_period_id='0')
{	global $_ROSARIO;

	if($marking_period_id==0)
	{
		// there should be exactly one fy marking period
		$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID FROM SCHOOL_MARKING_PERIODS WHERE MP='FY' AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"));
		$marking_period_id = $RET[1]['MARKING_PERIOD_ID'];
		$mp = 'FY';
	}
	elseif(!$mp)
		$mp = GetMP($marking_period_id,'MP');

	if(!isset($_ROSARIO['GetAllMP'][$mp]))
	{
		switch($mp)
		{
			case 'PRO':
				// there should be exactly one fy marking period
				$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID FROM SCHOOL_MARKING_PERIODS WHERE MP='FY' AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"));
				$fy = $RET[1]['MARKING_PERIOD_ID'];

				$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,PARENT_ID FROM SCHOOL_MARKING_PERIODS WHERE MP='QTR' AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"));
				
				//modif Francois: error if no quarters
				if (!$RET)
					return ErrorMessage(array(_('No quarters found')), 'fatal');
					
				foreach($RET as $value)
				{
					$_ROSARIO['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = "'".$fy."','".$value['PARENT_ID']."','".$value['MARKING_PERIOD_ID']."'";
					$_ROSARIO['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] .= ','.GetChildrenMP($mp,$value['MARKING_PERIOD_ID']);
					if(mb_substr($_ROSARIO['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']],-1)==',')
						$_ROSARIO['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = mb_substr($_ROSARIO['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']],0,-1);
				}
			break;

			case 'QTR':
				// there should be exactly one fy marking period
				$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID FROM SCHOOL_MARKING_PERIODS WHERE MP='FY' AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"));
				$fy = $RET[1]['MARKING_PERIOD_ID'];

				$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,PARENT_ID FROM SCHOOL_MARKING_PERIODS WHERE MP='QTR' AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"));
				
				//modif Francois: error if no quarters
				if (!$RET)
					return ErrorMessage(array(_('No quarters found')), 'fatal');
					
				foreach($RET as $value)
					$_ROSARIO['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = "'".$fy."','".$value['PARENT_ID']."','".$value['MARKING_PERIOD_ID']."'";
			break;

			case 'SEM':
				// there should be exactly one fy marking period
				$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID FROM SCHOOL_MARKING_PERIODS WHERE MP='FY' AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"));
				$fy = $RET[1]['MARKING_PERIOD_ID'];

				$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,PARENT_ID FROM SCHOOL_MARKING_PERIODS WHERE MP='QTR' AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"),array(),array('PARENT_ID'));
				
				//modif Francois: error if no quarters
				if (!$RET)
					return ErrorMessage(array(_('No quarters found')), 'fatal');
					
				foreach($RET as $sem=>$value)
				{
					$_ROSARIO['GetAllMP'][$mp][$sem] = "'".$fy."','".$sem."'";
					foreach($value as $qtr)
						$_ROSARIO['GetAllMP'][$mp][$sem] .= ",'".$qtr['MARKING_PERIOD_ID']."'";
				}
				$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID FROM SCHOOL_MARKING_PERIODS s WHERE MP='SEM' AND NOT EXISTS (SELECT '' FROM SCHOOL_MARKING_PERIODS q WHERE q.MP='QTR' AND q.PARENT_ID=s.MARKING_PERIOD_ID) AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"));
				foreach($RET as $value)
					$_ROSARIO['GetAllMP'][$mp][$value['MARKING_PERIOD_ID']] = "'".$fy."','".$value['MARKING_PERIOD_ID']."'";
			break;

			case 'FY':
				// there should be exactly one fy marking period which better be $marking_period_id
				$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,PARENT_ID FROM SCHOOL_MARKING_PERIODS WHERE MP='QTR' AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"),array(),array('PARENT_ID'));
				$_ROSARIO['GetAllMP'][$mp][$marking_period_id] = "'".$marking_period_id."'";
				
				//modif Francois: error if no quarters
				if (!$RET)
					return ErrorMessage(array(_('No quarters found')), 'fatal');
					
				foreach($RET as $sem=>$value)
				{
					$_ROSARIO['GetAllMP'][$mp][$marking_period_id] .= ",'".$sem."'";
					foreach($value as $qtr)
						$_ROSARIO['GetAllMP'][$mp][$marking_period_id] .= ",'".$qtr['MARKING_PERIOD_ID']."'";
				}
				$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID 
				FROM SCHOOL_MARKING_PERIODS s 
				WHERE MP='SEM' AND NOT EXISTS (SELECT '' FROM SCHOOL_MARKING_PERIODS q WHERE q.MP='QTR' AND q.PARENT_ID=s.MARKING_PERIOD_ID) 
				AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"));
				foreach($RET as $value)
					$_ROSARIO['GetAllMP'][$mp][$marking_period_id] .= ",'".$value['MARKING_PERIOD_ID']."'";
			break;
		}
	}

	return $_ROSARIO['GetAllMP'][$mp][$marking_period_id];
}

function GetParentMP($mp,$marking_period_id='0')
{	global $_ROSARIO;

	if(!isset($_ROSARIO['GetParentMP'][$mp]))
	{
		switch($mp)
		{
			case 'QTR':

			break;

			case 'SEM':
				$_ROSARIO['GetParentMP'][$mp] = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,PARENT_ID FROM SCHOOL_MARKING_PERIODS WHERE MP='QTR' AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"),array(),array('MARKING_PERIOD_ID'));
			break;

			case 'FY':
				$_ROSARIO['GetParentMP'][$mp] = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,PARENT_ID FROM SCHOOL_MARKING_PERIODS WHERE MP='SEM' AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"),array(),array('MARKING_PERIOD_ID'));
			break;
		}
	}

	return $_ROSARIO['GetParentMP'][$mp][$marking_period_id][1]['PARENT_ID'];
}

function GetChildrenMP($mp,$marking_period_id='0')
{	global $_ROSARIO;

	switch($mp)
	{
		case 'FY':
			if(!isset($_ROSARIO['GetChildrenMP']['FY']))
			{
				$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,PARENT_ID FROM SCHOOL_MARKING_PERIODS WHERE MP='QTR' AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"),array(),array('PARENT_ID'));
				foreach($RET as $sem=>$value)
				{
					$_ROSARIO['GetChildrenMP'][$mp]['0'] .= ",'".$sem."'";
					foreach($value as $qtr)
						$_ROSARIO['GetChildrenMP'][$mp]['0'] .= ",'".$qtr['MARKING_PERIOD_ID']."'";
				}
				$_ROSARIO['GetChildrenMP'][$mp]['0'] = mb_substr($_ROSARIO['GetChildrenMP'][$mp]['0'],1);
			}
			return $_ROSARIO['GetChildrenMP'][$mp]['0'];
		break;

		case 'SEM':
			if(GetMP($marking_period_id,'MP')=='QTR')
				$marking_period_id = GetParentMP('SEM',$marking_period_id);
			if(!$_ROSARIO['GetChildrenMP']['SEM'])
			{
				$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,PARENT_ID FROM SCHOOL_MARKING_PERIODS WHERE MP='QTR' AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"),array(),array('PARENT_ID'));
				foreach($RET as $sem=>$value)
				{
					foreach($value as $qtr)
						$_ROSARIO['GetChildrenMP'][$mp][$sem] .= ",'".$qtr['MARKING_PERIOD_ID']."'";
					$_ROSARIO['GetChildrenMP'][$mp][$sem] = mb_substr($_ROSARIO['GetChildrenMP'][$mp][$sem],1);
				}
			}
			return $_ROSARIO['GetChildrenMP'][$mp][$marking_period_id];
		break;

		case 'QTR':
			return "'".$marking_period_id."'";
		break;

		case 'PRO':
			if(!isset($_ROSARIO['GetChildrenMP']['PRO']))
			{
				$RET = DBGet(DBQuery("SELECT MARKING_PERIOD_ID,PARENT_ID FROM SCHOOL_MARKING_PERIODS WHERE MP='PRO' AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"),array(),array('PARENT_ID'));
				foreach($RET as $qtr=>$value)
				{
					foreach($value as $pro)
						$_ROSARIO['GetChildrenMP'][$mp][$qtr] .= ",'".$pro['MARKING_PERIOD_ID']."'";
					$_ROSARIO['GetChildrenMP'][$mp][$qtr] = mb_substr($_ROSARIO['GetChildrenMP'][$mp][$qtr],1);
				}
			}
			return $_ROSARIO['GetChildrenMP'][$mp][$marking_period_id];
		break;
	}
}


function GetCurrentMP($mp,$date,$error=true)
{	global $_ROSARIO;

	if(!isset($_ROSARIO['GetCurrentMP'][$date][$mp]))
	 	$_ROSARIO['GetCurrentMP'][$date][$mp] = DBGet(DBQuery("SELECT MARKING_PERIOD_ID FROM SCHOOL_MARKING_PERIODS WHERE MP='".$mp."' AND '".$date."' BETWEEN START_DATE AND END_DATE AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."'"));

	if(isset($_ROSARIO['GetCurrentMP'][$date][$mp][1]['MARKING_PERIOD_ID']))
		return $_ROSARIO['GetCurrentMP'][$date][$mp][1]['MARKING_PERIOD_ID'];
	elseif($error)
		ErrorMessage(array(_('You are not currently in a marking period')),'fatal');
}
?>
