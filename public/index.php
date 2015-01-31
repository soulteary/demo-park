<?php
/**
 * @name: demo playground
 * @file: index.php
 * @desc: main file for project
 * @author: [soulteary](soulteary@qq.com)
 */

/** 脚本开始时间 **/
$start = microtime();

/** 请先更新TOKEN **/
define("TOKEN", '');

/** 请选择皮肤 [dark, light]**/
define("THEME", 'dark');

include('app.php');
