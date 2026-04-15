<?php
namespace app\captcha\controller;
use think\Session;
use \think\Request;
class Captcha
{
    public function index()
    {
		$captcha = new \think\captcha\Captcha();
		$img=$captcha->entry();
		return $img;
    }
}

