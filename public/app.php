<?php
if (!defined('TOKEN')) exit;

/**
 * @name: demo playground
 * @file: app.php
 * @desc: main function file for project
 * @author: [soulteary](soulteary@qq.com)
 */

date_default_timezone_set('Asia/Hong_Kong');

/** 页脚显示的时间起点 **/
define("sinceYear", 2012);

/** 页面标题 **/
define("SiteTitle", "soulteary's code playground");
/** 页面描述 **/
define("SiteDesc", "soulteary's code snippet list.");

/** 项目数据缓存 **/
define("AppData", "demo.json");
/** 页面缓存 **/
define("PageCache", "page.cache");

/** 基础路径 **/
define("DirRootPath", dirname(__FILE__) . "/");
define("UriRootPath", "http://" . $_SERVER["HTTP_HOST"] . "/");

/** assets目录 **/
define("AssetsDir", "assets/");
define("JsDir", AssetsDir . "js/");
define("CssDir", AssetsDir . "css/");

/** 示例文件目录 **/
define("CaseDir", "cases");

/** 页面对于客户端默认缓存为0.5小时, 30*60s **/
define('CACHE_TIME', 1800);

/**
 * 获取当前时间戳
 *
 * @param $t
 *
 * @return float
 */
function getmicrotime($t)
{
    list($usec, $sec) = explode(" ", $t);

    return ((float)$usec + (float)$sec);
}

/**
 * 获取运行时间戳
 * @return string
 */
function timestamp()
{
    global $start;

    $end = microtime();
    $t = (getmicrotime($end) - getmicrotime($start));

    return round($t, 3) . 's';
}

/**
 * 数组排序
 * @param $arr
 * @param $keys
 * @param string $type
 * @param bool $noIndex
 * @return array
 */
function arraySort($arr, $keys, $type = 'asc', $noIndex = false)
{
    $value = $newArr = array();
    foreach ($arr as $k => $v) {
        $value[$k] = $v[$keys];
    }
    if ($type == 'asc') {
        asort($value);
    } else {
        arsort($value);
    }
    reset($value);
    foreach ($value as $k => $v) {
        if ($noIndex) {
            $newArr[] = $arr[$k];
        } else {
            $newArr[$k] = $arr[$k];
        }
    }
    return $newArr;
}


function buildCache()
{

    ob_start();

    $title = SiteTitle;
    $desc = SiteDesc;

    $css = file_get_contents(DirRootPath . CssDir . THEME . ".css");
    $jsData = file_get_contents(DirRootPath . AppData);
    $jsApp = file_get_contents(DirRootPath . JsDir . "app.js");
    $UriRootPath = UriRootPath;
    $lastModified = date("Y/m/d", filemtime(DirRootPath . AppData));
    echo <<<EOF
<!doctype html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title>$title</title>
    <meta name="description" content="$desc" />
    <style type="text/css">$css</style>
</head>
<body>
    <script id="tabTpl" type="text/html">
        <table cellspacing="0">
        <caption>$desc</caption>
        <thead>
            <tr>
                <th abbr="ID" class="col-id">#</th>
                <th abbr="Date">Date</th>
                <th abbr="Name">Name</th>
                <th abbr="Desc">Desc</th>
                <th abbr="Url" class="col-opt">Url</th>
            </tr>
        </thead>
        <tbody>
        {{each data.data as item index}}
        <tr>
            <td class="col-id">{{index+1}}</td>
            <td>{{item.date}}</td>
            <td><p class="c-name text-overflow">{{item.name}}</p></td>
            <td><p class="c-desc text-overflow">{{item.desc}}</p></td>
            <td class="col-opt"><a href="{{data.host}}{{item.path}}" title="View: {{item.desc}}" target="_blank">View</a></td>
        </tr>
        {{/each}}
        </tbody>
        <caption align="bottom" class="update-time">Last Modified:{{data.lastModified}}</caption>
        </table>
    </script>
    <script type="text/javascript">var Y = {"version":"20140506","data":$jsData,"host":"$UriRootPath","lastModified":"$lastModified"};$jsApp</script>
</body>
</html>
EOF;

    $html = ob_get_contents();
    ob_end_clean();
    $html = preg_replace(array('/>\s+</Um', '/>(\s+\n|\r)/'), array('><', '>'), $html);

    header('Page-Cache: Cached, cost ' . timestamp() . '.');

    file_put_contents(PageCache, $html);

    header('Location: ' . $UriRootPath);
}


/**
 * 行为钩子
 */
function actionHook()
{
    if (!empty($_REQUEST['do'])) {
        switch ($_REQUEST['do']) {
            case 'update':
                if (!empty($_REQUEST['token']) && ($_REQUEST['token'] == TOKEN)) {
                    $curYear = date("Y");
                    for ($year = sinceYear; $year <= $curYear; $year++) {
                        for ($sinceMonth = 1; $sinceMonth <= 12; $sinceMonth++) {
                            $directory = CaseDir . "/" . $year . "/" . $sinceMonth . "/";
                            if (!file_exists($directory)) {
                                if (!mkdir($directory, 0, true)) {
                                    die("something goes error.");
                                }
                            }
                            //sort the project by date
                            if ($handle = opendir($directory)) {
                                while (false !== ($file = readdir($handle))) {
                                    if ($file != "." && $file != "..") {
                                        $data = json_decode(file_get_contents($directory . $file . '/demo.json'));
                                        $object["name"] = $data->name;
                                        $object["date"] = $data->date;
                                        $object["desc"] = $data->desc;
                                        $object["path"] = $directory . $file;
                                        $object["id"] = strtotime($data->date); //convert date to number
                                        $Y[] = $object;
                                    }
                                }
                                closedir($handle);
                                //sort the array
                                if (isset($Y) && is_array($Y)) {
                                    $Y = arraySort($Y, "id", "desc", true);
                                }
                            }
                        }
                    }

                    if (isset($Y) && is_array($Y)) {
                        foreach ($Y as $k => $v) {
                            unset ($Y[$k]["id"]);
                        }
                        $data = json_encode($Y);
                        $file = DirRootPath . AppData;
                        if (file_exists($file)) {
                            @unlink($file);
                        }
                        file_put_contents($file, $data);
                    }
                    // update cache
                    buildCache();
                } else {
                    header('Location: ' . UriRootPath . '?token-error');
                    exit;
                }
                break;
        }
    } else {

        $html = file_get_contents(PageCache);
        if (CACHE_TIME) {

            header('Age: ' . CACHE_TIME);
            header('Cache-Control: max-age=' . CACHE_TIME);
            $now = time();
            $expire = gmdate('D, d M Y H:i:s', $now + CACHE_TIME) . ' GMT';
            header('Date: ' . gmdate('D, d M Y H:i:s', $now) . ' GMT');
            header('Expires: ' . $expire);

            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {

                if (time() > strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                    header('Last-Modified: ' . $expire);
                    header('Content-Length: ' . strlen($html));
                } else {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
                    header('Last-Modified: ' . $_SERVER['HTTP_IF_MODIFIED_SINCE']);
                    die;
                }
            } else {
                header('Content-Length: ' . strlen($html));
                header('Last-Modified: ' . $expire);

            }

        }
        header('Page-Cache: Cached, cost ' . timestamp() . '.');
        echo $html;
    }
}

actionHook();