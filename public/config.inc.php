<?php
/**
 * @name: demo playground
 * @file: config.inc.php
 * @desc: config file for project
 * @author: [soulteary](soulteary@qq.com)
 */

/** 页面标题 **/
define("SiteTitle", "soulteary's code playground");
/** 页面描述 **/
define("ListDesc", "soulteary's code snippet list.");
/** 示例项目信息文件 **/
define("ProjectInfoFile","demo.json");

define("DirRootPath", dirname(__FILE__) . "/" );
define("CaseDir", "cases");
define("UriRootPath", "http://" . $_SERVER["HTTP_HOST"] . "/");

/** 更新索引需要的token **/
define("token", '');
define("sinceYear", 2012);
