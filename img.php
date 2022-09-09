<?php

namespace sb;

class PageImg extends \sb\IncludePage
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
		$u = parse_url($this->server['REQUEST_URI']);
		$u['path'] = str_replace('/img.php/', '', $u['path']);
		
		// check: are we using images table images or legacy links from ON loader? 
		$images_id = basename($u['path']);
		if (is_numeric($images_id))
			{
			$relpath = $u['path'];
			}
		elseif ($images_id != 'upload.png')
			{
			$relpath = str_replace('images/', 'sc_images/', $u['path']);
			}
		elseif ($images_id == 'upload.png')
			return $this->blankImg("upload image detected", "/images/upload.png");
		if (!$relpath)
			return $this->blankImg("No relative path!");
		if (!$context = $this->env->get('settings', 'dfscontext'))
			return $this->blankImg("Unable to get image storage context");
		$fpath = "/home/sb4/$context/$relpath";
		if (!$fpath = realpath($fpath))
			return $this->blankImg("Unable to get real path requested 1");
		$dir = dirname($fpath);
		$last = basename($dir);
		if (strpos('sc_images/', $dir))
			return $this->blankImg("Unable to get real path requested 2");
		$cname = strtolower($fpath);
		$contentTypes = [
			'.png' => 'image/png',
			'.jpg' => 'image/jpeg',
			'.jpeg' => 'image/jpeg',
			'.gif' => 'image/gif',
			];
		$contentType = '';
		foreach($contentTypes AS $ext => $type)
			{
			$fext = substr($fpath, strlen($fpath) - strlen($ext));
			if ($fext == $ext)
				$contentType = $type;
			}
		header("Content-Type: $contentType");
		header("Last-modified: ".gmdate("D, d M Y H:i:s", filemtime($fpath)));
		header("Content-length: ".filesize($fpath));
		header('Accept-Ranges: bytes');
		header_remove('expires');
		header_remove('pragma');
		header_remove('cache-control');
		$fp = fopen($fpath, 'r');
		while ($line = fgets($fp, 65535))
			echo $line;
		die();
		}

	function blankImg($msg, $relpath="/images/broken.png")
		{

		if ($file = $this->env->which($relpath))
			{
			header("Content-Type: image/jpeg");
			die(file_get_contents($file));
			}
		die($msg);
		}
	}
