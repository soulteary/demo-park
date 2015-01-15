<?php
/**
 * @name: demo playground
 * @file: index.php
 * @desc: main file for project
 * @author: [苏洋](soulteary@qq.com)
 */
// load config.
include('config.inc.php');
date_default_timezone_set('Asia/Hong_Kong');
?><!doctype html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title><?= SiteTitle; ?></title>
    <script type="text/javascript">
        var Y = {"version":"20140506"};
    </script>
    <style type="text/css">
        body {
            font: normal 11px auto "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
            color: #4f6b72;
            background: #E6EAE9;
            min-width: 700px;
            margin: 0;
            padding-bottom: 100px;
            width: 100%;
            height: 100%;
        }
        a {
            color: #c75f3e;
        }

        a:hover {
            color: #FF916F;
        }
        p {
            margin: 0;
        }
        table {
            position: relative;
            margin-top: 100px;
            margin-left: 50%;
            width: 700px;
            left: -350px;
            padding: 0;
            border-left: 1px solid #c1dad7;
        }
        caption {
            padding: 0 0 5px 0;
            width: 700px;
            text-align: right;
            font-style: italic;
        }
        th {
            font-weight: bold;
            color: #4f6b72;
            border: 1px solid #c1dad7;
            border-left: none;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-align: left;
            padding: 6px 6px 6px 12px;
            background-color: #cae8ea;
        }
        th:hover {
            background-color: #bfe0e3;
        }
        tr {
            background: #fff;
            color: #4f6b72;
        }
        tr:hover {
            background: #ededed;
            color: #659BA8;
        }
        td {
            border-right: 1px solid #c1dad7;
            border-bottom: 1px solid #c1dad7;
            padding: 6px 6px 6px 12px;
        }
        .update-time {
            padding: 5px 0 0;
            text-align: left;
            color: #CECECE;
        }
        .col-id {
            padding-left: 6px;
            text-align: center;
        }
        .col-opt {
            padding-left: 6px;
            text-align: center;
        }
        .c-name, .c-desc {
            width: 220px;
        }
        .text-overflow {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }
    </style>
</head>
<body>
    <script id="tabTpl" type="text/html">
        <table cellspacing="0">
        <caption><?=ListDesc?></caption>
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
    <?php
    function array_sort($arr, $keys, $type = 'asc', $noIndex = false)
    {
        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }
        if ($type == 'asc') {
            asort($keysvalue);
        } else {
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            if ($noIndex) {
                $new_array[] = $arr[$k];
            } else {
                $new_array[$k] = $arr[$k];
            }
        }
        return $new_array;
    }

    if (!empty($_REQUEST['do'])) {
        switch ($_REQUEST['do']) {
            case 'update':
                if(!empty($_REQUEST['token']) && ($_REQUEST['token'] == token)){
                    // only generate current month index data.
                    $curYear = date("Y");
                    $curMonth = date("n");
                    for ($year = sinceYear; $year <= $curYear; $year++) {
                        for ($sinceMonth = 1; $sinceMonth <= 12; $sinceMonth++) {
                            $directory = CaseDir . "/" . $year . "/" . $sinceMonth . "/";
                            if (!file_exists($directory)) {
                                if (!mkdir($directory, 0, true)) {
                                    die("something goes error.");
                                }
                            }
                            //scan current month project json file
                            //sort the project by date
                            if ($handle = opendir($directory)) {
                                $i = 1;
                                while (false !== ($file = readdir($handle))) {
                                    if ($file != "." && $file != "..") {
                                        $data = json_decode(file_get_contents($directory . $file . '/demo.json'));
                                        $object["name"] = $data->name;
                                        $object["date"] = $data->date;
                                        $object["desc"] = $data->desc;
                                        $object["path"] = $directory.$file;
                                        $object["id"] = strtotime($data->date); //convert date to number
                                        $Y[] = $object;
                                    }
                                }
                                closedir($handle);

                                //sort the array
                                if(isset($Y) && is_array($Y)){
                                    $Y = array_sort($Y, "id", "desc", true);
                                }
                            }
                        }
                    }

                    if(isset($Y) && is_array($Y)){
                        foreach ($Y as $k => $v) {
                            unset ($Y[$k]["id"]);
                        }
                        $data = json_encode($Y);
                        $file = DirRootPath . ProjectInfoFile;
                        if (file_exists($file)) {
                            @unlink($file);
                        }
                        file_put_contents($file, $data);
                    }
                }
                ?>
        <script type="text/javascript">document.location.href="/"</script>
                <?php
                break;
        }
    } else {
        ?>
<script type="text/javascript">Y.data = <?php include(DirRootPath . ProjectInfoFile);?>;Y.host="<?=UriRootPath;?>";Y.lastModified = "<?=date("Y/m/d",filemtime(DirRootPath . ProjectInfoFile));?>";</script>
        <?php
    }
    ?>

    <script type="text/javascript">
!function(e){"use strict";var n=function(e,r){return n["string"==typeof r?"compile":"render"].apply(n,arguments)};n.version="2.0.4",n.openTag="<%",n.closeTag="%>",n.isEscape=!0,n.isCompress=!1,n.parser=null,n.render=function(e,r){var t=n.get(e)||a({id:e,name:"Render Error",message:"No Template"});return t(r)},n.compile=function(e,t){function o(r){try{return new l(r,e)+""}catch(i){return s?a(i)():n.compile(e,t,!0)(r)}}var c=arguments,s=c[2],u="anonymous";"string"!=typeof t&&(s=c[1],t=c[0],e=u);try{var l=i(e,t,s)}catch(p){return p.id=e||t,p.name="Syntax Error",a(p)}return o.prototype=l.prototype,o.toString=function(){return l.toString()},e!==u&&(r[e]=o),o};var r=n.cache={},t=n.helpers=function(){var e=function(n,r){return"string"!=typeof n&&(r=typeof n,"number"===r?n+="":n="function"===r?e(n.call(n)):""),n},r={"<":"&#60;",">":"&#62;",'"':"&#34;","'":"&#39;","&":"&#38;"},t=function(n){return e(n).replace(/&(?![\w#]+;)|[<>"']/g,function(e){return r[e]})},a=Array.isArray||function(e){return"[object Array]"==={}.toString.call(e)},i=function(e,n){if(a(e))for(var r=0,t=e.length;t>r;r++)n.call(e,e[r],r,e);else for(r in e)n.call(e,e[r],r)};return{$include:n.render,$string:e,$escape:t,$each:i}}();n.helper=function(e,n){t[e]=n},n.onerror=function(n){var r="Template Error\n\n";for(var t in n)r+="<"+t+">\n"+n[t]+"\n\n";e.console&&console.error(r)},n.get=function(t){var a;if(r.hasOwnProperty(t))a=r[t];else if("document"in e){var i=document.getElementById(t);if(i){var o=i.value||i.innerHTML;a=n.compile(t,o.replace(/^\s*|\s*$/g,""))}}return a};var a=function(e){return n.onerror(e),function(){return"{Template Error}"}},i=function(){var e=t.$each,r="break,case,catch,continue,debugger,default,delete,do,else,false,finally,for,function,if,in,instanceof,new,null,return,switch,this,throw,true,try,typeof,var,void,while,with,abstract,boolean,byte,char,class,const,double,enum,export,extends,final,float,goto,implements,import,int,interface,long,native,package,private,protected,public,short,static,super,synchronized,throws,transient,volatile,arguments,let,yield,undefined",a=/\/\*[\w\W]*?\*\/|\/\/[^\n]*\n|\/\/[^\n]*$|"(?:[^"\\]|\\[\w\W])*"|'(?:[^'\\]|\\[\w\W])*'|[\s\t\n]*\.[\s\t\n]*[$\w\.]+/g,i=/[^\w$]+/g,o=new RegExp(["\\b"+r.replace(/,/g,"\\b|\\b")+"\\b"].join("|"),"g"),c=/^\d[^,]*|,\d[^,]*/g,s=/^,+|,+$/g,u=function(e){return e.replace(a,"").replace(i,",").replace(o,"").replace(c,"").replace(s,"").split(/^$|,+/)};return function(r,a,i){function o(e){return m+=e.split(/\n/).length-1,n.isCompress&&(e=e.replace(/[\n\r\t\s]+/g," ").replace(/<!--.*?-->/g,"")),e&&(e=x[1]+p(e)+x[2]+"\n"),e}function c(e){var r=m;if($?e=$(e):i&&(e=e.replace(/\n/g,function(){return m++,"$line="+m+";"})),0===e.indexOf("=")){var a=!/^=[=#]/.test(e);if(e=e.replace(/^=[=#]?|[\s;]*$/g,""),a&&n.isEscape){var o=e.replace(/\s*\([^\)]+\)/,"");t.hasOwnProperty(o)||/^(include|print)$/.test(o)||(e="$escape("+e+")")}else e="$string("+e+")";e=x[1]+e+x[2]}return i&&(e="$line="+r+";"+e),s(e),e+"\n"}function s(n){n=u(n),e(n,function(e){e&&!v.hasOwnProperty(e)&&(l(e),v[e]=!0)})}function l(e){var n;"print"===e?n=k:"include"===e?(y.$include=t.$include,n=E):(n="$data."+e,t.hasOwnProperty(e)&&(y[e]=t[e],n=0===e.indexOf("$")?"$helpers."+e:n+"===undefined?$helpers."+e+":"+n)),w+=e+"="+n+","}function p(e){return"'"+e.replace(/('|\\)/g,"\\$1").replace(/\r/g,"\\r").replace(/\n/g,"\\n")+"'"}var f=n.openTag,d=n.closeTag,$=n.parser,g=a,h="",m=1,v={$data:1,$id:1,$helpers:1,$out:1,$line:1},y={},w="var $helpers=this,"+(i?"$line=0,":""),b="".trim,x=b?["$out='';","$out+=",";","$out"]:["$out=[];","$out.push(",");","$out.join('')"],T=b?"$out+=$text;return $text;":"$out.push($text);",k="function($text){"+T+"}",E="function(id,data){data=data||$data;var $text=$helpers.$include(id,data,$id);"+T+"}";e(g.split(f),function(e){e=e.split(d);var n=e[0],r=e[1];1===e.length?h+=o(n):(h+=c(n),r&&(h+=o(r)))}),g=h,i&&(g="try{"+g+"}catch(e){"+"throw {"+"id:$id,"+"name:'Render Error',"+"message:e.message,"+"line:$line,"+"source:"+p(a)+".split(/\\n/)[$line-1].replace(/^[\\s\\t]+/,'')"+"};"+"}"),g=w+x[0]+g+"return new String("+x[3]+");";try{var j=new Function("$data","$id",g);return j.prototype=y,j}catch(O){throw O.temp="function anonymous($data,$id) {"+g+"}",O}}}();"function"==typeof define?define(function(){return n}):"undefined"!=typeof exports&&(module.exports=n),e.template=n}(this),function(e){e.openTag="{{",e.closeTag="}}",e.parser=function(n){n=n.replace(/^\s/,"");var r=n.split(" "),t=r.shift(),a=r.join(" ");switch(t){case"if":n="if("+a+"){";break;case"else":r="if"===r.shift()?" if("+r.join(" ")+")":"",n="}else"+r+"{";break;case"/if":n="}";break;case"each":var i=r[0]||"$data",o=r[1]||"as",c=r[2]||"$value",s=r[3]||"$index",u=c+","+s;"as"!==o&&(i="[]"),n="$each("+i+",function("+u+"){";break;case"/each":n="});";break;case"echo":n="print("+a+");";break;case"include":n="include("+r.join(",")+");";break;default:e.helpers.hasOwnProperty(t)?n="=#"+t+"("+r.join(",")+");":(n=n.replace(/[\s;]*$/,""),n="="+n)}return n}}(this.template);
Y && Y.data ? document.getElementsByTagName('body')[0].innerHTML = template.render
('tabTpl', {"data":Y}):void 0;</script>
</body>
</html>