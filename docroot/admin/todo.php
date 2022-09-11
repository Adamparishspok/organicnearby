<?php

namespace sb;

class PageAdminTodo extends \sb\IncludePage
	{

	function init()
		{
		return true;
		}

	function finit()
		{
		return true;
		}

	function page_prompt()
		{
		$dis = &$this->dis['Prompt'];
		$dis['show']='';
		return true;
		}

	}
